<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('admin-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Admin Module
                </a>
            </div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-6">User Management</h2>

            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <!-- Filters -->
            <div class="mb-4 flex items-end space-x-8">
                <div class="flex-1">
                    <x-label for="search" value="{{ __('Search') }}" />
                    <x-input id="search" type="text" class="mt-1 block w-full" wire:model.live.debounce.300ms="search" placeholder="Search by name or email..." />
                </div>

                <div class="flex-1">
                    <x-label for="userLevelFilter" value="{{ __('User Level') }}" />
                    <select wire:model.live="userLevelFilter" id="userLevelFilter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">All</option>
                        @foreach ($userLevels as $level)
                            <option value="{{ $level->id }}">{{ $level->user_level_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto mt-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Registered</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button wire:click="openActivateModal({{ $user->id }})" class="text-custom-orange hover:text-orange-700"><i class="fas fa-user-check"></i></button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if ($user->user_level_id)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-left">{{ $user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-left">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-left">{{ $user->created_at->format('F j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center justify-center h-48">
                                        <p class="text-lg text-gray-500 mb-4">No users found.</p>
                                        @if ($search || $userLevelFilter)
                                            <p class="text-sm text-gray-500 mb-4">Adjust your search or filter to see more results.</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Activate User Modal -->
    <x-dialog-modal wire:model="showActivateModal">
        <x-slot name="title">
            Activate User
        </x-slot>

        <x-slot name="content">
            @if ($selectedUser)
                <div class="space-y-4">
                    <p><strong>Name:</strong> {{ $selectedUser->name }}</p>
                    <p><strong>Email:</strong> {{ $selectedUser->email }}</p>

                    <hr class="my-4">

                    <div>
                        <x-label for="selectedUserLevel" value="{{ __('User Level') }}" />
                        <select wire:model="selectedUserLevel" id="selectedUserLevel" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                            <option value="">Select a user level</option>
                            @foreach ($userLevels as $level)
                                <option value="{{ $level->id }}">{{ $level->user_level_name }}</option>
                            @endforeach
                        </select>
                        <x-input-error for="selectedUserLevel" class="mt-2" />
                    </div>
                </div>
            @endif
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showActivateModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="updateUserLevel" wire:loading.attr="disabled">
                Update
            </button>
        </x-slot>
    </x-dialog-modal>
</div>
