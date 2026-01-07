<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Employee</h4>
            <p class="text-base">{{ $record->user->name }}</p>
        </div>
        <div>
            <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Date</h4>
            <p class="text-base">{{ $record->deduction_date->format('d F Y') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Type</h4>
            <p class="text-base">{{ $record->deduction_type_label }}</p>
        </div>
        <div>
            <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Amount</h4>
            <p class="text-base font-bold text-red-600">Rp {{ number_format($record->deduction_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    @if($record->minutes_late > 0)
    <div>
        <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Minutes Late</h4>
        <p class="text-base">{{ $record->minutes_late }} minutes</p>
    </div>
    @endif

    @if($record->minutes_early > 0)
    <div>
        <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Minutes Early Leave</h4>
        <p class="text-base">{{ $record->minutes_early }} minutes</p>
    </div>
    @endif

    @if($record->hours_short > 0)
    <div>
        <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Hours Short</h4>
        <p class="text-base">{{ number_format($record->hours_short, 2) }} hours</p>
    </div>
    @endif

    <div>
        <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Reason</h4>
        <p class="text-base">{{ $record->reason }}</p>
    </div>

    @if($record->calculation_details)
    <div>
        <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400 mb-2">Calculation Details</h4>
        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded text-sm">
            <pre class="whitespace-pre-wrap">{{ json_encode($record->calculation_details, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    @endif

    @if($record->is_approved)
    <div class="border-t pt-4">
        <h4 class="font-semibold text-sm text-gray-500 dark:text-gray-400">Approval Info</h4>
        <p class="text-base">Approved by: {{ $record->approvedBy->name }}</p>
        <p class="text-sm text-gray-500">{{ $record->approved_at->format('d F Y H:i') }}</p>
    </div>
    @endif
</div>
