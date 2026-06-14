<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lesson;
use App\Models\MicroTopic;

// Get the first lesson to attach these topics to (or create a dummy one)
$lesson = Lesson::first();
if (!$lesson) {
    echo "No lessons found to attach data to!";
    exit;
}

// Clear old topics for this lesson to prevent duplicates
MicroTopic::where('lesson_id', $lesson->id)->delete();

$topics = [
    [
        'topic_name' => 'Computer Basic Components',
        'estimated_minutes' => 5,
        'description' => 'Understand the main physical components that make up a standard desktop computer system.',
        'key_takeaway' => 'A computer needs a CPU to think, RAM to hold current tasks, and an SSD/HDD to store things permanently.',
        'concept_cards' => [
            ['emoji' => '🖥️', 'title' => 'What is Hardware?', 'body' => 'Hardware refers to the physical, tangible parts of a computer. If you can touch it, drop it, or break it—it\'s hardware!'],
            ['emoji' => '🧠', 'title' => 'The CPU (Brain)', 'body' => 'The Central Processing Unit (CPU) is the brain of the computer. It handles all instructions and calculations needed to run programs.'],
            ['emoji' => '⚡', 'title' => 'RAM (Short-term)', 'body' => 'Random Access Memory (RAM) temporarily stores data the CPU needs right now. Once the computer turns off, RAM is wiped clean!'],
            ['emoji' => '💾', 'title' => 'Storage (Long-term)', 'body' => 'Hard Drives (HDD) or Solid State Drives (SSD) store your OS, files, and programs permanently, even when powered off.']
        ]
    ],
    [
        'topic_name' => 'Motherboard & Power Supply',
        'estimated_minutes' => 6,
        'description' => 'The foundation that connects all components and the unit that gives them life.',
        'key_takeaway' => 'The PSU provides safe power, while the Motherboard connects everything together so they can communicate.',
        'concept_cards' => [
            ['emoji' => '🏙️', 'title' => 'The Motherboard', 'body' => 'The Motherboard is the main circuit board. It acts like a city grid, connecting the CPU, RAM, Storage, and PCIe cards together.'],
            ['emoji' => '🔌', 'title' => 'PSU', 'body' => 'The PSU converts AC power from your wall outlet into the DC power that computer components need to operate safely.'],
            ['emoji' => '🔋', 'title' => 'CMOS Battery', 'body' => 'A small coin-cell battery on the motherboard that keeps the system clock running and saves BIOS settings when the PC is unplugged.']
        ]
    ],
    [
        'topic_name' => 'Input and Output Devices',
        'estimated_minutes' => 4,
        'description' => 'How humans interact with the computer and how the computer responds.',
        'key_takeaway' => 'Input devices tell the computer what to do. Output devices show you the results!',
        'concept_cards' => [
            ['emoji' => '⌨️', 'title' => 'Input Devices', 'body' => 'Devices used to send data TO the computer. Common examples include Keyboards, Mice, Scanners, and Microphones.'],
            ['emoji' => '🖨️', 'title' => 'Output Devices', 'body' => 'Devices used to receive data FROM the computer. Common examples include Monitors, Printers, and Speakers.'],
            ['emoji' => '🔄', 'title' => 'I/O Ports', 'body' => 'Ports (like USB, HDMI, Audio jacks) act as the doors allowing peripherals to plug directly into the Motherboard.']
        ]
    ],
    [
        'topic_name' => 'Thermal Management',
        'estimated_minutes' => 5,
        'description' => 'Why components get hot and how we prevent them from melting down.',
        'key_takeaway' => 'Proper cooling and thermal paste are absolutely critical to keep hardware alive and running at maximum speed.',
        'concept_cards' => [
            ['emoji' => '🌡️', 'title' => 'Why Cooling?', 'body' => 'CPUs and GPUs generate massive amounts of heat. Without cooling, they will overheat, throttle performance, or permanently break.'],
            ['emoji' => '❄️', 'title' => 'Heat Sinks', 'body' => 'A Heat Sink absorbs heat. Thermal paste is applied between the CPU and Heat Sink to fill microscopic air gaps and transfer heat perfectly.'],
            ['emoji' => '💨', 'title' => 'Air vs Liquid', 'body' => 'Air coolers use fans to blow heat away. AIO Liquid coolers use circulating water to carry heat to a radiator for better efficiency.']
        ]
    ],
    [
        'topic_name' => 'Assembling & BIOS',
        'estimated_minutes' => 7,
        'description' => 'Hardware assembly and fundamental low-level configuration.',
        'key_takeaway' => 'Assemble with anti-static safety, and use the BIOS to tell the PC which hard drive to boot from.',
        'concept_cards' => [
            ['emoji' => '🛠️', 'title' => 'Preventing ESD', 'body' => 'Always ground yourself using an Anti-Static Wrist Strap to prevent Electrostatic Discharge (ESD) from frying sensitive PC parts.'],
            ['emoji' => '🧩', 'title' => 'Seating Components', 'body' => 'The CPU goes into the socket, RAM into DIMM slots. Never force components! They have notches indicating orientation.'],
            ['emoji' => '⚙️', 'title' => 'BIOS / UEFI', 'body' => 'Basic Input/Output System is firmware on the motherboard. It runs POST to verify hardware health before the OS loads.'],
            ['emoji' => '⏱️', 'title' => 'Boot Sequence', 'body' => 'The BIOS determines the Boot Order. To install an OS, you change the order so it reads your Windows Installation USB drive first!']
        ]
    ],
    [
        'topic_name' => 'Operating Systems & Drivers',
        'estimated_minutes' => 8,
        'description' => 'Breathing life into the machine using software correctly.',
        'key_takeaway' => 'Install the OS in a dedicated partition, apply Windows Updates, and install Drivers for maximum performance.',
        'concept_cards' => [
            ['emoji' => '💾', 'title' => 'Installation Media', 'body' => 'An OS is installed using a bootable USB Flash Drive. Tools like "Rufus" are used to make these drives.'],
            ['emoji' => '💽', 'title' => 'Disk Partitions', 'body' => 'During OS setup, physical drives are split into logical "Partitions" (e.g., Drive C: for Windows, Drive D: for personal files).'],
            ['emoji' => '🏎️', 'title' => 'Device Drivers', 'body' => 'Drivers are software translators! They teach the Operating System exactly how to communicate with motherboard hardware at full speed.'],
            ['emoji' => '🔄', 'title' => 'Windows Updates', 'body' => 'Once Windows is running, always run Windows Update immediately. It downloads security patches and finds missing drivers.']
        ]
    ]
];

foreach ($topics as $tp) {
    MicroTopic::create([
        'lesson_id' => $lesson->id,
        'topic_name' => $tp['topic_name'],
        'description' => $tp['description'],
        'estimated_minutes' => $tp['estimated_minutes'],
        'key_takeaway' => $tp['key_takeaway'],
        'concept_cards' => $tp['concept_cards'],
    ]);
}

echo "Successfully seeded " . count($topics) . " micro-topics with flashcards to Lesson ID: " . $lesson->id . "!\n";
