@php
    /** @var \App\Models\User $record */
    $user = $record;
    // make sure profile exists (persist if not)
    $profile = $user->employeeProfile;
    if (!$profile) {
        $profile = \App\Models\EmployeeProfile::create(['user_id' => $user->id]);
    }

    $fields = [
        ['label' => 'NIK', 'value' => $profile->national_id_number ?? '-'],
        [
            'label' => 'Tanggal Lahir',
            'value' => $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->format('d M Y') : '-',
        ],
        [
            'label' => 'Tanggal Masuk',
            'value' => $profile->hire_date ? \Carbon\Carbon::parse($profile->hire_date)->format('d M Y') : '-',
        ],
        ['label' => 'No HP', 'value' => $profile->phone_number ?? '-'],
        ['label' => 'Alamat', 'value' => $profile->address ?? '-', 'span' => 2],
        ['label' => 'Pendidikan', 'value' => $profile->last_education ?? '-'],
        ['label' => 'Status', 'value' => ucfirst($profile->marital_status ?? '-')],
        ['label' => 'Rekening BJB', 'value' => $profile->bjb_bank_account_number ?? '-'],
        ['label' => 'NPWP', 'value' => $profile->tax_identification_number ?? '-'],
        ['label' => 'Email Pribadi', 'value' => $profile->personal_email ?? '-'],
    ];
@endphp

<x-filament-panels::page>
    <div class="space-y-8">

        {{-- MAIN PROFILE CARD --}}
        <div class="bg-white dark:bg-gray-900 shadow-xl rounded-3xl overflow-hidden">

            {{-- HEADER --}}
            <div class="bg-gradient-to-r from-primary-600 to-primary-800 px-8 py-8">
                <div class="flex items-center gap-6">

                    {{-- PHOTO --}}
                    @if ($profile?->profile_photo)
                        <img src="{{ asset('storage/' . $profile->profile_photo) }}"
                            class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-lg" />
                    @else
                        <div
                            class="w-24 h-24 rounded-2xl bg-white/20 backdrop-blur
                            flex items-center justify-center
                            text-3xl font-bold text-white shadow-lg">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif

                    {{-- NAME --}}
                    <div class="text-white">
                        <h2 class="text-2xl font-bold">{{ $user->name }}</h2>
                        <p class="text-sm opacity-90">
                            {{ $profile?->position_title ?? 'Belum diatur' }}
                        </p>
                        <p class="text-sm opacity-75">
                            {{ $user->email }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- BODY --}}
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    @foreach ($fields as $f)
                        <div
                            class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-5 shadow-sm
                                    hover:shadow-md transition">

                            <p class="text-xs font-semibold tracking-wide text-gray-400 uppercase mb-1">
                                {{ $f['label'] }}
                            </p>

                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-200">
                                {{ $f['value'] }}
                            </p>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>


        {{-- ATTACHMENT CARD --}}
        <div class="bg-white dark:bg-gray-900 shadow-xl rounded-3xl p-8">

            <h3 class="text-lg font-bold mb-6 text-gray-800 dark:text-gray-200">
                📎 Attachments
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                @php
                    $attachments = [
                        'Attachment KTP' => $profile->attachment_ktp,
                        'Attachment NPWP' => $profile->attachment_npwp,
                        'Attachment Kontrak' => $profile->attachment_kontrak,
                    ];
                @endphp

                @foreach ($attachments as $label => $path)
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-5 shadow-sm">

                        <p class="text-sm font-semibold mb-3 text-gray-700 dark:text-gray-300">
                            {{ $label }}
                        </p>

                        @if ($path)
                            <a href="{{ asset('storage/' . $path) }}" target="_blank"
                                class="inline-block px-4 py-2 text-sm font-medium
                                      bg-primary-600 text-white rounded-xl
                                      hover:bg-primary-700 transition">
                                View File
                            </a>
                        @else
                            <p class="text-sm text-gray-400">
                                No file uploaded
                            </p>
                        @endif

                    </div>
                @endforeach

            </div>

        </div>

    </div>
</x-filament-panels::page>
