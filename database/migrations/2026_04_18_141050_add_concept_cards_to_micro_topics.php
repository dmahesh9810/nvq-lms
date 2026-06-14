<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('micro_topics', function (Blueprint $table) {
            // Phase 9: Rich Lesson Flow
            // concept_cards: JSON array of {title, emoji, body} card objects
            $table->json('concept_cards')->nullable()->after('content_html');
            // key_takeaway: 1-line summary shown after cards
            $table->string('key_takeaway')->nullable()->after('concept_cards');
            // estimated_minutes: shown on node before user starts
            $table->unsignedTinyInteger('estimated_minutes')->default(5)->after('key_takeaway');
        });
    }

    public function down(): void
    {
        Schema::table('micro_topics', function (Blueprint $table) {
            $table->dropColumn(['concept_cards', 'key_takeaway', 'estimated_minutes']);
        });
    }
};
