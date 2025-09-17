<div class="py-12" wire:poll.5s>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('cashier-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Cashier Module
                </a>
            </div>

            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Remittances</h2>

            <!-- COD Transactions -->
            <div class="mb-12">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Pending for Payment COD Transactions</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($codTransactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button wire:click="openAddPaymentModal({{ $transaction->id }})" class="text-custom-orange hover:text-orange-700" @if(is_null( $transaction->id )) disabled @endif><i class="fas fa-money-bill-wave"></i></button>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->id }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->client->name }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">{{ $transaction->created_at->format('F j, Y g:i a') }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        <div class="flex flex-col items-center">
                                            <span>₱{{ number_format($transaction->adjusted_total, 2) }}</span>
                                            @if ($transaction->adjusted_total != $transaction->total_amount)
                                                <span class="text-xs text-gray-500 line-through">₱{{ number_format($transaction->total_amount, 2) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No COD transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Client Credit Payments -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-700">Client Credit Payments</h3>
                    <button wire:click="openAddClientPaymentModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                        <i class="fa-solid fa-plus mr-2"></i> Add Client Credit Payment
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th> <!-- New Header -->
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Client Name</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Amount Received</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th> <!-- New Header -->
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($clientCreditPayments as $clientPayment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <button wire:click="viewClientCreditPaymentReceipt({{ $clientPayment->id }})" class="text-custom-orange hover:text-orange-700" wire:key="client-payment-receipt-{{ $clientPayment->id }}"><i class="fa-solid fa-eye"></i></button>
                                    </td> <!-- New Action Button -->
                                    <td class="px-6 py-4 text-center whitespace-nowrap">{{ $clientPayment->client->name }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">{{ $clientPayment->created_at->format('F j, Y g:i a') }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">₱{{ number_format($clientPayment->amount_received, 2) }}</td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">{{ $clientPayment->paymentMethod->payment_method_name ?? 'N/A' }}</td> <!-- New Data -->
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No client credit payments found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal (for COD Transactions) -->
    <x-dialog-modal wire:model="showAddPaymentModal" wire:ignore.self>
        <x-slot name="title">
            Add Payment for COD Transaction
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="remittancePaymentMethodId" value="{{ __('Payment Method') }}" />
                    <select wire:model.live="remittancePaymentMethodId" id="remittancePaymentMethodId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">Select Payment Method</option>
                            @foreach ($paymentMethods as $method)
                                @if (!in_array($method->id, [3, 7]))
                                    <option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>
                                @endif
                            @endforeach
                    </select>
                    <x-input-error for="remittancePaymentMethodId" class="mt-2" />
                </div>

                @if (in_array($remittancePaymentMethodId, [2, 4, 5]))
                    <div>
                        <x-label for="remittanceReferenceNumber" value="{{ __('Reference Number') }}" />
                        <x-input id="remittanceReferenceNumber" type="text" class="mt-1 block w-full" wire:model.defer="remittanceReferenceNumber" />
                        <x-input-error for="remittanceReferenceNumber" class="mt-2" />
                    </div>
                @endif

                @if ($remittancePaymentMethodId == 6)
                    <div>
                        <x-label for="remittanceCheckNumber" value="{{ __('Check Number') }}" />
                        <x-input id="remittanceCheckNumber" type="text" class="mt-1 block w-full" wire:model.defer="remittanceCheckNumber" />
                        <x-input-error for="remittanceCheckNumber" class="mt-2" />
                    </div>
                @endif

                @if ($remittancePaymentMethodId == 1)
                    <div>
                        <x-label for="remittanceAmountReceived" value="{{ __('Amount Received') }}" />
                        <x-input id="remittanceAmountReceived" type="number" class="mt-1 block w-full" wire:model.live="remittanceAmountReceived" />
                        <x-input-error for="remittanceAmountReceived" class="mt-2" />
                    </div>
                    <div class="text-lg">
                        <strong>Amount Change:</strong> ₱{{ number_format($remittanceAmountChange, 2) }}
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddPaymentModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="addCodRemittancePayment" wire:loading.attr="disabled">
                Add Payment
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Add Client Payment Modal -->
    <x-dialog-modal wire:model="showAddClientPaymentModal" wire:ignore.self>
        <x-slot name="title">
            Add Client Credit Payment
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="searchClient" value="{{ __('Client Name') }}" />
                    <div class="relative">
                        <x-input id="searchClient" type="text" class="mt-1 block w-full" wire:model.live.debounce.300ms="searchClient" placeholder="Search client by name..." />
                        @if ($selectedClientId)
                        <x-secondary-button class="mt-2" wire:click="clearSelectedClient">Clear</x-secondary-button>    
                        @endif
                    </div>
                    <x-input-error for="selectedClientId" class="mt-2" />

                    @if (!empty($searchClient) && $filteredClients->count() > 0 && !$selectedClientId)
                        <div class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            @foreach ($filteredClients as $client)
                                <div wire:click="selectClient({{ $client->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100">
                                    {{ $client->name }}
                                </div>
                            @endforeach
                        </div>
                    @elseif (!empty($searchClient) && $filteredClients->count() == 0 && !$selectedClientId)
                        <div class="mt-2 px-4 py-2 text-gray-500">No clients found with unpaid credit dues.</div>
                    @endif
                </div>

                <div>
                    <x-label for="clientPaymentMethodId" value="{{ __('Payment Method') }}" />
                    <select wire:model.live="clientPaymentMethodId" id="clientPaymentMethodId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">Select Payment Method</option>
                        @foreach ($allPaymentMethods as $method)
                            @if (!in_array($method->id, [3, 7]))
                                <option value="{{ $method->id }}">{{ $method->payment_method_name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <x-input-error for="clientPaymentMethodId" class="mt-2" />
                </div>

                <div>
                    <x-label for="clientPaymentAmountReceived" value="{{ __('Amount Received') }}" />
                    <x-input id="clientPaymentAmountReceived" type="number" class="mt-1 block w-full" wire:model.defer="clientPaymentAmountReceived" />
                    <x-input-error for="clientPaymentAmountReceived" class="mt-2" />
                </div>

                @if (in_array($clientPaymentMethodId, [2, 4, 5])) {{-- GCash, Card Payment, Paymaya --}}
                    <div>
                        <x-label for="clientPaymentReferenceNumber" value="{{ __('Reference Number') }}" />
                        <x-input id="clientPaymentReferenceNumber" type="text" class="mt-1 block w-full" wire:model.defer="clientPaymentReferenceNumber" />
                        <x-input-error for="clientPaymentReferenceNumber" class="mt-2" />
                    </div>
                @endif

                @if ($clientPaymentMethodId == 6) {{-- Check --}}
                    <div>
                        <x-label for="clientPaymentCheckNumber" value="{{ __('Check Number') }}" />
                        <x-input id="clientPaymentCheckNumber" type="text" class="mt-1 block w-full" wire:model.defer="clientPaymentCheckNumber" />
                        <x-input-error for="clientPaymentCheckNumber" class="mt-2" />
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddClientPaymentModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="addClientCreditPayment" wire:loading.attr="disabled">
                Add Payment
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Client Credit Payment Receipt Modal -->
    <x-dialog-modal wire:model="showClientCreditReceiptModal" wire:ignore.self>
        <x-slot name="title">
            Client Credit Payment Receipt (ID: {{ $selectedClientCreditPayment->id ?? '' }})
        </x-slot>

        <x-slot name="content">
            @if ($selectedClientCreditPayment)
                <div class="p-4 bg-white rounded-lg shadow-md">
                    <div class="text-center mb-4">
                        <h3 class="text-xl font-bold">CLIENT CREDIT PAYMENT RECEIPT</h3>
                        <p class="text-sm text-gray-600">Date: {{ $selectedClientCreditPayment->created_at->format('F j, Y g:i a') }}</p>
                        <p class="text-sm text-gray-600">Payment ID: {{ $selectedClientCreditPayment->id }}</p>
                    </div>

                    <div class="mb-4">
                        <p><strong>Client:</strong> {{ $selectedClientCreditPayment->client->name ?? 'N/A' }}</p>
                        <p><strong>Payment Method:</strong> {{ $selectedClientCreditPayment->paymentMethod->payment_method_name ?? 'N/A' }}</p>
                        @if ($selectedClientCreditPayment->reference_number)
                            <p><strong>Reference Number:</strong> {{ $selectedClientCreditPayment->reference_number }}</p>
                        @endif
                        @if ($selectedClientCreditPayment->check_number)
                            <p><strong>Check Number:</strong> {{ $selectedClientCreditPayment->check_number }}</p>
                        @endif
                    </div>

                    <div class="border-t border-b border-gray-200 py-4 mb-4 text-right">
                        <p class="text-lg font-semibold">Amount Received: ₱{{ number_format($selectedClientCreditPayment->amount_received, 2) }}</p>
                    </div>

                    <div class="text-center text-sm text-gray-700 mt-6">
                        <p>Thank you for your payment!</p>
                    </div>
                </div>
            @else
                <p>No client credit payment selected for receipt.</p>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showClientCreditReceiptModal', false)" wire:loading.attr="disabled">
                Close
            </x-secondary-button>
        </x-slot>
    </x-dialog-modal>
</div>
