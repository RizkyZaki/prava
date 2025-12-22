<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header with navigation --}}
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

            <x-filament::button
                href="{{ route('filament.admin.resources.events.create') }}"
                icon="heroicon-m-plus"
                size="sm"
            >
                Tambah Kegiatan
            </x-filament::button>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @php
                $stats = $this->getStats();
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Kegiatan</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Akan Datang</div>
                <div class="text-2xl font-bold text-info-600">{{ $stats['upcoming'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Berlangsung</div>
                <div class="text-2xl font-bold text-warning-600">{{ $stats['ongoing'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Selesai</div>
                <div class="text-2xl font-bold text-success-600">{{ $stats['completed'] }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
                <div class="text-sm text-gray-600 dark:text-gray-400">Rapat</div>
                <div class="text-2xl font-bold text-primary-600">{{ $stats['by_type']['meeting'] }}</div>
            </div>
        </div>

        {{-- Calendar --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
                {{-- Header Days --}}
                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                    <div class="bg-gray-50 dark:bg-gray-800 p-3 text-center text-sm font-semibold text-gray-700 dark:text-gray-300">
                        {{ $day }}
                    </div>
                @endforeach

                {{-- Calendar Days --}}
                @foreach($this->getCalendarData() as $week)
                    @foreach($week as $day)
                        <div class="bg-white dark:bg-gray-900 min-h-[120px] p-2 relative
                            {{ !$day['isCurrentMonth'] ? 'opacity-50' : '' }}
                            {{ $day['isToday'] ? 'ring-2 ring-primary-500' : '' }}">

                            {{-- Date Number --}}
                            <div class="text-sm font-semibold mb-2
                                {{ $day['isToday'] ? 'text-primary-600' : 'text-gray-700 dark:text-gray-300' }}
                                {{ $day['date']->isWeekend() ? 'text-red-500' : '' }}">
                                {{ $day['date']->day }}
                            </div>

                            {{-- Events for this day --}}
                            <div class="space-y-1">
                                @foreach($day['events']->take(3) as $event)
                                    <div
                                        wire:click="selectEvent({{ $event->id }})"
                                        class="text-xs px-2 py-1 rounded cursor-pointer hover:opacity-80 transition"
                                        style="background-color: {{ $event->color }}20; border-left: 3px solid {{ $event->color }};"
                                    >
                                        <div class="font-medium truncate" style="color: {{ $event->color }};">
                                            {{ $event->title }}
                                        </div>
                                        @if(!$event->all_day)
                                            <div class="text-gray-600 dark:text-gray-400">
                                                {{ $event->start_date->format('H:i') }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                @if($day['events']->count() > 3)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 px-2">
                                        +{{ $day['events']->count() - 3 }} lainnya
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        {{-- Legend --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Tipe Kegiatan:</h3>
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-info-500"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Rapat</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-danger-500"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Deadline</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-success-500"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Libur</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-warning-500"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Training</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded bg-gray-500"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-400">Lainnya</span>
                </div>
            </div>
        </div>

        {{-- Event Detail Modal --}}
        @if($selectedEventId && $this->getSelectedEvent())
            @php
                $selectedEvent = $this->getSelectedEvent();
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" wire:click="closeEventDetail">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6" wire:click.stop>
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ $selectedEvent->title }}
                        </h3>
                        <button wire:click="closeEventDetail" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                  style="background-color: {{ $selectedEvent->color }}20; color: {{ $selectedEvent->color }};">
                                {{ $selectedEvent->type_label }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ml-2
                                {{ $selectedEvent->status == 'scheduled' ? 'bg-info-100 text-info-800' : '' }}
                                {{ $selectedEvent->status == 'ongoing' ? 'bg-warning-100 text-warning-800' : '' }}
                                {{ $selectedEvent->status == 'completed' ? 'bg-success-100 text-success-800' : '' }}
                                {{ $selectedEvent->status == 'cancelled' ? 'bg-danger-100 text-danger-800' : '' }}">
                                {{ $selectedEvent->status_label }}
                            </span>
                        </div>

                        @if($selectedEvent->description)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Deskripsi:</h4>
                                <div class="text-gray-600 dark:text-gray-400 prose prose-sm max-w-none">
                                    {!! $selectedEvent->description !!}
                                </div>
                            </div>
                        @endif

                        <div>
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Waktu:</h4>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ $selectedEvent->formatted_date_range }}
                            </p>
                        </div>

                        @if($selectedEvent->location)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Lokasi:</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $selectedEvent->location }}</p>
                            </div>
                        @endif

                        @if($selectedEvent->participants && count($selectedEvent->participants) > 0)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Peserta:</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($selectedEvent->participants_users as $participant)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                            {{ $participant->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($selectedEvent->notes)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Catatan:</h4>
                                <p class="text-gray-600 dark:text-gray-400">{{ $selectedEvent->notes }}</p>
                            </div>
                        @endif

                        <div class="flex gap-2 pt-4 border-t">
                            <x-filament::button
                                href="{{ route('filament.admin.resources.events.edit', $selectedEvent->id) }}"
                                icon="heroicon-m-pencil"
                                size="sm"
                            >
                                Edit
                            </x-filament::button>
                            <x-filament::button
                                wire:click="closeEventDetail"
                                color="gray"
                                size="sm"
                            >
                                Tutup
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
