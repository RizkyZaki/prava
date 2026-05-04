<div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📸</span>
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
                            <span class="text-3xl">✅</span>
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
                            <div class="absolute -bottom-2 -right-2 flex h-8 w-8 items-center justify-center rounded-full bg-green-100 dark:bg-green-900 text-lg">
                                ✓
                            </div>
                        </div>
                    </div>

                    {{-- Button Action --}}
                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <button wire:click="showRegisterForm"
                            class="flex items-center justify-center gap-2 flex-1 rounded-lg bg-blue-600 px-4 py-2.5 font-medium text-white transition hover:bg-blue-700 active:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600">
                            🔄 Ganti Wajah
                        </button>
                        <button wire:click="deleteFace"
                            onclick="return confirm('Anda yakin ingin menghapus data wajah? Anda harus mendaftarkan ulang untuk menggunakan face recognition.')"
                            class="flex items-center justify-center gap-2 flex-1 rounded-lg bg-red-600 px-4 py-2.5 font-medium text-white transition hover:bg-red-700 active:bg-red-800 dark:bg-red-700 dark:hover:bg-red-600">
                            🗑️ Hapus
                        </button>
                    </div>
                </div>
            </div>
        @else
            {{-- Not Registered Badge --}}
            <div class="overflow-hidden rounded-lg border border-amber-200 bg-gradient-to-br from-amber-50 to-yellow-50 dark:border-amber-900/30 dark:from-amber-950/40 dark:to-yellow-950/40">
                <div class="space-y-4 p-4 sm:p-6">
                    <div class="flex items-start gap-4">
                        <span class="text-3xl">⚠️</span>
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
                        ➕ Daftarkan Wajah Sekarang
                    </button>
                </div>
            </div>
        @endif

        {{-- Register Form --}}
        @if ($showForm)
            <div class="space-y-4 overflow-hidden rounded-lg border border-indigo-200 bg-gradient-to-br from-indigo-50 to-blue-50 p-4 dark:border-indigo-900/30 dark:from-indigo-950/40 dark:to-blue-950/40 sm:p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📸</span>
                        <h4 class="font-semibold text-gray-900 dark:text-white">
                            Buka Kamera
                        </h4>
                    </div>
                    <button wire:click="hideRegisterForm" class="rounded-lg p-1 text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 text-xl">
                        ✕
                    </button>
                </div>

                {{-- Camera Section --}}
                @if ($showCamera && !$previewImage)
                    <div class="space-y-4">
                        {{-- Video Stream --}}
                        <div class="flex flex-col items-center gap-4">
                            <video id="cameraStream"
                                class="w-full max-w-sm rounded-xl bg-gray-900 object-cover ring-4 ring-indigo-300 dark:ring-indigo-700"
                                style="display: none; aspect-ratio: 1;"
                                playsinline
                                autoplay
                                muted>
                            </video>

                            {{-- Fallback message --}}
                            <div id="cameraLoading" class="w-full max-w-sm rounded-xl bg-gray-200 dark:bg-gray-700 p-8 flex items-center justify-center text-gray-600 dark:text-gray-400 aspect-square">
                                <div class="text-center">
                                    <p class="text-lg font-semibold mb-2">Mengakses kamera...</p>
                                    <p class="text-sm">Klik izinkan ketika browser minta akses kamera</p>
                                </div>
                            </div>
                        </div>

                        {{-- Canvas untuk capture (hidden) --}}
                        <canvas id="captureCanvas" style="display: none;"></canvas>

                        {{-- Capture Button --}}
                        <button type="button"
                            id="captureBtn"
                            class="w-full flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-3 font-medium text-white transition hover:bg-indigo-700 active:bg-indigo-800 dark:bg-indigo-700 dark:hover:bg-indigo-600"
                            onclick="capturePhoto()">
                            📷 Ambil Foto
                        </button>

                        {{-- Tips --}}
                        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/40">
                            <div class="text-sm">
                                <p class="font-semibold mb-2 text-blue-900 dark:text-blue-50">💡 Tips untuk Hasil Terbaik:</p>
                                <ul class="list-inside list-disc space-y-1 text-blue-800 dark:text-blue-100">
                                    <li>Pastikan pencahayaan cukup baik dan merata</li>
                                    <li>Wajah harus terlihat jelas dan menghadap kamera</li>
                                    <li>Hindari kacamata hitam, kacamata biasa OK</li>
                                    <li>Posisikan wajah di tengah frame</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Preview Section --}}
                @if ($previewImage)
                    <div class="space-y-4">
                        <div class="flex flex-col items-center gap-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Preview Foto:</p>
                            <img src="{{ $previewImage }}" alt="Preview"
                                class="h-56 w-56 rounded-xl object-cover ring-4 ring-indigo-300 dark:ring-indigo-700">
                        </div>

                        {{-- Buttons --}}
                        <form wire:submit="registerFace" class="space-y-4">
                            <div class="flex flex-col gap-3">
                                <button type="submit"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 font-medium text-white transition hover:bg-green-700 active:bg-green-800 dark:bg-green-700 dark:hover:bg-green-600">
                                    ✓ Simpan Wajah
                                </button>
                                <button type="button" wire:click="retakePhoto"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 font-medium text-white transition hover:bg-blue-700 active:bg-blue-800 dark:bg-blue-700 dark:hover:bg-blue-600">
                                    🔄 Ambil Ulang
                                </button>
                                <button type="button" wire:click="hideRegisterForm"
                                    class="flex items-center justify-center gap-2 rounded-lg bg-gray-200 px-4 py-2.5 font-medium text-gray-900 transition hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                                    ❌ Batal
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Auto-activate camera on load --}}
            @script
            <script>
                let cameraStream = null;

                document.addEventListener('livewire:navigated', function() {
                    if (document.getElementById('cameraStream')) {
                        activateCamera();
                    }
                });

                // Initialize on first render
                if (document.getElementById('cameraStream')) {
                    setTimeout(() => {
                        activateCamera();
                    }, 500);
                }

                function activateCamera() {
                    const video = document.getElementById('cameraStream');
                    const loading = document.getElementById('cameraLoading');

                    if (!video) return;

                    navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: { ideal: 640 },
                            height: { ideal: 640 }
                        },
                        audio: false
                    }).then(stream => {
                        cameraStream = stream;
                        video.srcObject = stream;
                        video.style.display = 'block';
                        if (loading) loading.style.display = 'none';
                    }).catch(err => {
                        console.error('Error accessing camera:', err);
                        if (loading) {
                            loading.innerHTML = '<div class="text-center"><p class="text-lg font-semibold mb-2 text-red-600">❌ Akses Kamera Ditolak</p><p class="text-sm">Silakan izinkan akses kamera di browser settings</p></div>';
                        }
                    });
                }

                function capturePhoto() {
                    const video = document.getElementById('cameraStream');
                    const canvas = document.getElementById('captureCanvas');
                    const ctx = canvas.getContext('2d');

                    if (!video || !cameraStream) {
                        alert('Kamera belum siap');
                        return;
                    }

                    // Set canvas size sesuai video
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 640;

                    // Draw video frame to canvas
                    ctx.drawImage(video, 0, 0);

                    // Get image as data URL
                    const imageData = canvas.toDataURL('image/jpeg', 0.9);

                    // Stop camera stream
                    if (cameraStream) {
                        cameraStream.getTracks().forEach(track => track.stop());
                        cameraStream = null;
                    }

                    // Send to Livewire
                    @this.saveCapturedImage(imageData);
                }

                // Cleanup when component is destroyed
                document.addEventListener('livewire:remove', function() {
                    if (cameraStream) {
                        cameraStream.getTracks().forEach(track => track.stop());
                        cameraStream = null;
                    }
                });
            </script>
            @endscript
        @endif
    </div>
</div>

