@php
    $profile = $user->employeeProfile;
@endphp

<div class="space-y-6">
    {{-- basic user info --}}
    <div class="flex items-center gap-4">
        @if ($profile?->profile_photo)
            <img src="{{ asset('storage/' . $profile->profile_photo) }}" class="w-16 h-16 rounded-full object-cover" />
        @else
            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center text-xl font-semibold text-gray-600">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
        @endif
        <div>
            <h2 class="text-xl font-semibold">{{ $user->name }}</h2>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
        </div>
    </div>

    {{-- general user details --}}
    <div class="mt-4 space-y-1 text-sm">
        <p><strong>Fingerprint ID:</strong> {{ $user->fingerprint_id ?? '-' }}</p>
        <p><strong>Roles:</strong> {{ $user->roles->pluck('name')->join(', ') ?: '-' }}</p>
        <p><strong>Status:</strong> {{ $user->is_active ? 'Active' : 'Inactive' }}</p>
        <p><strong>Created:</strong> {{ $user->created_at?->format('d M Y') ?? '-' }}</p>
    </div>

    @if (Auth::user()->hasRole('super_admin'))
        <div>
            <h3 class="text-lg font-medium">Employee Profile</h3>
            @if ($profile)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 mt-4">
                    <div>
                        <p class="text-xs uppercase text-gray-400">NIK</p>
                        <p class="font-medium">{{ $profile->national_id_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">Tanggal Lahir</p>
                        <p class="font-medium">
                            {{ $profile->birth_date ? \Carbon\Carbon::parse($profile->birth_date)->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">Tanggal Masuk</p>
                        <p class="font-medium">
                            {{ $profile->hire_date ? \Carbon\Carbon::parse($profile->hire_date)->format('d M Y') : '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">No HP</p>
                        <p class="font-medium">{{ $profile->phone_number ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-xs uppercase text-gray-400">Alamat</p>
                        <p class="font-medium">{{ $profile->address ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">Pendidikan</p>
                        <p class="font-medium">{{ $profile->last_education ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">Status Pernikahan</p>
                        <p class="font-medium">{{ ucfirst($profile->marital_status ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">Rekening BJB</p>
                        <p class="font-medium">{{ $profile->bjb_bank_account_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">NPWP</p>
                        <p class="font-medium">{{ $profile->tax_identification_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400">Email Pribadi</p>
                        <p class="font-medium">{{ $profile->personal_email ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="text-sm font-semibold">Attachments</h4>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        @foreach ([
                            'KTP' => $profile->attachment_ktp,
                            'NPWP' => $profile->attachment_npwp,
                            'Kontrak' => $profile->attachment_kontrak,
                        ] as $label => $path)
                            @if ($path)
                                <li><a href="{{ asset('storage/' . $path) }}" target="_blank" class="text-blue-600 hover:underline">{{ $label }}</a></li>
                            @endif
                        @endforeach
                        @if (!($profile->attachment_ktp || $profile->attachment_npwp || $profile->attachment_kontrak))
                            <li class="text-gray-500">No attachments</li>
                        @endif
                    </ul>
                </div>
            @else
                <p class="text-gray-500">No employee profile available.</p>
            @endif
        </div>
    @endif
</div>
