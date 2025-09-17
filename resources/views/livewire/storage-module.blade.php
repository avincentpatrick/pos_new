<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl p-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">
                    Storage Modules
                </h1>
                <p class="mt-2 text-gray-600">
                    Select a module to begin your session.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                <!-- Start Shift -->
                <button wire:click="openStartShiftModal" @if($shiftStarted) disabled @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:filter disabled:grayscale">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-play text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Start Shift</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Begin session</p>
                </button>

                <!-- Add Stock -->
                <a @if($shiftStarted) href="{{ route('add-stock') }}" @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 @if(!$shiftStarted) opacity-50 cursor-not-allowed filter grayscale @endif">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-plus text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Add Stock</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">New inventory</p>
                </a>

                <!-- Fulfill Orders -->
                <a @if($shiftStarted) href="{{ route('fulfill-orders') }}" @endif class="group flex flex-col items-center justify-center p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300 @if(!$shiftStarted) opacity-50 cursor-not-allowed filter grayscale @endif">
                    <div class="w-20 h-20 bg-gradient-to-br from-custom-orange to-orange-600 text-white rounded-full flex items-center justify-center transition-all duration-300 group-hover:from-orange-500 group-hover:to-orange-700">
                        <i class="fa-solid fa-box-open text-4xl"></i>
                    </div>
                    <h2 class="mt-5 text-lg font-semibold text-gray-800 text-center">Fulfill Orders</h2>
                    <p class="mt-1 text-sm text-gray-500 text-center">Process orders</p>
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

    <!-- Start Shift Stock Count Modal -->
    <x-dialog-modal wire:model="showStartShiftModal">
        <x-slot name="title">
            Start Shift Stock Count
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                @foreach ($products as $product)
                    <div>
                        <x-label for="stock-{{ $product->id }}" value="{{ $product->product_name }}" />
                        <x-input id="stock-{{ $product->id }}" type="number" class="mt-1 block w-full"
                            wire:model.live="stockCountQuantities.{{ $product->id }}" min="0" />
                        <x-input-error for="stockCountQuantities.{{ $product->id }}" class="mt-2" />
                    </div>
                @endforeach
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

    <!-- End Shift Stock Count Modal -->
    <x-dialog-modal wire:model="showEndShiftModal">
        <x-slot name="title">
            End Shift Stock Count
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                @foreach ($products as $product)
                    <div>
                        <x-label for="end-stock-{{ $product->id }}" value="{{ $product->product_name }}" />
                        <x-input id="end-stock-{{ $product->id }}" type="number" class="mt-1 block w-full"
                            wire:model.live="endShiftStockCountQuantities.{{ $product->id }}" min="0" />
                        <x-input-error for="endShiftStockCountQuantities.{{ $product->id }}" class="mt-2" />
                    </div>
                @endforeach
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEndShiftModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-button class="ms-2" wire:click="endShift" wire:loading.attr="disabled">
                End Shift
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Shift Report Modal -->
    <x-dialog-modal wire:model="showShiftReportModal">
        <x-slot name="title">
            Storage Shift Report
        </x-slot>

        <x-slot name="content">
            @if ($selectedStorageDutyLog)
                <div class="p-4 bg-white rounded-lg">
                    <div class="mb-4">
                        <p><strong>Storage Personnel:</strong> {{ $selectedStorageDutyLog->user->name }}</p>
                        <p><strong>Shift Started:</strong> {{ $selectedStorageDutyLog->time_in->format('F j, Y g:i a') }}</p>
                        <p><strong>Shift Ended:</strong> {{ $selectedStorageDutyLog->time_out->format('F j, Y g:i a') }}</p>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-700 mb-4">Product Stock Summary</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Count</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restocks</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Initial Dispensed</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Received by Client</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Returns</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Running Stock</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loss</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Count</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discrepancy</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($shiftReport as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['product_name'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['start_stock_count'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['restocks'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['initial_dispensed'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['actual_received'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['returns'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['system_computed_remaining_stock'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['loss'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['end_stock_count_reported'] }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $item['discrepancy'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <p>No report data available.</p>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showShiftReportModal', false)" wire:loading.attr="disabled">
                Close
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
