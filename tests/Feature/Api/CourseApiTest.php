<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;

class CourseApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_courses()
    {
        $user = User::factory()->create(['role' => 'admin']);
        Course::factory(3)->create(['status' => 'active']);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/courses');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => []]);
    }

    public function test_unauthenticated_user_cannot_fetch_courses()
    {
        $response = $this->getJson('/api/courses');
        $response->assertStatus(401);
    }
}
