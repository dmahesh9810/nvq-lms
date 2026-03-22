<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            // New tracking fields for instructor review
            $table->foreignId('instructor_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            $table->text('instructor_review')->nullable()->after('file_path');
            $table->timestamp('instructor_reviewed_at')->nullable()->after('instructor_review');
        });

        // Modify the status enum safely using raw SQL
        // Replaces 'graded' with 'assessed' and adds 'reviewed'
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assignment_submissions MODIFY COLUMN status ENUM('submitted', 'resubmitted', 'reviewed', 'assessed') DEFAULT 'submitted'");
        } else {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->string('status')->default('submitted');
            });
        }
    }

    public function down(): void
    {
        // Revert enum
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE assignment_submissions MODIFY COLUMN status ENUM('submitted', 'resubmitted', 'graded') DEFAULT 'submitted'");
        } else {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->string('status')->default('submitted');
            });
        }

        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropColumn(['instructor_id', 'instructor_review', 'instructor_reviewed_at']);
        });
    }
};
