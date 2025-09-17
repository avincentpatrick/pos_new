<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('admin-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Admin Modules
                </a>
            </div>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Products</h2>
            </div>

            <div class="mt-6">
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange" placeholder="Search products...">
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Product ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Kilogram</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Retail Price</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button wire:click="openEditProductModal({{ $product->id }})" class="text-custom-orange hover:text-orange-700"><i class="fa-solid fa-edit"></i></button>
                                    <button wire:click="deleteProduct({{ $product->id }})" class="text-red-600 hover:text-red-900 ml-4"><i class="fa-solid fa-trash"></i></button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">{{ $product->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('product-detail', $product) }}" class="text-blue-500 hover:underline">{{ $product->product_name }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">{{ $product->product_description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">{{ $product->kilogram }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">₱{{ number_format($product->retail_price, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center justify-center h-48">
                                        <p class="text-lg text-gray-500 mb-4">No products found.</p>
                                        @if ($search)
                                            <p class="text-sm text-gray-500 mb-4">Adjust your search to see more results.</p>
                                        @endif
                                        <button wire:click="openAddProductModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                            <i class="fa-solid fa-plus mr-2"></i> Add Product
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <x-dialog-modal wire:model="showAddProductModal">
        <x-slot name="title">
            Add Product
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-label for="product_name">
                    {!! __('Product Name') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="product_name" type="text" class="mt-1 block w-full" wire:model.defer="product_name" />
                <x-input-error for="product_name" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="product_description" value="{{ __('Description') }}" />
                <textarea id="product_description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring focus:ring-custom-orange focus:ring-opacity-50" wire:model.defer="product_description"></textarea>
                <x-input-error for="product_description" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="kilogram" value="{{ __('Kilogram') }}" />
                <x-input id="kilogram" type="number" class="mt-1 block w-full" wire:model.defer="kilogram" />
                <x-input-error for="kilogram" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="retail_price">
                    {!! __('Retail Price') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="retail_price" type="number" class="mt-1 block w-full" wire:model.defer="retail_price" />
                <x-input-error for="retail_price" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="high_level">
                    {!! __('High Level') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="high_level" type="number" class="mt-1 block w-full" wire:model.defer="high_level" />
                <x-input-error for="high_level" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="running_low_level">
                    {!! __('Running Low Level') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="running_low_level" type="number" class="mt-1 block w-full" wire:model.defer="running_low_level" />
                <x-input-error for="running_low_level" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="critical_level">
                    {!! __('Critical Level') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="critical_level" type="number" class="mt-1 block w-full" wire:model.defer="critical_level" />
                <x-input-error for="critical_level" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddProductModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="addProduct" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Edit Product Modal -->
    <x-dialog-modal wire:model="showEditProductModal">
        <x-slot name="title">
            Edit Product
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-label for="product_name">
                    {!! __('Product Name') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="product_name" type="text" class="mt-1 block w-full" wire:model.defer="product_name" />
                <x-input-error for="product_name" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="product_description" value="{{ __('Description') }}" />
                <textarea id="product_description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring focus:ring-custom-orange focus:ring-opacity-50" wire:model.defer="product_description"></textarea>
                <x-input-error for="product_description" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="kilogram" value="{{ __('Kilogram') }}" />
                <x-input id="kilogram" type="number" class="mt-1 block w-full" wire:model.defer="kilogram" />
                <x-input-error for="kilogram" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="retail_price">
                    {!! __('Retail Price') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="retail_price" type="number" class="mt-1 block w-full" wire:model.defer="retail_price" />
                <x-input-error for="retail_price" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="high_level">
                    {!! __('High Level') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="high_level" type="number" class="mt-1 block w-full" wire:model.defer="high_level" />
                <x-input-error for="high_level" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="running_low_level">
                    {!! __('Running Low Level') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="running_low_level" type="number" class="mt-1 block w-full" wire:model.defer="running_low_level" />
                <x-input-error for="running_low_level" class="mt-2" />
            </div>
            <div class="mt-4">
                <x-label for="critical_level">
                    {!! __('Critical Level') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="critical_level" type="number" class="mt-1 block w-full" wire:model.defer="critical_level" />
                <x-input-error for="critical_level" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEditProductModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="updateProduct" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    @livewire('delete-confirmation-modal')
</div>
