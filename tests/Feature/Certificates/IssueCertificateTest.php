<?php

namespace Tests\Feature\Certificates;

use App\Models\Assignment;
use App\Models\AssignmentResult;
use App\Models\AssignmentSubmission;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\User;
use App\Services\CourseCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueCertificateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_issues_a_certificate_when_all_course_units_are_competent()
    {
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $unit1 = Unit::factory()->create(['module_id' => $module->id]);
        $unit2 = Unit::factory()->create(['module_id' => $module->id]);

        $assignment1 = Assignment::factory()->create(['unit_id' => $unit1->id]);
        $assignment2 = Assignment::factory()->create(['unit_id' => $unit2->id]);

        $submission1 = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $student->id,
            'status' => 'graded'
        ]);

        $submission2 = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment2->id,
            'user_id' => $student->id,
            'status' => 'graded'
        ]);

        AssignmentResult::factory()->create([
            'submission_id' => $submission1->id,
            'competency_status' => 'competent'
        ]);

        AssignmentResult::factory()->create([
            'submission_id' => $submission2->id,
            'competency_status' => 'competent'
        ]);

        $service = app(CourseCompletionService::class);
        $certificate = $service->awardCertificateIfEligible($student, $course);

        $this->assertNotNull($certificate);
        $this->assertInstanceOf(Certificate::class , $certificate);
        $this->assertDatabaseHas('certificates', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    /** @test */
    public function it_does_not_issue_a_certificate_if_not_all_units_are_competent()
    {
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::factory()->create();
        $module = Module::factory()->create(['course_id' => $course->id]);
        $unit1 = Unit::factory()->create(['module_id' => $module->id]);
        $unit2 = Unit::factory()->create(['module_id' => $module->id]);

        $assignment1 = Assignment::factory()->create(['unit_id' => $unit1->id]);
        $assignment2 = Assignment::factory()->create(['unit_id' => $unit2->id]);

        $submission1 = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment1->id,
            'user_id' => $student->id,
            'status' => 'graded'
        ]);

        $submission2 = AssignmentSubmission::factory()->create([
            'assignment_id' => $assignment2->id,
            'user_id' => $student->id,
            'status' => 'graded'
        ]);

        AssignmentResult::factory()->create([
            'submission_id' => $submission1->id,
            'competency_status' => 'competent'
        ]);

        AssignmentResult::factory()->create([
            'submission_id' => $submission2->id,
            'competency_status' => 'not_yet_competent'
        ]);

        $service = app(CourseCompletionService::class);
        $certificate = $service->awardCertificateIfEligible($student, $course);

        $this->assertNull($certificate);
        $this->assertDatabaseMissing('certificates', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }
}
