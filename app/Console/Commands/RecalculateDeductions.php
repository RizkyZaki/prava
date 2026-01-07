<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Services\SalaryDeductionService;
use Illuminate\Console\Command;

class RecalculateDeductions extends Command
{
    protected $signature = 'deductions:recalculate';

    protected $description = 'Recalculate salary deductions for all attendances';

    public function handle()
    {
        $this->info('Recalculating deductions...');

        $service = new SalaryDeductionService();
        $attendances = Attendance::all();

        $created = 0;
        $skipped = 0;

        foreach ($attendances as $attendance) {
            $result = $service->calculateDeduction($attendance);

            if ($result) {
                $this->line("✓ Attendance #{$attendance->id} ({$attendance->attendance_date}): Rp " . number_format($result->deduction_amount, 0, ',', '.'));
                $created++;
            } else {
                $this->line("- Attendance #{$attendance->id} ({$attendance->attendance_date}): No deduction");
                $skipped++;
            }
        }

        $this->info("\nSummary:");
        $this->info("  Created/Updated: {$created}");
        $this->info("  Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}
