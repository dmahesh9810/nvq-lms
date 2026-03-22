<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstructorWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $instructor;
    private Course $course;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instructor = User::factory()->create(['role' => 'instructor']);
        $this->course = Course::factory()->create(['instructor_id' => $this->instructor->id]);
        $module = Module::factory()->create(['course_id' => $this->course->id]);
        $this->unit = Unit::factory()->create(['module_id' => $module->id]);
    }

    // ─────────────────────────────────────────────────────────────────
    // Assignment CRUD
    // ─────────────────────────────────────────────────────────────────

    public function test_instructor_can_create_assignment()
    {
        $response = $this->actingAs($this->instructor)->post(route('instructor.assignments.store'), [
            'unit_id'     => $this->unit->id,
            'title'       => 'Test Assignment',
            'description' => 'Test Description',
            'max_marks'   => 100,
            'is_active'   => true,
        ]);

        $response->assertRedirect(route('instructor.assignments.index'));
        $this->assertDatabaseHas('assignments', [
            'title'   => 'Test Assignment',
            'unit_id' => $this->unit->id,
        ]);
    }

    public function test_other_instructor_cannot_create_assignment_for_unowned_unit()
    {
        $otherInstructor = User::factory()->create(['role' => 'instructor']);

        $response = $this->actingAs($otherInstructor)->post(route('instructor.assignments.store'), [
            'unit_id'   => $this->unit->id,
            'title'     => 'Stolen Assignment',
            'is_active' => true,
        ]);

        // Should get a 403 forbidden
        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────
    // Submissions
    // ─────────────────────────────────────────────────────────────────

    public function test_instructor_can_view_submissions()
    {
        $assignment = Assignment::factory()->create(['unit_id' => $this->unit->id]);
        $student = User::factory()->create(['role' => 'student']);
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'status'        => AssignmentSubmission::STATUS_SUBMITTED,
            'file_path'     => 'test.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->instructor)->get(route('instructor.assignments.submissions', $assignment));
        $response->assertStatus(200);
        $response->assertSee($student->name);
    }

    public function test_instructor_cannot_view_another_instructors_submissions()
    {
        $otherInstructor = User::factory()->create(['role' => 'instructor']);
        $assignment = Assignment::factory()->create(['unit_id' => $this->unit->id]);

        $response = $this->actingAs($otherInstructor)->get(route('instructor.assignments.submissions', $assignment));
        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────
    // Review & Mark C/NYC
    // ─────────────────────────────────────────────────────────────────

    public function test_instructor_can_review_and_mark_competent()
    {
        $assignment = Assignment::factory()->create(['unit_id' => $this->unit->id]);
        $student = User::factory()->create(['role' => 'student']);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'status'        => AssignmentSubmission::STATUS_SUBMITTED,
            'file_path'     => 'test.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->instructor)->post(
            route('instructor.assignments.submissions.review', $submission),
            [
                'instructor_review'           => 'Good initial effort.',
                'instructor_competency_status' => 'competent',
            ]
        );

        $response->assertRedirect();
        $submission->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED, $submission->status);
        $this->assertEquals('Good initial effort.', $submission->instructor_review);
        $this->assertEquals('competent', $submission->instructor_competency_status);
        $this->assertEquals($this->instructor->id, $submission->instructor_id);
    }

    public function test_instructor_can_review_and_mark_not_yet_competent()
    {
        $assignment = Assignment::factory()->create(['unit_id' => $this->unit->id]);
        $student = User::factory()->create(['role' => 'student']);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'status'        => AssignmentSubmission::STATUS_SUBMITTED,
            'file_path'     => 'test.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->instructor)->post(
            route('instructor.assignments.submissions.review', $submission),
            [
                'instructor_review'           => 'Needs more work.',
                'instructor_competency_status' => 'not_yet_competent',
            ]
        );

        $response->assertRedirect();
        $submission->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED, $submission->status);
        $this->assertEquals('not_yet_competent', $submission->instructor_competency_status);
    }

    public function test_instructor_cannot_alter_review_after_assessor_verified()
    {
        $assignment = Assignment::factory()->create(['unit_id' => $this->unit->id]);
        $student = User::factory()->create(['role' => 'student']);

        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id'       => $student->id,
            'status'        => AssignmentSubmission::STATUS_ASSESSOR_VERIFIED,
            'instructor_id' => $this->instructor->id,
            'verified_at'   => now(),
            'file_path'     => 'test.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->instructor)->post(
            route('instructor.assignments.submissions.review', $submission),
            [
                'instructor_review'           => 'Trying to change...',
                'instructor_competency_status' => 'competent',
            ]
        );

        $response->assertSessionHas('error');
    }
}
