<div class="py-12" wire:poll.5s>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('admin-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Admin Modules
                </a>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Stock Movement Monitoring</h2>

            <!-- Filters -->
            <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4 items-end">
                <div>
                    <x-label for="selectedProductId" value="{{ __('Product') }}" />
                    <select wire:model.live="selectedProductId" id="selectedProductId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <option value="">All Products</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-label for="selectedDate" value="{{ __('Select Date') }}" />
                    <x-input id="selectedDate" type="date" class="mt-1 block w-full" wire:model.live="selectedDate" max="{{ now()->format('Y-m-d') }}" />
                </div>
            </div>

            @if ($selectedStorageDutyLog)
                <div class="bg-gray-100 p-4 rounded-lg shadow-sm mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Selected Storage Shift Details:</h3>
                    <p><strong>Storage Man:</strong> {{ $selectedStorageDutyLog->user->name ?? 'N/A' }}</p>
                    <p><strong>Time In:</strong> {{ $selectedStorageDutyLog->time_in->format('F d, Y h:i A') }}</p>
                    <p><strong>Time Out:</strong> {{ $selectedStorageDutyLog->time_out ? $selectedStorageDutyLog->time_out->format('M d, Y h:i A') : 'Ongoing' }}</p>
                </div>
            @else
                <div class="bg-yellow-100 p-4 rounded-lg shadow-sm mb-6 text-yellow-800">
                    <p>No storage shift found for the selected date. Please select a date with an active or completed shift.</p>
                </div>
            @endif

            <!-- Product Summary Table -->
            <div class="mt-6 mb-8 overflow-x-auto">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Product Summary</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Previous Closing Stock</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Start Count</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Restocks</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Initial Dispensed</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Received by Client</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Returns</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Running Stock</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Loss</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Closing Count</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Discrepancy</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($productSummary as $summary)
                            @php
                                $runningStock = $summary['running_stock'];
                                $highLevel = $summary['high_level'];
                                $runningLowLevel = $summary['running_low_level'];
                                $criticalLevel = $summary['critical_level'];
                                $stockClass = '';
                                if ($runningStock <= $criticalLevel) {
                                    $stockClass = 'text-red-500';
                                } elseif ($runningStock <= $runningLowLevel) {
                                    $stockClass = 'text-yellow-500';
                                } elseif ($runningStock >= $highLevel) {
                                    $stockClass = 'text-green-500';
                                }
                            @endphp
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $summary['product_name'] }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ number_format($summary['previous_closing_stock'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ number_format($summary['start_count'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ number_format($summary['restocks'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ number_format($summary['initial_dispensed'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ number_format($summary['actual_received'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ number_format($summary['returns'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap font-bold {{ $stockClass }}">{{ number_format($summary['running_stock'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ number_format($summary['loss'], 0) }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ is_numeric($summary['closing_count']) ? number_format($summary['closing_count'], 0) : $summary['closing_count'] }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ is_numeric($summary['discrepancy']) ? number_format($summary['discrepancy'], 0) : $summary['discrepancy'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No product summary data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6 overflow-x-auto">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Stock Movement History</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Storage Man Duty</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($movements as $movement)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $movement['product_name'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $movement['type'] }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap font-bold
                                    @if ($movement['type'] == 'Addition' || $movement['type'] == 'Return')
                                        text-green-500
                                    @else
                                        text-red-500
                                    @endif
                                ">
                                    {{ $movement['quantity'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $movement['user'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $movement['date']->format('F d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No stock movements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</div>
