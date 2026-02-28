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
        $this->authorizeCourse($course);

        return view('instructor.modules.create', compact('course'));
    }

    /**
     * Store a new module.
     */
    public function store(ModuleRequest $request, Course $course)
    {
        $this->authorizeCourse($course);

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
     */
    public function edit(Course $course, Module $module)
    {
        $this->authorizeCourse($course);

        return view('instructor.modules.edit', compact('course', 'module'));
    }

    /**
     * Update module.
     */
    public function update(ModuleRequest $request, Course $course, Module $module)
    {
        $this->authorizeCourse($course);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $module->update($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Module updated successfully!');
    }

    /**
     * Delete a module.
     */
    public function destroy(Course $course, Module $module)
    {
        $this->authorizeCourse($course);

        $module->delete();

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Module deleted!');
    }

    private function authorizeCourse(Course $course): void
    {
        if ($course->instructor_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
