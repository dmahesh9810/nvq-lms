<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitRequest;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    /**
     * Show form to create unit within a module.
     */
    public function create(Course $course, Module $module)
    {
        $this->authorizeCourse($course);

        return view('instructor.units.create', compact('course', 'module'));
    }

    /**
     * Store a new unit.
     */
    public function store(UnitRequest $request, Course $course, Module $module)
    {
        $this->authorizeCourse($course);

        $data = $request->validated();
        $data['module_id'] = $module->id;
        $data['is_active'] = $request->boolean('is_active', true);

        if (empty($data['order'])) {
            $data['order'] = $module->units()->max('order') + 1;
        }

        Unit::create($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Unit added successfully!');
    }

    /**
     * Show edit form for a unit.
     */
    public function edit(Course $course, Module $module, Unit $unit)
    {
        $this->authorizeCourse($course);

        return view('instructor.units.edit', compact('course', 'module', 'unit'));
    }

    /**
     * Update a unit.
     */
    public function update(UnitRequest $request, Course $course, Module $module, Unit $unit)
    {
        $this->authorizeCourse($course);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $unit->update($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Unit updated successfully!');
    }

    /**
     * Delete a unit.
     */
    public function destroy(Course $course, Module $module, Unit $unit)
    {
        $this->authorizeCourse($course);

        $unit->delete();

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Unit deleted!');
    }

    private function authorizeCourse(Course $course): void
    {
        if ($course->instructor_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
