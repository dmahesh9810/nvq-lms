<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use App\Models\Certificate;
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

    public function test_admin_can_view_certificates()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.certificates.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_revoke_certificate()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();
        
        $certificate = Certificate::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_number' => 'CERT-123',
            'status' => 'active',
            'issued_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->patch(route('admin.certificates.revoke', $certificate));
        
        $response->assertRedirect();
        $this->assertEquals('revoked', $certificate->refresh()->status);
    }
}
