<x-filament-panels::page>

    @php
        $profile = \App\Models\EmployeeProfile::where('user_id', auth()->id())->first();
        $user = auth()->user();

        $fields = [
            ['label' => 'NIK', 'value' => $profile?->national_id_number],
            [
                'label' => 'Tanggal Lahir',
                'value' => $profile?->birth_date
                    ? \Carbon\Carbon::parse($profile->birth_date)->format('d M Y')
                    : '-',
            ],
            [
                'label' => 'Tanggal Masuk',
                'value' => $profile?->hire_date
                    ? \Carbon\Carbon::parse($profile->hire_date)->format('d M Y')
                    : '-',
            ],
            ['label' => 'No HP', 'value' => $profile?->phone_number ?? '-'],
            ['label' => 'Alamat', 'value' => $profile?->address ?? '-', 'span' => 2],
            ['label' => 'Pendidikan', 'value' => $profile?->last_education ?? '-'],
            ['label' => 'Status', 'value' => ucfirst($profile?->marital_status ?? '-')],
            ['label' => 'Rekening BJB', 'value' => $profile?->bjb_bank_account_number ?? '-'],
            ['label' => 'NPWP', 'value' => $profile?->tax_identification_number ?? '-'],
            ['label' => 'Email Pribadi', 'value' => $profile?->personal_email ?? '-'],
        ];
    @endphp

    {{-- PROFILE CARD --}}
    <div class="w-full">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-8 py-8 border-b border-gray-200 dark:border-gray-800 flex flex-col md:flex-row items-center gap-6">
                <div class="flex-shrink-0">
                    @if ($profile?->profile_photo)
                        <img src="{{ asset('storage/' . $profile->profile_photo) }}"
                             class="w-28 h-28 rounded-full object-cover border border-gray-300 dark:border-gray-700">
                    @else
                        <div
                            class="w-28 h-28 rounded-full bg-gray-100 dark:bg-gray-800
                                flex items-center justify-center
                                text-2xl font-semibold text-gray-600 dark:text-gray-300">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-3xl font-semibold text-gray-900 dark:text-white">
                        {{ $user->name }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $profile?->position_title ?? 'Belum diatur' }}
                    </p>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ $user->email }}
                    </p>
                </div>
            </div>

            <div class="px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
                    @foreach ($fields as $f)
                        <div class="{{ isset($f['span']) ? 'md:col-span-' . $f['span'] : '' }}">
                            <div class="space-y-1">
                                <p class="text-xs uppercase tracking-wide text-gray-400">
                                    {{ $f['label'] }}
                                </p>
                                <p class="text-sm text-gray-900 dark:text-gray-200 font-medium">
                                    {{ $f['value'] }}
                                </p>
                            </div>
                            <div class="mt-3 border-b border-gray-100 dark:border-gray-800"></div>
                        </div>
                    @endforeach
                </div>

                @php
                    $attachments = [
                        ['label' => 'Attachment KTP', 'path' => $profile?->attachment_ktp],
                        ['label' => 'Attachment NPWP', 'path' => $profile?->attachment_npwp],
                        ['label' => 'Attachment Kontrak', 'path' => $profile?->attachment_kontrak],
                    ];
                @endphp

                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Attachments</h3>
                    <ul class="space-y-2">
                        @foreach ($attachments as $att)
                            @if ($att['path'])
                                <li>
                                    <a href="{{ asset('storage/' . $att['path']) }}" target="_blank"
                                       class="text-blue-600 dark:text-blue-400 hover:underline">
                                        {{ $att['label'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                        @if (collect($attachments)->where('path')->isEmpty())
                            <li class="text-sm text-gray-500">No attachments available.</li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- FORM --}}
    <form wire:submit.prevent="save" class="space-y-8">

        <div class="bg-white dark:bg-gray-900 shadow-xl rounded-2xl p-8 border border-gray-100 dark:border-gray-800">
            {{ $this->form }}
        </div>

        <div class="flex justify-end pt-4">
            <x-filament::button type="submit" size="lg" class="px-10">
                Save Changes
            </x-filament::button>
        </div>

    </form>

</x-filament-panels::page>
