<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alter micro_topics to link to units directly and add Gamification columns
        Schema::table('micro_topics', function (Blueprint $table) {
            $table->dropForeign(['lesson_id']);
            $table->dropColumn(['lesson_id', 'topic_name', 'description']);

            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('title');
            $table->text('content_html')->nullable();
            $table->integer('order')->default(1);
            $table->integer('xp_reward')->default(10);
        });

        // Create micro_quiz_questions
        Schema::create('micro_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('micro_topic_id')->constrained('micro_topics')->cascadeOnDelete();
            $table->string('question_text');
            $table->timestamps();
        });

        // Create micro_quiz_options
        Schema::create('micro_quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('micro_quiz_question_id')->constrained('micro_quiz_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('micro_quiz_options');
        Schema::dropIfExists('micro_quiz_questions');

        Schema::table('micro_topics', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'title', 'content_html', 'order', 'xp_reward']);

            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->string('topic_name');
            $table->text('description')->nullable();
        });
    }
};
