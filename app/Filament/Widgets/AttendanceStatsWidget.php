<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AttendanceStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected ?string $pollingInterval = '60s';

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');

        if ($isSuperAdmin) {
            return $this->getSuperAdminStats();
        } else {
            return $this->getUserStats();
        }
    }

    protected function getSuperAdminStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now();

        // Today's attendance
        $todayAttendance = Attendance::whereDate('attendance_date', $today)->count();
        $totalEmployees = \App\Models\User::count();

        // This month stats
        $thisMonthPresent = Attendance::whereYear('attendance_date', $thisMonth->year)
            ->whereMonth('attendance_date', $thisMonth->month)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $thisMonthLate = Attendance::whereYear('attendance_date', $thisMonth->year)
            ->whereMonth('attendance_date', $thisMonth->month)
            ->where('status', 'late')
            ->count();

        $thisMonthLeave = Attendance::whereYear('attendance_date', $thisMonth->year)
            ->whereMonth('attendance_date', $thisMonth->month)
            ->where('status', 'leave')
            ->count();

        return [
            Stat::make('Hadir Hari Ini', $todayAttendance . ' / ' . $totalEmployees)
                ->description('Total karyawan yang sudah absen')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Total Kehadiran Bulan Ini', $thisMonthPresent)
                ->description('Termasuk yang terlambat')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Keterlambatan Bulan Ini', $thisMonthLate)
                ->description('Total keterlambatan')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Cuti Bulan Ini', $thisMonthLeave)
                ->description('Total yang sedang cuti')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('gray'),
        ];
    }

    protected function getUserStats(): array
    {
        $user = auth()->user();
        $thisMonth = Carbon::now();

        // Check today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', Carbon::today())
            ->first();

        // This month stats
        $thisMonthPresent = Attendance::where('user_id', $user->id)
            ->whereYear('attendance_date', $thisMonth->year)
            ->whereMonth('attendance_date', $thisMonth->month)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $thisMonthLate = Attendance::where('user_id', $user->id)
            ->whereYear('attendance_date', $thisMonth->year)
            ->whereMonth('attendance_date', $thisMonth->month)
            ->where('status', 'late')
            ->count();

        $workingDays = $this->getWorkingDaysThisMonth();
        $attendanceRate = $workingDays > 0 ? round(($thisMonthPresent / $workingDays) * 100, 1) : 0;

        // Average work hours this month
        $avgWorkHours = Attendance::where('user_id', $user->id)
            ->whereYear('attendance_date', $thisMonth->year)
            ->whereMonth('attendance_date', $thisMonth->month)
            ->whereNotNull('work_duration')
            ->avg('work_duration');

        $avgWorkHoursFormatted = $avgWorkHours
            ? sprintf('%.1f jam', $avgWorkHours / 60)
            : '0 jam';

        return [
            Stat::make('Status Hari Ini', $todayAttendance ? $todayAttendance->status_label : 'Belum Absen')
                ->description(
                    $todayAttendance && $todayAttendance->check_in
                        ? 'Masuk: ' . $todayAttendance->check_in->format('H:i')
                        : 'Belum check-in'
                )
                ->descriptionIcon('heroicon-m-clock')
                ->color($todayAttendance ? $todayAttendance->status_color : 'gray'),

            Stat::make('Kehadiran Bulan Ini', $thisMonthPresent . ' / ' . $workingDays)
                ->description($attendanceRate . '% tingkat kehadiran')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Keterlambatan', $thisMonthLate)
                ->description('Total keterlambatan bulan ini')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($thisMonthLate > 0 ? 'warning' : 'success'),

            Stat::make('Rata-rata Jam Kerja', $avgWorkHoursFormatted)
                ->description('Per hari bulan ini')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }

    protected function getWorkingDaysThisMonth(): int
    {
        $now = Carbon::now();
        $totalDays = $now->daysInMonth;
        $workingDays = 0;

        for ($day = 1; $day <= $totalDays; $day++) {
            $date = Carbon::create($now->year, $now->month, $day);
            if (!$date->isWeekend() && !$date->isFuture()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }
}
