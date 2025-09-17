<div class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-2xl sm:rounded-2xl p-8">
            <div class="mb-4">
                <a href="{{ route('storage-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Storage Modules
                </a>
            </div>
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-800">
                    Add Stock
                </h1>
                <p class="mt-2 text-gray-600">
                    Adjust the stock levels for each product.
                </p>
            </div>

            <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
                @forelse ($products as $product)
                    <div class="group flex flex-col items-center justify-between p-6 bg-white rounded-xl shadow-lg hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800 text-center">{{ $product->product_name }}</h2>
                        </div>
                        <div class="my-4 text-center">
                            <p class="text-sm text-gray-500">Current Stock</p>
                            <p class="text-5xl font-bold 
                                @if($total_stocks[$product->id] >= $product->high_level) text-green-500 @elseif($total_stocks[$product->id] >= $product->running_low_level) text-orange-500 @else text-red-500 @endif
                            ">
                                {{ $total_stocks[$product->id] }}
                            </p>
                            <p class="text-sm font-semibold
                                @if($total_stocks[$product->id] >= $product->high_level) text-green-500 @elseif($total_stocks[$product->id] >= $product->running_low_level) text-orange-500 @else text-red-500 @endif
                            ">
                                @if($total_stocks[$product->id] >= $product->high_level)
                                    Available
                                @elseif($total_stocks[$product->id] >= $product->running_low_level)
                                    Running Low
                                @else
                                    Critical Stock
                                @endif
                            </p>
                        </div>
                        <div class="text-sm text-gray-600 mt-2">
                            <p>High Level: {{ $product->high_level }}</p>
                            <p>Running Low: {{ $product->running_low_level }}</p>
                            <p>Critical Level: {{ $product->critical_level }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="number" wire:model.defer="adjustments.{{ $product->id }}" min="{{ -$total_stocks[$product->id] }}" class="w-20 text-center border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500">No products found.</p>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="saveStocks" class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
