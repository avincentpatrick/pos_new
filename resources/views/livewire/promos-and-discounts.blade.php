<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('admin-module') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-600 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 disabled:opacity-25 transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Admin Module
                </a>
            </div>

            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Promos and Discounts</h2>

            <!-- Promo Packages Section -->
            <div class="mb-8">
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Promo Packages</h3>
                <div class="mb-4 flex justify-between items-center">
                    <x-input type="text" wire:model.live.debounce.300ms="searchPromoPackages" placeholder="Search promo packages..." class="w-full" />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Package Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($promoPackages as $package)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $package->promo_package_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $package->validity_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editPromoPackage({{ $package->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                        <button wire:click="deletePromoPackage({{ $package->id }})" class="text-red-600 hover:text-red-900 mr-2">Delete</button>
                                        <button wire:click="openApplyToClientsModal('promo_package', {{ $package->id }})" class="text-green-600 hover:text-green-900">Apply to Clients</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center justify-center h-48">
                                            <p class="text-lg text-gray-500 mb-4">No promo packages found.</p>
                                            @if ($searchPromoPackages)
                                                <p class="text-sm text-gray-500 mb-4">Adjust your search to see more results.</p>
                                            @endif
                                            <button wire:click="openAddPromoPackageModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                                <i class="fa-solid fa-plus mr-2"></i> Add Promo Package
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $promoPackages->links('pagination::tailwind', ['pageName' => 'promoPackagesPage']) }}
                </div>
            </div>

            <!-- Special Price Sets Section -->
            <div>
                <h3 class="text-xl font-semibold text-gray-700 mb-4">Special Price Sets</h3>
                <div class="mb-4 flex justify-between items-center">
                    <x-input type="text" wire:model.live.debounce.300ms="searchSpecialPriceSets" placeholder="Search special price sets..." class="w-full" />
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Set Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validity Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($specialPriceSets as $set)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $set->special_price_set_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $set->validity_date }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button wire:click="editSpecialPriceSet({{ $set->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                                        <button wire:click="deleteSpecialPriceSet({{ $set->id }})" class="text-red-600 hover:text-red-900 mr-2">Delete</button>
                                        <button wire:click="openApplyToClientsModal('special_price_set', {{ $set->id }})" class="text-green-600 hover:text-green-900">Apply to Clients</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center justify-center h-48">
                                            <p class="text-lg text-gray-500 mb-4">No special price sets found.</p>
                                            @if ($searchSpecialPriceSets)
                                                <p class="text-sm text-gray-500 mb-4">Adjust your search to see more results.</p>
                                            @endif
                                            <button wire:click="openAddSpecialPriceSetModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                                <i class="fa-solid fa-plus mr-2"></i> Add Special Price Set
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $specialPriceSets->links('pagination::tailwind', ['pageName' => 'specialPriceSetsPage']) }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Promo Package Modal -->
    <x-dialog-modal wire:model="showAddPromoPackageModal">
        <x-slot name="title">
            Add New Promo Package
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="newPromoPackageName">
                        {!! __('Package Name') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="newPromoPackageName" type="text" class="mt-1 block w-full" wire:model.defer="newPromoPackageName" />
                    <x-input-error for="newPromoPackageName" class="mt-2" />
                </div>
                <div>
                    <x-label for="newPromoPackageValidityDate">
                        {!! __('Validity Date') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="newPromoPackageValidityDate" type="date" class="mt-1 block w-full" wire:model.defer="newPromoPackageValidityDate" />
                    <x-input-error for="newPromoPackageValidityDate" class="mt-2" />
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Promos per Product</h4>
                    <div class="overflow-y-auto max-h-60 border rounded-md p-2">
                        @forelse ($products as $product)
                            <div class="flex items-center py-2 border-b last:border-b-0">
                                <x-label for="promo-product-{{ $product->id }}" class="w-1/3">{{ $product->product_name }}</x-label>
                                <div class="flex-1 flex items-center ml-4">
                                    <x-input id="promo-product-{{ $product->id }}-min-buy" type="number" class="w-1/2" wire:model.defer="promoProducts.{{ $product->id }}.minimum_buy" placeholder="Min Buy" />
                                    <x-input id="promo-product-{{ $product->id }}-get-free" type="number" class="w-1/2 ml-2" wire:model.defer="promoProducts.{{ $product->id }}.get_free" placeholder="Get Free" />
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center">No products available to set promos.</p>
                        @endforelse
                    </div>
                    <x-input-error for="promoProducts" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeAddPromoPackageModal" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>
            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="savePromoPackage" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Edit Promo Package Modal -->
    <x-dialog-modal wire:model="showEditPromoPackageModal">
        <x-slot name="title">
            Edit Promo Package
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="editingPromoPackageName">
                        {!! __('Package Name') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="editingPromoPackageName" type="text" class="mt-1 block w-full" wire:model.defer="editingPromoPackageName" />
                    <x-input-error for="editingPromoPackageName" class="mt-2" />
                </div>
                <div>
                    <x-label for="editingPromoPackageValidityDate">
                        {!! __('Validity Date') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="editingPromoPackageValidityDate" type="date" class="mt-1 block w-full" wire:model.defer="editingPromoPackageValidityDate" />
                    <x-input-error for="editingPromoPackageValidityDate" class="mt-2" />
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Promos per Product</h4>
                    <div class="overflow-y-auto max-h-60 border rounded-md p-2">
                        @forelse ($products as $product)
                            <div class="flex items-center py-2 border-b last:border-b-0">
                                <x-label for="editing-promo-product-{{ $product->id }}" class="w-1/3">{{ $product->product_name }}</x-label>
                                <div class="flex-1 flex items-center ml-4">
                                    <x-input id="editing-promo-product-{{ $product->id }}-min-buy" type="number" class="w-1/2" wire:model.defer="editingPromoProducts.{{ $product->id }}.minimum_buy" placeholder="Min Buy" />
                                    <x-input id="editing-promo-product-{{ $product->id }}-get-free" type="number" class="w-1/2 ml-2" wire:model.defer="editingPromoProducts.{{ $product->id }}.get_free" placeholder="Get Free" />
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center">No products available to set promos.</p>
                        @endforelse
                    </div>
                    <x-input-error for="editingPromoProducts" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEditPromoPackageModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>
            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="updatePromoPackage" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Add Special Price Set Modal -->
    <x-dialog-modal wire:model="showAddSpecialPriceSetModal">
        <x-slot name="title">
            Add New Special Price Set
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="newSpecialPriceSetName">
                        {!! __('Set Name') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="newSpecialPriceSetName" type="text" class="mt-1 block w-full" wire:model.defer="newSpecialPriceSetName" />
                    <x-input-error for="newSpecialPriceSetName" class="mt-2" />
                </div>
                <div>
                    <x-label for="newSpecialPriceSetValidityDate">
                        {!! __('Validity Date') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="newSpecialPriceSetValidityDate" type="date" class="mt-1 block w-full" wire:model.defer="newSpecialPriceSetValidityDate" />
                    <x-input-error for="newSpecialPriceSetValidityDate" class="mt-2" />
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Special Prices per Product</h4>
                    <div class="overflow-y-auto max-h-60 border rounded-md p-2">
                        @forelse ($products as $product)
                            <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                                <x-label for="special-price-product-{{ $product->id }}" class="w-1/2">{{ $product->product_name }}</x-label>
                                <x-input id="special-price-product-{{ $product->id }}" type="number" class="w-1/2 ml-4" wire:model.defer="specialPriceSetProducts.{{ $product->id }}" placeholder="Enter special price" />
                            </div>
                        @empty
                            <p class="text-gray-500 text-center">No products available to set special prices.</p>
                        @endforelse
                    </div>
                    <x-input-error for="specialPriceSetProducts" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeAddSpecialPriceSetModal" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>
            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="saveSpecialPriceSet" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Edit Special Price Set Modal -->
    <x-dialog-modal wire:model="showEditSpecialPriceSetModal">
        <x-slot name="title">
            Edit Special Price Set
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="editingSpecialPriceSetName">
                        {!! __('Set Name') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="editingSpecialPriceSetName" type="text" class="mt-1 block w-full" wire:model.defer="editingSpecialPriceSetName" />
                    <x-input-error for="editingSpecialPriceSetName" class="mt-2" />
                </div>
                <div>
                    <x-label for="editingSpecialPriceSetValidityDate">
                        {!! __('Validity Date') !!} <span class='text-red-500'>*</span>
                    </x-label>
                    <x-input id="editingSpecialPriceSetValidityDate" type="date" class="mt-1 block w-full" wire:model.defer="editingSpecialPriceSetValidityDate" />
                    <x-input-error for="editingSpecialPriceSetValidityDate" class="mt-2" />
                </div>

                <div class="mt-6">
                    <h4 class="text-lg font-semibold text-gray-700 mb-2">Special Prices per Product</h4>
                    <div class="overflow-y-auto max-h-60 border rounded-md p-2">
                        @forelse ($products as $product)
                            <div class="flex items-center justify-between py-2 border-b last:border-b-0">
                                <x-label for="editing-special-price-product-{{ $product->id }}" class="w-1/2">{{ $product->product_name }}</x-label>
                                <x-input id="editing-special-price-product-{{ $product->id }}" type="number" class="w-1/2 ml-4" wire:model.defer="editingSpecialPriceSetProducts.{{ $product->id }}" placeholder="Enter special price" />
                            </div>
                        @empty
                            <p class="text-gray-500 text-center">No products available to set special prices.</p>
                        @endforelse
                    </div>
                    <x-input-error for="editingSpecialPriceSetProducts" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEditSpecialPriceSetModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>
            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="updateSpecialPriceSet" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Apply to Clients Modal -->
    <x-dialog-modal wire:model="showApplyToClientsModal">
        <x-slot name="title">
            Apply
            @if ($applyingToType === 'promo_package')
                Promo Package
            @elseif ($applyingToType === 'special_price_set')
                Special Price Set
            @endif
            to Clients
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-input type="text" wire:model.live.debounce.300ms="searchClients" placeholder="Search clients..." class="w-full" />
                </div>

                <div class="overflow-y-auto max-h-60 border rounded-md p-2">
                    @forelse ($clients as $client)
                        @php
                            $isDisabled = false;
                            $tooltip = '';

                            if ($applyingToType === 'promo_package') {
                                if ($client->clientPromo && $client->clientPromo->promo_package_id !== $applyingToId) {
                                    $isDisabled = true;
                                    $tooltip = 'Already subscribed to another promo package.';
                                }
                            } elseif ($applyingToType === 'special_price_set') {
                                if ($client->clientSpecialPrice && $client->clientSpecialPrice->special_price_set_id !== $applyingToId) {
                                    $isDisabled = true;
                                    $tooltip = 'Already subscribed to another special price set.';
                                }
                            }
                        @endphp
                        <div class="flex items-center py-2 border-b last:border-b-0 {{ $isDisabled ? 'bg-gray-100 text-gray-500' : '' }}" title="{{ $tooltip }}">
                            <input type="checkbox" wire:model.defer="selectedClientIds" value="{{ $client->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ $isDisabled ? 'disabled' : '' }} />
                            <x-label for="client-{{ $client->id }}" class="ml-2 {{ $isDisabled ? 'line-through' : '' }}">{{ $client->name }}</x-label>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center">No clients found.</p>
                    @endforelse
                </div>
                <x-input-error for="selectedClientIds" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="closeApplyToClientsModal" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>
            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="applyToClients" wire:loading.attr="disabled">
                Apply
            </button>
        </x-slot>
    </x-dialog-modal>
</div>
