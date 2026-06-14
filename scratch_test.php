<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $course = \App\Models\Course::with(['modules.units.lessons.microTopics'])->first();
    echo json_encode($course);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
