<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Course $course;
    private Assignment $assignment;

    protected function setUp(): void
    {
        parent::setUp();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $this->course = Course::factory()->create(['instructor_id' => $instructor->id, 'status' => 'published']);
        $module = Module::factory()->create(['course_id' => $this->course->id]);
        $unit = Unit::factory()->create(['module_id' => $module->id]);
        $this->assignment = Assignment::factory()->create(['unit_id' => $unit->id, 'is_active' => true]);
        $this->student = User::factory()->create(['role' => 'student']);
    }

    // ─────────────────────────────────────────────────────────────────
    // Authentication
    // ─────────────────────────────────────────────────────────────────

    public function test_student_can_register()
    {
        $response = $this->post('/register', [
            'name'                  => 'New Student',
            'email'                 => 'newstudent@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
            'role'                  => 'student',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'newstudent@example.com']);
    }

    public function test_student_can_login()
    {
        $response = $this->post('/login', [
            'email'    => $this->student->email,
            'password' => 'password',
        ]);
        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_unauthenticated_user_is_redirected_from_dashboard()
    {
        $response = $this->get('/student/dashboard');
        $response->assertRedirect('/login');
    }

    // ─────────────────────────────────────────────────────────────────
    // Course Enrollment
    // ─────────────────────────────────────────────────────────────────

    public function test_student_can_enroll_in_course()
    {
        $response = $this->actingAs($this->student)->post(route('student.courses.enroll', $this->course));

        $response->assertRedirect(route('student.courses.show', $this->course));
        $this->assertTrue($this->course->enrollments()->where('user_id', $this->student->id)->exists());
    }

    public function test_student_cannot_enroll_twice()
    {
        $this->course->enrollments()->create(['user_id' => $this->student->id]);

        $response = $this->actingAs($this->student)->post(route('student.courses.enroll', $this->course));

        // Should redirect (not crash); enrollment count stays at 1
        $response->assertRedirect();
        $this->assertEquals(1, $this->course->enrollments()->where('user_id', $this->student->id)->count());
    }

    // ─────────────────────────────────────────────────────────────────
    // Assignment Submission
    // ─────────────────────────────────────────────────────────────────

    public function test_student_can_submit_assignment()
    {
        $this->course->enrollments()->create(['user_id' => $this->student->id]);
        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->student)
            ->post(route('student.assignments.submit', $this->assignment), ['file' => $file]);

        $response->assertRedirect(route('student.assignments.show', $this->assignment));
        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $this->assignment->id,
            'user_id'       => $this->student->id,
            'status'        => AssignmentSubmission::STATUS_SUBMITTED,
        ]);

        $submission = AssignmentSubmission::first();
        Storage::disk('public')->assertExists($submission->file_path);
    }

    public function test_student_can_resubmit_assignment_and_reset_status()
    {
        $this->course->enrollments()->create(['user_id' => $this->student->id]);
        Storage::fake('public');

        $sub = AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $this->student->id,
            'status'        => AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED,
            'instructor_id' => 1,
            'file_path'     => 'fake/path.pdf',
            'submitted_at'  => now(),
        ]);

        $newFile = UploadedFile::fake()->create('new_document.pdf', 100);
        $response = $this->actingAs($this->student)
            ->post(route('student.assignments.submit', $this->assignment), ['file' => $newFile]);

        $response->assertSessionHas('success');
        $sub->refresh();
        $this->assertEquals(AssignmentSubmission::STATUS_RESUBMITTED, $sub->status);
        $this->assertNull($sub->instructor_id);
    }

    public function test_student_cannot_resubmit_after_assessor_verification()
    {
        $this->course->enrollments()->create(['user_id' => $this->student->id]);
        Storage::fake('public');

        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $this->student->id,
            'status'        => AssignmentSubmission::STATUS_ASSESSOR_VERIFIED,
            'instructor_id' => 1,
            'verified_at'   => now(),
            'file_path'     => 'fake/path.pdf',
            'submitted_at'  => now(),
        ]);

        $newFile = UploadedFile::fake()->create('blocked.pdf', 100);
        $response = $this->actingAs($this->student)
            ->post(route('student.assignments.submit', $this->assignment), ['file' => $newFile]);

        $response->assertSessionHas('error');
    }

    // ─────────────────────────────────────────────────────────────────
    // Submission Status View
    // ─────────────────────────────────────────────────────────────────

    public function test_student_can_view_submission_status()
    {
        $this->course->enrollments()->create(['user_id' => $this->student->id]);
        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id'       => $this->student->id,
            'status'        => AssignmentSubmission::STATUS_INSTRUCTOR_ASSESSED,
            'file_path'     => 'fake/path.pdf',
            'submitted_at'  => now(),
        ]);

        $response = $this->actingAs($this->student)->get(route('student.assignments.show', $this->assignment));

        $response->assertStatus(200);
        $response->assertSee('Instructor Assessed');
    }
}
