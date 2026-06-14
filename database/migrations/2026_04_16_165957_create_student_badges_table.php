<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('badge_key');          // e.g. 'first_step', 'on_fire'
            $table->string('badge_name');          // e.g. '🌱 First Step'
            $table->string('badge_emoji', 10)->default('🏅');
            $table->string('badge_description')->nullable();
            $table->timestamp('earned_at')->useCurrent();
            $table->timestamps();

            // Prevent duplicate badges per user
            $table->unique(['user_id', 'badge_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_badges');
    }
};
