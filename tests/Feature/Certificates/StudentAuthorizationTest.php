<?php

namespace Tests\Feature\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_student_cannot_download_another_students_certificate()
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $certificate = Certificate::factory()->create([
            'user_id' => $student1->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($student2)->get(route('student.certificates.download', $certificate));

        $response->assertStatus(403);
    }

    /** @test */
    public function a_student_can_download_their_own_certificate()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $certificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active'
        ]);

        $response = $this->actingAs($student)->get(route('student.certificates.download', $certificate));

        $response->assertStatus(200);
        // Using response headers check because DOMPdf streams the file
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function a_student_can_view_their_certificates_on_index()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course1 = Course::factory()->create(['title' => 'First Course']);
        $course2 = Course::factory()->create(['title' => 'Second Course']);

        Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course1->id,
        ]);

        $otherStudent = User::factory()->create(['role' => 'student']);
        Certificate::factory()->create([
            'user_id' => $otherStudent->id,
            'course_id' => $course2->id,
        ]);

        $response = $this->actingAs($student)->get(route('student.certificates.index'));

        $response->assertStatus(200);
        $response->assertSee('First Course');
        $response->assertDontSee('Second Course');
    }
}
