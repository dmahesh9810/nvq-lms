<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentResult;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $assessor;
    private User $instructor;
    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assessor    = User::factory()->create(['role' => 'assessor']);
        $this->instructor  = User::factory()->create(['role' => 'instructor']);

        $course   = Course::factory()->create(['instructor_id' => $this->instructor->id]);
        $module   = Module::factory()->create(['course_id' => $course->id]);
        $unit     = Unit::factory()->create(['module_id' => $module->id]);
        $this->assignment = Assignment::factory()->create(['unit_id' => $unit->id]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Queue Visibility
    // ─────────────────────────────────────────────────────────────────

    public function test_assessor_sees_only_instructor_assessed_submissions()
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        // Submitted-only → hidden
        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $student1->id,
            'status'        => AssignmentSubmission::STATUS_SUBMITTED,
            'file_path'     => 'hide_me.pdf',
            'submitted_at'  => now(),
        ]);

        // Instructor assessed → visible
        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $student2->id,
            'status'        => AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED,
            'instructor_id' => $this->instructor->id,
            'file_path'     => 'show_me.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->assessor)->get(route('assessor.grading.index'));

        $response->assertSee($student2->name);
        $response->assertDontSee($student1->name);
    }

    public function test_assessor_does_not_see_already_verified_submissions_in_pending()
    {
        $student = User::factory()->create(['role' => 'student']);

        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $student->id,
            'status'        => AssignmentSubmission::STATUS_ASSESSOR_VERIFIED,
            'instructor_id' => $this->instructor->id,
            'verified_at'   => now(),
            'file_path'     => 'already_done.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->assessor)->get(route('assessor.grading.index'));

        // The already-verified student should NOT appear in the pending list header
        $response->assertSee('No pending submissions');
    }

    // ─────────────────────────────────────────────────────────────────
    // Verify & Endorse
    // ─────────────────────────────────────────────────────────────────

    public function test_assessor_can_verify_submission()
    {
        $student = User::factory()->create(['role' => 'student']);
        $submission = AssignmentSubmission::create([
            'assignment_id'              => $this->assignment->id,
            'user_id'                    => $student->id,
            'status'                     => AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED,
            'instructor_competency_status' => 'competent',
            'instructor_id'              => $this->instructor->id,
            'file_path'                  => 'grade_this.pdf',
            'submitted_at'               => now(),
        ]);

        $response = $this->actingAs($this->assessor)->post(
            route('assessor.grading.verify', $submission),
            ['action' => 'verify', 'note' => 'Looks good.']
        );

        $response->assertRedirect(route('assessor.grading.index'));
        $submission->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_ASSESSOR_VERIFIED, $submission->status);
        $this->assertEquals($this->assessor->id, $submission->assessor_id);
        $this->assertEquals('Looks good.', $submission->assessor_verification_note);

        $this->assertDatabaseHas('assignment_results', [
            'submission_id'    => $submission->id,
            'assessor_id'      => $this->assessor->id,
            'competency_status' => 'competent',
        ]);

        $this->assertDatabaseHas('verification_logs', [
            'submission_id' => $submission->id,
            'assessor_id'   => $this->assessor->id,
            'action'        => 'verify',
            'note'          => 'Looks good.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Reject
    // ─────────────────────────────────────────────────────────────────

    public function test_assessor_can_reject_submission()
    {
        $student = User::factory()->create(['role' => 'student']);
        $submission = AssignmentSubmission::create([
            'assignment_id'              => $this->assignment->id,
            'user_id'                    => $student->id,
            'status'                     => AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED,
            'instructor_competency_status' => 'not_yet_competent',
            'instructor_id'              => $this->instructor->id,
            'file_path'                  => 'to_reject.pdf',
            'submitted_at'               => now(),
        ]);

        $response = $this->actingAs($this->assessor)->post(
            route('assessor.grading.verify', $submission),
            ['action' => 'reject', 'note' => 'Instructor evaluation needs improvement.']
        );

        $response->assertRedirect(route('assessor.grading.index'));
        $submission->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_ASSESSOR_REJECTED, $submission->status);
        $this->assertEquals($this->assessor->id, $submission->assessor_id);

        $this->assertDatabaseHas('verification_logs', [
            'submission_id' => $submission->id,
            'assessor_id'   => $this->assessor->id,
            'action'        => 'reject',
        ]);
    }

    public function test_rejected_submission_does_not_create_assignment_result()
    {
        $student = User::factory()->create(['role' => 'student']);
        $submission = AssignmentSubmission::create([
            'assignment_id'              => $this->assignment->id,
            'user_id'                    => $student->id,
            'status'                     => AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED,
            'instructor_competency_status' => 'not_yet_competent',
            'instructor_id'              => $this->instructor->id,
            'file_path'                  => 'reject_no_result.pdf',
            'submitted_at'               => now(),
        ]);

        $this->actingAs($this->assessor)->post(
            route('assessor.grading.verify', $submission),
            ['action' => 'reject']
        );

        $this->assertDatabaseMissing('assignment_results', [
            'submission_id' => $submission->id,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Prevent Double Verification
    // ─────────────────────────────────────────────────────────────────

    public function test_assessor_prevented_from_double_verification()
    {
        $student = User::factory()->create(['role' => 'student']);
        $submission = AssignmentSubmission::create([
            'assignment_id'              => $this->assignment->id,
            'user_id'                    => $student->id,
            'status'                     => AssignmentSubmission::STATUS_ASSESSOR_VERIFIED,
            'instructor_competency_status' => 'competent',
            'instructor_id'              => $this->instructor->id,
            'verified_at'                => now(),
            'file_path'                  => 'already_done.pdf',
            'submitted_at'               => now(),
        ]);

        AssignmentResult::create([
            'submission_id'    => $submission->id,
            'assessor_id'      => $this->assessor->id,
            'competency_status' => 'competent',
            'graded_at'        => now(),
        ]);

        // Attempt to re-verify (should be blocked)
        $response = $this->actingAs($this->assessor)->post(
            route('assessor.grading.verify', $submission),
            ['action' => 'reject']
        );

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('assignment_results', [
            'submission_id'    => $submission->id,
            'competency_status' => 'competent',
        ]);
    }

    public function test_assessor_cannot_verify_unassessed_submission()
    {
        $student = User::factory()->create(['role' => 'student']);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $student->id,
            'status'        => AssignmentSubmission::STATUS_SUBMITTED, // not yet instructor_assessed
            'file_path'     => 'fresh.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->assessor)->post(
            route('assessor.grading.verify', $submission),
            ['action' => 'verify']
        );

        $response->assertSessionHas('error');
    }
}
