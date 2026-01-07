<div class="fi-wi-stats-overview-card relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    @php
        $data = $this->getData();
        $salary = $data['salary'];
        $monthlyPayroll = $data['monthlyPayroll'];
        $monthlyDeductions = $data['monthlyDeductions'];
        $currentMonth = $data['currentMonth'];
    @endphp

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    💰 My Salary Information
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $currentMonth }}
                </p>
            </div>
        </div>

        @if($salary)
            <!-- Salary Overview with Blur Effect -->
            <div class="space-y-4">
                <!-- Gross Salary -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-green-600 dark:text-green-400 uppercase tracking-wide mb-1">
                                Gross Salary
                            </p>
                            <div class="group cursor-help">
                                <p class="blur-md group-hover:blur-none text-2xl font-bold text-green-700 dark:text-green-300 transition-all duration-300">
                                    Rp {{ number_format($salary->gross_salary, 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Hover to reveal
                                </p>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-green-500/10 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Deductions -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg p-4 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-red-600 dark:text-red-400 uppercase tracking-wide mb-1">
                                This Month's Deductions
                            </p>
                            <div class="group cursor-help">
                                <p class="blur-md group-hover:blur-none text-2xl font-bold text-red-700 dark:text-red-300 transition-all duration-300">
                                    Rp {{ number_format($monthlyDeductions->sum('deduction_amount'), 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                                    {{ $monthlyDeductions->count() }} deduction(s)
                                </p>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-red-500/10 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Net Salary -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4 transition-all hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wide mb-1">
                                Estimated Net Salary
                            </p>
                            <div class="group cursor-help">
                                <p class="blur-md group-hover:blur-none text-2xl font-bold text-blue-700 dark:text-blue-300 transition-all duration-300">
                                    Rp {{ number_format($salary->gross_salary - $monthlyDeductions->sum('deduction_amount'), 0, ',', '.') }}
                                </p>
                                <p class="text-xs text-blue-600 dark:text-blue-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    Hover to reveal
                                </p>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-500/10 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deduction Details -->
            @if($monthlyDeductions->count() > 0)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                        📋 This Month's Deduction Details
                    </h4>
                    <div class="space-y-2">
                        @foreach($monthlyDeductions as $deduction)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800/50 rounded-lg p-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $deduction->deduction_date->format('d M Y') }}
                                        </span>
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $deduction->deduction_type === 'late' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' : ($deduction->deduction_type === 'absent' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400') }}">
                                            {{ $deduction->deduction_type_label }}
                                        </span>
                                        @if(!$deduction->is_approved)
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                                Pending
                                            </span>
                                        @endif
                                    </div>
                                    @if($deduction->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $deduction->description }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                                        - Rp {{ number_format($deduction->deduction_amount, 0, ',', '.') }}
                                    </p>
                                    @if($deduction->minutes_late > 0)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $deduction->minutes_late }} min
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 text-center">
                    <p class="text-sm font-medium text-green-700 dark:text-green-400">
                        🎉 No deductions this month! Keep up the good work!
                    </p>
                </div>
            @endif

            <!-- Salary Breakdown -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                    💵 Salary Breakdown
                </h4>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Base Salary:</span>
                        <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
                    </div>
                    @if($salary->transport_allowance > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Transport:</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($salary->transport_allowance, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($salary->meal_allowance > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Meal:</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($salary->meal_allowance, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($salary->position_allowance > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Position:</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($salary->position_allowance, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($salary->other_allowance > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Other:</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($salary->other_allowance, 0, ',', '.') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <!-- No Salary Configured -->
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-6 text-center">
                <svg class="w-12 h-12 text-yellow-600 dark:text-yellow-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h4 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300 mb-2">
                    No Salary Configuration
                </h4>
                <p class="text-sm text-yellow-700 dark:text-yellow-400">
                    Your salary has not been configured yet. Please contact HR or your administrator.
                </p>
            </div>
        @endif
    </div>
</div>
