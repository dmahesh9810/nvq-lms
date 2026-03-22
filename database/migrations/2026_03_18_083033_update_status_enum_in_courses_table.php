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
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE courses MODIFY COLUMN status ENUM('draft', 'pending', 'published', 'rejected', 'archived') DEFAULT 'draft'");
        } else {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('courses', function (Blueprint $table) {
                $table->string('status')->default('draft');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE courses MODIFY COLUMN status ENUM('draft', 'published', 'archived') DEFAULT 'draft'");
        } else {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('status');
            });
            Schema::table('courses', function (Blueprint $table) {
                $table->string('status')->default('draft');
            });
        }
    }
};

