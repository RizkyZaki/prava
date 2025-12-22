<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;

echo "=== FIXING WORK DURATION ===\n\n";

$attendance = Attendance::find(1);

if ($attendance) {
    echo "Before:\n";
    echo "- Check-in: " . $attendance->check_in->format('Y-m-d H:i:s') . "\n";
    echo "- Check-out: " . $attendance->check_out->format('Y-m-d H:i:s') . "\n";
    echo "- Work Duration: " . ($attendance->work_duration ?? 'NULL') . " minutes\n\n";

    // Trigger save to recalculate
    $attendance->save();

    // Refresh from database
    $attendance->refresh();

    echo "After:\n";
    echo "- Check-in: " . $attendance->check_in->format('Y-m-d H:i:s') . "\n";
    echo "- Check-out: " . $attendance->check_out->format('Y-m-d H:i:s') . "\n";
    echo "- Work Duration: " . $attendance->work_duration . " minutes\n";
    echo "- Formatted: " . $attendance->formatted_work_duration . "\n";
    echo "\n✅ Work duration recalculated successfully!\n";
} else {
    echo "❌ Attendance ID 1 not found!\n";
}
