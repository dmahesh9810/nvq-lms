<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->string('result')->nullable()->after('score');
            $table->dateTime('started_at')->nullable()->after('result');
            $table->dateTime('completed_at')->nullable()->after('started_at');

            $table->dropColumn(['percentage', 'passed', 'attempted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->dateTime('attempted_at')->default(now());

            $table->dropColumn(['result', 'started_at', 'completed_at']);
        });
    }
};
