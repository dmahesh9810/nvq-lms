<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$topic = \App\Models\MicroTopic::first();
if ($topic) {
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
    
    $topic->update([
        'concept_cards' => json_encode($cards),
        'key_takeaway' => 'Swipeable cards prepare you for the ultimate quiz challenge by chunking data perfectly.',
        'estimated_minutes' => 3
    ]);
    
    echo "Successfully injected concept cards into Topic ID: " . $topic->id . "\n";
} else {
    echo "No topics found in the DB to attach cards to.\n";
}
