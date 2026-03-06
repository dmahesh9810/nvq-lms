<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = Unit::with('module')->get();

        foreach ($units as $unit) {
            $moduleTitle = $unit->module->title;

            // Generate contextual lesson names based on the parent module
            $lessonTitles = $this->getLessonTitlesForModule($moduleTitle);

            foreach ($lessonTitles as $index => $title) {
                Lesson::firstOrCreate(
                [
                    'unit_id' => $unit->id,
                    'title' => $title,
                ],
                [
                    'content' => '<p>In this lesson, we will cover the essential concepts and practical applications of <strong>' . $title . '</strong>. This is a critical component of the National Vocational Qualification Level 4 standard.</p><p>Please ensure you complete the reading material before attempting the unit quiz.</p>',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'order' => $index + 1,
                    'is_active' => true,
                ]
                );
            }
        }
    }

    private function getLessonTitlesForModule(string $moduleTitle): array
    {
        // Specialized NVQ Lesson mapping for hardware technicians
        if (str_contains(strtolower($moduleTitle), 'electricity and electronics')) {
            return ['Ohm\'s Law and AC/DC Current', 'Using a Multimeter Safely', 'Resistors, Capacitors, and Diodes', 'Identifying Short Circuits', 'Basic Soldering Techniques'];
        }
        if (str_contains(strtolower($moduleTitle), 'computer fundamentals')) {
            return ['History of Computing', 'Data Representation and Binary', 'The Boot Process Explained', 'Input and Output Devices', 'Storage Media Deep Dive'];
        }
        if (str_contains(strtolower($moduleTitle), 'functions of operating systems')) {
            return ['Kernel vs User Space', 'File System Architectures (NTFS/ext4)', 'Task Scheduling and Memory Management', 'CLI vs GUI Environments', 'Device Drivers and IRQ'];
        }
        if (str_contains(strtolower($moduleTitle), 'components of the computer system')) {
            return ['Motherboard Form Factors', 'CPU Architectures (x86 vs ARM)', 'RAM Types and Speeds', 'Power Supply Ratings (80 Plus)', 'Cooling Systems and Thermal Paste'];
        }
        if (str_contains(strtolower($moduleTitle), 'cyber security')) {
            return ['Threat Landscapes and Malware', 'Firewall Configurations', 'Encryption Standards', 'Social Engineering Attacks', 'Securing the BIOS/UEFI'];
        }
        if (str_contains(strtolower($moduleTitle), 'assemble computers')) {
            return ['Workspace Preparation and ESD', 'Motherboard and CPU Seating', 'RAM and Storage Installation', 'Front Panel Header Wiring', 'First Boot and POST Codes'];
        }
        if (str_contains(strtolower($moduleTitle), 'install and configure computer software')) {
            return ['Windows 11 Clean Installation', 'Linux Desktop Deployment', 'Driver Updates and Rollbacks', 'Automating Software Deployments', 'OS Cloning and Imaging Tools'];
        }
        if (str_contains(strtolower($moduleTitle), 'network systems')) {
            return ['OSI Model Fundamentals', 'IPv4 Addressing and Subnetting', 'Crimping CAT6 Cables (T568B)', 'Configuring SOHO Routers', 'Troubleshooting DNS and DHCP'];
        }

        // Generic fallback for any other module
        return [
            'Introduction to ' . $moduleTitle,
            'Core Concepts and Terminology',
            'Practical Workshop Demonstration',
            'Common Issues and Troubleshooting',
            'Review and Assessment Preparation'
        ];
    }
}
