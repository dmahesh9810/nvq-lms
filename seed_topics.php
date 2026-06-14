<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$topics = ['Web Development', 'Database Management', 'UI/UX Design', 'API Integration', 'Software Security'];
$topicIds = [];
foreach($topics as $t) {
    // Check if exists first to avoid duplicates if run multiple times
    $mt = \App\Models\MicroTopic::firstOrCreate(['topic_name' => $t], ['lesson_id' => 1]);
    $topicIds[] = $mt->id;
}

$questions = \App\Models\QuizQuestion::all();
foreach($questions as $q) {
    if (!$q->micro_topic_id) {
        $q->micro_topic_id = $topicIds[array_rand($topicIds)];
        $q->save();
    }
}

// Manually trigger sync for user ID 1 (Assuming Mahesh is user 1)
$userId = 1; 
app(\App\Http\Controllers\Api\KnowledgeTrackerController::class)->syncMastery($userId);

echo "Micro topics generated and synced successfully.";
