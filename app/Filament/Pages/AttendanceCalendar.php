<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class AttendanceCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.pages.attendance-calendar';

    protected static ?string $title = 'Kalender Absensi';

    protected static ?string $navigationLabel = 'Kalender Absensi';

    protected static string|\UnitEnum|null $navigationGroup = 'Human Resources';

    protected static ?int $navigationSort = 2;

    public ?int $selectedUserId = null;

    public int $currentMonth;

    public int $currentYear;

    public Collection $attendances;

    public function mount(): void
    {
        // Super admin default lihat semua user, non-admin cuma lihat diri sendiri
        if (!Auth::user()->hasRole('super_admin')) {
            $this->selectedUserId = Auth::id();
        }
        // else: super admin selectedUserId = null (tampilkan semua)

        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->loadAttendances();
    }

    public function loadAttendances(): void
    {
        $query = Attendance::query()
            ->whereYear('attendance_date', $this->currentYear)
            ->whereMonth('attendance_date', $this->currentMonth)
            ->with('user');

        if (!Auth::user()->hasRole('super_admin')) {
            $query->where('user_id', Auth::id());
        } elseif ($this->selectedUserId) {
            $query->where('user_id', $this->selectedUserId);
        }

        $this->attendances = $query->get();
    }

    public function previousMonth(): void
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
        $this->loadAttendances();
    }

    public function nextMonth(): void
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
        $this->loadAttendances();
    }

    public function today(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->loadAttendances();
    }

    public function updatedSelectedUserId(): void
    {
        $this->loadAttendances();
    }

    public function getCalendarData(): array
    {
        $startOfMonth = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // Start from Monday of the week containing the first day of month
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);

        // End on Sunday of the week containing the last day of month
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $currentWeek = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();
            $attendance = $this->attendances->first(function ($att) use ($dateString) {
                return $att->attendance_date->toDateString() === $dateString;
            });

            $currentWeek[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month == $this->currentMonth,
                'isToday' => $date->isToday(),
                'attendance' => $attendance,
            ];

            // If Sunday (end of week), add to weeks array
            if ($date->dayOfWeek === Carbon::SUNDAY) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }
        }

        return $weeks;
    }

    public function getMonthName(): string
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return $monthNames[$this->currentMonth] . ' ' . $this->currentYear;
    }

    public function getUsers(): array
    {
        if (!Auth::user()->hasRole('super_admin')) {
            return [];
        }

        return User::pluck('name', 'id')->toArray();
    }

    public function getStats(): array
    {
        $totalDays = Carbon::create($this->currentYear, $this->currentMonth, 1)->daysInMonth;
        $workingDays = 0;
        $weekends = 0;

        for ($day = 1; $day <= $totalDays; $day++) {
            $date = Carbon::create($this->currentYear, $this->currentMonth, $day);
            if ($date->isWeekend()) {
                $weekends++;
            } else {
                $workingDays++;
            }
        }

        $presentDays = $this->attendances->where('status', 'present')->count();
        $lateDays = $this->attendances->where('status', 'late')->count();
        $absentDays = $workingDays - $this->attendances->whereIn('status', ['present', 'late', 'half_day'])->count();
        $leaveDays = $this->attendances->where('status', 'leave')->count();

        return [
            'working_days' => $workingDays,
            'weekends' => $weekends,
            'present' => $presentDays,
            'late' => $lateDays,
            'absent' => $absentDays,
            'leave' => $leaveDays,
        ];
    }
}
