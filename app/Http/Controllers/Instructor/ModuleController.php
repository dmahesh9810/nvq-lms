<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleRequest;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
    /**
     * Show form to create a new module within a course.
     */
    public function create(Course $course)
    {
        abort_unless(\Illuminate\Support\Facades\Auth::user()->isAdmin(), 403, 'Only administrators can add modules.');

        $this->authorizeModule($course);

        return view('instructor.modules.create', compact('course'));
    }

    /**
     * Store a new module.
     */
    public function store(ModuleRequest $request, Course $course)
    {
        abort_unless(Auth::user()->isAdmin(), 403, 'Only administrators can add modules.');

        $this->authorizeModule($course);

        $data = $request->validated();
        $data['course_id'] = $course->id;
        $data['is_active'] = $request->boolean('is_active', true);

        // Auto-assign next order if not set
        if (empty($data['order'])) {
            $data['order'] = $course->modules()->max('order') + 1;
        }

        Module::create($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Module added successfully!');
    }

    /**
     * Show edit form for a module.
     * Admin-only: instructors must use the change request flow.
     */
    public function edit(Course $course, Module $module)
    {
        abort_unless(Auth::user()->isAdmin(), 403,
            'Instructors cannot edit modules directly. Please use the "Request Edit" button.');

        $this->authorizeModule($course, $module);

        return view('instructor.modules.edit', compact('course', 'module'));
    }

    /**
     * Update module.
     * Admin-only: instructors must use the change request flow.
     */
    public function update(ModuleRequest $request, Course $course, Module $module)
    {
        abort_unless(Auth::user()->isAdmin(), 403,
            'Instructors cannot update modules directly. Please submit a Request Edit instead.');

        $this->authorizeModule($course, $module);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $module->update($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Module updated successfully!');
    }

    /**
     * Delete a module.
     * Admin-only: instructors must use the change request flow.
     */
    public function destroy(Course $course, Module $module)
    {
        abort_unless(Auth::user()->isAdmin(), 403,
            'Instructors cannot delete modules directly. Please submit a Request Delete instead.');

        $this->authorizeModule($course, $module);

        $module->delete();

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Module deleted!');
    }

    private function authorizeModule(Course $course, ?Module $module = null): void
    {
        // 1. Check Course-level access (Primary owner, Admin, or Course-assigned)
        $isCourseAdmin = $course->instructor_id === \Illuminate\Support\Facades\Auth::id() || 
                         \Illuminate\Support\Facades\Auth::user()->isAdmin() || 
                         $course->assignedInstructors()->where('users.id', \Illuminate\Support\Facades\Auth::id())->exists();

        if ($isCourseAdmin) {
            return;
        }

        // 2. If module is provided, check Module-level assignment.
        if ($module) {
            $isModuleAssigned = $module->assignedInstructors()->where('users.id', \Illuminate\Support\Facades\Auth::id())->exists();
            if ($isModuleAssigned) {
                return;
            }
        }

        abort(403, 'You do not own this course and are not assigned to it or this module.');
    }
}
