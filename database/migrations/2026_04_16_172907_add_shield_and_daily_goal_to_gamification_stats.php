<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_gamification_stats', function (Blueprint $table) {
            // ── Phase 7C: Streak Shield ────────────────────────────────
            $table->boolean('streak_shield_active')->default(false)->after('longest_streak');
            $table->timestamp('streak_shield_used_at')->nullable()->after('streak_shield_active');

            // ── Phase 7D: Daily Goal ───────────────────────────────────
            $table->unsignedTinyInteger('daily_goal')->default(3)->after('streak_shield_used_at');
            // 1=Easy(1 node), 3=Medium(3 nodes), 5=Hard(5 nodes)
            $table->unsignedSmallInteger('daily_nodes_today')->default(0)->after('daily_goal');
            $table->date('daily_goal_last_reset')->nullable()->after('daily_nodes_today');
        });
    }

    public function down(): void
    {
        Schema::table('student_gamification_stats', function (Blueprint $table) {
            $table->dropColumn([
                'streak_shield_active', 'streak_shield_used_at',
                'daily_goal', 'daily_nodes_today', 'daily_goal_last_reset',
            ]);
        });
    }
};
