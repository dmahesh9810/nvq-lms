<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\MicroTopic;
use Illuminate\Support\Str;

class M01ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Injecting Real NVQ Level 4 (M01) Data...');

        // 1. Ensure instructor exists
        $instructor = User::firstOrCreate([
            'email' => 'instructor1@iqbrave.com'
        ], [
            'name' => 'NVQ Principal Assessor',
            'password' => bcrypt('password'),
            'role' => User::ROLE_INSTRUCTOR,
        ]);

        // 2. Create the Real Course
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'Information & Communication Technology Technician',
            'slug' => 'ict-nvq-level-4',
            'description' => 'Real NVQ Level 4 certification covering K72S004 standards.',
            'thumbnail' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=640',
            'status' => 'published',
        ]);

        // 3. Create the Real Module & Unit
        $module = Module::create([
            'course_id' => $course->id,
            'title' => 'M01 - Maintaining Files & Folders',
            'order' => 1,
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'module_id' => $module->id,
            'title' => 'Use the Computer & Manage Files within Standard Operating Systems',
            'nvq_unit_code' => 'K72S004U01',
            'order' => 1,
            'is_active' => true,
        ]);

        $lesson = \App\Models\Lesson::create([
            'unit_id' => $unit->id,
            'title' => 'Operating System Basics',
            'order' => 1,
            'is_active' => true,
            'type' => 'video'
        ]);


        // 4. Create Gamified Micro-Topics based on Real Study Notes
        $topics = [
            [
                'title' => 'Computer Boot Operations',
                'order' => 1,
                'video_url' => 'https://www.youtube.com/watch?v=6orsmFndx_o',
                'content_html' => '<h3>Start Up Process:</h3><p>UPS → Monitor → CPU → Wait for POST (Power On Self Test) → OS Load → Login Screen.</p><h3>Shut Down Process:</h3><p>Close all Apps safely to avoid Data Loss. Save your files!</p><h3>Power Options:</h3><p><b>Sleep:</b> Saves state to RAM. Fast wake up but requires continuous low power.</p><p><b>Hibernate:</b> Saves state to Hard Disk. Zero power required to maintain state, but slower wake up.</p>',
                'question_text' => 'What is the correct standard sequence for powering on a Desktop Computer?',
                'options' => [
                    ['text' => 'CPU → Monitor → UPS', 'is_correct' => false],
                    ['text' => 'Monitor → UPS → CPU', 'is_correct' => false],
                    ['text' => 'UPS → Monitor → CPU', 'is_correct' => true],
                    ['text' => 'UPS → CPU → Monitor', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'Basic Computer Configuration',
                'order' => 2,
                'video_url' => 'https://www.youtube.com/watch?v=Rb3REhGn6bk',
                'content_html' => '<h3>System Information</h3><p>Use <b>Windows Key + Pause/Break</b> to quickly view Processor type, RAM size, OS Version, and System Type (32/64 bit).</p><h3>Installing Software & Fonts</h3><p>Run installer (.exe/.msi). To install fonts (.ttf/.otf), right click font file and select Install. Warning: Always read License Agreements and avoid Pirated Software!</p><h3>Regional Settings</h3><p>Settings → Time & Language → Region. E.g., Change Sri Lanka region to show Currency as Rs.</p><h3>Keyboard Languages</h3><p>You can add Sinhala Keyboard via Time & Language. Use <b>Windows Key + Space</b> to switch.</p>',
                'question_text' => 'Which quick shortcut directly opens the System Properties to view RAM and CPU details?',
                'options' => [
                    ['text' => 'Windows Key + Break/Pause', 'is_correct' => true],
                    ['text' => 'Ctrl + Shift + Esc', 'is_correct' => false],
                    ['text' => 'Windows Key + Space', 'is_correct' => false],
                    ['text' => 'Alt + F4', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'Windows Desktop Environment',
                'order' => 3,
                'video_url' => 'https://www.youtube.com/watch?v=RhkN2GG0Wt8',
                'content_html' => '<h3>Desktop Components</h3><p>Taskbar: Bottom bar holding open apps and the clock. Recycle Bin: Where deleted files are temporarily stored.</p><h3>Working with Windows</h3><p>Minimize hides the app to the taskbar. Maximize fills the screen. Use <b>Alt + Tab</b> to rapidly switch between open windows.</p>',
                'question_text' => 'Where are deleted files temporarily stored before being permanently wiped?',
                'options' => [
                    ['text' => 'Windows Taskbar', 'is_correct' => false],
                    ['text' => 'C: \\Windows\\Temp', 'is_correct' => false],
                    ['text' => 'Notification Area', 'is_correct' => false],
                    ['text' => 'Recycle Bin', 'is_correct' => true],
                ]
            ],
            [
                'title' => 'Files & Folders Operations',
                'order' => 4,
                'video_url' => 'https://www.youtube.com/watch?v=MXstHoNaSeM',
                'content_html' => '<h3>File System Hierarchy & Types</h3><p>Drive (C:) → Folder → Sub-folder → File.</p><p>Important Extensions: <b>.docx</b> (Word), <b>.xlsx</b> (Excel), <b>.mp4</b> (Video), <b>.txt</b> (Text).</p><h3>Search Filters</h3><p>Use <b>*</b> (Wildcard for any characters) or <b>?</b> (Single character). E.g. search *.jpg to find images.</p><h3>COPY vs MOVE</h3><p><b>COPY (Ctrl+C, Ctrl+V):</b> Duplicate is created.</p><p><b>MOVE (Ctrl+X, Ctrl+V):</b> File is relocated.</p>',
                'question_text' => 'What happens to the original file when you perform a MOVE (Cut & Paste) operation?',
                'options' => [
                    ['text' => 'A duplicate copy is created', 'is_correct' => false],
                    ['text' => 'The original is permanently relocated and removed from source', 'is_correct' => true],
                    ['text' => 'The original file stays in the exact same place untouched', 'is_correct' => false],
                    ['text' => 'File becomes Read-Only', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'File & Folder Attributes',
                'order' => 5,
                'video_url' => 'https://www.youtube.com/watch?v=v9r4XaFB6V4',
                'content_html' => '<h3>Attributes Control Behavior</h3><p><b>Read-Only:</b> File can be viewed but not edited or deleted.</p><p><b>Hidden:</b> Normal viewing will not show the file unless explicitly enabled.</p><p><b>Archive:</b> Used by backup software to know a file has changed.</p><p><b>System:</b> Critical OS Files. Danger to modify!</p>',
                'question_text' => 'Which file attribute prevents a file from being edited or accidentally deleted?',
                'options' => [
                    ['text' => 'Archive Attribute', 'is_correct' => false],
                    ['text' => 'Hidden Attribute', 'is_correct' => false],
                    ['text' => 'Read-Only Attribute', 'is_correct' => true],
                    ['text' => 'System Attribute', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'Compress & Extract Files (ZIP)',
                'order' => 6,
                'video_url' => 'https://www.youtube.com/watch?v=VuUNJy2mTAE',
                'content_html' => '<h3>Why Compress?</h3><p>Reduces file size, saves storage space, allows bypassing Email attachment limits, and binds multiple files into one single archive.</p><p>Common formats: <b>.zip</b> (Built into Windows), .rar, .7z</p>',
                'question_text' => 'Which compression format is supported by default without third-party software on Windows?',
                'options' => [
                    ['text' => '.rar', 'is_correct' => false],
                    ['text' => '.zip', 'is_correct' => true],
                    ['text' => '.7z', 'is_correct' => false],
                    ['text' => '.tar.gz', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'System Maintenance Tools',
                'order' => 7,
                'video_url' => 'https://www.youtube.com/watch?v=b_Y2WHblBHg',
                'content_html' => '<h3>Maintenance Tools</h3><p><b>Disk Defragmentation:</b> Rearranges scattered file blocks to speed up read/write operations. ONLY for HDD. Do NOT defrag SSD drives!</p><p><b>Disk Cleanup:</b> Deletes Temporary files, Recycle bin items, and Browser Cache.</p><h3>Backup Types</h3><p>Full Backup (Everything), Incremental (Changes since last backup).</p>',
                'question_text' => 'Which storage drive type should NEVER be subjected to Disk Defragmentation?',
                'options' => [
                    ['text' => 'HDD Drives', 'is_correct' => false],
                    ['text' => 'USB Flash Drives', 'is_correct' => false],
                    ['text' => 'SSD (Solid State Drives)', 'is_correct' => true],
                    ['text' => 'External Hard Disks', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'Antivirus & Local Security',
                'order' => 8,
                'video_url' => 'https://www.youtube.com/watch?v=aO9J8KCZP7M',
                'content_html' => '<h3>Malware Threats & Spread</h3><p>Ransomware: Encrypts files and demands money. Trojan: Hides as a safe program. Phishing: Fake sites stealing logins.</p><p><b>How they spread:</b> Infected USB drives, malicious email attachments, and unknown websites are primary vectors. NEVER insert unknown USBs.</p><h3>Updating Virus Definitions</h3><p>Always update virus definitions regularly. An outdated database cannot identify brand-new viruses circulating.</p>',
                'question_text' => 'Why is it absolutely necessary to regularly update Virus Definitions?',
                'options' => [
                    ['text' => 'To make the computer run significantly faster', 'is_correct' => false],
                    ['text' => 'To allow the antivirus database to recognize newly created viruses', 'is_correct' => true],
                    ['text' => 'To unlock premium features in the software', 'is_correct' => false],
                    ['text' => 'To backup your local files automatically', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'Text Editing System (Notepad)',
                'order' => 9,
                'video_url' => 'https://www.youtube.com/watch?v=NdG6z-kMRj8',
                'content_html' => '<h3>Basic Text Editor</h3><p>Notepad is the default built-in simple text editor on Windows.</p><h3>Universal Shortcuts</h3><p>Ctrl + A: Select All<br>Ctrl + C: Copy<br>Ctrl + V: Paste<br>Ctrl + Z: Undo<br>Ctrl + S: Save file</p>',
                'question_text' => 'Which keyboard shortcut is universally used to SELECT ALL text in an editor?',
                'options' => [
                    ['text' => 'Ctrl + V', 'is_correct' => false],
                    ['text' => 'Ctrl + X', 'is_correct' => false],
                    ['text' => 'Ctrl + S', 'is_correct' => false],
                    ['text' => 'Ctrl + A', 'is_correct' => true],
                ]
            ],
            [
                'title' => 'Cyber Laws of Sri Lanka',
                'order' => 10,
                'video_url' => 'https://www.youtube.com/watch?v=6rMGXnI-Kpg',
                'content_html' => '<h3>Sri Lanka Computer Laws</h3><p><b>Computer Crimes Act No. 24 of 2007:</b> Handles Hacking, Unauthorized Access, and organized Cyber Crimes.</p><p><b>Intellectual Property Act No. 36 of 2003:</b> Governs software copyrights. Beware: Using pirated software is ILLEGAL under this act!</p>',
                'question_text' => 'Using pirated or unauthorized software in Sri Lanka violates which major legal act?',
                'options' => [
                    ['text' => 'Computer Crimes Act No. 24', 'is_correct' => false],
                    ['text' => 'Intellectual Property Act No. 36', 'is_correct' => true],
                    ['text' => 'Payment Devices Frauds Act No. 30', 'is_correct' => false],
                    ['text' => 'Sri Lanka Telecommunications Act', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'Worker Attitudes & Behaviors',
                'order' => 11,
                'video_url' => 'https://www.youtube.com/watch?v=xlSTKhCCxqQ',
                'content_html' => '<h3>Professional Traits</h3><p>NCS highly prioritizes workplace attitude. Key traits include:</p><p><b>Responsibility:</b> Completing tasks fully without excuses.</p><p><b>Punctuality:</b> Arriving on time and meeting deadlines.</p><p><b>Integrity & Honesty:</b> Truthfulness, maintaining workplace secrets, and strict absence of cheating.</p>',
                'question_text' => 'What does "Punctuality" firmly refer to within a professional NVQ workplace setting?',
                'options' => [
                    ['text' => 'Arriving on time and meeting deadlines consistently', 'is_correct' => true],
                    ['text' => 'Taking the initiative to help others with tasks', 'is_correct' => false],
                    ['text' => 'Operating completely independently without instructions', 'is_correct' => false],
                    ['text' => 'Being totally truthful and avoiding all corruption or cheating', 'is_correct' => false],
                ]
            ],
            [
                'title' => 'M01 Final Checkpoint (Exam)',
                'order' => 12,
                'content_html' => '<h2>🏆 Final Mega Quiz</h2><p>You have reached the end of Module 01! Take this final exam consisting of 5 random questions. Test your complete knowledge of files, folders, shortcuts, and cyber laws before receiving your M01 Badge!</p>',
                'questions' => [
                    [
                        'question_text' => 'Which Storage Drive type should NEVER be defragmented?',
                        'options' => [
                            ['text' => 'HDD (Hard Disk Drive)', 'is_correct' => false],
                            ['text' => 'SSD (Solid State Drive)', 'is_correct' => true],
                            ['text' => 'External USB Drives', 'is_correct' => false],
                            ['text' => 'Cloud Storage', 'is_correct' => false],
                        ]
                    ],
                    [
                        'question_text' => 'Which of the following is the standard shortcut to permanently delete a file (Bypassing the Recycle Bin)?',
                        'options' => [
                            ['text' => 'Ctrl + Delete', 'is_correct' => false],
                            ['text' => 'Alt + Delete', 'is_correct' => false],
                            ['text' => 'Shift + Delete', 'is_correct' => true],
                            ['text' => 'Windows + Delete', 'is_correct' => false],
                        ]
                    ],
                    [
                        'question_text' => 'Under Sri Lankan Law, hacking and unauthorized computer access is fundamentally governed by:',
                        'options' => [
                            ['text' => 'Intellectual Property Act', 'is_correct' => false],
                            ['text' => 'Payment Devices Frauds Act', 'is_correct' => false],
                            ['text' => 'Sri Lanka Telecommunications Act', 'is_correct' => false],
                            ['text' => 'Computer Crimes Act No. 24', 'is_correct' => true],
                        ]
                    ],
                    [
                        'question_text' => 'Which wildcards are correctly used in Windows File Explorer search functions?',
                        'options' => [
                            ['text' => '* (Asterisk) and ? (Question Mark)', 'is_correct' => true],
                            ['text' => '@ (At) and # (Hash)', 'is_correct' => false],
                            ['text' => '% (Percent) and $ (Dollar)', 'is_correct' => false],
                            ['text' => '> (Greater than) and < (Less than)', 'is_correct' => false],
                        ]
                    ],
                    [
                        'question_text' => 'What is the primary function of the "Archive" file attribute?',
                        'options' => [
                            ['text' => 'It automatically compresses the file into a ZIP.', 'is_correct' => false],
                            ['text' => 'It hides the file from the operating system.', 'is_correct' => false],
                            ['text' => 'It marks files that have been modified since the last Backup.', 'is_correct' => true],
                            ['text' => 'It encrypts the file against malicious Trojans.', 'is_correct' => false],
                        ]
                    ]
                ]
            ],
            [
                'title' => '🛠️ Practical Task: Folder & ZIP',
                'order' => 13,
                'is_practical' => true,
                'grading_rules' => [
                    'required_folder' => 'NVQ_Exam',
                    'required_file' => 'MyDetails.txt',
                ],
                'content_html' => '<h2>⚙️ Practical Assignment</h2><p>ඔයාගේ ඉගෙන ගත් දේ ප්‍රායෝගිකව prove කිරීමේ.
Computer එකේ Desktop එකේ "NVQ_Exam" Folder හදලා, "MyDetails.txt" Text File ඒ ඇතුළේ save කරලා ZIP කරන්න.</p>',
            ],
        ];

        foreach ($topics as $index => $topicData) {
            $mt = MicroTopic::create([
                'lesson_id' => $lesson->id,
                'topic_name' => $topicData['title'],
                'description' => $topicData['content_html'],
                'video_url' => $topicData['video_url'] ?? null,
                'order' => $topicData['order'],
                'xp_reward' => 20,
                'is_practical' => $topicData['is_practical'] ?? false,
                'grading_rules' => isset($topicData['grading_rules'])
                    ? json_encode($topicData['grading_rules'])
                    : null,
            ]);

            if (isset($topicData['questions'])) {
                foreach ($topicData['questions'] as $qData) {
                    $question = \App\Models\MicroQuizQuestion::create([
                        'micro_topic_id' => $mt->id,
                        'question_text' => $qData['question_text'],
                    ]);

                    foreach ($qData['options'] as $opt) {
                        \App\Models\MicroQuizOption::create([
                            'micro_quiz_question_id' => $question->id,
                            'option_text' => $opt['text'],
                            'is_correct' => $opt['is_correct'],
                        ]);
                    }
                }
            } else if (!isset($topicData['is_practical']) || !$topicData['is_practical']) {
                // Add single Micro Quiz Question (only for non-practical nodes)
                $question = \App\Models\MicroQuizQuestion::create([
                    'micro_topic_id' => $mt->id,
                    'question_text' => $topicData['question_text'],
                ]);

                foreach ($topicData['options'] as $opt) {
                    \App\Models\MicroQuizOption::create([
                        'micro_quiz_question_id' => $question->id,
                        'option_text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                    ]);
                }
            }
        }

        $this->command->info('M01 Module Micro-Topics Integrated Successfully.');
    }
}
