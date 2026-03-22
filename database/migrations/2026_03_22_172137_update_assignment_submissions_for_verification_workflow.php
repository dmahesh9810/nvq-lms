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
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->string('instructor_competency_status')->nullable()->after('instructor_id');
            $table->text('assessor_verification_note')->nullable()->after('assessor_id');
            $table->timestamp('verified_at')->nullable()->after('assessor_verification_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropColumn(['instructor_competency_status', 'assessor_verification_note', 'verified_at']);
        });
    }
};
