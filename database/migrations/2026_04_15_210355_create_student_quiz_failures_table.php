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
        Schema::create('student_quiz_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('micro_topic_id')->constrained()->onDelete('cascade');
            $table->integer('fail_count')->default(1);
            $table->timestamp('last_failed_at')->nullable();
            $table->timestamp('next_review_at')->nullable(); // spaced repetition schedule
            $table->timestamps();

            // One record per user per topic — updated on each fail
            $table->unique(['user_id', 'micro_topic_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_quiz_failures');
    }
};
