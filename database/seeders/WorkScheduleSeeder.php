<?php

namespace Database\Seeders;

use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WorkSchedule::create([
            'name' => 'Default Schedule',
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'late_tolerance_minutes' => 15,
            'early_leave_tolerance_minutes' => 15,
            'daily_work_hours' => 8,
            'hourly_deduction_rate' => 5, // 5% per hour
            'is_default' => true,
            'is_active' => true,
            'description' => 'Jadwal kerja standar: 08:00 - 16:00 dengan istirahat 12:00 - 13:00',
        ]);

        WorkSchedule::create([
            'name' => 'Shift Pagi',
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
            'break_start' => '11:00:00',
            'break_end' => '12:00:00',
            'late_tolerance_minutes' => 10,
            'early_leave_tolerance_minutes' => 10,
            'daily_work_hours' => 8,
            'hourly_deduction_rate' => 5,
            'is_default' => false,
            'is_active' => true,
            'description' => 'Shift pagi: 07:00 - 15:00 dengan istirahat 11:00 - 12:00',
        ]);

        WorkSchedule::create([
            'name' => 'Shift Siang',
            'start_time' => '13:00:00',
            'end_time' => '21:00:00',
            'break_start' => '17:00:00',
            'break_end' => '18:00:00',
            'late_tolerance_minutes' => 10,
            'early_leave_tolerance_minutes' => 10,
            'daily_work_hours' => 8,
            'hourly_deduction_rate' => 5,
            'is_default' => false,
            'is_active' => true,
            'description' => 'Shift siang: 13:00 - 21:00 dengan istirahat 17:00 - 18:00',
        ]);
    }
}
