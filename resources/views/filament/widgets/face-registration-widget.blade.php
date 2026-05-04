<div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="space-y-6">
        {{-- Header --}}
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

        {{-- Status: Sudah Terdaftar --}}
        @if ($userFace)
            <div
                style="border-radius: 1rem; overflow: hidden; border: 1px solid #bbf7d0; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
                {{-- Top accent bar --}}
                <div style="height: 4px; background: linear-gradient(90deg, #16a34a, #22c55e, #4ade80);"></div>

                <div style="padding: 1.5rem;">
                    {{-- Badge status --}}
                    <div style="display: flex; align-items: center; gap: 0.625rem; margin-bottom: 1.25rem;">
                        <div
                            style="display:flex; align-items:center; justify-content:center; width:2rem; height:2rem; border-radius:9999px; background-color:#16a34a;">
                            <span style="color:white; font-size:0.875rem;">✓</span>
                        </div>
                        <div>
                            <p style="font-weight: 700; color: #14532d; font-size: 0.95rem;">Wajah Anda Sudah Terdaftar
                            </p>
                            <p style="font-size: 0.75rem; color: #166534;">
                                Terdaftar pada: <strong>{{ $userFace->registered_at->format('d M Y, H:i') }}</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Face preview card --}}
                    <div style="display: flex; justify-content: center; margin-bottom: 1.5rem;">
                        <div style="position: relative; display: inline-block;">
                            {{-- Glow ring --}}
                            <div
                                style="position:absolute; inset:-4px; border-radius:1.25rem; background: linear-gradient(135deg, #16a34a, #22c55e, #4ade80); z-index:0;">
                            </div>
                            <img src="{{ $userFace->getFaceImageUrl() }}" alt="Registered Face"
                                style="position:relative; z-index:1; width:10rem; height:10rem; border-radius:1rem; object-fit:cover; border: 3px solid white; display:block;">
                            {{-- Verified badge --}}
                            <div
                                style="position:absolute; bottom:-10px; right:-10px; z-index:2; width:2.25rem; height:2.25rem; border-radius:9999px; background:linear-gradient(135deg,#16a34a,#22c55e); display:flex; align-items:center; justify-content:center; border: 2px solid white; box-shadow: 0 2px 8px rgba(22,163,74,0.4);">
                                <span style="color:white; font-size:1rem; line-height:1;">✓</span>
                            </div>
                        </div>
                    </div>

                    {{-- Info pill --}}
                    <div style="display:flex; justify-content:center; margin-bottom:1.25rem;">
                        <div
                            style="display:inline-flex; align-items:center; gap:0.375rem; background-color:#dcfce7; border:1px solid #86efac; border-radius:9999px; padding:0.25rem 0.875rem;">
                            <span style="font-size:0.7rem;">🟢</span>
                            <span style="font-size:0.75rem; font-weight:600; color:#15803d;">Aktif & Siap
                                Digunakan</span>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div style="display: flex; flex-direction: column; gap: 0.625rem;">
                        <div style="display: flex; gap: 0.75rem;">
                            <button wire:click="showRegisterForm"
                                style="flex:1; display:flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.625rem; background:linear-gradient(135deg,#2563eb,#3b82f6); padding:0.65rem 1rem; font-weight:600; color:#ffffff; border:none; cursor:pointer; font-size:0.875rem; box-shadow: 0 2px 8px rgba(37,99,235,0.3); transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                🔄 Ganti Wajah
                            </button>
                            <button wire:click="deleteFace"
                                onclick="return confirm('Anda yakin ingin menghapus data wajah? Anda harus mendaftarkan ulang untuk menggunakan face recognition.')"
                                style="flex:1; display:flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.625rem; background:linear-gradient(135deg,#dc2626,#ef4444); padding:0.65rem 1rem; font-weight:600; color:#ffffff; border:none; cursor:pointer; font-size:0.875rem; box-shadow: 0 2px 8px rgba(220,38,38,0.3); transition: opacity 0.2s;"
                                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                🗑️ Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- Not Registered --}}
            <div
                style="border-radius: 1rem; overflow: hidden; border: 1px solid #fde68a; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);">
                <div style="height: 4px; background: linear-gradient(90deg, #d97706, #f59e0b, #fbbf24);"></div>
                <div style="padding: 1.5rem;">
                    <div style="display: flex; align-items: flex-start; gap: 1rem; margin-bottom: 1.25rem;">
                        <span style="font-size: 2rem; line-height: 1;">⚠️</span>
                        <div>
                            <p style="font-weight: 700; color: #78350f; font-size: 0.95rem;">Wajah Belum Terdaftar</p>
                            <p style="font-size: 0.8rem; color: #92400e; margin-top: 0.25rem;">
                                Anda perlu mendaftarkan wajah untuk menggunakan fitur face recognition di attendance
                                remote
                            </p>
                        </div>
                    </div>
                    <button wire:click="showRegisterForm"
                        style="width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.625rem; background:linear-gradient(135deg,#d97706,#f59e0b); padding:0.75rem 1rem; font-weight:600; color:#ffffff; border:none; cursor:pointer; font-size:0.875rem; box-shadow: 0 2px 8px rgba(217,119,6,0.35);"
                        onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                        ➕ Daftarkan Wajah Sekarang
                    </button>
                </div>
            </div>
        @endif

        {{-- Register Form --}}
        @if ($showForm)
            <div
                style="border-radius: 1rem; overflow: hidden; border: 1px solid #c7d2fe; background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);">
                <div style="height: 4px; background: linear-gradient(90deg, #4f46e5, #6366f1, #818cf8);"></div>
                <div style="padding: 1.5rem;" class="space-y-4">

                    {{-- Form Header --}}
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.5rem;">📸</span>
                            <h4 style="font-weight: 700; color: #1e1b4b; font-size: 0.95rem;">Ambil Foto Wajah</h4>
                        </div>
                        <button wire:click="hideRegisterForm"
                            style="display:flex; align-items:center; justify-content:center; width:2rem; height:2rem; border-radius:9999px; background-color:#e0e7ff; border:none; cursor:pointer; color:#4f46e5; font-size:1rem; font-weight:bold;"
                            onmouseover="this.style.backgroundColor='#c7d2fe'"
                            onmouseout="this.style.backgroundColor='#e0e7ff'">
                            ✕
                        </button>
                    </div>

                    {{-- Camera Section --}}
                    @if ($showCamera && !$previewImage)
                        <div class="space-y-4">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                                <video id="cameraStream"
                                    style="display: none; width: 100%; max-width: 20rem; border-radius: 1rem; background: #111827; object-fit: cover; aspect-ratio: 1; border: 3px solid #6366f1; box-shadow: 0 0 0 4px #c7d2fe;"
                                    playsinline autoplay muted>
                                </video>
                                <div id="cameraLoading"
                                    style="width: 100%; max-width: 20rem; border-radius: 1rem; background: #e5e7eb; padding: 3rem 1rem; display: flex; align-items: center; justify-content: center; aspect-ratio: 1; color: #6b7280; text-align: center;">
                                    <div>
                                        <p style="font-weight: 600; margin-bottom: 0.5rem;">Mengakses kamera...</p>
                                        <p style="font-size: 0.8rem;">Klik izinkan ketika browser minta akses kamera</p>
                                    </div>
                                </div>
                            </div>

                            <canvas id="captureCanvas" style="display: none;"></canvas>

                            <button type="button" id="captureBtn" onclick="window.capturePhotoFromCamera()"
                                style="width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.625rem; background:linear-gradient(135deg,#4f46e5,#6366f1); padding:0.75rem 1rem; font-weight:600; color:#ffffff; border:none; cursor:pointer; font-size:0.9rem; box-shadow: 0 2px 8px rgba(79,70,229,0.35);"
                                onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                📷 Ambil Foto
                            </button>

                            {{-- Tips --}}
                            <div
                                style="border-radius: 0.75rem; border: 1px solid #bfdbfe; background-color: #eff6ff; padding: 1rem;">
                                <p style="font-weight: 600; color: #1e40af; font-size: 0.8rem; margin-bottom: 0.5rem;">
                                    💡 Tips untuk Hasil Terbaik:</p>
                                <ul
                                    style="list-style: disc inside; font-size: 0.8rem; color: #1d4ed8; line-height: 1.8;">
                                    <li>Pastikan pencahayaan cukup baik dan merata</li>
                                    <li>Wajah harus terlihat jelas dan menghadap kamera</li>
                                    <li>Hindari kacamata hitam, kacamata biasa OK</li>
                                    <li>Posisikan wajah di tengah frame</li>
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Preview Section --}}
                    @if ($previewImage)
                        <div class="space-y-4">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
                                <p style="font-size: 0.8rem; font-weight: 600; color: #4338ca;">Preview Foto:</p>
                                <div style="position:relative; display:inline-block;">
                                    <div
                                        style="position:absolute; inset:-3px; border-radius:1.1rem; background:linear-gradient(135deg,#4f46e5,#818cf8); z-index:0;">
                                    </div>
                                    <img src="{{ $previewImage }}" alt="Preview"
                                        style="position:relative; z-index:1; width:14rem; height:14rem; border-radius:1rem; object-fit:cover; border:3px solid white; display:block;">
                                </div>
                            </div>

                            <form wire:submit="registerFace" class="space-y-3">
                                <button type="submit"
                                    style="width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.625rem; background:linear-gradient(135deg,#16a34a,#22c55e); padding:0.75rem 1rem; font-weight:600; color:#ffffff; border:none; cursor:pointer; font-size:0.9rem; box-shadow: 0 2px 8px rgba(22,163,74,0.35);"
                                    onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                                    ✅ Simpan Wajah
                                </button>
                                <button type="button" wire:click="hideRegisterForm"
                                    style="width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem; border-radius:0.625rem; background-color:#e5e7eb; padding:0.75rem 1rem; font-weight:600; color:#374151; border:none; cursor:pointer; font-size:0.9rem;"
                                    onmouseover="this.style.backgroundColor='#d1d5db'"
                                    onmouseout="this.style.backgroundColor='#e5e7eb'">
                                    Batal
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            @script
                <script>
                    let cameraStream = null;
                    const componentId = @json($this->getId());

                    function activateCamera() {
                        const video = document.getElementById('cameraStream');
                        const loading = document.getElementById('cameraLoading');

                        if (!video || !video.parentElement) return;

                        if (cameraStream) {
                            cameraStream.getTracks().forEach(track => track.stop());
                            cameraStream = null;
                        }

                        navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: 'user',
                                width: {
                                    ideal: 640
                                },
                                height: {
                                    ideal: 640
                                }
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
                                loading.innerHTML =
                                    '<div style="text-align:center"><p style="font-weight:600;color:#dc2626;margin-bottom:0.25rem;">Akses Kamera Ditolak</p><p style="font-size:0.8rem;">Silakan izinkan akses kamera di browser settings</p></div>';
                            }
                        });
                    }

                    function stopCamera() {
                        if (cameraStream) {
                            cameraStream.getTracks().forEach(track => track.stop());
                            cameraStream = null;
                        }
                    }

                    function capturePhotoFromCamera() {
                        const video = document.getElementById('cameraStream');
                        const canvas = document.getElementById('captureCanvas');
                        const ctx = canvas.getContext('2d');

                        if (!video || !cameraStream) {
                            alert('Kamera belum siap');
                            return;
                        }

                        canvas.width = video.videoWidth || 640;
                        canvas.height = video.videoHeight || 640;
                        ctx.drawImage(video, 0, 0);

                        const imageData = canvas.toDataURL('image/jpeg', 0.9);
                        stopCamera();

                        if (window.Livewire?.find(componentId)) {
                            window.Livewire.find(componentId).call('saveCapturedImage', imageData);
                        }
                    }

                    window.capturePhotoFromCamera = capturePhotoFromCamera;
                    window.stopCamera = stopCamera;
                    window.activateCamera = activateCamera;

                    Livewire.on('opening-camera-form', () => {
                        setTimeout(() => activateCamera(), 100);
                    });

                    Livewire.on('closing-camera-form', () => {
                        stopCamera();
                    });

                    setTimeout(() => {
                        if (document.getElementById('cameraStream')) activateCamera();
                    }, 500);

                    document.addEventListener('livewire:remove', function() {
                        stopCamera();
                    });
                </script>
            @endscript
        @endif
    </div>
</div>
