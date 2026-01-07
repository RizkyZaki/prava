<div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 p-8 shadow-xl">
    @php
        $data = $this->getViewData();
    @endphp

    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute -right-20 -top-20 h-40 w-40 rounded-full bg-white blur-3xl"></div>
        <div class="absolute -bottom-20 -left-20 h-40 w-40 rounded-full bg-white blur-3xl"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex items-center justify-between">
        <div class="space-y-2">
            <div class="flex items-center gap-3">
                <span class="text-5xl animate-bounce">{{ $data['icon'] }}</span>
                <div>
                    <h2 class="text-3xl font-bold text-white">
                        {{ $data['greeting'] }}, {{ $data['userName'] }}!
                    </h2>
                    <p class="text-blue-100 text-lg mt-1">
                        {{ $data['role'] }} • {{ $data['currentDate'] }}
                    </p>
                </div>
            </div>
            <p class="text-white/90 text-sm mt-4 max-w-2xl">
                Selamat datang kembali di sistem manajemen kantor. Semoga hari Anda produktif dan menyenangkan! 💼
            </p>
        </div>

        <!-- Quick Info Cards -->
        <div class="hidden lg:flex gap-4">
            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center min-w-[120px]">
                <div class="text-white/80 text-xs uppercase tracking-wide mb-1">Waktu</div>
                <div class="text-white text-2xl font-bold" id="current-time">
                    {{ now()->format('H:i') }}
                </div>
            </div>
            <div class="bg-white/20 backdrop-blur-sm rounded-lg p-4 text-center min-w-[120px]">
                <div class="text-white/80 text-xs uppercase tracking-wide mb-1">Hari Ke</div>
                <div class="text-white text-2xl font-bold">
                    {{ now()->dayOfYear }}
                </div>
                <div class="text-white/70 text-xs">dari {{ now()->daysInYear }} hari</div>
            </div>
        </div>
    </div>

    <!-- Decorative Elements -->
    <div class="absolute bottom-0 right-0 opacity-20">
        <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="100" cy="100" r="80" stroke="white" stroke-width="2" stroke-dasharray="5,5"/>
            <circle cx="100" cy="100" r="60" stroke="white" stroke-width="2" stroke-dasharray="5,5"/>
            <circle cx="100" cy="100" r="40" stroke="white" stroke-width="2" stroke-dasharray="5,5"/>
        </svg>
    </div>
</div>

<script>
    // Update time every minute
    setInterval(() => {
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            const now = new Date();
            timeElement.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        }
    }, 60000);
</script>
