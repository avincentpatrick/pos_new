<div class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-4">
            <a href="{{ route('cashier-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back to Cashier Modules
            </a>
        </div> 
        <div class="flex gap-6 w-full">
            <!-- Left Panel: Select Customer, Product Selection & Transaction Summary -->
            <div class="flex-1 basis-2/5 bg-white p-6 rounded-lg shadow">
                <!-- Select Customer Section -->
                <div class="mb-6 w-full">
                    <div class="w-full relative">
                        <label for="customer" class="block text-xl font-bold mb-4">Customer name</label>
                        
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live="search"
                                placeholder="Search customer..."
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange"
                                @if($selectedCustomerId) disabled @endif
                            >

                            @if ($selectedCustomerId)
                                <x-secondary-button class="mt-2" wire:click="clearSelectedCustomer">Clear</x-secondary-button>
                            @endif
                        </div>

                        @if (!$selectedCustomerId && strlen($search) > 1)
                            <div class="absolute z-10 w-full bg-white border border-gray-200 rounded-md shadow-lg mt-1">
                                @if (count($customers))
                                    @foreach ($customers as $customer)
                                        <div 
                                            wire:click="selectCustomer({{ $customer->id }})"
                                            class="px-4 py-2 hover:bg-blue-100 cursor-pointer"
                                        >
                                            {{ $customer->name }}
                                        </div>
                                    @endforeach
                                @else
                                    <div class="px-4 py-2 text-gray-500 italic">No matches found.</div>
                                    <div class="px-4 py-2">
                                        <button wire:click="openAddClientModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                            Add New Client
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Transaction Summary Section as a Table -->
                <h2 class="text-xl font-bold mb-4">Products</h2>
                <div class="overflow-x-auto">
                    <table class="w-full bg-white border">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-3 px-4 text-left">Product</th>
                                <th class="py-3 px-4 text-left">Unit Price</th>
                                <th class="py-3 px-4 text-left">Rem. Stock</th>
                                <th class="py-3 px-4 text-left">Quantity</th>
                                <th class="py-3 px-4 text-left">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                            @php
                                $specialPrice = isset($clientSpecialPrices[$product->id]) ? $clientSpecialPrices[$product->id]->special_price : null;
                                $price = $specialPrice ?? $product->retail_price;
                                $promo = isset($clientPromos[$product->id]) ? $clientPromos[$product->id] : null;
                            @endphp
                            <tr class="border-b">
                                <td class="py-3 px-4">
                                    {{ $product->product_name }}
                                    @if($promo)
                                        <span class="ml-2 text-xs text-white bg-green-500 font-bold px-2 py-1 rounded-full">
                                            ({{ $promo->minimum_buy }}+{{ $promo->get_free }})
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($specialPrice)
                                        <del class="text-red-500">₱{{ number_format($product->retail_price, 2) }}</del>
                                        <span class="font-bold text-green-600">₱{{ number_format($specialPrice, 2) }}</span>
                                    @else
                                        ₱{{ number_format($product->retail_price, 2) }}
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-bold 
                                    @if($product->remaining_stock >= $product->high_level) text-green-500 @elseif($product->remaining_stock <= $product->critical_level) text-red-500 @elseif($product->remaining_stock <= $product->running_low_level) text-orange-500 @else text-gray-700 @endif
                                ">
                                    {{ $product->remaining_stock }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <input type="number" wire:model.live.debounce.500ms="quantities.{{ $product->id }}" min="0" max="{{ $product->remaining_stock }}" class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                                        @if(isset($freeQuantities[$product->id]) && $freeQuantities[$product->id] > 0)
                                            <span class="text-sm font-bold text-green-600">+{{ $freeQuantities[$product->id] }} Free</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    ₱{{ number_format(($quantities[$product->id] ?? 0) * $price, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel: Delivery/Pick-up, Payment Details & Order Options -->
            <div class="flex-1 basis-3/5 bg-white p-6 rounded-lg shadow">
                <!-- Order Type Selection -->
                <div class="mb-6">
                    <label class="block text-xl font-bold mb-2">Order Type</label>
                    <div class="flex gap-4">
                        @foreach ($orderTypes as $orderType)
                            <label class="flex items-center gap-2">
                                <input type="radio" name="order_type" value="{{ $orderType->id }}" wire:model="selectedOrderType" class="form-radio h-5 w-5 text-custom-orange focus:ring-custom-orange">
                                {{ $orderType->order_type_name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Details Section -->
                <h2 class="text-xl font-bold mb-4">Payment Details</h2>
                <div class="space-y-4">
                    @foreach ($products as $product)
                        @if (isset($quantities[$product->id]) && $quantities[$product->id] > 0)
                            @php
                                $specialPrice = isset($clientSpecialPrices[$product->id]) ? $clientSpecialPrices[$product->id]->special_price : null;
                                $price = $specialPrice ?? $product->retail_price;
                                $freeQuantity = $freeQuantities[$product->id] ?? 0;
                            @endphp
                            <div class="flex justify-between">
                                <span>{{ $product->product_name }} (x{{ $quantities[$product->id] }})</span>
                                <span>₱{{ number_format($price * $quantities[$product->id], 2) }}</span>
                            </div>
                            @if($freeQuantity > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>{{ $product->product_name }} (Free) (x{{ $freeQuantity }})</span>
                                    <span>₱0.00</span>
                                </div>
                            @endif
                        @endif
                    @endforeach
                    <div class="flex justify-between">
                        <span>Delivery Fee:</span>
                        <span>₱0.00</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>₱{{ number_format($total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-xl font-bold">
                        <span>Total:</span>
                        <span>₱{{ number_format($total_amount, 2) }}</span>
                    </div>
                </div>

                <!-- Payment Method Selection -->
                <div class="mt-6">
                    <label class="block mb-2 font-semibold">Select Payment Method:</label>
                    <div class="flex flex-wrap gap-4">
                        @foreach ($paymentMethods as $paymentMethod)
                            <label class="flex items-center gap-2">
                                <input type="radio" name="payment_method" value="{{ $paymentMethod->id }}" wire:model.live="paymentMethodId" class="form-radio h-5 w-5 text-custom-orange focus:ring-custom-orange">
                                {{ $paymentMethod->payment_method_name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Additional Order Notes -->
                <div class="mt-6">
                    <label for="notes" class="block mb-2 font-semibold">Notes for Order:</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full border rounded p-2" placeholder="Enter any special instructions..." wire:model="notes"></textarea>
                </div>

                <!-- Action Buttons -->
                <button wire:click.prevent="checkout" class="w-full p-3 mt-6 bg-custom-orange hover:bg-orange-700 rounded-md transition font-semibold text-xs text-white uppercase tracking-widest">
                    Checkout
                </button>
                <button class="w-full p-3 mt-3 bg-white border border-gray-300 rounded-md transition font-semibold text-xs text-gray-700 uppercase tracking-widest">
                    Clear Order
                </button>
            </div>
        </div>
    </div>

    <!-- Add Client Modal -->
    <x-dialog-modal wire:model="showAddClientModal">
        <x-slot name="title">
            Add Client
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-label for="name">Name <span class="text-red-500">*</span></x-label>
                <x-input id="name" type="text" class="mt-1 block w-full" wire:model.defer="name" />
                <x-input-error for="name" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="company" value="{{ __('Company') }}" />
                <x-input id="company" type="text" class="mt-1 block w-full" wire:model.defer="company" />
                <x-input-error for="company" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="contact_no" value="{{ __('Contact No.') }}" />
                <x-input id="contact_no" type="text" class="mt-1 block w-full" wire:model.defer="contact_no" />
                <x-input-error for="contact_no" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" type="email" class="mt-1 block w-full" wire:model.defer="email" />
                <x-input-error for="email" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="address" value="{{ __('Address') }}" />
                <x-input id="address" type="text" class="mt-1 block w-full" wire:model.defer="address" />
                <x-input-error for="address" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="google_map_pin" value="{{ __('Google Map Pin') }}" />
                <x-input id="google_map_pin" type="text" class="mt-1 block w-full" wire:model.defer="google_map_pin" />
                <x-input-error for="google_map_pin" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddClientModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-button class="ms-2" wire:click="addClient" wire:loading.attr="disabled">
                Save
            </x-button>
        </x-slot>
    </x-dialog-modal>

    <!-- Checkout Modal -->
    <x-dialog-modal wire:model="showCheckoutModal">
        <x-slot name="title">
            Finalize Sale
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div class="text-lg">
                    <strong>Total Amount:</strong> ₱{{ number_format($total_amount, 2) }}
                </div>

                @if (in_array($paymentMethodId, [2, 4, 5]))
                    <div>
                        <x-label for="paymentReferenceNumber" value="{{ __('Reference Number') }}" />
                        <x-input id="paymentReferenceNumber" type="text" class="mt-1 block w-full" wire:model="paymentReferenceNumber" />
                    </div>
                @endif

                @if ($paymentMethodId == 6)
                    <div>
                        <x-label for="paymentCheckNumber" value="{{ __('Check Number') }}" />
                        <x-input id="paymentCheckNumber" type="text" class="mt-1 block w-full" wire:model="paymentCheckNumber" />
                    </div>
                @endif

                @if ($paymentMethodId == 1)
                <div>
                    <x-label for="paymentAmountReceived" value="{{ __('Amount Received') }}" />
                    <x-input id="paymentAmountReceived" type="number" class="mt-1 block w-full" wire:model.live="paymentAmountReceived" />
                </div>
                @endif

                @if ($paymentMethodId == 1)
                    <div class="text-lg">
                        <strong>Amount Change:</strong> ₱{{ number_format($paymentAmountChange, 2) }}
                    </div>
                @endif
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showCheckoutModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="finalizeSale" wire:loading.attr="disabled">
                Confirm Sale
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Receipt Modal -->
    <x-dialog-modal wire:model="showReceiptModal">
        <x-slot name="title">
            Transaction Receipt
        </x-slot>

        <x-slot name="content">
            @if ($lastTransaction)
                <div id="addSaleReceipt" class="p-4 bg-white rounded-lg">
                    <div class="text-center mb-4">
                        <h2 class="text-2xl font-bold">ELMACRIS ICE TRADING</h2>
                        <p>Solid Road, Brgy. San Manuel, 5300 Puerto Princesa City (Capital), Palawan</p>
                    </div>
                    <div class="mb-4">
                        <p><strong>Date:</strong> {{ $lastTransaction->created_at->format('m-d-y') }}</p>
                        <p><strong>Sold to:</strong> {{ $lastTransaction->client->name }}</p>
                        <p><strong>Address:</strong> {{ $lastTransaction->client->address }}</p>
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
                            @foreach ($lastTransaction->sales as $sale)
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
                        <p>Total: ₱{{ number_format($lastTransaction->total_amount, 2) }}</p>
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="resetForm" wire:loading.attr="disabled">
                Close
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" onclick="printReceiptContent('addSaleReceipt')">
                Print
            </button>
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
