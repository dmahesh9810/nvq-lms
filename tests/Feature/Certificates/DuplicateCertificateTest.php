<?php

namespace Tests\Feature\Certificates;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Services\CourseCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateCertificateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_does_not_create_duplicate_certificates_for_the_same_course()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();

        $existingCertificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);

        // We mock the service to bypass the actual logic check and force eligibility True
        $mockService = $this->partialMock(CourseCompletionService::class , function ($mock) {
            $mock->shouldReceive('isCompetent')->andReturn(true);
        });

        $returnedCertificate = $mockService->awardCertificateIfEligible($student, $course);

        $this->assertEquals($existingCertificate->id, $returnedCertificate->id);

        $this->assertDatabaseCount('certificates', 1);
    }
}
