<div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    📸 Registrasi Wajah untuk Face Recognition
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Daftarkan wajah Anda untuk digunakan dalam attendance berbasis face recognition
                </p>
            </div>
        </div>

        {{-- Status --}}
        @if ($userFace)
            <div class="rounded-lg bg-green-50 p-4 ring-1 ring-green-200 dark:bg-green-950/30 dark:ring-green-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-green-900 dark:text-green-100">
                            ✓ Wajah Anda Sudah Terdaftar
                        </p>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Terdaftar pada: {{ $userFace->registered_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                </div>

                {{-- Preview Registered Face --}}
                <div class="mt-4 text-center">
                    <img src="{{ $userFace->getFaceImageUrl() }}" alt="Registered Face"
                        class="mx-auto h-32 w-32 rounded-lg object-cover ring-2 ring-green-200 dark:ring-green-800">
                </div>

                {{-- Button Action --}}
                <div class="mt-4 flex gap-2">
                    <button wire:click="showRegisterForm"
                        class="flex-1 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-600">
                        Ganti Wajah
                    </button>
                    <button wire:click="deleteFace"
                        onclick="return confirm('Anda yakin ingin menghapus data wajah? Anda harus mendaftarkan ulang untuk menggunakan face recognition.')"
                        class="flex-1 rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600">
                        Hapus
                    </button>
                </div>
            </div>
        @else
            {{-- Not Registered Badge --}}
            <div class="rounded-lg bg-yellow-50 p-4 ring-1 ring-yellow-200 dark:bg-yellow-950/30 dark:ring-yellow-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4v2m0 4v2M7.08 6.47A9 9 0 1020.92 17.53M7.08 6.47L4.95 4.34m16.84 16.84l2.12 2.12">
                            </path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-yellow-900 dark:text-yellow-100">
                            ⚠ Wajah Belum Terdaftar
                        </p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300">
                            Anda perlu mendaftarkan wajah untuk menggunakan fitur face recognition di attendance
                            remote
                        </p>
                    </div>
                </div>

                {{-- Register Button --}}
                <div class="mt-4">
                    <button wire:click="showRegisterForm"
                        class="w-full rounded-lg bg-yellow-600 px-4 py-2 text-white hover:bg-yellow-700 dark:bg-yellow-700 dark:hover:bg-yellow-600">
                        Daftarkan Wajah Sekarang
                    </button>
                </div>
            </div>
        @endif

        {{-- Register Form --}}
        @if ($showForm)
            <div class="space-y-4 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                <div class="flex items-center justify-between">
                    <h4 class="font-semibold text-gray-900 dark:text-white">
                        📤 Upload Foto Wajah Anda
                    </h4>
                    <button wire:click="hideRegisterForm" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                        ✕
                    </button>
                </div>

                <form wire:submit="registerFace" class="space-y-4">
                    {{ $this->form }}

                    {{-- Preview --}}
                    @if ($previewImage)
                        <div class="text-center">
                            <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Preview:</p>
                            <img src="{{ $previewImage }}" alt="Preview"
                                class="mx-auto h-40 w-40 rounded-lg object-cover ring-2 ring-blue-300 dark:ring-blue-700">
                        </div>
                    @endif

                    {{-- Buttons --}}
                    <div class="flex gap-2">
                        <button type="submit"
                            class="flex-1 rounded-lg bg-green-600 px-4 py-2 text-white hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600">
                            ✓ Simpan Wajah
                        </button>
                        <button type="button" wire:click="hideRegisterForm"
                            class="flex-1 rounded-lg bg-gray-300 px-4 py-2 text-gray-900 hover:bg-gray-400 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                            Batal
                        </button>
                    </div>

                    {{-- Info --}}
                    <div class="rounded-lg bg-blue-100 p-3 text-sm text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                        <p class="font-semibold">💡 Tips:</p>
                        <ul class="mt-2 list-inside list-disc space-y-1">
                            <li>Pastikan pencahayaan cukup baik</li>
                            <li>Wajah harus terlihat jelas dan menghadap kamera</li>
                            <li>Hindari menggunakan kacamata hitam atau topi</li>
                            <li>File harus dalam format JPEG, PNG, GIF, atau WebP (maksimal 5 MB)</li>
                        </ul>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
