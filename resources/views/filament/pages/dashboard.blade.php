<x-filament-panels::page>
    @php
        $user = auth()->user();
        $isSuperAdmin = $user->hasRole('super_admin');
    @endphp

    {{-- Welcome Section --}}
    <div class="mb-6">
        @livewire(\App\Filament\Widgets\WelcomeWidget::class)
    </div>

    {{-- My Salary Widget - Show for ALL users --}}
    @if(!$isSuperAdmin)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                @livewire(\App\Filament\Widgets\MySalaryWidget::class)
            </div>
            <div class="lg:col-span-1">
                @livewire(\App\Filament\Widgets\MyAttendanceWidget::class)
            </div>
        </div>
    @endif

    @if($isSuperAdmin)
        {{-- Super Admin Dashboard --}}

        {{-- Stats Row --}}
        <div class="grid grid-cols-1 gap-6 mb-6">
            @livewire(\App\Filament\Widgets\StatsOverview::class)
        </div>

        <div class="grid grid-cols-1 gap-6 mb-6">
            @livewire(\App\Filament\Widgets\AttendanceStatsWidget::class)
        </div>

        {{-- Charts Row - 3 columns on large screens --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                @livewire(\App\Filament\Widgets\MonthlyTicketTrendChart::class)
            </div>
            <div class="lg:col-span-1">
                @livewire(\App\Filament\Widgets\TicketsPerProjectChart::class)
            </div>
        </div>

        {{-- Recent Activity & User Stats --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2">
                @livewire(\App\Filament\Widgets\RecentActivityTable::class)
            </div>
            <div class="lg:col-span-1">
                @livewire(\App\Filament\Widgets\UserStatisticsChart::class)
            </div>
        </div>

        {{-- Project Timeline - Full Width --}}
        <div class="mb-6">
            @livewire(\App\Filament\Widgets\ProjectTimeline::class)
        </div>

    @else
        {{-- Regular User Dashboard --}}

        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 gap-6 mb-6">
            @livewire(\App\Filament\Widgets\StatsOverview::class)
        </div>

        {{-- Upcoming Events --}}
        <div class="grid grid-cols-1 gap-6">
            @livewire(\App\Filament\Widgets\UpcomingEventsWidget::class)
        </div>

    @endif
</x-filament-panels::page>
