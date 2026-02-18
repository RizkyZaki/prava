@php
    $record = $getRecord();
    $items = $record->projectItems()->orderBy('id')->get();
    $totalValue = $record->total_items_value;
@endphp

<div class="space-y-4">
    @if($items->count() > 0)
        {{-- Summary Card --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Nilai Item</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Total Item</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $items->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700">
                        <th class="text-left px-3 py-3 text-gray-600 dark:text-gray-300 font-semibold">No</th>
                        <th class="text-left px-3 py-3 text-gray-600 dark:text-gray-300 font-semibold">Nama Item</th>
                        <th class="text-left px-3 py-3 text-gray-600 dark:text-gray-300 font-semibold">Keterangan</th>
                        <th class="text-right px-3 py-3 text-gray-600 dark:text-gray-300 font-semibold">Jumlah</th>
                        <th class="text-left px-3 py-3 text-gray-600 dark:text-gray-300 font-semibold">Satuan</th>
                        <th class="text-right px-3 py-3 text-gray-600 dark:text-gray-300 font-semibold">Harga Satuan</th>
                        <th class="text-right px-3 py-3 text-gray-600 dark:text-gray-300 font-semibold">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $index => $item)
                        <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-3 py-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                            <td class="px-3 py-3">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ $item->item_name }}</p>
                            </td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-400">
                                {{ $item->description ? \Str::limit($item->description, 50) : '-' }}
                            </td>
                            <td class="px-3 py-3 text-right font-medium text-gray-900 dark:text-white">
                                {{ number_format($item->quantity, 2, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-gray-600 dark:text-gray-400">
                                {{ $item->unit }}
                            </td>
                            <td class="px-3 py-3 text-right text-gray-900 dark:text-white">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="px-3 py-3 text-right font-semibold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-700 font-bold border-t-2 border-gray-200 dark:border-gray-600">
                        <td colspan="6" class="px-3 py-3 text-gray-700 dark:text-gray-300 text-right">Total Keseluruhan</td>
                        <td class="px-3 py-3 text-right text-blue-700 dark:text-blue-300">Rp {{ number_format($totalValue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($record->project_value && $totalValue != $record->project_value)
            <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Perbedaan Nilai</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                            Total breakdown item (Rp {{ number_format($totalValue, 0, ',', '.') }})
                            @if($totalValue > $record->project_value)
                                <strong>melebihi</strong>
                            @else
                                <strong>kurang dari</strong>
                            @endif
                            nilai kontrak (Rp {{ number_format($record->project_value, 0, ',', '.') }}).
                            Selisih: Rp {{ number_format(abs($totalValue - $record->project_value), 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Belum ada breakdown item</p>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">Edit project untuk menambahkan item kegiatan/pengadaan</p>
        </div>
    @endif
</div>
