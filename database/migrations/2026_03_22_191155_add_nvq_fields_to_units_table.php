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
        Schema::table('units', function (Blueprint $table) {
            $table->string('nvq_unit_code')->nullable()->after('description');
            $table->text('learning_outcomes')->nullable()->after('nvq_unit_code');
            $table->text('performance_criteria')->nullable()->after('learning_outcomes');
            $table->text('assessment_criteria')->nullable()->after('performance_criteria');
            $table->tinyInteger('nvq_level')->nullable()->default(4)->after('assessment_criteria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn([
                'nvq_unit_code',
                'learning_outcomes',
                'performance_criteria',
                'assessment_criteria',
                'nvq_level'
            ]);
        });
    }
};
