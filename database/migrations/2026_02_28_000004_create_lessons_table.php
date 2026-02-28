<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     * Lessons are individual learning items within a unit.
     * Supports video (YouTube URL), PDF upload, and rich text content.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url')->nullable(); // YouTube embed URL
            $table->string('pdf_path')->nullable(); // Laravel storage path
            $table->longText('content')->nullable(); // Rich text / HTML
            $table->enum('type', ['video', 'pdf', 'text', 'mixed'])->default('text');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
