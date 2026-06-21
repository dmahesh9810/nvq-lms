<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;

class SeedNvqQuiz extends Command
{
    protected $signature = 'seed:nvq-quiz';
    protected $description = 'Seed NVQ-style simulation questions';

    public function handle()
    {
        $quiz = Quiz::first();
        
        if (!$quiz) {
            $this->error('No quiz found!');
            return;
        }

        $quiz->update([
            'title' => 'NVQ Level 4 ICT Troubleshooting Simulation',
            'description' => 'A practical simulation test assessing your ability to troubleshoot real-world ICT scenarios according to NVQ competency standards.'
        ]);

        // Delete old factory questions
        $quiz->questions()->delete();

        // Question 1
        $q1 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Simulation Scenario: You are called to a server room where a newly installed NAS (Network Attached Storage) device is inaccessible from the local network. The link lights are active. What is the most appropriate FIRST step according to NVQ standard operating procedures?',
            'question_type' => 'mcq'
        ]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Immediately format the NAS drives to reset the configuration.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Verify the IP configuration (Subnet Mask and Gateway) of the NAS device.', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Replace the network switch as it is likely faulty.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'Perform a hard reset on the main office router.', 'is_correct' => false]);

        // Question 2
        $q2 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Simulation Scenario: A client complains that their laptop battery drains extremely fast even when the laptop is closed. As an ICT Technician, which power management setting in the OS should you inspect first to resolve this issue?',
            'question_type' => 'mcq'
        ]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'Screen brightness settings.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'The "Lid Close Action" (Sleep vs. Hibernate vs. Do Nothing).', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'The graphical processing unit (GPU) clock speed.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'The BIOS boot order configuration.', 'is_correct' => false]);

        // Question 3
        $q3 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Simulation Scenario: You are deploying a web application. The application works locally on port 8000, but when deployed to a Linux cloud server, users get a "Connection Refused" error. The application is running on the server. What is the most likely cause?',
            'question_type' => 'mcq'
        ]);
        QuizOption::create(['question_id' => $q3->id, 'option_text' => 'The server is missing a graphics card (GPU).', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q3->id, 'option_text' => 'The firewall is blocking inbound traffic on port 8000.', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q3->id, 'option_text' => 'The domain name is too long.', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q3->id, 'option_text' => 'The server needs more RAM to process the request.', 'is_correct' => false]);

        $this->info('NVQ Simulation Questions Seeded Successfully!');
    }
}
