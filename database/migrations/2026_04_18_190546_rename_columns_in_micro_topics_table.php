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
            $table->renameColumn('unit_id', 'lesson_id');
            $table->renameColumn('title', 'topic_name');
            $table->renameColumn('content_html', 'description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('micro_topics', function (Blueprint $table) {
            $table->renameColumn('lesson_id', 'unit_id');
            $table->renameColumn('topic_name', 'title');
            $table->renameColumn('description', 'content_html');
        });
    }
};
