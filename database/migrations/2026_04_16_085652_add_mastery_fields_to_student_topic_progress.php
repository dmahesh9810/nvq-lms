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
        Schema::table('student_topic_progress', function (Blueprint $table) {
            $table->unsignedTinyInteger('mastery_score')->default(0)->after('attempts');       // 0-100
            $table->unsignedTinyInteger('correct_streak')->default(0)->after('mastery_score'); // consecutive correct
            $table->timestamp('mastered_at')->nullable()->after('correct_streak');             // when >= 80
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_topic_progress', function (Blueprint $table) {
            $table->dropColumn(['mastery_score', 'correct_streak', 'mastered_at']);
        });
    }
};
