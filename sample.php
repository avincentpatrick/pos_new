<div class="bg-gray-100 min-h-screen">
    <!-- Page Header -->
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">Sales</h2>
    </x-slot>
    @if( $cashier_duty_log_status_id == 1 )
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white shadow p-4 flex justify-between items-center rounded-lg text-sm text-gray-700 mb-4">
            <div>
                <p class="text-lg">
                    <span class="font-semibold">Status:</span> 
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-green-800 bg-green-200 font-medium">Active</span>
                </p>
                <p class="text-lg font-semibold">Cashier: <span class="font-normal">{{ Auth::user()->name }}</span></p>
            </div>
            <div class="flex items-center gap-6">
                <!-- Date & Time Display -->
                <div x-data="{ time: '' }" x-init="setInterval(() => { 
                    const now = new Date(); 
                    time = now.toLocaleString('en-PH', { hour12: true }); 
                }, 1000)">
                    <p class="text-sm"><span class="font-semibold">Date & Time:</span> <span x-text="time" class="text-gray-600"></span></p>
                </div>
                <!-- End Shift Button -->
                <button class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition text-sm font-semibold" wire:click="openEndShiftModal">
                    End Shift
                </button>
            </div>
        </div>
        <div class="flex gap-6 w-full">
            <!-- Left Panel: Select Customer, Product Selection & Transaction Summary -->
            <div class="flex-1 basis-1/3 bg-white p-6 rounded-lg shadow">
                <!-- Select Customer Section -->
                <div class="mb-6 w-full">
                    <div class="w-full relative">
                        <label for="customer" class="block text-xl font-bold mb-4">Customer name</label>
                        
                        <div class="relative">
                            <input 
                                type="text" 
                                wire:model.live="search"
                                placeholder="Search customer..."
                                class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10"
                                @if($selectedCustomerId) disabled @endif
                            >

                            @if ($selectedCustomerId)
                                <button wire:click="clearSelectedCustomer" class="mt-2 px-4 py-2 bg-gray-500 text-white rounded">Clear</button>
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
                                    <button 
                                        type="button"
                                        wire:click="openAddCustomerModal"
                                        class="w-full text-left px-4 py-2 text-blue-600 hover:bg-blue-50"
                                    >
                                        Add "{{ $search }}" as new customer
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product Selection Section
                <h2 class="text-xl font-bold mb-4">Select a Product</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <button class="p-4 bg-blue-100 border rounded-lg text-center hover:bg-blue-200 transition">
                        <p class="font-semibold">1 kg Purified Tube Ice</p>
                        <p class="text-gray-600">₱50.00</p>
                    </button>
                    <button class="p-4 bg-blue-100 border rounded-lg text-center hover:bg-blue-200 transition">
                        <p class="font-semibold">5 kgs Purified Tube Ice</p>
                        <p class="text-gray-600">₱250.00</p>
                    </button>
                    <button class="p-4 bg-blue-100 border rounded-lg text-center hover:bg-blue-200 transition">
                        <p class="font-semibold">30 kgs Purified Tube Ice</p>
                        <p class="text-gray-600">₱1,500.00</p>
                    </button>
                    <button class="p-4 bg-blue-100 border rounded-lg text-center hover:bg-blue-200 transition">
                        <p class="font-semibold">1 Truck Purified Tube Ice</p>
                        <p class="text-gray-600">₱15,500.00</p>
                    </button>
                </div> -->

                <!-- Transaction Summary Section as a Table -->
                <h2 class="text-xl font-bold mb-4">Products</h2>
                <div class="overflow-x-auto">
                    <table class="w-full bg-white border">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-3 px-4 text-left">Product</th>
                                <th class="py-3 px-4 text-left">Unit Price</th>
                                <th class="py-3 px-4 text-left">Quantity</th>
                                <th class="py-3 px-4 text-left">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stocks as $stock)
                            <tr class="border-b">
                                <td class="py-3 px-4">{{ $stock->product->product_name }}</td>
                                <td class="py-3 px-4">₱{{ number_format($stock->price, 2) }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            wire:click.prevent="decrementQuantity({{ $stock->id }})" 
                                            class="p-2 bg-gray-200 rounded hover:bg-gray-300 transition"
                                            type="button"
                                        >-</button>
                                        <span class="text-lg">{{ $quantities[$stock->id] ?? 0 }}</span>
                                        <button 
                                            wire:click.prevent="incrementQuantity({{ $stock->id }})" 
                                            class="p-2 bg-gray-200 rounded hover:bg-gray-300 transition"
                                            type="button"
                                        >+</button>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    ₱{{ number_format(($quantities[$stock->id] ?? 0) * $stock->price, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel: Delivery/Pick-up, Payment Details & Order Options -->
            <div class="flex-1 basis-2/3 bg-white p-6 rounded-lg shadow">
                <!-- Order Type Selection -->
                <div class="mb-6">
                    <label class="block text-xl font-bold mb-2">Order Type</label>
                    <div class="flex gap-4">
                        @foreach ($order_types as $order_type)
                            <label class="flex items-center gap-2">
                                <input wire:model="order_type_id" type="radio" name="order_type_id" value="{{ $order_type->id }}" class="form-radio h-5 w-5 text-blue-600">
                                {{ $order_type->order_type_name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Payment Details Section -->
                <h2 class="text-xl font-bold mb-4">Payment Details</h2>
                <div class="space-y-4">
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
                    <div class="flex gap-4">
                        @foreach ($payment_methods as $payment_method)
                            <label class="flex items-center gap-2">
                                <input wire:model="payment_method_id" type="radio" name="payment_method_id" value="{{ $payment_method->id }}" class="form-radio h-5 w-5 text-blue-600">
                                {{ $payment_method->payment_method_name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Additional Order Notes -->
                <div class="mt-6">
                    <label for="notes" class="block mb-2 font-semibold">Notes for Order:</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full border rounded p-2" placeholder="Enter any special instructions..."></textarea>
                </div>

                <!-- Action Buttons -->
                <button wire:click="prepareCheckout" class="w-full bg-blue-600 text-white p-3 mt-6 rounded hover:bg-blue-700 transition">
                    Checkout
                </button>
                <button class="w-full bg-red-600 text-white p-3 mt-4 rounded hover:bg-red-700 transition">
                    Clear Order
                </button>
            </div>
        </div>
    </div>
    @else
    <div class="max-w-7xl mx-auto px-4">
        <div class="bg-white shadow p-4 flex justify-between items-center rounded-lg text-sm text-gray-700 mb-4">
            <div>
                <p class="text-lg">
                    <span class="font-semibold">Status:</span> 
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-red-800 bg-red-200 font-medium">Inactive</span>
                </p>
                <p class="text-lg font-semibold">Cashier: <span class="font-normal">{{ Auth::user()->name }}</span></p>
            </div>
            <div class="flex items-center gap-6">
                <!-- Date & Time Display -->
                <div x-data="{ time: '' }" x-init="setInterval(() => { 
                    const now = new Date(); 
                    time = now.toLocaleString('en-PH', { hour12: true }); 
                }, 1000)">
                    <p class="text-sm"><span class="font-semibold">Date & Time:</span> <span x-text="time" class="text-gray-600"></span></p>
                </div>
                <!-- Start Shift Button -->
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition-colors duration-200" wire:click="openStartShiftModal">
                    Start Shift
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($showStartShiftModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <form wire:submit.prevent="startShift">
                <div class="mb-2">
                    <label class="block text-gray-700">Starting Cash</label>
                    <input type="number" 
                        step="0.01" 
                        wire:model.defer="total_cash_in" 
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        placeholder="Cash amount">
                    @error('total_cash_in') 
                    <span class="text-red-500 text-sm">{{ $message }}</span> 
                    @enderror
                </div>
                <div class="flex gap-2 mt-4">
                    <button type="button" wire:click="closeModal" class="w-1/2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="submit" class="w-1/2 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition">
                        Start Shift
                    </button>
                </div>
            </form>
        </div>
    </div>

    @endif

    @if($showEndShiftModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <form wire:submit.prevent="endShift">
                <div class="mb-2">
                    <label class="block text-gray-700">Ending Cash</label>
                    <input type="number" 
                        step="0.01" 
                        wire:model.defer="total_cash_out" 
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        placeholder="Cash amount">
                    @error('total_cash_out')
                    <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Total Sales in Cash</label>
                    <input type="number" 
                        step="0.01" 
                        wire:model.defer="total_sales_in_cash" 
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                        placeholder="Cash amount">
                    @error('total_sales_in_cash') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="flex gap-2 mt-4">
                     <button type="button" wire:click="closeModal" class="w-1/2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition">
                        Cancel
                    </button>
                    <button type="submit" class="w-1/2 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                        End Shift
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @if($showAddCustomerModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Add Customer</h2>
            <form wire:submit.prevent="store">
                <div class="mb-2">
                    <label class="block text-gray-700">Name</label>
                    <input type="text" wire:model.defer="name" class="w-full p-2 border rounded">
                    @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Company</label>
                    <input type="text" wire:model.defer="company" class="w-full p-2 border rounded">
                    @error('company') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Contact no.</label>
                    <input type="text" wire:model.defer="contact_no" class="w-full p-2 border rounded">
                    @error('contact_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Email</label>
                    <input type="email" wire:model.defer="email" class="w-full p-2 border rounded">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Address</label>
                    <input type="text" wire:model.defer="address" class="w-full p-2 border rounded">
                    @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="closeModal" class="mr-2 px-4 py-2 bg-gray-500 text-white rounded">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
    @if($showCheckoutModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Checkout</h2>
            <form wire:submit.prevent="checkout">
                <div class="mb-2">
                    <label class="block text-gray-700">Total Amount: </label>
                    <span class="font-semibold text-black">₱{{ number_format($total_amount, 2) }}</span>
                </div>
                @if(in_array($payment_method_id, [1, 6]))
                <div class="mb-2">
                    <label class="block text-gray-700">Payment amount: </label>
                    <input type="number" step="0.01" wire:model.live="amount_received" class="w-full p-2 border rounded">
                    @error('amount_received') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Change: </label>
                    <span class="font-semibold text-black">₱{{ number_format($amount_received - $total_amount, 2) }}</span>
                </div>
                    @if($amount_received < $total_amount)
                    <div class="mb-2">
                        <label class="block text-gray-700">Remaining Balance: </label>
                        <span class="font-semibold text-black">₱{{ number_format($total_amount - $amount_received, 2) }}</span>
                    </div>
                    @endif
                @endif
                @if(in_array($payment_method_id, [2, 3, 4]))
                <div class="mb-2">
                    <label class="block text-gray-700">Reference no.</label>
                    <input type="text" wire:model="reference_number" class="w-full p-2 border rounded">
                    @error('reference_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Payment amount</label>
                    <input type="number" step="0.01" wire:model.live="amount_received" class="w-full p-2 border rounded">
                    @error('amount_received') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                    @if($amount_received < $total_amount)
                    <div class="mb-2">
                        <label class="block text-gray-700">Remaining Balance: </label>
                        <span class="font-semibold text-black">₱{{ number_format($total_amount - $amount_received, 2) }}</span>
                    </div>
                    @endif
                @endif
                @if($payment_method_id == 5)
                <div class="mb-2">
                    <label class="block text-gray-700">Cheque no.</label>
                    <input type="text" wire:model="check_number" class="w-full p-2 border rounded">
                    @error('check_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Payment amount</label>
                    <input type="number" step="0.01" wire:model.live="amount_received" class="w-full p-2 border rounded">
                    @error('amount_received') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                    @if($amount_received < $total_amount)
                    <div class="mb-2">
                        <label class="block text-gray-700">Remaining Balance: </label>
                        <span class="font-semibold text-black">₱{{ number_format($total_amount - $amount_received, 2) }}</span>
                    </div>
                    @endif
                @endif

                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="$set('showCheckoutModal', false)" class="mr-2 px-4 py-2 bg-gray-500 text-white rounded">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>