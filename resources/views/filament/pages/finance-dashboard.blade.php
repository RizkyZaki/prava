<x-filament-panels::page>
    @php
        $formatRupiah = function($amount) {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        };
    @endphp

    {{-- Company Filter --}}
    <div class="mb-6">
        <form method="GET" class="flex items-center gap-4">
            <select name="company_id" onchange="this.form.submit()"
                class="fi-select-input block w-64 rounded-lg border-gray-300 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Semua Perusahaan</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $selectedCompanyId == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        {{-- Total Saldo Kas --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-3">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-primary-50 p-3 dark:bg-primary-500/10">
                        <x-heroicon-o-wallet class="h-6 w-6 text-primary-600 dark:text-primary-400" />
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Saldo Kas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $formatRupiah($totalCashBalance) }}</p>
                </div>
            </div>
        </div>

        {{-- Pemasukan Bulan Ini --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-3">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-success-50 p-3 dark:bg-success-500/10">
                        <x-heroicon-o-arrow-trending-up class="h-6 w-6 text-success-600 dark:text-success-400" />
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pemasukan Bulan Ini</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $formatRupiah($totalIncomesThisMonth) }}</p>
                    @if($incomeChange != 0)
                        <p class="text-xs mt-1 {{ $incomeChange > 0 ? 'text-success-600' : 'text-danger-600' }}">
                            {{ $incomeChange > 0 ? '+' : '' }}{{ $incomeChange }}% dari bulan lalu
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pengeluaran Bulan Ini --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-3">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-danger-50 p-3 dark:bg-danger-500/10">
                        <x-heroicon-o-arrow-trending-down class="h-6 w-6 text-danger-600 dark:text-danger-400" />
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pengeluaran Bulan Ini</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $formatRupiah($totalExpensesThisMonth) }}</p>
                    @if($expenseChange != 0)
                        <p class="text-xs mt-1 {{ $expenseChange > 0 ? 'text-danger-600' : 'text-success-600' }}">
                            {{ $expenseChange > 0 ? '+' : '' }}{{ $expenseChange }}% dari bulan lalu
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Pemasukan Bulan Lalu --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-3">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-500/10">
                        <x-heroicon-o-clock class="h-6 w-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pemasukan Bulan Lalu</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $formatRupiah($totalIncomesLastMonth) }}</p>
                </div>
            </div>
        </div>

        {{-- Pengeluaran Bulan Lalu --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-3">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-warning-50 p-3 dark:bg-warning-500/10">
                        <x-heroicon-o-clock class="h-6 w-6 text-warning-600 dark:text-warning-400" />
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pengeluaran Bulan Lalu</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $formatRupiah($totalExpensesLastMonth) }}</p>
                </div>
            </div>
        </div>

        {{-- Pending Approval --}}
        <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center gap-x-3">
                <div class="flex-shrink-0">
                    <div class="rounded-lg bg-info-50 p-3 dark:bg-info-500/10">
                        <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-info-600 dark:text-info-400" />
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Menunggu Persetujuan</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $pendingExpenses + $pendingIncomes }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $formatRupiah($pendingAmount + $pendingIncomeAmount) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Monthly Income vs Expense Trend (Bar Chart) --}}
        <div class="lg:col-span-2 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <x-heroicon-o-chart-bar class="inline-block h-5 w-5 mr-1" />
                Pemasukan vs Pengeluaran (12 Bulan Terakhir)
            </h3>
            <div style="height: 300px;">
                <canvas id="monthlyIncomeExpenseChart"></canvas>
            </div>
        </div>

        {{-- Category Breakdown (Doughnut Chart) --}}
        <div class="lg:col-span-1 rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <x-heroicon-o-chart-pie class="inline-block h-5 w-5 mr-1" />
                Pengeluaran per Kategori
            </h3>
            <div style="height: 300px;">
                <canvas id="categoryExpenseChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Company Comparison + Cash Accounts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Company Expense Comparison --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <x-heroicon-o-building-office-2 class="inline-block h-5 w-5 mr-1" />
                Pengeluaran per Perusahaan (Bulan Ini)
            </h3>
            <div style="height: 250px;">
                <canvas id="companyExpenseChart"></canvas>
            </div>
        </div>

        {{-- Cash Account Balances --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                <x-heroicon-o-wallet class="inline-block h-5 w-5 mr-1" />
                Saldo Kas
            </h3>
            <div class="space-y-3 max-h-[250px] overflow-y-auto">
                @forelse($cashAccounts as $account)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $account->name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $account->company->name }}
                                @if($account->bank_name) &middot; {{ $account->bank_name }} @endif
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold {{ $account->current_balance < 0 ? 'text-danger-600' : 'text-success-600' }}">
                                {{ $formatRupiah($account->current_balance) }}
                            </p>
                            <p class="text-xs text-gray-400">Awal: {{ $formatRupiah($account->initial_balance) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-500 dark:text-gray-400">
                        <x-heroicon-o-wallet class="h-12 w-12 mx-auto mb-2 opacity-50" />
                        <p>Belum ada kas yang dibuat</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent Expenses Table --}}
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <x-heroicon-o-arrow-trending-down class="inline-block h-5 w-5 mr-1 text-danger-600" />
            Pengeluaran Terbaru
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Perusahaan</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Judul</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Kategori</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Kas</th>
                        <th class="text-right py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Jumlah</th>
                        <th class="text-center py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentExpenses as $expense)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="py-3 px-2 text-gray-700 dark:text-gray-300">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td class="py-3 px-2">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                                    {{ $expense->company->name }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-gray-900 dark:text-white font-medium">{{ $expense->title }}</td>
                            <td class="py-3 px-2">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                    {{ $expense->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-gray-500 dark:text-gray-400">{{ $expense->cashAccount->name ?? '-' }}</td>
                            <td class="py-3 px-2 text-right font-bold text-gray-900 dark:text-white">{{ $formatRupiah($expense->amount) }}</td>
                            <td class="py-3 px-2 text-center">
                                @if($expense->status === 'approved')
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                        Disetujui
                                    </span>
                                @elseif($expense->status === 'pending')
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Belum ada data pengeluaran
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Incomes Table --}}
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            <x-heroicon-o-arrow-trending-up class="inline-block h-5 w-5 mr-1 text-success-600" />
            Pemasukan Terbaru
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Tanggal</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Perusahaan</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Judul</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Sumber</th>
                        <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Kas</th>
                        <th class="text-right py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Jumlah</th>
                        <th class="text-center py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentIncomes as $income)
                        <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="py-3 px-2 text-gray-700 dark:text-gray-300">{{ $income->income_date->format('d M Y') }}</td>
                            <td class="py-3 px-2">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400">
                                    {{ $income->company->name }}
                                </span>
                            </td>
                            <td class="py-3 px-2 text-gray-900 dark:text-white font-medium">{{ $income->title }}</td>
                            <td class="py-3 px-2">
                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-info-50 text-info-700 dark:bg-info-500/10 dark:text-info-400">
                                    @if($income->source === 'project') Project
                                    @elseif($income->source === 'jasa') Jasa
                                    @elseif($income->source === 'penjualan') Penjualan
                                    @else Lainnya
                                    @endif
                                </span>
                            </td>
                            <td class="py-3 px-2 text-gray-500 dark:text-gray-400">{{ $income->cashAccount->name ?? '-' }}</td>
                            <td class="py-3 px-2 text-right font-bold text-gray-900 dark:text-white">{{ $formatRupiah($income->amount) }}</td>
                            <td class="py-3 px-2 text-center">
                                @if($income->status === 'approved')
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-400">
                                        Disetujui
                                    </span>
                                @elseif($income->status === 'pending')
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-400">
                                        Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium bg-danger-50 text-danger-700 dark:bg-danger-500/10 dark:text-danger-400">
                                        Ditolak
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                Belum ada data pemasukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart.js --}}
    @assets
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endassets
    <script>
        function initFinanceCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initFinanceCharts, 100);
                return;
            }
            if (!document.getElementById('monthlyIncomeExpenseChart')) return;
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#9CA3AF' : '#6B7280';
            const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

            // Monthly Income vs Expense Bar Chart
            const monthlyExpenseData = @json($monthlyExpenses);
            const monthlyIncomeData = @json($monthlyIncomes);
            new Chart(document.getElementById('monthlyIncomeExpenseChart'), {
                type: 'bar',
                data: {
                    labels: monthlyExpenseData.map(d => d.month),
                    datasets: [
                        {
                            label: 'Pemasukan',
                            data: monthlyIncomeData.map(d => d.amount),
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Pengeluaran',
                            data: monthlyExpenseData.map(d => d.amount),
                            backgroundColor: 'rgba(239, 68, 68, 0.7)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: { color: textColor }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': Rp ' + ctx.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'jt';
                                    if (value >= 1000) return 'Rp ' + (value/1000).toFixed(0) + 'rb';
                                    return 'Rp ' + value;
                                }
                            },
                            grid: { color: gridColor }
                        },
                        x: {
                            ticks: { color: textColor, maxRotation: 45 },
                            grid: { display: false }
                        }
                    }
                }
            });

            // Category Doughnut Chart
            const catData = @json($categoryExpenses);
            const defaultColors = [
                '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
                '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6366F1'
            ];
            new Chart(document.getElementById('categoryExpenseChart'), {
                type: 'doughnut',
                data: {
                    labels: catData.map(d => d.name),
                    datasets: [{
                        data: catData.map(d => parseFloat(d.total)),
                        backgroundColor: catData.map((d, i) => d.color || defaultColors[i % defaultColors.length]),
                        borderWidth: 2,
                        borderColor: isDark ? '#1F2937' : '#FFFFFF',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, padding: 12, usePointStyle: true }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.label + ': Rp ' + ctx.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // Company Comparison Bar Chart
            const compData = @json($companyExpenses);
            new Chart(document.getElementById('companyExpenseChart'), {
                type: 'bar',
                data: {
                    labels: compData.map(d => d.name),
                    datasets: [{
                        label: 'Pengeluaran',
                        data: compData.map(d => parseFloat(d.total)),
                        backgroundColor: ['rgba(59, 130, 246, 0.7)', 'rgba(239, 68, 68, 0.7)', 'rgba(16, 185, 129, 0.7)'],
                        borderColor: ['rgba(59, 130, 246, 1)', 'rgba(239, 68, 68, 1)', 'rgba(16, 185, 129, 1)'],
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return 'Rp ' + ctx.raw.toLocaleString('id-ID');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                color: textColor,
                                callback: function(value) {
                                    if (value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'jt';
                                    if (value >= 1000) return 'Rp ' + (value/1000).toFixed(0) + 'rb';
                                    return 'Rp ' + value;
                                }
                            },
                            grid: { color: gridColor }
                        },
                        y: {
                            ticks: { color: textColor },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        // Init on various events for SPA compatibility
        document.addEventListener('DOMContentLoaded', initFinanceCharts);
        document.addEventListener('livewire:navigated', initFinanceCharts);
        // Also try init immediately in case scripts loaded
        initFinanceCharts();
    </script>
</x-filament-panels::page>
