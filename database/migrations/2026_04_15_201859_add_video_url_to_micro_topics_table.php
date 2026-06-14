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
            $table->string('video_url')->nullable()->after('content_html');
        });
    }

    public function down(): void
    {
        Schema::table('micro_topics', function (Blueprint $table) {
            $table->dropColumn('video_url');
        });
    }
};
