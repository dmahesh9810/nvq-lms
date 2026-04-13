<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentCriteria;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    /**
     * Get assignment details including grading criteria
     */
    public function show($id)
    {
        // For dynamic knowledge tracking, let's load criterias attached to the assignment
        $assignment = Assignment::with(['criterias.microTopic'])->findOrFail($id);

        return response()->json([
            'data' => $assignment
        ]);
    }

    /**
     * Submit assignment (upload file)
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,zip|max:10240', // Max 10MB approx
        ]);

        $assignment = Assignment::findOrFail($id);
        
        $path = $request->file('file')->store('assignments/' . $assignment->id, 'public');

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'file_path' => $path,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Assignment submitted successfully',
            'data' => $submission
        ]);
    }

    /**
     * Instructor grading API. 
     * Handles criterion-based grading to support tracking.
     */
    public function markCriteria(Request $request, $id)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*.criteria_id' => 'required|exists:assignment_criterias,id',
            'grades.*.marks_awarded' => 'required|numeric',
            'overall_status' => 'required|string', // competent, not_yet_competent
        ]);

        $submission = AssignmentSubmission::findOrFail($id);
        
        // This is a minimal representation. 
        // In a complete system, we would insert into `assignment_results` or a pivot table.
        // For the sake of the MVP APIs, we'll return a success payload.
        // It helps set the architecture for the frontend to submit grades per criteria.

        $submission->update(['status' => $request->overall_status, 'verified_at' => now()]);

        return response()->json([
            'message' => 'Submission graded based on criteria successfully.'
        ]);
    }
}
