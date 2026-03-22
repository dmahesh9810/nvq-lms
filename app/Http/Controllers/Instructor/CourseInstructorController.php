<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseInstructorController extends Controller
{
    public function syncCourseInstructors(Request $request, Course $course)
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can assign instructors.');

        $request->validate([
            'instructor_ids' => 'array',
            'instructor_ids.*' => 'exists:users,id',
        ]);

        $instructorIds = $request->input('instructor_ids', []);
        
        // Ensure primary instructor is always retained
        if (!in_array($course->instructor_id, $instructorIds)) {
            $instructorIds[] = $course->instructor_id;
        }

        // Find instructors who were removed from course-level assignment
        $currentIds = $course->assignedInstructors()->pluck('users.id')->toArray();
        $removedIds = array_diff($currentIds, $instructorIds);

        // Clean orphaned module assignments for removed instructors
        if (!empty($removedIds)) {
            $moduleIds = $course->modules()->pluck('id')->toArray();
            if (!empty($moduleIds)) {
                DB::table('module_user')
                    ->whereIn('module_id', $moduleIds)
                    ->whereIn('user_id', $removedIds)
                    ->delete();
            }
        }

        // Sync with role default
        $syncData = [];
        foreach ($instructorIds as $id) {
            $syncData[$id] = ['role' => ($id == $course->instructor_id) ? 'creator' : 'instructor'];
        }

        $course->assignedInstructors()->sync($syncData);

        return back()->with('success', 'Course instructors updated successfully.');
    }

    public function syncModuleInstructors(Request $request, Course $course, Module $module)
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can assign instructors.');

        $request->validate([
            'instructor_ids' => 'array',
            'instructor_ids.*' => 'exists:users,id',
        ]);

        $module->assignedInstructors()->sync($request->input('instructor_ids', []));

        return back()->with('success', 'Module instructors updated successfully.');
    }
}
