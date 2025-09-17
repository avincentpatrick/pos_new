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
                <h2 class="text-2xl font-semibold text-gray-800">Clients</h2>
            </div>

            <div class="mt-6">
                <input wire:model.live.debounce.300ms="search" type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Search clients...">
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Client ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact No.</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Added</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($clients as $client)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button wire:click="openEditClientModal({{ $client->id }})" class="text-custom-orange hover:text-orange-700"><i class="fa-solid fa-edit"></i></button>
                                    <button wire:click="deleteClient({{ $client->id }})" class="text-red-600 hover:text-red-900 ml-4"><i class="fa-solid fa-trash"></i></button>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">{{ $client->id }}</td>
                                <td class="px-6 py-4 text-left whitespace-nowrap">
                                    
                                    @if($userLevel == 1)
                                    <a href="{{ route('client-detail', $client) }}" class="text-blue-500 hover:underline">{{ $client->name }}</a>
                                    @elseif($userLevel == 2)
                                    {{ $client->name }}
                                    @endif
                                    
                                </td>
                                <td class="px-6 py-4 text-left whitespace-nowrap">{{ $client->company }}</td>
                                <td class="px-6 py-4 text-left whitespace-nowrap">{{ $client->contact_no }}</td>
                                <td class="px-6 py-4 text-left whitespace-nowrap">{{ $client->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-left">{{ $client->created_at->format('F j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center justify-center h-48">
                                        <p class="text-lg text-gray-500 mb-4">No clients found.</p>
                                        @if ($search)
                                            <p class="text-sm text-gray-500 mb-4">Adjust your search to see more results.</p>
                                        @endif
                                        <button wire:click="openAddClientModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                            <i class="fa-solid fa-plus mr-2"></i> Add Client
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $clients->links() }}
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

            <div class="mt-4">
                <x-label for="clientPromoPackageId" value="{{ __('Promo Package') }}" />
                <select wire:model.defer="clientPromoPackageId" id="clientPromoPackageId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">None</option>
                    @foreach ($promoPackages as $package)
                        <option value="{{ $package->id }}">{{ $package->promo_package_name }}</option>
                    @endforeach
                </select>
                <x-input-error for="clientPromoPackageId" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-label for="clientSpecialPriceSetId" value="{{ __('Special Price Set') }}" />
                <select wire:model.defer="clientSpecialPriceSetId" id="clientSpecialPriceSetId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">None</option>
                    @foreach ($specialPriceSets as $set)
                        <option value="{{ $set->id }}">{{ $set->special_price_set_name }}</option>
                    @endforeach
                </select>
                <x-input-error for="clientSpecialPriceSetId" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddClientModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="addClient" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Edit Client Modal -->
    <x-dialog-modal wire:model="showEditClientModal">
        <x-slot name="title">
            Edit Client
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

            <div class="mt-4">
                <x-label for="clientPromoPackageId" value="{{ __('Promo Package') }}" />
                <select wire:model.defer="clientPromoPackageId" id="clientPromoPackageId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">None</option>
                    @foreach ($promoPackages as $package)
                        <option value="{{ $package->id }}">{{ $package->promo_package_name }}</option>
                    @endforeach
                </select>
                <x-input-error for="clientPromoPackageId" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-label for="clientSpecialPriceSetId" value="{{ __('Special Price Set') }}" />
                <select wire:model.defer="clientSpecialPriceSetId" id="clientSpecialPriceSetId" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <option value="">None</option>
                    @foreach ($specialPriceSets as $set)
                        <option value="{{ $set->id }}">{{ $set->special_price_set_name }}</option>
                    @endforeach
                </select>
                <x-input-error for="clientSpecialPriceSetId" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEditClientModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="updateClient" wire:loading.attr="disabled">
                Save
            </button>
        </x-slot>
    </x-dialog-modal>

    @livewire('delete-confirmation-modal')
</div>
