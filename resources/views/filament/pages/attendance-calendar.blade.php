<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header with navigation and user filter --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <x-filament::button
                    wire:click="previousMonth"
                    icon="heroicon-m-chevron-left"
                    size="sm"
                >
                    Prev
                </x-filament::button>

                <h2 class="text-xl font-bold">{{ $this->getMonthName() }}</h2>

                <x-filament::button
                    wire:click="nextMonth"
                    icon="heroicon-m-chevron-right"
                    icon-position="after"
                    size="sm"
                >
                    Next
                </x-filament::button>

                <x-filament::button
                    wire:click="today"
                    size="sm"
                    color="gray"
                >
                    Hari Ini
                </x-filament::button>
            </div>

            @if(auth()->user()->hasRole('super_admin'))
                <div class="w-full sm:w-64">
                    <select
                        wire:model.live="selectedUserId"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="">Semua User</option>
                        @foreach($this->getUsers() as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $stats = $this->getStats();
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Hari Kerja</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['working_days'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Hadir</div>
                <div class="text-2xl font-bold text-success-600">{{ $stats['present'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Terlambat</div>
                <div class="text-2xl font-bold text-warning-600">{{ $stats['late'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Tidak Hadir</div>
                <div class="text-2xl font-bold text-danger-600">{{ $stats['absent'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Cuti</div>
                <div class="text-2xl font-bold text-info-600">{{ $stats['leave'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Weekend</div>
                <div class="text-2xl font-bold text-gray-600">{{ $stats['weekends'] }}</div>
            </div>
        </div>

        {{-- Calendar --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                {{-- Header Days --}}
                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $day)
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ $day }}
                    </div>
                @endforeach

                {{-- Calendar Days --}}
                @foreach($this->getCalendarData() as $week)
                    @foreach($week as $day)
                        <div class="bg-white dark:bg-gray-900 min-h-[100px] p-2 relative
                            {{ !$day['isCurrentMonth'] ? 'opacity-50' : '' }}
                            {{ $day['isToday'] ? 'ring-2 ring-primary-500' : '' }}
                            {{ !$selectedUserId && $day['attendances']->count() > 0 ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800' : '' }}"
                            @if(!$selectedUserId && $day['attendances']->count() > 0)
                                wire:click="viewDateDetails('{{ $day['date']->toDateString() }}')"
                            @endif>

                            {{-- Date Number --}}
                            <div class="text-sm font-semibold mb-2
                                {{ $day['isToday'] ? 'text-primary-600' : 'text-gray-700 dark:text-gray-300' }}
                                {{ $day['date']->isWeekend() ? 'text-red-500' : '' }}">
                                {{ $day['date']->day }}
                            </div>

                            {{-- When viewing specific user OR non-admin --}}
                            @if($selectedUserId || !auth()->user()->hasRole('super_admin'))
                                @if($day['attendances']->count() > 0)
                                    @php
                                        $attendance = $day['attendances']->first();
                                        $colors = [
                                            'present' => 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200',
                                            'late' => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
                                            'half_day' => 'bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200',
                                            'absent' => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
                                            'leave' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                            'holiday' => 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200',
                                        ];
                                        $color = $colors[$attendance->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp

                                    <div class="space-y-1">
                                        <div class="text-xs px-2 py-1 rounded {{ $color }}">
                                            {{ $attendance->status_label }}
                                        </div>

                                        @if($attendance->check_in)
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Masuk:</span> {{ $attendance->check_in->format('H:i') }}
                                            </div>
                                        @endif

                                        @if($attendance->check_out)
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Pulang:</span> {{ $attendance->check_out->format('H:i') }}
                                            </div>
                                        @endif

                                        @if($attendance->late_duration)
                                            <div class="text-xs text-warning-600 dark:text-warning-400">
                                                Telat: {{ $attendance->late_duration }}m
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    @if($day['isCurrentMonth'] && !$day['date']->isWeekend() && $day['date']->isPast())
                                        <div class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            Tidak ada data
                                        </div>
                                    @endif
                                @endif
                            @else
                                {{-- When viewing all users (super admin) - show summary --}}
                                @if($day['attendances']->count() > 0)
                                    <div class="space-y-1">
                                        @if($day['summary']['present'] > 0)
                                            <div class="text-xs px-2 py-1 rounded bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200">
                                                {{ $day['summary']['present'] }} hadir
                                            </div>
                                        @endif

                                        @if($day['summary']['late'] > 0)
                                            <div class="text-xs px-2 py-1 rounded bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200">
                                                {{ $day['summary']['late'] }} terlambat
                                            </div>
                                        @endif

                                        @if($day['summary']['leave'] > 0)
                                            <div class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $day['summary']['leave'] }} cuti
                                            </div>
                                        @endif

                                        @if($day['summary']['half_day'] > 0)
                                            <div class="text-xs px-2 py-1 rounded bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200">
                                                {{ $day['summary']['half_day'] }} ½ hari
                                            </div>
                                        @endif

                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Klik untuk detail
                                        </div>
                                    </div>
                                @else
                                    @if($day['isCurrentMonth'] && !$day['date']->isWeekend() && $day['date']->isPast())
                                        <div class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400">
                                            Tidak ada data
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Legend --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Keterangan:</h3>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-success-100 dark:bg-success-900"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Hadir</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-warning-100 dark:bg-warning-900"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Terlambat</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-info-100 dark:bg-info-900"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Setengah Hari</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-danger-100 dark:bg-danger-900"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Tidak Hadir</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-gray-100 dark:bg-gray-700"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Cuti</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-primary-100 dark:bg-primary-900"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Libur</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal for date details --}}
    <x-filament::modal id="attendance-details" width="2xl">
        <x-slot name="heading">
            Detail Absensi - {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d F Y') : '' }}
        </x-slot>

        <div class="space-y-3">
            @foreach($this->getSelectedDateAttendances() as $attendance)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ $attendance->user->name ?? 'N/A' }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1 mt-1">
                            @if($attendance->check_in)
                                <div>Masuk: <span class="font-medium">{{ $attendance->check_in->format('H:i') }}</span></div>
                            @endif
                            @if($attendance->check_out)
                                <div>Pulang: <span class="font-medium">{{ $attendance->check_out->format('H:i') }}</span></div>
                            @endif
                            @if($attendance->late_duration)
                                <div class="text-warning-600">Keterlambatan: {{ $attendance->late_duration }} menit</div>
                            @endif
                        </div>
                    </div>
                    <div class="ml-4">
                        @php
                            $colors = [
                                'present' => 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200',
                                'late' => 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
                                'half_day' => 'bg-info-100 text-info-800 dark:bg-info-900 dark:text-info-200',
                                'absent' => 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
                                'leave' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                'holiday' => 'bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200',
                            ];
                            $color = $colors[$attendance->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1 text-xs rounded-full {{ $color }}">
                            {{ $attendance->status_label }}
                        </span>
                    </div>
                </div>
            @endforeach

            @if($this->getSelectedDateAttendances()->count() === 0)
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    Tidak ada data absensi
                </div>
            @endif
        </div>
    </x-filament::modal>
</x-filament-panels::page>
