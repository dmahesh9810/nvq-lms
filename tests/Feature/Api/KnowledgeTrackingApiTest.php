<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\StudentConceptMastery;
use App\Models\MicroTopic;
use App\Models\Lesson;

class KnowledgeTrackingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_fetch_knowledge_radar()
    {
        $user = User::factory()->create();

        $lesson = Lesson::factory()->create();
        $topic = MicroTopic::create(['lesson_id' => $lesson->id, 'topic_name' => 'Math Basics']);
        StudentConceptMastery::create([
            'student_id' => $user->id,
            'micro_topic_id' => $topic->id,
            'mastery_percentage' => 40,
            'total_attempts' => 5
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/student/knowledge-radar');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'chart' => [
                'labels',
                'data'
            ]
        ]);
    }

    public function test_student_can_fetch_weaknesses()
    {
        $user = User::factory()->create();

        $lesson = Lesson::factory()->create();
        $topic = MicroTopic::create(['lesson_id' => $lesson->id, 'topic_name' => 'Math Basics']);
        StudentConceptMastery::create([
            'student_id' => $user->id,
            'micro_topic_id' => $topic->id,
            'mastery_percentage' => 30, // less than 50
            'total_attempts' => 2
        ]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/student/weaknesses');

        $response->assertStatus(200);
        $response->assertJsonFragment(['mastery_percentage' => 30]);
    }
}
