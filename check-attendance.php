<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Attendance;
use App\Models\User;

echo "=== CHECKING ATTENDANCE DATA ===\n\n";

// Check all attendances
echo "All Attendances in Database:\n";
$attendances = Attendance::with('user')->get();
foreach ($attendances as $att) {
    echo sprintf(
        "- ID %d: User #%d (%s), Date: %s, Check-in: %s, Check-out: %s, Duration: %s min\n",
        $att->id,
        $att->user_id,
        $att->user->name ?? 'N/A',
        $att->attendance_date->format('Y-m-d'),
        $att->check_in ? $att->check_in->format('H:i') : 'NULL',
        $att->check_out ? $att->check_out->format('H:i') : 'NULL',
        $att->work_duration ?? 'NULL'
    );
}

echo "\n=== USERS IN SYSTEM ===\n";
$users = User::all();
foreach ($users as $user) {
    echo sprintf("- ID %d: %s (%s)\n", $user->id, $user->name, $user->email);
}

echo "\n=== CHECKING DECEMBER 2025 ===\n";
$decemberAttendances = Attendance::whereYear('attendance_date', 2025)
    ->whereMonth('attendance_date', 12)
    ->with('user')
    ->get();

echo "Total attendances in December 2025: " . $decemberAttendances->count() . "\n";
foreach ($decemberAttendances as $att) {
    echo sprintf(
        "- %s: User %s\n",
        $att->attendance_date->format('d/m/Y'),
        $att->user->name ?? 'N/A'
    );
}
