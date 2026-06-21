<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\MicroTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Content Studio — Phase A1
 * Allows instructors to paste lecture notes and have Gemini AI
 * automatically generate Concept Cards, Quiz Questions, and a Key Takeaway.
 */
class AiContentController extends Controller
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
    }

    /**
     * Show the AI Content Studio page for a lesson.
     */
    public function studio(Lesson $lesson)
    {
        return view('instructor.ai.studio', compact('lesson'));
    }

    /**
     * Generate concept cards + quiz questions from raw lecture notes via Gemini AI.
     * Called via AJAX from the Studio page. Returns JSON.
     */
    public function generate(Request $request, Lesson $lesson)
    {
        $request->validate([
            'topic_name'  => 'required|string|max:255',
            'notes'       => 'required|string|min:50',
            'card_count'  => 'integer|min:2|max:8',
            'quiz_count'  => 'integer|min:2|max:10',
        ]);

        $topicName  = $request->topic_name;
        $notes      = $request->notes;
        $cardCount  = $request->card_count ?? 4;
        $quizCount  = $request->quiz_count ?? 4;

        // ── Fallback if no Gemini key ──────────────────────────────────────
        if (empty($this->apiKey) || $this->apiKey === 'your_gemini_api_key_here') {
            return response()->json([
                'status'  => 'fallback',
                'message' => 'Gemini API key not configured. Using sample output.',
                'data'    => $this->getFallbackContent($topicName),
            ]);
        }

        $prompt = <<<EOT
You are an expert NVQ Level 4 ICT curriculum designer for Sri Lanka.

A lecturer has provided the following lecture notes about the topic: "{$topicName}"

LECTURE NOTES:
{$notes}

Your task is to convert these notes into structured interactive learning content.
Respond ONLY with a valid JSON object (no markdown, no explanation) in this exact format:

{
  "topic_name": "{$topicName}",
  "estimated_minutes": <number 3-15>,
  "key_takeaway": "<one powerful sentence summarizing the most important thing to remember>",
  "concept_cards": [
    {
      "emoji": "<relevant emoji>",
      "title": "<short card title, max 5 words>",
      "body": "<clear explanation, 2-3 sentences, simple English>"
    }
  ],
  "quiz_questions": [
    {
      "question": "<clear MCQ question>",
      "options": ["<option A>", "<option B>", "<option C>", "<option D>"],
      "answer": "<exact text of the correct option>"
    }
  ]
}

Rules:
- Generate exactly {$cardCount} concept_cards
- Generate exactly {$quizCount} quiz_questions
- Each question must have exactly 4 options
- The answer must exactly match one of the 4 options
- Use simple language suitable for Sri Lankan NVQ Level 4 ICT students
- Make questions test understanding, not just memory
EOT;

        try {
            $response = Http::timeout(30)->post("{$this->apiUrl}?key={$this->apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 3000,
                    'temperature'     => 0.4,
                    'responseMimeType' => 'application/json',
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Gemini AI Content Generation error: ' . $response->body());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'AI service error. Please try again.',
                ], 500);
            }

            $raw  = $response->json();
            $text = $raw['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Strip markdown fences if present
            $text = preg_replace('/^```json\s*/i', '', trim($text));
            $text = preg_replace('/```$/', '', trim($text));

            $content = json_decode($text, true);

            if (!$content) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'AI returned invalid JSON. Please try again.',
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'data'   => $content,
            ]);

        } catch (\Exception $e) {
            Log::error('AI Content Studio error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save the AI-generated content as a MicroTopic (with Concept Cards).
     */
    public function save(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'topic_name'        => 'required|string|max:255',
            'estimated_minutes' => 'required|integer|min:1',
            'key_takeaway'      => 'nullable|string',
            'concept_cards'     => 'nullable|json',
            'quiz_questions'    => 'nullable|json',
        ]);

        $cards     = $validated['concept_cards']  ? json_decode($validated['concept_cards'], true)  : [];
        $questions = $validated['quiz_questions'] ? json_decode($validated['quiz_questions'], true) : [];

        // Create the MicroTopic with concept cards
        $topic = $lesson->microTopics()->create([
            'topic_name'        => $validated['topic_name'],
            'estimated_minutes' => $validated['estimated_minutes'],
            'key_takeaway'      => $validated['key_takeaway'],
            'concept_cards'     => $cards,
        ]);

        // Create quiz questions for this topic
        foreach ($questions as $q) {
            if (empty($q['question']) || empty($q['options']) || empty($q['answer'])) continue;

            $quizQuestion = \App\Models\MicroQuizQuestion::create([
                'micro_topic_id' => $topic->id,
                'question_text'  => $q['question'],
            ]);

            foreach ($q['options'] as $optionText) {
                \App\Models\MicroQuizOption::create([
                    'micro_quiz_question_id' => $quizQuestion->id,
                    'option_text'  => $optionText,
                    'is_correct'   => ($optionText === $q['answer']),
                ]);
            }
        }

        return redirect()
            ->route('instructor.lessons.micro-topics.index', $lesson)
            ->with('success', "✨ AI generated topic \"{$topic->topic_name}\" with " . count($cards) . " concept cards and " . count($questions) . " quiz questions saved successfully!");
    }

    /**
     * Fallback sample content when API key is not set.
     */
    private function getFallbackContent(string $topicName): array
    {
        return [
            'topic_name'        => $topicName,
            'estimated_minutes' => 5,
            'key_takeaway'      => "Understanding {$topicName} is fundamental to NVQ Level 4 ICT.",
            'concept_cards' => [
                ['emoji' => '💡', 'title' => 'Core Concept', 'body' => "This is where your AI-generated explanation of {$topicName} will appear. Configure your Gemini API key to enable this feature."],
                ['emoji' => '📚', 'title' => 'Key Points',   'body' => 'The AI will extract the most important points from your lecture notes automatically.'],
                ['emoji' => '🔍', 'title' => 'Remember This', 'body' => 'A memorable summary will be generated to help students retain the concept.'],
            ],
            'quiz_questions' => [
                [
                    'question' => "What is the main concept of {$topicName}?",
                    'options'  => ['Option A', 'Option B', 'Option C', 'Option D'],
                    'answer'   => 'Option A',
                ],
            ],
        ];
    }
}
