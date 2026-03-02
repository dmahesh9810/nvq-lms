<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')
                  ->constrained('assignment_submissions')
                  ->cascadeOnDelete();
            $table->foreignId('assessor_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            // NVQ core competency status
            $table->enum('competency_status', ['competent', 'not_yet_competent']);
            $table->unsignedInteger('marks')->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_results');
    }
};
