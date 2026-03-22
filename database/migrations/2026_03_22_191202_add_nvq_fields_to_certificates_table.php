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
        Schema::table('certificates', function (Blueprint $table) {
            $table->tinyInteger('nvq_level')->nullable()->default(4)->after('course_id');
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['assessor_id']);
            $table->dropColumn(['nvq_level', 'assessor_id']);
        });
    }
};
