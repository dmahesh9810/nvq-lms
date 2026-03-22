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

    public function test_instructor_can_create_assignment()
    {
        $response = $this->actingAs($this->instructor)->post(route('instructor.assignments.store'), [
            'unit_id' => $this->unit->id,
            'title' => 'Test Assignment',
            'description' => 'Test Description',
            'max_marks' => 100,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('instructor.assignments.index'));
        $this->assertDatabaseHas('assignments', [
            'title' => 'Test Assignment',
            'unit_id' => $this->unit->id,
        ]);
    }

    public function test_instructor_can_view_submissions()
    {
        $assignment = Assignment::factory()->create(['unit_id' => $this->unit->id]);
        $student = User::factory()->create(['role' => 'student']);
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_SUBMITTED,
            'file_path' => 'test.pdf',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->instructor)->get(route('instructor.assignments.submissions', $assignment));
        $response->assertStatus(200);
        $response->assertSee($student->name);
    }

    public function test_instructor_can_review_and_forward_submission()
    {
        $assignment = Assignment::factory()->create(['unit_id' => $this->unit->id]);
        $student = User::factory()->create(['role' => 'student']);
        
        $submission = AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_SUBMITTED,
            'file_path' => 'test.pdf',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->instructor)->post(route('instructor.assignments.submissions.review', $submission), [
            'instructor_review' => 'Good initial effort.',
        ]);

        $response->assertRedirect();
        
        $submission->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_REVIEWED, $submission->status);
        $this->assertEquals('Good initial effort.', $submission->instructor_review);
        $this->assertEquals($this->instructor->id, $submission->instructor_id);
    }
}
