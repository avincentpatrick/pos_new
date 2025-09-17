<div class="py-12" wire:poll.5s>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-gray-100 overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('cashier-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Cashier Modules
                </a>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Finalize Delivery Batch</h2>

            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Pending Transactions List (Full Width) -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Pending Transactions for Delivery</h3>
                <div class="overflow-x-auto bg-white p-4 rounded-lg shadow-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Client Address</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($transactions as $transaction)
                                @php
                                    $salesCount = $transaction->sales->count();
                                    $paymentMethodId = $transaction->payments->first()->payment_method_id ?? null;
                                    $paymentMethodDisplay = '';
                                    $isFullyPaid = $transaction->remaining_balance <= 0;
                                    $firstPaymentMethodName = $transaction->payments->first()->paymentMethod->payment_method_name ?? 'N/A';

                                    if ($paymentMethodId === 3) { // Credit
                                        $paymentMethodDisplay = '<span class="text-gray-500 font-semibold">' . $firstPaymentMethodName . '</span>';
                                    } elseif ($paymentMethodId === 7) { // COD
                                        if (!$isFullyPaid) {
                                            $paymentMethodDisplay = '<span class="text-red-500 font-semibold">' . $firstPaymentMethodName . '<span class="block text-xs text-gray-500">(-₱' . number_format($transaction->remaining_balance, 2) . ')</span></span>';
                                        } else {
                                            $paymentMethodDisplay = '<span class="text-green-500 font-semibold">Paid</span>';
                                        }
                                    } else { // Other payment methods (assuming fully paid if not COD/Credit)
                                        $paymentMethodDisplay = '<span class="text-green-500 font-semibold">Paid</span>';
                                    }
                                @endphp
                                @foreach ($transaction->sales as $sale)
                                    <tr draggable="true"
                                        x-data="{ isDragging: false }"
                                        @dragstart="isDragging = true; event.dataTransfer.setData('transactionId', {{ $transaction->id }});"
                                        @dragend="isDragging = false"
                                        class="cursor-grab transition-all duration-200 ease-in-out @if($loop->parent->even) bg-gray-50 @else bg-white @endif"
                                        :class="{ 'opacity-50 scale-95': isDragging }">
                                        @if ($loop->first)
                                            <td class="px-6 py-2 whitespace-nowrap text-center" rowspan="{{ $salesCount }}">{{ $transaction->client->name ?? 'N/A' }}</td>
                                        @endif
                                        <td class="px-2 py-2 whitespace-nowrap text-center">{{ $sale->quantity }}</td>
                                        <td class="px-2 py-2 whitespace-nowrap text-center">{{ $sale->product->product_description ?? 'N/A' }}</td>
                                        @if ($loop->first)
                                            <td class="px-6 py-2 whitespace-nowrap text-center" rowspan="{{ $salesCount }}">{{ $transaction->client->address ?? 'N/A' }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap text-center" rowspan="{{ $salesCount }}">{{ $transaction->notes ?? 'N/A' }}</td>
                                            <td class="px-6 py-2 whitespace-nowrap text-center" rowspan="{{ $salesCount }}">
                                                {!! $paymentMethodDisplay !!}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-2 whitespace-nowrap text-center text-gray-500">No pending delivery transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Buttons for Batch Management -->
            <div class="mb-6 flex justify-end items-center">
                <button wire:click="openAddBatchModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                    <i class="fa-solid fa-plus mr-2"></i> Add Batch Delivery
                </button>
            </div>

            <!-- Active Delivery Batches (Tickets - Full Width) -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Delivery Slips</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($allDeliveryBatches as $batch)
                        <div wire:key="active-batch-{{ $batch->id }}" class="relative bg-yellow-100 p-6 rounded-lg shadow-2xl border border-gray-300 transform rotate-1 transition-all duration-300 hover:rotate-0 hover:shadow-xl cursor-pointer @if($currentBatch && $currentBatch->id === $batch->id) border-2 border-blue-500 ring-2 ring-blue-300 @endif"
                             wire:click="selectBatch({{ $batch->id }})"
                             x-data="{}"
                             @drop.prevent="$wire.dispatch('transactionDropped', { transactionId: event.dataTransfer.getData('transactionId'), batchId: {{ $batch->id }} })"
                             @dragover.prevent
                             @dragenter.prevent>
                            <div class="text-xs text-gray-600 mb-2">Date: {{ \Carbon\Carbon::parse($batch->created_at)->format('F d, Y') }}</div>
     
                                <div>Driver: <span class="font-semibold">{{ $batch->driver->personnel_name ?? 'N/A' }}</span></div>

                            
                                <div>Helper: <span class="font-semibold">{{ $batch->helper->personnel_name ?? 'N/A' }}</span></div>
                            <div class="text-center text-lg font-bold mb-4">Route: {{ $batch->route->route_name ?? 'N/A' }}</div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                            <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENT NAME</th>
                                            <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">QTY</th>
                                            <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">UNIT</th>
                                            <th scope="col" class="px-2 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">REMARKS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @php
                                            // Combine transactions from pendingTransactionsByBatch and existing deliveries
                                            $allBatchTransactions = collect($pendingTransactionsByBatch[$batch->id] ?? [])
                                                ->merge($batch->deliveries->map(fn($d) => $d->transaction)->filter())
                                                ->unique('id'); // Ensure unique transactions if a transaction is both pending and already delivered
                                        @endphp
                                        @forelse ($allBatchTransactions as $transaction)
                                            @php
                                                $salesCount = $transaction->sales->count();
                                                $paymentMethodId = $transaction->payments->first()->payment_method_id ?? null;
                                                $isFullyPaid = $transaction->remaining_balance <= 0;
                                                $firstPaymentMethodName = $transaction->payments->first()->paymentMethod->payment_method_name ?? 'N/A';

                                                $paymentMethodDisplay = '';
                                                if ($paymentMethodId === 3) { // Credit
                                                    $paymentMethodDisplay = '<span class="text-gray-500 font-semibold">' . $firstPaymentMethodName . '</span>';
                                                } elseif ($paymentMethodId === 7) { // COD
                                                    if (!$isFullyPaid) {
                                                        $paymentMethodDisplay = '<span class="text-red-500 font-semibold">' . $firstPaymentMethodName . '<span class="block text-xs text-gray-500">(-₱' . number_format($transaction->remaining_balance, 2) . ')</span></span>';
                                                    } else {
                                                        $paymentMethodDisplay = '<span class="text-green-500 font-semibold">Paid</span>';
                                                    }
                                                } else { // Other payment methods (assuming fully paid if not COD/Credit)
                                                    $paymentMethodDisplay = '<span class="text-green-500 font-semibold">Paid</span>';
                                                }
                                            @endphp
                                            @foreach ($transaction->sales as $sale)
                                                <tr class="@if($loop->parent->even) bg-gray-50 @else bg-white @endif">
                                                    @if ($loop->first)
                                                        <td class="px-2 py-1 whitespace-nowrap text-right" rowspan="{{ $salesCount }}">
                                                            <button wire:click="removeTransactionFromBatch({{ $batch->id }}, {{ $transaction->id }})" class="text-red-600 hover:text-red-900">
                                                                <i class="fa-solid fa-xmark"></i>
                                                            </button>
                                                        </td>
                                                        <td class="px-2 py-1 whitespace-nowrap text-left" rowspan="{{ $salesCount }}">{{ $transaction->client->name ?? 'N/A' }}</td>
                                                    @endif
                                                    <td class="px-2 py-1 whitespace-nowrap text-center">{{ $sale->quantity }}</td>
                                                    <td class="px-2 py-1 whitespace-nowrap text-center">{{ $sale->product->product_description ?? 'N/A' }}</td>
                                                    @if ($loop->first)
                                                        <td class="px-2 py-1 whitespace-nowrap text-center" rowspan="{{ $salesCount }}">{!! $paymentMethodDisplay !!}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-2 py-1 whitespace-nowrap text-center text-gray-500">No transactions added yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @php
                                $consolidatedQuantities = [];
                                $totalCollectibles = 0;
                                $codPaymentMethodId = 7; // Assuming COD has an ID of 7

                                foreach ($allBatchTransactions as $transaction) {
                                    foreach ($transaction->sales as $sale) {
                                        $unit = $sale->product->product_description ?? 'N/A';
                                        if (!isset($consolidatedQuantities[$unit])) {
                                            $consolidatedQuantities[$unit] = 0;
                                        }
                                        $consolidatedQuantities[$unit] += $sale->quantity;
                                    }

                                    // Check for COD and remaining balance
                                    $firstPayment = $transaction->payments->first();
                                    if ($firstPayment && $firstPayment->payment_method_id === $codPaymentMethodId && $transaction->remaining_balance > 0) {
                                        $totalCollectibles += $transaction->remaining_balance;
                                    }
                                }
                            @endphp

                            <div class="mt-4 text-left text-sm text-gray-700">
                                <p class="font-semibold mb-1">Consolidated Quantities:</p>
                                @forelse ($consolidatedQuantities as $unit => $quantity)
                                    <p class="ml-2">Total No. of "{{ $unit }}": {{ $quantity }}</p>
                                @empty
                                    <p class="ml-2">No items in this batch.</p>
                                @endforelse
                                <p class="font-semibold mt-2">Total Collectibles: ₱{{ number_format($totalCollectibles, 2) }}</p>
                            </div>

                            <div class="mt-4 text-right">
                                <x-button wire:click="finalizeBatchDelivery({{ $batch->id }})" class="bg-green-500 hover:bg-green-600 text-xs py-1 px-3">
                                    Finalize Batch
                                </x-button>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 col-span-full">No active delivery batches. Click "Add Batch Delivery" to create one.</p>
                    @endforelse
                </div>
            </div>

            <!-- Add Batch Delivery Modal -->
            <x-dialog-modal wire:model="showAddBatchModal">
                <x-slot name="title">
                    Add New Delivery Batch
                </x-slot>

                <x-slot name="content">
                    <div class="space-y-4">
                        <div>
                            <x-label for="selectedDriver" value="{{ __('Driver') }}" />
                            <select wire:model="selectedDriver" id="selectedDriver" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                                <option value="">Select Driver</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ in_array($driver->id, $unavailablePersonnelIds) ? 'disabled' : '' }}>{{ $driver->personnel_name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="selectedDriver" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="selectedHelper" value="{{ __('Helper') }}" />
                            <select wire:model="selectedHelper" id="selectedHelper" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                                <option value="">Select Helper (Optional)</option>
                                @foreach ($helpers as $helper)
                                    <option value="{{ $helper->id }}" {{ in_array($helper->id, $unavailablePersonnelIds) ? 'disabled' : '' }}>{{ $helper->personnel_name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="selectedHelper" class="mt-2" />
                        </div>

                        <div>
                            <x-label for="selectedRoute" value="{{ __('Route') }}" />
                            <select wire:model="selectedRoute" id="selectedRoute" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                                <option value="">Select Route</option>
                                @foreach ($routes as $route)
                                    <option value="{{ $route->id }}">{{ $route->route_name }}</option>
                                @endforeach
                            </select>
                            <x-input-error for="selectedRoute" class="mt-2" />
                        </div>
                    </div>
                </x-slot>

                <x-slot name="footer">
                    <x-secondary-button wire:click="$set('showAddBatchModal', false)" wire:loading.attr="disabled">
                        Cancel
                    </x-secondary-button>

                    <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="createDeliveryBatch" wire:loading.attr="disabled">
                        Create Batch
                    </button>
                </x-slot>
            </x-dialog-modal>

            <!-- Finalized Delivery Tickets Table -->
            <div class="mb-6 mt-12">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Ongoing Delivery</h3>
                <div class="overflow-x-auto bg-white p-4 rounded-lg shadow-md">
                    <div class="mb-4 flex justify-end">
                        <button wire:click="printSelectedBatches" :disabled="$wire.selectedBatchesForPrint.length === 0" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                            <i class="fa-solid fa-print mr-2"></i> Print Selected
                        </button>
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="p-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" wire:model.live="selectAllFinalizedBatches" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Batch ID</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Helper</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Route</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date and Time Created</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($finalizedDeliveryBatches as $batch)
                                <tr wire:key="finalized-batch-{{ $batch->id }}">
                                    <td class="p-2 whitespace-nowrap text-center">
                                        <input type="checkbox" wire:model="selectedBatchesForPrint" value="{{ $batch->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button wire:click="viewFinalizedBatch({{ $batch->id }})" class="text-custom-orange hover:text-orange-700">
                                            <i class="fa-solid fa-eye mr-1"></i>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $batch->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $batch->driver->personnel_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $batch->helper->personnel_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ $batch->route->route_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">{{ \Carbon\Carbon::parse($batch->created_at)->format('F j, Y g:i a') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No finalized delivery tickets found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View Finalized Ticket Modal -->
            <x-dialog-modal wire:model="showViewTicketModal">
                <x-slot name="title">
                    Delivery Batch #{{ $selectedFinalizedBatch->id ?? '' }}
                </x-slot>

                <x-slot name="content">
                    @if ($selectedFinalizedBatch)
                        <div class="relative bg-yellow-100 p-6 rounded-lg shadow-2xl border border-gray-300 transform rotate-1">
                            <div class="text-xs text-gray-600 mb-2">Date: {{ \Carbon\Carbon::parse($selectedFinalizedBatch->created_at)->format('F d, Y') }}</div>
                            <div>Driver: <span class="font-semibold">{{ $selectedFinalizedBatch->driver->driver_name ?? 'N/A' }}</span></div>
                            <div>Helper: <span class="font-semibold">{{ $selectedFinalizedBatch->helper->helper_name ?? 'N/A' }}</span></div>
                            <div class="text-center text-lg font-bold mb-4">Route: {{ $selectedFinalizedBatch->route->route_name ?? 'N/A' }}</div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CLIENT NAME</th>
                                            <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QTY</th>
                                            <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UNIT</th>
                                            <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">REMARKS</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @forelse ($selectedFinalizedBatch->deliveries as $delivery)
                                            @php
                                                $transaction = $delivery->transaction;
                                                $salesCount = $transaction->sales->count();
                                                $paymentMethodId = $transaction->payments->first()->payment_method_id ?? null;
                                                $isFullyPaid = $transaction->remaining_balance <= 0;
                                                $firstPaymentMethodName = $transaction->payments->first()->paymentMethod->payment_method_name ?? 'N/A';

                                                $paymentMethodDisplay = '';
                                                if ($paymentMethodId === 3) { // Credit
                                                    $paymentMethodDisplay = '<span class="text-gray-500 font-semibold">' . $firstPaymentMethodName . '</span>';
                                                } elseif ($paymentMethodId === 7) { // COD
                                                    if (!$isFullyPaid) {
                                                        $paymentMethodDisplay = '<span class="text-red-500 font-semibold">' . $firstPaymentMethodName . '<span class="block text-xs text-gray-500">(-₱' . number_format($transaction->remaining_balance, 2) . ')</span></span>';
                                                    } else {
                                                        $paymentMethodDisplay = '<span class="text-green-500 font-semibold">Paid</span>';
                                                    }
                                                } else { // Other payment methods (assuming fully paid if not COD/Credit)
                                                    $paymentMethodDisplay = '<span class="text-green-500 font-semibold">Paid</span>';
                                                }
                                            @endphp
                                            @foreach ($transaction->sales as $sale)
                                                <tr class="@if($loop->parent->even) bg-gray-50 @else bg-white @endif">
                                                    @if ($loop->first)
                                                        <td class="px-2 py-1 whitespace-nowrap" rowspan="{{ $salesCount }}">{{ $transaction->client->name ?? 'N/A' }}</td>
                                                    @endif
                                                    <td class="px-2 py-1 whitespace-nowrap">{{ $sale->quantity }}</td>
                                                    <td class="px-2 py-1 whitespace-nowrap">{{ $sale->product->product_description ?? 'N/A' }}</td>
                                                    @if ($loop->first)
                                                        <td class="px-2 py-1 whitespace-nowrap" rowspan="{{ $salesCount }}">{!! $paymentMethodDisplay !!}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-2 py-1 whitespace-nowrap text-center text-gray-500">No transactions in this batch.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @php
                                $consolidatedQuantities = [];
                                $totalCollectibles = 0;
                                $codPaymentMethodId = 7; // Assuming COD has an ID of 7

                                foreach ($selectedFinalizedBatch->deliveries as $delivery) {
                                    $transaction = $delivery->transaction;
                                    foreach ($transaction->sales as $sale) {
                                        $unit = $sale->product->product_description ?? 'N/A';
                                        if (!isset($consolidatedQuantities[$unit])) {
                                            $consolidatedQuantities[$unit] = 0;
                                        }
                                        $consolidatedQuantities[$unit] += $sale->quantity;
                                    }

                                    // Check for COD and remaining balance
                                    $firstPayment = $transaction->payments->first();
                                    if ($firstPayment && $firstPayment->payment_method_id === $codPaymentMethodId && $transaction->remaining_balance > 0) {
                                        $totalCollectibles += $transaction->remaining_balance;
                                    }
                                }
                            @endphp

                            <div class="mt-4 text-left text-sm text-gray-700">
                                <p class="font-semibold mb-1">Consolidated Quantities:</p>
                                @forelse ($consolidatedQuantities as $unit => $quantity)
                                    <p class="ml-2">Total No. of "{{ $unit }}": {{ $quantity }}</p>
                                @empty
                                    <p class="ml-2">No items in this batch.</p>
                                @endforelse
                                <p class="font-semibold mt-2">Total Collectibles: ₱{{ number_format($totalCollectibles, 2) }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500">No batch selected.</p>
                    @endif
                </x-slot>

                <x-slot name="footer">
                    @if ($selectedFinalizedBatch && $selectedFinalizedBatch->delivery_batch_status_id !== 2)
                        <x-button class="mr-2 bg-green-500 hover:bg-green-600" wire:click="markBatchAsCompleted({{ $selectedFinalizedBatch->id }})" wire:loading.attr="disabled">
                            Tag Delivery as Completed
                        </x-button>
                    @endif
                    <x-secondary-button wire:click="$set('showViewTicketModal', false)" wire:loading.attr="disabled">
                        Close
                    </x-secondary-button>
                </x-slot>
            </x-dialog-modal>
        </div>
    </div>
</div>
