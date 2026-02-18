<div class="space-y-4">
    {{-- Project Info --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Project</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $record->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Perusahaan</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $record->company?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Nilai Kegiatan / Kontrak</p>
                <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($projectValue, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ ucfirst($record->status) }}</p>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3 text-center">
            <p class="text-xs text-green-600 dark:text-green-400">Total Pencairan</p>
            <p class="text-lg font-bold text-green-700 dark:text-green-300">Rp {{ number_format($totalDisbursed, 0, ',', '.') }}</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 text-center">
            <p class="text-xs text-red-600 dark:text-red-400">Total Pengeluaran</p>
            <p class="text-lg font-bold text-red-700 dark:text-red-300">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 text-center">
            <p class="text-xs text-blue-600 dark:text-blue-400">Sisa (Pencairan - Pengeluaran)</p>
            @php $sisa = $totalDisbursed - $totalExpenses; @endphp
            <p class="text-lg font-bold {{ $sisa >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-red-700 dark:text-red-300' }}">
                Rp {{ number_format($sisa, 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Disbursement History --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Riwayat Pencairan</h3>
        @if($disbursements->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Tanggal</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">Keterangan</th>
                            <th class="text-left px-3 py-2 text-gray-600 dark:text-gray-300">No. Invoice</th>
                            <th class="text-right px-3 py-2 text-gray-600 dark:text-gray-300">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($disbursements as $d)
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $d->disbursement_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-gray-900 dark:text-white">{{ $d->description ?? '-' }}</td>
                                <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $d->invoice_number ?? '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($d->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-gray-400 dark:text-gray-500">
                <p>Belum ada pencairan untuk project ini.</p>
            </div>
        @endif
    </div>

    <div class="text-xs text-gray-400 dark:text-gray-500 text-center italic">
        * Invoice ini bersifat dummy / ringkasan internal
    </div>
</div>
