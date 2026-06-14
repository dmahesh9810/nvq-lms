<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\MicroTopic;
use Illuminate\Http\Request;

class MicroTopicController extends Controller
{
    /**
     * Display a listing of the micro-topics for a specific lesson.
     */
    public function index(Lesson $lesson)
    {
        $microTopics = $lesson->microTopics()->get();
        return view('instructor.lessons.micro_topics.index', compact('lesson', 'microTopics'));
    }

    /**
     * Show the form for creating a new micro-topic (Concept Cards Builder).
     */
    public function create(Lesson $lesson)
    {
        return view('instructor.lessons.micro_topics.create_cards', compact('lesson'));
    }

    /**
     * Store a newly created micro-topic in storage.
     */
    public function store(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'topic_name'        => 'required|string|max:255',
            'description'       => 'nullable|string',
            'estimated_minutes' => 'required|integer|min:1',
            'key_takeaway'      => 'nullable|string',
            'concept_cards'     => 'nullable|json', // Sent from JS as a JSON string
        ]);

        $cardsArray = $validated['concept_cards'] ? json_decode($validated['concept_cards'], true) : [];

        $lesson->microTopics()->create([
            'topic_name'        => $validated['topic_name'],
            'description'       => $validated['description'],
            'estimated_minutes' => $validated['estimated_minutes'],
            'key_takeaway'      => $validated['key_takeaway'],
            'concept_cards'     => $cardsArray,
        ]);

        return redirect()->route('instructor.lessons.micro-topics.index', $lesson)
            ->with('success', 'Micro-Topic and Concept Cards created successfully!');
    }

    /**
     * Show the form for editing the specified micro-topic.
     */
    public function edit(Lesson $lesson, MicroTopic $topic)
    {
        return view('instructor.lessons.micro_topics.edit_cards', compact('lesson', 'topic'));
    }

    /**
     * Update the specified micro-topic in storage.
     */
    public function update(Request $request, Lesson $lesson, MicroTopic $topic)
    {
        $validated = $request->validate([
            'topic_name'        => 'required|string|max:255',
            'description'       => 'nullable|string',
            'estimated_minutes' => 'required|integer|min:1',
            'key_takeaway'      => 'nullable|string',
            'concept_cards'     => 'nullable|json',
        ]);

        $cardsArray = $validated['concept_cards'] ? json_decode($validated['concept_cards'], true) : [];

        $topic->update([
            'topic_name'        => $validated['topic_name'],
            'description'       => $validated['description'],
            'estimated_minutes' => $validated['estimated_minutes'],
            'key_takeaway'      => $validated['key_takeaway'],
            'concept_cards'     => $cardsArray,
        ]);

        return redirect()->route('instructor.lessons.micro-topics.index', $lesson)
            ->with('success', 'Micro-Topic updated successfully!');
    }

    /**
     * Remove the specified micro-topic from storage.
     */
    public function destroy(Lesson $lesson, MicroTopic $topic)
    {
        $topic->delete();
        return redirect()->route('instructor.lessons.micro-topics.index', $lesson)
            ->with('success', 'Micro-Topic deleted successfully!');
    }
}
