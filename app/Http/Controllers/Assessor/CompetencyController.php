<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Unit;
use App\Models\User;
use App\Models\CompetencyAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompetencyController extends Controller
{
    /**
     * Show competency status for all units in a course for a specific student.
     */
    public function index(User $student, Course $course)
    {
        // Load course units and their modules
        $course->load(['modules.units.competencyAssessments' => function ($query) use ($student) {
            $query->where('user_id', $student->id);
        }]);

        return view('assessor.competency.index', compact('student', 'course'));
    }

    /**
     * Update the competency status of a specific unit for a specific student.
     */
    public function update(Request $request, User $student, Unit $unit)
    {
        $validated = $request->validate([
            'status' => 'required|in:not_assessed,not_competent,competent',
            'remarks' => 'nullable|string',
        ]);

        CompetencyAssessment::updateOrCreate(
            [
                'user_id' => $student->id,
                'unit_id' => $unit->id,
            ],
            [
                'assessor_id' => Auth::id(),
                'status' => $validated['status'],
                'remarks' => $validated['remarks'],
                'assessed_at' => now(),
            ]
        );

        // Check if all units are competent, issue certificate if eligible
        $course = $unit->module->course;
        app(\App\Services\CertificateService::class)->checkAndIssueCertificate($student, $course);

        return back()->with('success', 'Competency assessment updated successfully.');
    }
}
