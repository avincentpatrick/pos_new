<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                @if($userLevel == 1)
                <a href="{{ route('admin-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Admin Modules
                </a>
                @elseif($userLevel == 2)
                <a href="{{ route('cashier-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Cashier Modules
                </a>
                @endif
            </div>    
            
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Transactions</h2>
                @if ($userLevel == 3)
                <a href="{{ route('add-sale') }}" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                    <i class="fa-solid fa-plus mr-2"></i> Add Sale
                </a>
                @endif
            </div>
            
            <div class="mt-6 mb-4">
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange" placeholder="Search by Transaction ID or Customer Name...">
            </div>

            <div class="mt-4 flex flex-wrap gap-4">
                @if ($userLevel == 1)
                <div class="flex-1 min-w-[150px]">
                    <label for="startDate" class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input wire:model.live="startDate" id="startDate" type="date" max="{{ now()->toDateString() }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="endDate" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input wire:model.live="endDate" id="endDate" type="date" max="{{ now()->toDateString() }}" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                </div>
                @endif
                <div class="flex-1 min-w-[150px]">
                    <label for="paymentMethodFilter" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select wire:model.live="paymentMethodFilter" id="paymentMethodFilter" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">All Payment Methods</option>
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="orderTypeFilter" class="block text-sm font-medium text-gray-700">Order Type</label>
                    <select wire:model.live="orderTypeFilter" id="orderTypeFilter" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">All Order Types</option>
                        @foreach ($orderTypes as $orderType)
                            <option value="{{ $orderType->id }}">{{ $orderType->order_type_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700">Status</label>
                    <select wire:model.live="statusFilter" id="statusFilter" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">All Transactions</option>
                        <option value="unpaid">Unpaid (COD)</option>
                        <option value="paid">Paid</option>
                        <option value="not_applicable">N/A (Credit)</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Dispense Status</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Order Type</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button wire:click="viewTransaction({{ $transaction->id }})" class="text-custom-orange hover:text-orange-700"><i class="fa-solid fa-eye"></i></button>
                                    @if($transaction->dispense_status === 'Pending')
                                        <button wire:click="confirmDelete({{ $transaction->id }})" class="ml-2 text-red-500 hover:text-red-700"><i class="fa-solid fa-trash"></i></button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $isCodTransaction = $transaction->payments->contains('payment_method_id', 7);
                                        $isClientCreditTransaction = $transaction->payments->contains('payment_method_id', 3);
                                        $isFullyPaid = $transaction->remaining_balance <= 0;
                                    @endphp

                                    @if ($isClientCreditTransaction)
                                        <span class="text-gray-500 font-semibold">N/A (Credit)</span>
                                    @elseif ($isCodTransaction && !$isFullyPaid)
                                        <span class="text-red-500 font-semibold">Unpaid
                                            <span class="block text-xs text-gray-500">(-₱{{ number_format($transaction->remaining_balance, 2) }})</span>
                                        </span>
                                    @else
                                        <span class="text-green-500 font-semibold">Paid</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $status = $transaction->dispense_status;
                                        $colorClass = '';
                                        switch ($status) {
                                            case 'Pending':
                                                $colorClass = 'text-red-500';
                                                break;
                                            case 'Partially Dispensed':
                                                $colorClass = 'text-yellow-500';
                                                break;
                                            case 'Fully Dispensed':
                                                $colorClass = 'text-green-500';
                                                break;
                                            default:
                                                $colorClass = 'text-gray-500';
                                        }
                                    @endphp
                                    <span class="{{ $colorClass }} font-semibold">{{ $status }}</span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->client->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    {{ $transaction->payments->first()->paymentMethod->payment_method_name ?? 'N/A' }}
                                    @if ($transaction->payments->first()->payment_method_id == 1 && $transaction->remaining_balance > 0)
                                        <span class="block text-xs text-gray-500">Paid: ₱{{ number_format($transaction->payments->where('payment_method_id', 1)->sum('amount_received'), 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->orderType->order_type_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if ($transaction->adjusted_total != $transaction->total_amount)
                                        <span class="font-semibold">₱{{ number_format($transaction->adjusted_total, 2) }}</span>
                                        <span class="block text-xs text-gray-500 line-through">₱{{ number_format($transaction->total_amount, 2) }}</span>
                                    @else
                                        <span>₱{{ number_format($transaction->total_amount, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->created_at->format('F j, Y g:i a') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <x-dialog-modal wire:model="showDeleteModal">
        <x-slot name="title">
            Delete Transaction
        </x-slot>

        <x-slot name="content">
            Are you sure you want to delete this transaction? This action cannot be undone.
            <div class="mt-4">
                <x-label for="adminPassword" value="{{ __('Admin Password') }}" />
                <x-input id="adminPassword" type="password" class="mt-1 block w-full" wire:model.defer="adminPassword" />
                <x-input-error for="adminPassword" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-danger-button class="ms-2" wire:click="deleteTransaction" wire:loading.attr="disabled">
                Delete Transaction
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Receipt Modal -->
    <x-dialog-modal wire:model="showReceiptModal">
        <x-slot name="title">
            Transaction Receipt
        </x-slot>

        <x-slot name="content">
            @if ($selectedTransaction)
                <div id="transactionReceipt" class="p-4 bg-white rounded-lg">
                    <div class="text-center mb-4">
                        <h2 class="text-2xl font-bold">ELMACRIS ICE TRADING</h2>
                        <p>Solid Road, Brgy. San Manuel, 5300 Puerto Princesa City (Capital), Palawan</p>
                    </div>
                    <div class="mb-4">
                        <p><strong>Date:</strong> {{ $selectedTransaction->created_at->format('m-d-y') }}</p>
                        <p><strong>Sold to:</strong> {{ $selectedTransaction->client->name }}</p>
                        <p><strong>Address:</strong> {{ $selectedTransaction->client->address }}</p>
                        <p><strong>Order Type:</strong> {{ $selectedTransaction->orderType->order_type_name ?? 'N/A' }}</p>
                    </div>
                    <table class="w-full mb-4">
                        <thead>
                            <tr class="border-b">
                                <th class="py-2 text-left">QTY</th>
                                <th class="py-2 text-left">DESCRIPTION</th>
                                <th class="py-2 text-right">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($selectedTransaction->sales as $sale)
                                @php
                                    $paidQuantity = 0;
                                    $freeQuantity = 0;
                                    if ($sale->price > 0) {
                                        // Calculate paid quantity, ensuring no division by zero.
                                        $paidQuantity = round($sale->total / $sale->price);
                                        $freeQuantity = $sale->quantity - $paidQuantity;
                                    } else {
                                        // If price is 0, assume all items are free if total is also 0.
                                        $freeQuantity = $sale->quantity;
                                    }
                                @endphp

                                @if ($paidQuantity > 0)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $paidQuantity }}</td>
                                        <td class="py-2">{{ $sale->product->product_name }} @ ₱{{ number_format($sale->price, 2) }}</td>
                                        <td class="py-2 text-right">₱{{ number_format($sale->total, 2) }}</td>
                                    </tr>
                                @endif

                                @if ($freeQuantity > 0)
                                    <tr class="border-b">
                                        <td class="py-2">{{ $freeQuantity }}</td>
                                        <td class="py-2">{{ $sale->product->product_name }} (Free)</td>
                                        <td class="py-2 text-right">₱0.00</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-right font-bold text-xl">
                        <p>Total: ₱{{ number_format($selectedTransaction->adjusted_total, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <p>Amount Received: ₱{{ number_format($selectedTransaction->payments->sum('amount_received'), 2) }}</p>
                        <p>Change: ₱{{ number_format($selectedTransaction->amount_change, 2) }}</p>
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showReceiptModal', false)" wire:loading.attr="disabled">
                Close
            </x-secondary-button>

            <x-button class="ms-2" onclick="printReceiptContent('transactionReceipt')">
                Print
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>

<script>
    function printReceiptContent(elementId) {
        const printContents = document.getElementById(elementId).innerHTML;
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Print Receipt</title>');
        printWindow.document.write('<link rel="stylesheet" href="{{ asset('css/print-receipt.css') }}">');
        printWindow.document.write('<style> body { background-color: white; } </style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<div class="receipt-container">');
        printWindow.document.write(printContents);
        printWindow.document.write('</div>');
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 1000);
    }
</script>
