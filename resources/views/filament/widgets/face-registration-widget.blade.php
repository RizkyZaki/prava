<div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                        <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14m0 0v4m0-4V8m0 4H9m6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Registrasi Wajah untuk Face Recognition
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Daftarkan wajah Anda untuk attendance berbasis face recognition
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Status --}}
        @if ($userFace)
            <div class="overflow-hidden rounded-lg border border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 dark:border-green-900/30 dark:from-green-950/40 dark:to-emerald-950/40">
                <div class="space-y-4 p-4 sm:p-6">
                    {{-- Status Badge --}}
                    <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/50">
                                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-green-900 dark:text-green-100">
                                    Wajah Anda Sudah Terdaftar
                                </p>
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    Terdaftar pada: <span class="font-medium">{{ $userFace->registered_at->format('d M Y, H:i') }}</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Preview Registered Face --}}
                    <div class="flex justify-center pt-2">
                        <div class="relative">
                            <img src="{{ $userFace->getFaceImageUrl() }}" alt="Registered Face"
                                class="h-40 w-40 rounded-xl object-cover ring-4 ring-green-200 dark:ring-green-800">
                            <div class="absolute -bottom-2 -right-2 flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                                <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- Button Action --}}
                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <button wire:click="showRegisterForm"
                            class="flex items-center justify-center gap-2 flex-1 rounded-lg bg-blue-600 px-4 py-2.5 font-medium text-white transition hover:bg-blue-700 active:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Ganti Wajah
                        </button>
                        <button wire:click="deleteFace"
                            onclick="return confirm('Anda yakin ingin menghapus data wajah? Anda harus mendaftarkan ulang untuk menggunakan face recognition.')"
                            class="flex items-center justify-center gap-2 flex-1 rounded-lg bg-red-600 px-4 py-2.5 font-medium text-white transition hover:bg-red-700 active:bg-red-800 dark:bg-red-700 dark:hover:bg-red-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @else
            {{-- Not Registered Badge --}}
            <div class="overflow-hidden rounded-lg border border-amber-200 bg-gradient-to-br from-amber-50 to-yellow-50 dark:border-amber-900/30 dark:from-amber-950/40 dark:to-yellow-950/40">
                <div class="space-y-4 p-4 sm:p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/50">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 4v2m0-14a9 9 0 110 18 9 9 0 010-18z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="font-semibold text-amber-900 dark:text-amber-100">
                                Wajah Belum Terdaftar
                            </p>
                            <p class="text-sm text-amber-700 dark:text-amber-300">
                                Anda perlu mendaftarkan wajah untuk menggunakan fitur face recognition di attendance remote
                            </p>
                        </div>
                    </div>

                    {{-- Register Button --}}
                    <button wire:click="showRegisterForm"
                        class="w-full flex items-center justify-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 font-medium text-white transition hover:bg-amber-700 active:bg-amber-800 dark:bg-amber-700 dark:hover:bg-amber-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Daftarkan Wajah Sekarang
                    </button>
                </div>
            </div>
        @endif

        {{-- Register Form --}}
        @if ($showForm)
            <div class="space-y-4 overflow-hidden rounded-lg border border-indigo-200 bg-gradient-to-br from-indigo-50 to-blue-50 p-4 dark:border-indigo-900/30 dark:from-indigo-950/40 dark:to-blue-950/40 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-8" />
                        </svg>
                        <h4 class="font-semibold text-gray-900 dark:text-white">
                            Upload Foto Wajah Anda
                        </h4>
                    </div>
                    <button wire:click="hideRegisterForm" class="rounded-lg p-1 text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="registerFace" class="space-y-4">
                    {{ $this->form }}

                    {{-- Preview --}}
                    @if ($previewImage)
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Preview:</p>
                            <img src="{{ $previewImage }}" alt="Preview"
                                class="h-40 w-40 rounded-xl object-cover ring-4 ring-indigo-300 dark:ring-indigo-700">
                        </div>
                    @endif

                    {{-- Buttons --}}
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="submit"
                            class="flex items-center justify-center gap-2 flex-1 rounded-lg bg-green-600 px-4 py-2.5 font-medium text-white transition hover:bg-green-700 active:bg-green-800 dark:bg-green-700 dark:hover:bg-green-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Simpan Wajah
                        </button>
                        <button type="button" wire:click="hideRegisterForm"
                            class="flex items-center justify-center gap-2 flex-1 rounded-lg bg-gray-200 px-4 py-2.5 font-medium text-gray-900 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batal
                        </button>
                    </div>

                    {{-- Tips --}}
                    <div class="rounded-lg bg-blue-100 p-4 dark:bg-blue-900/40">
                        <div class="flex gap-3">
                            <svg class="h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                <p class="font-semibold mb-2">Tips untuk Hasil Terbaik:</p>
                                <ul class="list-inside list-disc space-y-1">
                                    <li>Pastikan pencahayaan cukup baik dan merata</li>
                                    <li>Wajah harus terlihat jelas dan menghadap kamera</li>
                                    <li>Hindari kacamata hitam, kacamata biasa OK</li>
                                    <li>Format: JPEG, PNG, GIF, WebP (max 5 MB)</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>

