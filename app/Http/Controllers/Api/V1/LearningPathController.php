<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\MicroTopic;
use App\Models\StudentTopicProgress;

class LearningPathController extends Controller
{
    public function getPath(Request $request)
    {
        $userId = $request->user()->id;

        // Fetch the real NVQ Gamified Course
        $course = Course::first();
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'No course found'], 404);
        }

        // Fetch all micro topics ordered
        $microTopics = MicroTopic::orderBy('order')->get();

        // Fetch this student's completion records in one DB query (efficient!)
        $completedTopicIds = StudentTopicProgress::where('user_id', $userId)
            ->where('is_completed', true)
            ->pluck('micro_topic_id')
            ->toArray();

        $nodes = [];
        $previousCompleted = true; // first node is always unlocked

        foreach ($microTopics as $index => $topic) {
            $isCompleted = in_array($topic->id, $completedTopicIds);

            // Status logic: completed → active (unlocked) → locked
            if ($isCompleted) {
                $status = 'completed';
            } elseif ($previousCompleted) {
                $status = 'active'; // This is the next node to do
            } else {
                $status = 'locked';
            }

            $nodes[] = [
                'id'           => $topic->id,
                'type'         => $topic->is_practical ? 'practical' : 'micro_topic',
                'title'        => $topic->title,
                'status'       => $status,
                'is_practical' => (bool) $topic->is_practical,
                'stars'        => $isCompleted ? 1 : 0,
            ];

            $previousCompleted = $isCompleted;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'course_title' => $course->title,
                'nodes'        => $nodes,
            ]
        ]);
    }
}
