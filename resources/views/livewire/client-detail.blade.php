<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('clients') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Clients
                </a>
            </div>    
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">{{ $client->name }}</h2>
                <p class="text-gray-600">{{ $client->company }}</p>
                <p class="text-gray-600">{{ $client->address }}</p>
                <p class="text-gray-600">{{ $client->contact_no }}{{ $client->email ? ' | '.$client->email : '' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-blue-100 p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-blue-800">Total Sales</h3>
                    <p class="text-2xl font-bold text-blue-900">₱{{ number_format($total_sales, 2) }}</p>
                </div>
                <div class="bg-red-100 p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-red-800">Total Credit</h3>
                    <p class="text-2xl font-bold text-red-900">₱{{ number_format($total_credit, 2) }}</p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-green-800">Transactions</h3>
                    <p class="text-2xl font-bold text-green-900">{{ $number_of_transactions }}</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg shadow">
                    <h3 class="text-lg font-semibold text-yellow-800">KGs of Ice Ordered</h3>
                    <p class="text-2xl font-bold text-yellow-900">{{ $total_kgs_ordered }}</p>
                </div>
            </div>

            <h3 class="text-xl font-semibold text-gray-800 mb-4">Transaction History</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Receipt</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button wire:click="viewTransaction({{ $transaction->id }})" class="text-indigo-600 hover:text-indigo-900"><i class="fa-solid fa-eye"></i></button>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->id }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->created_at->format('F j, Y g:i a') }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">₱{{ number_format($transaction->total_amount, 2) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    {{ $transaction->paymentMethod->payment_method_name ?? 'N/A' }}
                                    @if ($transaction->payment_method_id == 1 && $transaction->remaining_balance > 0)
                                        <span class="block text-xs text-gray-500">Paid: ₱{{ number_format($transaction->amount_received, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @if ($transaction->payment_method_id == 1)
                                        @if ($transaction->remaining_balance > 0)
                                            <span class="text-orange-500 font-semibold">Remaining Balance
                                                <span class="block text-xs text-gray-500">(-{{ number_format($transaction->remaining_balance, 2) }})</span>
                                            </span>
                                        @else
                                            <span class="text-green-500 font-semibold">Paid</span>
                                        @endif
                                    @elseif (in_array($transaction->payment_method_id, [3, 7]))
                                        <span class="text-red-500 font-semibold">Unpaid</span>
                                    @else
                                        <span class="text-green-500 font-semibold">Paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No transactions found.</td>
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

    <!-- Receipt Modal -->
    <x-dialog-modal wire:model="showReceiptModal">
        <x-slot name="title">
            Transaction Receipt
        </x-slot>

        <x-slot name="content">
            @if ($selectedTransaction)
                <div class="p-4 bg-white rounded-lg">
                    <div class="text-center mb-4">
                        <h2 class="text-2xl font-bold">ELMACRIS ICE TRADING</h2>
                        <p>Solid Road, Brgy. San Manuel, 5300 Puerto Princesa City (Capital), Palawan</p>
                    </div>
                    <div class="mb-4">
                        <p><strong>Date:</strong> {{ $selectedTransaction->created_at->format('m-d-y') }}</p>
                        <p><strong>Delivered to:</strong> {{ $selectedTransaction->client->name }}</p>
                        <p><strong>Address:</strong> {{ $selectedTransaction->client->address }}</p>
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
                                <tr class="border-b">
                                    <td class="py-2">{{ $sale->quantity }}</td>
                                    <td class="py-2">{{ $sale->product->product_name }} @ ₱{{ number_format($sale->price, 2) }}</td>
                                    <td class="py-2 text-right">₱{{ number_format($sale->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-right font-bold text-xl">
                        <p>Total: ₱{{ number_format($selectedTransaction->total_amount, 2) }}</p>
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showReceiptModal', false)" wire:loading.attr="disabled">
                Close
            </x-secondary-button>

            <x-button class="ms-2" onclick="window.print()">
                Print
            </x-button>
        </x-slot>
    </x-dialog-modal>
</div>
