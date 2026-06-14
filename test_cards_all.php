<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$topics = \App\Models\MicroTopic::all();
$count = 0;
foreach ($topics as $topic) {
    $cards = [
        [
            'title' => 'Welcome to Flashcards! 🚀',
            'body' => 'This is the new swipeable Concept Cards UI. You can swipe left and right to navigate through bite-sized knowledge.',
            'emoji' => '🔥'
        ],
        [
            'title' => 'Micro-learning',
            'body' => 'Instead of long paragraphs, learning is chunked into small cards. It increases engagement immensely.',
            'emoji' => '🧠'
        ],
        [
            'title' => 'Ready for the Quiz?',
            'body' => 'Swipe to the next screen to see the Key Takeaway and begin your assessment.',
            'emoji' => '🎯'
        ]
    ];
    
    // Explicitly using the array directly now that $casts is added
    $topic->update([
        'concept_cards' => $cards,
        'key_takeaway' => 'Swipeable cards prepare you for the ultimate quiz challenge by chunking data perfectly.',
        'estimated_minutes' => 3
    ]);
    $count++;
}
echo "Successfully fixed concept cards for " . $count . " Micro Topics.\n";
