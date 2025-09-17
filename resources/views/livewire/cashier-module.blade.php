<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl p-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">
                    Cashier Modules
                </h1>
                <p class="mt-2 text-gray-600">
                    Select a module to begin your session.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                <!-- Start Shift -->
                <button wire:click="openStartShiftModal" @if($shiftStarted) disabled @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:filter disabled:grayscale">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-play text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Start Shift</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Begin session</p>
                </button>

                <!-- Add Client -->
                <a @if($shiftStarted) href="{{ route('clients') }}" @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 @if(!$shiftStarted) opacity-50 cursor-not-allowed filter grayscale @endif">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-user-plus text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Add Client</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">New client</p>
                </a>

                <!-- Add Sale -->
                <a @if($shiftStarted) href="{{ route('add-sale') }}" @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 @if(!$shiftStarted) opacity-50 cursor-not-allowed filter grayscale @endif">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-plus text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Add Sale</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">New transaction</p>
                </a>

                <!-- Transactions -->
                <a @if($shiftStarted) href="{{ route('transactions') }}" @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 @if(!$shiftStarted) opacity-50 cursor-not-allowed filter grayscale @endif">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-list text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Transactions</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">View sales history</p>
                </a>

                <!-- Add Remittance -->
                <a @if($shiftStarted) href="{{ route('remittances') }}" @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 @if(!$shiftStarted) opacity-50 cursor-not-allowed filter grayscale @endif">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-money-bill-wave text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Remittances</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Submit cash</p>
                </a>

                <!-- Finalize Batch Delivery -->
                <a @if($shiftStarted) href="{{ route('finalize-delivery-batch') }}" @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 @if(!$shiftStarted) opacity-50 cursor-not-allowed filter grayscale @endif">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-truck text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Finalize Batch</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Delivery dispatching</p>
                </a>

                <!-- End Shift -->
                <button wire:click="openEndShiftModal" @if(!$shiftStarted) disabled @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:filter disabled:grayscale">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-stop text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">End Shift</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Conclude session</p>
                </button>
            </div>
        </div>
    </div>

    <!-- Start Shift Modal -->
    <x-dialog-modal wire:model="showStartShiftModal">
        <x-slot name="title">
            Start Shift
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cash Denominations</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Denomination
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($denominations as $denomination)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ₱{{ number_format($denomination->value, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <x-input type="number" class="w-24 text-right"
                                            wire:model.live="cashCountQuantities.{{ $denomination->id }}"
                                            min="0"
                                            wire:change="calculateTotalCashIn" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        ₱{{ number_format((float)($cashCountQuantities[$denomination->id] ?? 0) * $denomination->value, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td colspan="2" class="px-6 py-3 text-right text-base font-bold text-gray-800 uppercase">
                                    Grand Total:
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-base font-bold text-gray-800 text-right">
                                    ₱{{ number_format($this->totalCashIn, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <x-input-error for="cashCountQuantities" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showStartShiftModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="startShift" wire:loading.attr="disabled">
                Start Shift
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- End Shift Modal -->
    <x-dialog-modal wire:model="showEndShiftModal">
        <x-slot name="title">
            End Shift
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Cash Denominations (End Shift)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Denomination
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Quantity
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($denominations as $denomination)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        ₱{{ number_format($denomination->value, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <x-input type="number" class="w-24 text-right"
                                            wire:model.live="endShiftCashCountQuantities.{{ $denomination->id }}"
                                            min="0"
                                            wire:change="calculateTotalCashOut" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                        ₱{{ number_format((float)($endShiftCashCountQuantities[$denomination->id] ?? 0) * $denomination->value, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50">
                                <td colspan="2" class="px-6 py-3 text-right text-base font-bold text-gray-800 uppercase">
                                    Grand Total:
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-base font-bold text-gray-800 text-right">
                                    ₱{{ number_format($this->totalCashOutComputed, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <x-input-error for="endShiftCashCountQuantities" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEndShiftModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="endShift" wire:loading.attr="disabled">
                End Shift
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Daily Report Modal -->
    <x-dialog-modal wire:model="showDailyReportModal">
        <x-slot name="title">
            Daily End of Shift Report
        </x-slot>

        <x-slot name="content">
            @if(!empty($dailyReportData))
                <div class="space-y-4">
                    <p><strong>Time In:</strong> {{ \Carbon\Carbon::parse($dailyReportData['time_in'])->format('m/d/Y h:i A') }}</p>
                    <p><strong>Time Out:</strong> {{ \Carbon\Carbon::parse($dailyReportData['time_out'])->format('m/d/Y h:i A') }}</p>
                    <hr>
                    <h3 class="text-lg font-bold">Sales Summary</h3>
                    <p><strong>Starting Cash:</strong> {{ number_format($dailyReportData['total_cash_in'], 2) }}</p>
                    <p><strong>Ending Cash (Actual):</strong> {{ number_format($dailyReportData['total_cash_out'], 2) }}</p>
                    <p><strong>Ending Cash (System):</strong> {{ number_format($dailyReportData['system_computed_cash'], 2) }}</p>
                    <p><strong>Discrepancy:</strong> <span class="{{ $dailyReportData['discrepancy'] == 0 ? 'text-green-500' : 'text-red-500' }}">{{ number_format($dailyReportData['discrepancy'], 2) }}</span></p>
                    <hr>
                    <h3 class="text-lg font-bold">Sales by Payment Method</h3>
                    <ul>
                        @foreach($dailyReportData['sales_by_payment_method'] as $methodId => $total)
                            <li>{{ $dailyReportData['payment_methods'][$methodId] ?? 'N/A' }}: {{ number_format($total, 2) }}</li>
                        @endforeach
                    </ul>
                    <hr>
                    <h3 class="text-lg font-bold">Sales by Product</h3>
                    <ul>
                        @foreach($dailyReportData['sales_by_product'] as $productName => $data)
                            <li>{{ $productName }}: {{ $data['quantity'] }} pcs - {{ number_format($data['total'], 2) }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDailyReportModal', false)" wire:loading.attr="disabled">
                Close
            </x-secondary-button>
            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" onclick="window.print()">
                Print
            </button>
        </x-slot>
    </x-dialog-modal>
</div>
