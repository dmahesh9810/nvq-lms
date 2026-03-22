<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete()->after('instructor_reviewed_at');
        });

        // Use raw SQL to safely change an ENUM column into a VARCHAR, but skip for SQLite (used in testing)
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assignment_submissions MODIFY COLUMN status VARCHAR(50) DEFAULT 'submitted'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropForeign(['assessor_id']);
            $table->dropColumn('assessor_id');
        });

        // Reverting back to ENUM
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assignment_submissions MODIFY COLUMN status ENUM('submitted', 'resubmitted', 'reviewed', 'assessed') DEFAULT 'submitted'");
        }
    }
};
