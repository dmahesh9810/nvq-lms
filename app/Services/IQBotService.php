<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IQ-Bot: Powered by Google Gemini (free tier).
 * Generates a clear, student-friendly explanation when a quiz answer is wrong.
 */
class IQBotService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
    }

    /**
     * Explain why a specific answer is wrong and what the correct answer is.
     *
     * @param string $question       The quiz question text
     * @param string $wrongAnswer    What the student selected
     * @param string $correctAnswer  The correct answer
     * @param string $topicTitle     The topic name for context
     */
    public function explainWrongAnswer(
        string $question,
        string $wrongAnswer,
        string $correctAnswer,
        string $topicTitle
    ): string {
        if (empty($this->apiKey) || $this->apiKey === 'your_gemini_api_key_here') {
            return $this->getFallbackExplanation($question, $wrongAnswer, $correctAnswer);
        }

        $prompt = <<<EOT
You are IQ-Bot, a friendly AI tutor for NVQ Level 4 ICT students in Sri Lanka.

Topic: {$topicTitle}
Question: {$question}
Student selected: "{$wrongAnswer}" ❌
Correct answer: "{$correctAnswer}" ✅

Explain in 2-3 short paragraphs:
1. Why "{$wrongAnswer}" is incorrect
2. Why "{$correctAnswer}" is the right answer and the key concept behind it
3. A simple memory tip to remember this

Use simple, encouraging language. Mix English with brief Sinhala phrases if helpful.
Keep it under 120 words total. Format with clear paragraphs.
EOT;

        try {
            $response = Http::timeout(15)->post("{$this->apiUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens'  => 500,
                    'temperature'      => 0.7,
                    'thinkingConfig'   => ['thinkingBudget' => 0], // disable thinking for speed
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['candidates'][0]['content']['parts'][0]['text']
                    ?? $this->getFallbackExplanation($question, $wrongAnswer, $correctAnswer);
            }

            Log::warning('Gemini API error: ' . $response->status() . ' - ' . $response->body());
            return $this->getFallbackExplanation($question, $wrongAnswer, $correctAnswer);

        } catch (\Exception $e) {
            Log::error('IQBot error: ' . $e->getMessage());
            return $this->getFallbackExplanation($question, $wrongAnswer, $correctAnswer);
        }
    }

    /**
     * Fallback explanation when API key not set (works offline!)
     */
    private function getFallbackExplanation(string $question, string $wrong, string $correct): string
    {
        return "❌ **\"{$wrong}\"** is not the correct answer here.\n\n"
             . "✅ The correct answer is **\"{$correct}\"**. "
             . "Review this topic carefully and try to understand the key concept behind it.\n\n"
             . "💡 **Tip:** Re-read the topic notes and try the question again!";
    }
}
