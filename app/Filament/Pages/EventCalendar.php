<?php

namespace App\Filament\Pages;

use App\Models\Event;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class EventCalendar extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.pages.event-calendar';

    protected static ?string $title = 'Kalender Kegiatan';

    protected static ?string $navigationLabel = 'Kalender Kegiatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Kalender & Kegiatan';

    protected static ?int $navigationSort = 2;

    public int $currentMonth;

    public int $currentYear;

    public Collection $events;

    public ?int $selectedEventId = null;

    public function mount(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->loadEvents();
    }

    public function loadEvents(): void
    {
        $this->events = Event::forMonth($this->currentYear, $this->currentMonth)
            ->orderBy('start_date', 'asc')
            ->get();
    }

    public function previousMonth(): void
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
        $this->loadEvents();
    }

    public function nextMonth(): void
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
        $this->loadEvents();
    }

    public function today(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
        $this->loadEvents();
    }

    public function selectEvent($eventId): void
    {
        $this->selectedEventId = $eventId;
    }

    public function closeEventDetail(): void
    {
        $this->selectedEventId = null;
    }

    public function getSelectedEvent()
    {
        if (!$this->selectedEventId) {
            return null;
        }

        return Event::find($this->selectedEventId);
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
            // Get events for this date
            $dayEvents = $this->events->filter(function ($event) use ($date) {
                $eventStart = $event->start_date->copy()->startOfDay();
                $eventEnd = $event->end_date ? $event->end_date->copy()->endOfDay() : $eventStart->copy()->endOfDay();

                return $date->between($eventStart, $eventEnd);
            });

            $currentWeek[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month == $this->currentMonth,
                'isToday' => $date->isToday(),
                'events' => $dayEvents,
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

    public function getStats(): array
    {
        $totalEvents = $this->events->count();
        $upcomingEvents = $this->events->filter(fn($e) => $e->isUpcoming())->count();
        $ongoingEvents = $this->events->filter(fn($e) => $e->isOngoing())->count();
        $completedEvents = $this->events->where('status', 'completed')->count();

        $byType = [
            'meeting' => $this->events->where('type', 'meeting')->count(),
            'deadline' => $this->events->where('type', 'deadline')->count(),
            'holiday' => $this->events->where('type', 'holiday')->count(),
            'training' => $this->events->where('type', 'training')->count(),
            'other' => $this->events->where('type', 'other')->count(),
        ];

        return [
            'total' => $totalEvents,
            'upcoming' => $upcomingEvents,
            'ongoing' => $ongoingEvents,
            'completed' => $completedEvents,
            'by_type' => $byType,
        ];
    }
}
