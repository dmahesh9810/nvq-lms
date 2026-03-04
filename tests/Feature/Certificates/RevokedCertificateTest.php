<?php

namespace Tests\Feature\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokedCertificateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_student_cannot_download_a_revoked_certificate()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $certificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'revoked'
        ]);

        $response = $this->actingAs($student)->get(route('student.certificates.download', $certificate));

        $response->assertStatus(302);
        // Will be redirected back with an error flash message due to logic in controller
        $response->assertSessionHas('error');
    }
}
