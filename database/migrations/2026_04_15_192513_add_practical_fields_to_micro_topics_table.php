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
        Schema::table('micro_topics', function (Blueprint $table) {
            $table->boolean('is_practical')->default(false)->after('content_html');
            $table->json('grading_rules')->nullable()->after('is_practical');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('micro_topics', function (Blueprint $table) {
            $table->dropColumn(['is_practical', 'grading_rules']);
        });
    }
};
