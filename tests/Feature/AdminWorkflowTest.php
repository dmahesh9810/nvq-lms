<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\User;
use App\Models\VerificationLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ─────────────────────────────────────────────────────────────────
    // Course Approval
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_approve_course()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)->patch(route('admin.courses.approve', $course));

        $response->assertRedirect();
        $this->assertEquals('published', $course->refresh()->status);
    }

    public function test_admin_can_reject_course()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id, 'status' => 'pending']);

        $response = $this->actingAs($this->admin)->patch(route('admin.courses.reject', $course));

        $response->assertRedirect();
        $this->assertEquals('rejected', $course->refresh()->status);
    }

    public function test_non_admin_cannot_approve_course()
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id, 'status' => 'pending']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->patch(route('admin.courses.approve', $course));

        $response->assertStatus(403);
        $this->assertEquals('pending', $course->refresh()->status);
    }

    // ─────────────────────────────────────────────────────────────────
    // TVEC Verification Logs
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_verification_logs_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.audits.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_see_verification_log_entries()
    {
        $assessor    = User::factory()->create(['role' => 'assessor']);
        $instructor  = User::factory()->create(['role' => 'instructor']);
        $student     = User::factory()->create(['role' => 'student']);
        $course      = Course::factory()->create(['instructor_id' => $instructor->id]);
        $module      = Module::factory()->create(['course_id' => $course->id]);
        $unit        = Unit::factory()->create(['module_id' => $module->id]);
        $assignment  = Assignment::factory()->create(['unit_id' => $unit->id]);

        $submission  = AssignmentSubmission::create([
            'assignment_id'              => $assignment->id,
            'user_id'                    => $student->id,
            'status'                     => AssignmentSubmission::STATUS_ASSESSOR_VERIFIED,
            'instructor_id'              => $instructor->id,
            'instructor_competency_status' => 'competent',
            'verified_at'                => now(),
            'file_path'                  => 'verify_log_test.pdf',
            'submitted_at'               => now(),
        ]);

        VerificationLog::create([
            'assessor_id'   => $assessor->id,
            'instructor_id' => $instructor->id,
            'submission_id' => $submission->id,
            'action'        => 'verify',
            'note'          => 'All looks correct.',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.audits.index'));

        $response->assertStatus(200);
        $response->assertSee($assessor->name);
        $response->assertSee($instructor->name);
        $response->assertSee('Verified');
    }

    public function test_non_admin_cannot_see_verification_logs()
    {
        $assessor = User::factory()->create(['role' => 'assessor']);

        $response = $this->actingAs($assessor)->get(route('admin.audits.index'));
        $response->assertStatus(403);
    }

    // ─────────────────────────────────────────────────────────────────
    // Certificates
    // ─────────────────────────────────────────────────────────────────

    public function test_admin_can_view_certificates()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.certificates.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_revoke_certificate()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course  = Course::factory()->create();

        $certificate = Certificate::create([
            'user_id'            => $student->id,
            'course_id'          => $course->id,
            'certificate_number' => 'CERT-123',
            'status'             => 'active',
            'issued_at'          => now(),
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.certificates.revoke', $certificate));

        $response->assertRedirect();
        $this->assertEquals('revoked', $certificate->refresh()->status);
    }

    public function test_admin_can_reinstate_certificate()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course  = Course::factory()->create();

        $certificate = Certificate::create([
            'user_id'            => $student->id,
            'course_id'          => $course->id,
            'certificate_number' => 'CERT-456',
            'status'             => 'revoked',
            'issued_at'          => now(),
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.certificates.reinstate', $certificate));

        $response->assertRedirect();
        $this->assertEquals('active', $certificate->refresh()->status);
    }
}
