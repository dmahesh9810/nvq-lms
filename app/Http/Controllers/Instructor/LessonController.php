<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\LessonRequest;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Show the create lesson form.
     */
    public function create(Course $course, Module $module, Unit $unit)
    {
        $this->authorizeCourse($course);

        return view('instructor.lessons.create', compact('course', 'module', 'unit'));
    }

    /**
     * Store a new lesson with optional media uploads.
     */
    public function store(LessonRequest $request, Course $course, Module $module, Unit $unit)
    {
        $this->authorizeCourse($course);

        $data = $request->validated();
        $data['unit_id'] = $unit->id;
        $data['is_active'] = $request->boolean('is_active', true);

        // Auto-order
        if (empty($data['order'])) {
            $data['order'] = $unit->lessons()->max('order') + 1;
        }

        // Handle PDF upload
        if ($request->hasFile('pdf_file')) {
            $data['pdf_path'] = $request->file('pdf_file')->store('lessons/pdfs', 'public');
        }
        // Remove the pdf_file key (not a DB column)
        unset($data['pdf_file']);

        Lesson::create($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Lesson created successfully!');
    }

    /**
     * Show edit form for a lesson.
     */
    public function edit(Course $course, Module $module, Unit $unit, Lesson $lesson)
    {
        $this->authorizeCourse($course);

        return view('instructor.lessons.edit', compact('course', 'module', 'unit', 'lesson'));
    }

    /**
     * Update lesson, replacing PDF if a new one is uploaded.
     */
    public function update(LessonRequest $request, Course $course, Module $module, Unit $unit, Lesson $lesson)
    {
        $this->authorizeCourse($course);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle PDF replacement
        if ($request->hasFile('pdf_file')) {
            // Delete the old PDF
            if ($lesson->pdf_path) {
                Storage::disk('public')->delete($lesson->pdf_path);
            }
            $data['pdf_path'] = $request->file('pdf_file')->store('lessons/pdfs', 'public');
        }
        unset($data['pdf_file']);

        $lesson->update($data);

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Lesson updated successfully!');
    }

    /**
     * Delete a lesson and clean up any stored files.
     */
    public function destroy(Course $course, Module $module, Unit $unit, Lesson $lesson)
    {
        $this->authorizeCourse($course);

        if ($lesson->pdf_path) {
            Storage::disk('public')->delete($lesson->pdf_path);
        }

        $lesson->delete();

        return redirect()
            ->route('instructor.courses.show', $course)
            ->with('success', 'Lesson deleted!');
    }

    private function authorizeCourse(Course $course): void
    {
        if ($course->instructor_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }
    }
}
