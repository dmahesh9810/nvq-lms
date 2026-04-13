<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    /**
     * Get all courses enrolled by the authenticated student.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // If admin/instructor, maybe return all courses or assigned, but for mobile app it's usually students.
        if ($user->isStudent()) {
            $courses = $user->courses()->withPivot('status')->get();
        } else {
            // For instructors/admins, just return all active courses for simplicity in testing
            $courses = Course::where('status', 'active')->get();
        }

        return response()->json([
            'data' => $courses
        ]);
    }

    /**
     * Get details of a specific course, including its modules and lessons.
     */
    public function show(Request $request, $id)
    {
        $course = Course::with(['modules.lessons.microTopics'])->findOrFail($id);

        return response()->json([
            'data' => $course
        ]);
    }

    /**
     * Get learning materials (Notes/Videos) for a specific lesson.
     */
    public function lessonMaterials(Request $request, $id)
    {
        $lesson = \App\Models\Lesson::findOrFail($id);
        
        return response()->json([
            'data' => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'content' => $lesson->content,
                // Assuming there might be attributes like video_url or document_url added later
                'video_url' => $lesson->video_url ?? null,
                'document_url' => $lesson->document_url ?? null,
            ]
        ]);
    }
}
