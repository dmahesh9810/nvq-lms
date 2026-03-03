<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CertificateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'course_id' => \App\Models\Course::factory(),
            'certificate_number' => \App\Models\Certificate::generateNumber(),
            'issued_at' => now(),
            'status' => 'active',
        ];
    }
}
