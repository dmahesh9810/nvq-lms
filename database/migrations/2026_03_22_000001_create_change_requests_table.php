<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['course', 'module', 'unit', 'lesson']);
            $table->enum('action', ['update', 'delete']);
            $table->unsignedBigInteger('target_id');
            $table->string('target_title')->nullable(); // snapshot of target title at request time
            $table->json('payload')->nullable();        // proposed field changes for update
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            // Prevent duplicate pending requests for the same resource
            $table->unique(
                ['user_id', 'type', 'target_id', 'action', 'status'],
                'change_requests_no_duplicate_pending'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};
