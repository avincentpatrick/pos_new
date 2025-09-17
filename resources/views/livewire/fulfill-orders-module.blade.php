<div class="py-12 bg-gray-50" wire:poll.5s>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl p-8">
            <div class="mb-4">
                <a href="{{ route('storage-module') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 disabled:opacity-25 transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Storage Module
                </a>
            </div>

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">
                    Fulfill Orders
                </h1>
                <p class="mt-2 text-gray-600">
                    Manage pending and fulfilled sales orders.
                </p>
            </div>

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

            <!-- Pending Transactions for Dispense -->
            <div class="mb-12">
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Pending Transactions for Dispense</h2>

                <!-- Filters -->
                <div class="mb-4 flex space-x-8">
                    <div>
                        <label for="productFilter" class="block text-sm font-medium text-gray-700">Filter by Product</label>
                        <select id="productFilter" wire:model.live="productFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All Products</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="orderTypeFilter" class="block text-sm font-medium text-gray-700">Filter by Order Type</label>
                        <select id="orderTypeFilter" wire:model.live="orderTypeFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            <option value="">All Order Types</option>
                            @foreach ($orderTypes as $orderType)
                                <option value="{{ $orderType->id }}">{{ $orderType->order_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($pendingTransactions as $sale)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button wire:click="dispenseOrder({{ $sale->id }})" wire:loading.attr="disabled" wire:target="dispenseOrder({{ $sale->id }})" class="text-blue-600 hover:text-blue-900">Dispense</button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->transaction->id ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->transaction->client->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->product->product_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->product->product_description ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->transaction->orderType->order_type_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->transaction->created_at->format('F j, Y g:i a') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No pending transactions for dispense.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $pendingTransactions->links() }}
                </div>
            </div>

            <!-- Fulfilled Orders -->
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 mb-4">Fulfilled Orders</h2>

                <!-- Filters for Fulfilled Orders -->
                <div class="mb-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="fulfilledStatusFilter" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="fulfilledStatusFilter" wire:model.live="fulfilledStatusFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Status</option>
                                @foreach ($dispenseStatusTypes as $status)
                                    <option value="{{ $status->id }}">{{ $status->dispense_status_type_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fulfilledProductFilter" class="block text-sm font-medium text-gray-700">Product</label>
                            <select id="fulfilledProductFilter" wire:model.live="fulfilledProductFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Products</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="fulfilledOrderTypeFilter" class="block text-sm font-medium text-gray-700">Order Type</label>
                            <select id="fulfilledOrderTypeFilter" wire:model.live="fulfilledOrderTypeFilter" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Order Types</option>
                                @foreach ($orderTypes as $orderType)
                                    <option value="{{ $orderType->id }}">{{ $orderType->order_type_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="fulfilledStartDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input id="fulfilledStartDate" type="date" wire:model.live="fulfilledStartDate" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        </div>
                        <div>
                            <label for="fulfilledEndDate" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input id="fulfilledEndDate" type="date" wire:model.live="fulfilledEndDate" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        </div>
                    </div>
                </div>

                @if ($selectedOrders)
                    <div class="mb-4">
                        <button wire:click="openConfirmModal" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Tag as Completed Selected ({{ count($selectedOrders) }})
                        </button>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Dispensed</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($fulfilledOrders as $sale)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" wire:model.live="selectedOrders" value="{{ $sale->id }}"
                                            class="disabled:opacity-50 disabled:cursor-not-allowed"
                                            @if($sale->stockMovement->dispense_status_type_id != 2) disabled @endif>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button wire:click="viewOrder({{ $sale->id }})" class="text-indigo-600 hover:text-indigo-900">View</button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusId = $sale->stockMovement->dispense_status_type_id ?? 0;
                                            $statusName = $sale->stockMovement->dispenseStatusType->dispense_status_type_name ?? 'N/A';
                                            $colorClass = '';
                                            switch ($statusId) {
                                                case 1: // Returned
                                                    $colorClass = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800';
                                                    break;
                                                case 2: // Ongoing Delivery
                                                    $colorClass = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 3: // Completed
                                                    $colorClass = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800';
                                                    break;
                                                default:
                                                    $colorClass = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800';
                                                    break;
                                            }
                                        @endphp
                                        <span class="{{ $colorClass }}">
                                            {{ $statusName }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->transaction->client->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->product->product_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->product->product_description ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->transaction->orderType->order_type_name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $sale->stockMovement->created_at->format('F j, Y g:i a') ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No fulfilled orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $fulfilledOrders->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <x-dialog-modal wire:model="showConfirmModal">
        <x-slot name="title">
            Confirm Update
        </x-slot>

        <x-slot name="content">
            <p>Are you sure you want to tag the following orders as "Completed"?</p>
            <ul class="mt-4 list-disc list-inside">
                @foreach ($ordersToConfirm as $order)
                    <li>{{ $order->product->product_name }} (Qty: {{ $order->quantity }}) for {{ $order->transaction->client->name ?? 'N/A' }}</li>
                @endforeach
            </ul>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showConfirmModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-button class="ml-2 bg-green-600 hover:bg-green-700" wire:click="confirmTagSelectedAsCompleted" wire:loading.attr="disabled">
                Confirm
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Details Modal -->
    <x-dialog-modal wire:model="showDetailsModal">
        <x-slot name="title">
            Fulfilled Order Details
        </x-slot>

        <x-slot name="content">
            @if ($selectedSale)
                <div class="space-y-4">
                    <p><strong>Transaction ID:</strong> {{ $selectedSale->transaction->id ?? 'N/A' }}</p>
                    <p><strong>Customer Name:</strong> {{ $selectedSale->transaction->client->name ?? 'N/A' }}</p>
                    <p><strong>Product Name:</strong> {{ $selectedSale->product->product_name ?? 'N/A' }}</p>
                    <p><strong>Quantity:</strong> {{ $selectedSale->quantity }}</p>
                    <p><strong>Description:</strong> {{ $selectedSale->product->product_description ?? 'N/A' }}</p>
                    <p><strong>Order Type:</strong> {{ $selectedSale->transaction->orderType->order_type_name ?? 'N/A' }}</p>
                    <p><strong>Status:</strong> {{ $selectedSale->stockMovement->dispenseStatusType->dispense_status_type_name ?? 'N/A' }}</p>
                    <p><strong>Transaction Date:</strong> {{ $selectedSale->transaction->created_at->format('F j, Y g:i a') ?? 'N/A' }}</p>
                    <p><strong>Date Dispensed:</strong> {{ $selectedSale->stockMovement->created_at->format('F j, Y g:i a') ?? 'N/A' }}</p>

                    @if ($selectedSale->stockMovement->dispense_status_type_id == 3)
                        <p><strong>Actual Quantity Dispensed:</strong> {{ $selectedSale->stockMovement->actual_quantity_dispensed ?? 'N/A' }}</p>
                    @elseif ($selectedSale->stockMovement->dispense_status_type_id == 1)
                        <p><strong>Actual Quantity Dispensed:</strong> {{ $selectedSale->stockMovement->actual_quantity_dispensed ?? 'N/A' }}</p>
                        <p><strong>Actual Quantity Returned:</strong> {{ $selectedSale->stockMovement->actual_quantity_returned ?? 'N/A' }}</p>
                        <p><strong>Reason for Return:</strong> {{ $selectedSale->stockMovement->returnReason->return_reason_name ?? 'N/A' }}</p>
                        @if ($selectedSale->stockMovement->return_reason_specify)
                            <p><strong>Specify Reason:</strong> {{ $selectedSale->stockMovement->return_reason_specify }}</p>
                        @endif
                        <p><strong>Return Remarks:</strong> {{ $selectedSale->stockMovement->return_remarks ?? 'N/A' }}</p>
                    @endif

                    <hr class="my-4">

                    <!-- Update Status Form -->
                    @if ($selectedSale->stockMovement->dispense_status_type_id == 2)
                        <div class="space-y-4">
                            <div>
                                <x-label for="selectedStatusId" value="{{ __('Update Status') }}" />
                                <select wire:model.live="selectedStatusId" id="selectedStatusId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option>Please select Status</option>
                                    <option value="3">Completed</option>
                                    <option value="1">Returned</option>
                                </select>
                            </div>
                            @if ($selectedStatusId == 1)
                                <div>
                                    <x-label for="actualQuantityDispensed" value="{{ __('Actual Quantity Dispensed') }}" />
                                    <x-input id="actualQuantityDispensed" type="number" class="mt-1 block w-full" wire:model.defer="actualQuantityDispensed" />
                                    <x-input-error for="actualQuantityDispensed" class="mt-2" />
                                </div>

                                <div>
                                    <x-label for="actualQuantityReturned" value="{{ __('Actual Quantity Returned') }}" />
                                    <x-input id="actualQuantityReturned" type="number" class="mt-1 block w-full" wire:model.defer="actualQuantityReturned" />
                                    <x-input-error for="actualQuantityReturned" class="mt-2" />
                                </div>

                                <div>
                                    <x-label for="returnReasonId" value="{{ __('Reason for Return') }}" />
                                    <select wire:model.live="returnReasonId" id="returnReasonId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Select a reason</option>
                                        @foreach ($returnReasons as $reason)
                                            <option value="{{ $reason->id }}">{{ $reason->return_reason_name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error for="returnReasonId" class="mt-2" />
                                </div>

                                @if ($returnReasonId == 5)
                                    <div>
                                        <x-label for="specifyReason" value="{{ __('Specify Reason') }}" />
                                        <x-input id="specifyReason" type="text" class="mt-1 block w-full" wire:model.defer="specifyReason" />
                                        <x-input-error for="specifyReason" class="mt-2" />
                                    </div>
                                @endif

                                <div>
                                    <x-label for="return_remarks" value="{{ __('Return Remarks') }}" />
                                    <textarea id="return_remarks" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" wire:model.defer="return_remarks"></textarea>
                                    <x-input-error for="return_remarks" class="mt-2" />
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center text-gray-600 text-lg">
                            This order is already {{ strtolower($selectedSale->stockMovement->dispenseStatusType->dispense_status_type_name) }} and cannot be updated.
                        </div>
                    @endif
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDetailsModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-button
                class="ml-2"
                wire:click="updateFulfilledOrder"
                wire:loading.attr="disabled"
                :disabled="!$selectedSale || $selectedSale->stockMovement?->dispense_status_type_id !== 2"
            >
                Update Order
            </x-button>

        </x-slot>
    </x-dialog-modal>
</div>
