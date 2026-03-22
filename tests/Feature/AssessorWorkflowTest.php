<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentResult;
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
    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->assessor = User::factory()->create(['role' => 'assessor']);
        
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $unit = Unit::factory()->create(['module_id' => $module->id]);
        $this->assignment = Assignment::factory()->create(['unit_id' => $unit->id]);
    }

    public function test_assessor_sees_only_reviewed_submissions()
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);

        // A truly submitted one (should be hidden)
        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $student1->id,
            'status' => AssignmentSubmission::STATUS_SUBMITTED,
            'file_path' => 'hide_me.pdf',
            'submitted_at' => now(),
        ]);

        // A reviewed one (should be visible)
        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $student2->id,
            'status' => AssignmentSubmission::STATUS_REVIEWED,
            'file_path' => 'show_me.pdf',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->assessor)->get(route('assessor.grading.index'));
        
        // Assert view gets the reviewed submission (student2) but not the submitted one (student1)
        $response->assertSee($student2->name);
        $response->assertDontSee($student1->name);
    }

    public function test_assessor_can_mark_competency()
    {
        $student = User::factory()->create(['role' => 'student']);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_REVIEWED,
            'file_path' => 'grade_this.pdf',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->assessor)->post(route('assessor.grading.grade', $submission), [
            'competency_status' => 'competent',
            'marks' => 90,
            'feedback' => 'Great work.',
        ]);

        $response->assertRedirect(route('assessor.grading.index'));
        
        $submission->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_ASSESSED, $submission->status);
        $this->assertEquals($this->assessor->id, $submission->assessor_id);

        $this->assertDatabaseHas('assignment_results', [
            'submission_id' => $submission->id,
            'assessor_id' => $this->assessor->id,
            'competency_status' => 'competent',
        ]);
    }

    public function test_assessor_prevented_from_double_assessment()
    {
        $student = User::factory()->create(['role' => 'student']);
        $submission = AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $student->id,
            'status' => AssignmentSubmission::STATUS_ASSESSED,
            'file_path' => 'already_done.pdf',
            'submitted_at' => now(),
        ]);
        
        AssignmentResult::create([
            'submission_id' => $submission->id,
            'assessor_id' => $this->assessor->id,
            'competency_status' => 'competent',
            'graded_at' => now(),
        ]);

        $response = $this->actingAs($this->assessor)->post(route('assessor.grading.grade', $submission), [
            'competency_status' => 'not_yet_competent',
        ]);

        // Should be blocked and not update the database
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('assignment_results', [
            'submission_id' => $submission->id,
            'competency_status' => 'competent',
        ]);
    }
}
