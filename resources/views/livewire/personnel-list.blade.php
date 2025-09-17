<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('admin-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Admin Module
                </a>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Personnel Management</h2>

            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <!-- Filters -->
            <div class="mb-4 flex items-end space-x-8">
                <div class="flex-1">
                    <x-label for="search" value="{{ __('Search') }}" />
                    <x-input id="search" type="text" class="mt-1 block w-full" wire:model.live.debounce.300ms="search" placeholder="Search by name..." />
                </div>

                <div class="flex-1">
                    <x-label for="personnelTypeFilter" value="{{ __('Personnel Type') }}" />
                    <select wire:model.live="personnelTypeFilter" id="personnelTypeFilter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">All</option>
                        @foreach ($personnelTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->personnel_type_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto mt-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Personnel Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Registered</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($personnel as $person)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button wire:click="openEditPersonnelModal({{ $person->id }})" class="text-custom-orange hover:text-orange-700"><i class="fa-solid fa-edit"></i></button>
                                    <button wire:click="confirmDeletePersonnel({{ $person->id }})" class="text-red-600 hover:text-red-900 ml-4"><i class="fa-solid fa-trash"></i></button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $person->personnel_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $person->personnelType->personnel_type_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $person->created_at->format('F j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center justify-center h-48">
                                        <p class="text-lg text-gray-500 mb-4">No personnel found.</p>
                                        @if ($search || $personnelTypeFilter)
                                            <p class="text-sm text-gray-500 mb-4">Adjust your filters or clear the search to see more results.</p>
                                        @endif
                                        <button wire:click="openAddPersonnelModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                            <i class="fa-solid fa-plus mr-2"></i> Add New Personnel
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $personnel->links() }}
            </div>
        </div>
    </div>

    <!-- Add Personnel Modal -->
    <x-dialog-modal wire:model="showAddPersonnelModal">
        <x-slot name="title">
            Add New Personnel
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="personnel_name" value="{{ __('Name') }}" />
                    <x-input id="personnel_name" type="text" class="mt-1 block w-full" wire:model.defer="personnel_name" />
                    <x-input-error for="personnel_name" class="mt-2" />
                </div>

                <div>
                    <x-label for="personnel_type_id" value="{{ __('Personnel Type') }}" />
                    <select wire:model.defer="personnel_type_id" id="personnel_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select a personnel type</option>
                        @foreach ($personnelTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->personnel_type_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="personnel_type_id" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddPersonnelModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="addPersonnel" wire:loading.attr="disabled">
                Add Personnel
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Edit Personnel Modal -->
    <x-dialog-modal wire:model="showEditPersonnelModal">
        <x-slot name="title">
            Edit Personnel
        </x-slot>

        <x-slot name="content">
            <div class="space-y-4">
                <div>
                    <x-label for="edit_personnel_name" value="{{ __('Name') }}" />
                    <x-input id="edit_personnel_name" type="text" class="mt-1 block w-full" wire:model.defer="edit_personnel_name" />
                    <x-input-error for="edit_personnel_name" class="mt-2" />
                </div>

                <div>
                    <x-label for="edit_personnel_type_id" value="{{ __('Personnel Type') }}" />
                    <select wire:model.defer="edit_personnel_type_id" id="edit_personnel_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Select a personnel type</option>
                        @foreach ($personnelTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->personnel_type_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error for="edit_personnel_type_id" class="mt-2" />
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEditPersonnelModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="updatePersonnel" wire:loading.attr="disabled">
                Save Changes
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Delete Confirmation Modal -->
    <x-dialog-modal wire:model="showDeleteConfirmationModal">
        <x-slot name="title">
            Confirm Delete Personnel
        </x-slot>

        <x-slot name="content">
            <p class="text-gray-700">Are you sure you want to delete this personnel record? This action cannot be undone.</p>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteConfirmationModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="deletePersonnel" wire:loading.attr="disabled">
                Delete Personnel
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
