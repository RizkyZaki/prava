@php
    $record = $getRecord();
    $expenses = $record->expenses()->with(['category', 'creator'])->orderBy('expense_date', 'desc')->get();
    $disbursements = $record->disbursements()->with('creator')->orderBy('disbursement_date', 'desc')->get();
    $totalExpenses = $record->total_expenses;
    $totalDisbursements = $record->total_disbursements;
    $projectValue = $record->project_value ?? 0;

    // Monthly data for chart (last 6 months)
    $months = collect();
    for ($i = 5; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $monthKey = $date->format('Y-m');
        $monthLabel = $date->translatedFormat('M Y');

        $monthExpense = $record->expenses()
            ->where('status', 'approved')
            ->whereYear('expense_date', $date->year)
            ->whereMonth('expense_date', $date->month)
            ->sum('amount');

        $monthDisbursement = $record->disbursements()
            ->whereYear('disbursement_date', $date->year)
            ->whereMonth('disbursement_date', $date->month)
            ->sum('amount');

        $months->push([
            'label' => $monthLabel,
            'expense' => (float) $monthExpense,
            'disbursement' => (float) $monthDisbursement,
        ]);
    }
@endphp

<div class="space-y-6">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 text-center">
            <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Nilai Kontrak</p>
            <p class="text-lg font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($projectValue, 0, ',', '.') }}</p>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 text-center">
            <p class="text-xs text-green-600 dark:text-green-400 font-medium">Total Pencairan</p>
            <p class="text-lg font-bold text-green-700 dark:text-green-300">Rp {{ number_format($totalDisbursements, 0, ',', '.') }}</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-center">
            <p class="text-xs text-red-600 dark:text-red-400 font-medium">Total Pengeluaran</p>
            <p class="text-lg font-bold text-red-700 dark:text-red-300">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Grafik Pencairan vs Pengeluaran (6 Bulan Terakhir)</h3>
        <canvas id="projectFinanceChart" height="200"></canvas>
    </div>

    {{-- Disbursements Table --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Riwayat Pencairan</h3>
        @if($disbursements->count() > 0)
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Tanggal</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Keterangan</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Oleh</th>
                            <th class="text-right px-3 py-2 text-gray-600 dark:text-gray-300">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disbursements as $d)
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $d->disbursement_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $d->description ?? '-' }}</td>
                                <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $d->creator?->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-700 font-bold">
                            <td colspan="3" class="px-3 py-2 text-gray-700 dark:text-gray-300">Total</td>
                            <td class="px-3 py-2 text-right text-green-700 dark:text-green-300">Rp {{ number_format($totalDisbursements, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada pencairan.</p>
        @endif
    </div>

    {{-- Expenses Table --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Riwayat Pengeluaran</h3>
        @if($expenses->count() > 0)
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700">
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Tanggal</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Judul</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Kategori</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Status</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Oleh</th>
                            <th class="text-right px-3 py-2 text-gray-600 dark:text-gray-300">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $e)
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $e->expense_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-gray-900 dark:text-white">{{ \Str::limit($e->title, 30) }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                        {{ $e->category?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    @php
                                        $statusColor = match($e->status) {
                                            'approved' => 'green',
                                            'rejected' => 'red',
                                            default => 'yellow',
                                        };
                                        $statusLabel = match($e->status) {
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            default => 'Pending',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 dark:bg-{{ $statusColor }}-900/30 dark:text-{{ $statusColor }}-400">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $e->creator?->name ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-red-600 dark:text-red-400">Rp {{ number_format($e->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-gray-700 font-bold">
                            <td colspan="5" class="px-3 py-2 text-gray-700 dark:text-gray-300">Total (Approved)</td>
                            <td class="px-3 py-2 text-right text-red-700 dark:text-red-300">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada pengeluaran.</p>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('projectFinanceChart');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($months->pluck('label')),
                datasets: [
                    {
                        label: 'Pencairan',
                        data: @json($months->pluck('disbursement')),
                        backgroundColor: 'rgba(34, 197, 94, 0.7)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Pengeluaran',
                        data: @json($months->pluck('expense')),
                        backgroundColor: 'rgba(239, 68, 68, 0.7)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                }
            }
        });
    });
</script>
