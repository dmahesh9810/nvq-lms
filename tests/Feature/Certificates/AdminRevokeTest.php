<?php

namespace Tests\Feature\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRevokeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_revoke_an_active_certificate()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $certificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.certificates.revoke', $certificate));

        $response->assertRedirect(route('admin.certificates.index'));
        $this->assertEquals('revoked', $certificate->fresh()->status);
    }

    /** @test */
    public function a_non_admin_cannot_revoke_a_certificate()
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create();

        $certificate = Certificate::factory()->create([
            'user_id' => $student1->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($student2)
            ->patch(route('admin.certificates.revoke', $certificate));
        $response->assertStatus(403);

        $response = $this->actingAs($instructor)
            ->patch(route('admin.certificates.revoke', $certificate));
        $response->assertStatus(403);

        $this->assertEquals('active', $certificate->fresh()->status);
    }

    /** @test */
    public function an_admin_can_reinstate_a_revoked_certificate()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $certificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'revoked'
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.certificates.reinstate', $certificate));

        $response->assertRedirect(route('admin.certificates.index'));
        $this->assertEquals('active', $certificate->fresh()->status);
    }
}
