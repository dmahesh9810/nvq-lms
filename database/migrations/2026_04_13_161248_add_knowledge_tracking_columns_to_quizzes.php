<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->foreignId('micro_topic_id')->nullable()->constrained('micro_topics')->nullOnDelete();
            $table->integer('difficulty_level')->default(1)->comment('1: Easy, 2: Medium, 3: Hard');
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->integer('time_taken_seconds')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropForeign(['micro_topic_id']);
            $table->dropColumn(['micro_topic_id', 'difficulty_level']);
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropColumn(['time_taken_seconds']);
        });
    }
};
