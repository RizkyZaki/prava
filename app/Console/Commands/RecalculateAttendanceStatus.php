<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecalculateAttendanceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:recalculate-status {--month= : Month to recalculate (YYYY-MM format)} {--all : Recalculate all attendance records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate attendance status based on check-in time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Attendance::whereNotNull('check_in');

        if ($this->option('all')) {
            $this->info('Recalculating status for ALL attendance records...');
        } elseif ($month = $this->option('month')) {
            try {
                $date = Carbon::createFromFormat('Y-m', $month);
                $query->whereYear('attendance_date', $date->year)
                    ->whereMonth('attendance_date', $date->month);
                $this->info("Recalculating status for {$date->format('F Y')}...");
            } catch (\Exception $e) {
                $this->error('Invalid month format. Use YYYY-MM (e.g., 2026-01)');
                return 1;
            }
        } else {
            // Default to current month
            $now = Carbon::now();
            $query->whereYear('attendance_date', $now->year)
                ->whereMonth('attendance_date', $now->month);
            $this->info("Recalculating status for current month ({$now->format('F Y')})...");
        }

        $attendances = $query->get();
        $total = $attendances->count();
        $updated = 0;
        $lateCount = 0;
        $presentCount = 0;

        if ($total === 0) {
            $this->warn('No attendance records found to recalculate.');
            return 0;
        }

        $this->info("Found {$total} attendance records to check.");

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        foreach ($attendances as $attendance) {
            $oldStatus = $attendance->status;
            $checkInTime = Carbon::parse($attendance->check_in);
            $scheduledStart = $checkInTime->copy()->setTime(Attendance::WORK_START_HOUR, 0, 0);
            $lateThreshold = $scheduledStart->copy()->addMinutes(Attendance::LATE_THRESHOLD_MINUTES);

            $newStatus = $checkInTime->greaterThan($lateThreshold) ? 'late' : 'present';

            if ($oldStatus !== $newStatus) {
                $attendance->status = $newStatus;
                $attendance->saveQuietly(); // Save without triggering events
                $updated++;
            }

            if ($newStatus === 'late') {
                $lateCount++;
            } elseif ($newStatus === 'present') {
                $presentCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Recalculation complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total records checked', $total],
                ['Records updated', $updated],
                ['Present', $presentCount],
                ['Late', $lateCount],
            ]
        );

        return 0;
    }
}
