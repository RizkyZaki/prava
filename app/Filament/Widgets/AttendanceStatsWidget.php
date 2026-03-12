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
            Stat::make(__('widget.stat.present_today'), $todayAttendance . ' / ' . $totalEmployees)
                ->description(__('widget.stat.desc.present_today'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make(__('widget.stat.present_this_month'), $thisMonthPresent)
                ->description(__('widget.stat.desc.present_this_month'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make(__('widget.stat.late_this_month'), $thisMonthLate)
                ->description(__('widget.stat.desc.late_this_month'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make(__('widget.stat.leave_this_month'), $thisMonthLeave)
                ->description(__('widget.stat.desc.leave_this_month'))
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
            ? sprintf('%.1f %s', $avgWorkHours / 60, __('widget.hours_abbr'))
            : '0 ' . __('widget.hours_abbr');

        return [
            Stat::make(__('widget.stat.today_status'), $todayAttendance ? $todayAttendance->status_label : __('widget.stat.not_checked_in'))
                ->description(
                    $todayAttendance && $todayAttendance->check_in
                        ? __('widget.stat.checkin_time') . ': ' . $todayAttendance->check_in->format('H:i')
                        : __('widget.stat.not_checked_in_yet')
                )
                ->descriptionIcon('heroicon-m-clock')
                ->color($todayAttendance ? $todayAttendance->status_color : 'gray'),

            Stat::make(__('widget.stat.attendance_this_month'), $thisMonthPresent . ' / ' . $workingDays)
                ->description($attendanceRate . '% ' . __('widget.stat.desc.attendance_rate_suffix'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make(__('widget.stat.late'), $thisMonthLate)
                ->description(__('widget.stat.desc.late_this_month_user'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($thisMonthLate > 0 ? 'warning' : 'success'),

            Stat::make(__('widget.stat.avg_work_hours'), $avgWorkHoursFormatted)
                ->description(__('widget.stat.desc.per_day'))
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
