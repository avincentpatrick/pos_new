<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('products') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Products
                </a>
            </div>
            <div class="mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">{{ $product->product_name }}</h2>
                <p class="text-gray-600">{{ $product->product_description }}</p>
                <p class="text-gray-600">Retail Price: ₱{{ number_format($product->retail_price, 2) }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-blue-100 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-800">Total Stock</h3>
                        <p class="text-2xl font-bold text-blue-900">{{ $totalStock }}</p>
                    </div>
                    <i class="fa-solid fa-boxes-stacked text-blue-500 text-3xl"></i>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-800">Remaining Stocks</h3>
                        <p class="text-2xl font-bold text-yellow-900">{{ $remainingStock }}</p>
                    </div>
                    <i class="fa-solid fa-warehouse text-yellow-500 text-3xl"></i>
                </div>
                <div class="bg-green-100 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-green-800">Products Sold</h3>
                        <p class="text-2xl font-bold text-green-900">{{ $soldProductsCount }}</p>
                    </div>
                    <i class="fa-solid fa-chart-line text-green-500 text-3xl"></i>
                </div>
                <div class="bg-purple-100 p-4 rounded-lg shadow flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-purple-800">Total Sales</h3>
                        <p class="text-2xl font-bold text-purple-900">₱{{ number_format($totalSales, 2) }}</p>
                    </div>
                    <i class="fa-solid fa-cash-register text-purple-500 text-3xl"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Sales Logs</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($salesLogs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">₱{{ number_format($log->total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No sales logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $salesLogs->links() }}
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Restocking Logs</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($stockLogs as $log)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $log->quantity }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No restocking logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $stockLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
