<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
            <div class="mb-4">
                <a href="{{ route('admin-module') }}" class="inline-flex items-center px-4 py-2 bg-custom-dark-blue hover:bg-blue-900 rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Admin Modules
                </a>
            </div>
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Expenses</h2>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input wire:model.live.debounce.300ms="search" type="text" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange" placeholder="Search by expense type or specify...">
                </div>
                <div>
                    <select wire:model.live="expenseTypeFilter" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring-custom-orange">
                        <option value="">All Expense Types</option>
                        @foreach($expenseTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->expense_type_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expense Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specify</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <button wire:click="openEditExpenseModal({{ $expense->id }})" class="text-custom-orange hover:text-orange-700"><i class="fa-solid fa-edit"></i></button>
                                    <button wire:click="confirmDeleteExpense({{ $expense->id }})" class="text-red-600 hover:text-red-900 ml-4"><i class="fa-solid fa-trash"></i></button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">{{ $expense->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $expense->expenseType->expense_type_name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $expense->expense_type_specify ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">₱{{ number_format($expense->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">{{ $expense->created_at->format('M d, Y h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center justify-center h-48">
                                        <p class="text-lg text-gray-500 mb-4">No expenses found.</p>
                                        @if ($search || $expenseTypeFilter)
                                            <p class="text-sm text-gray-500 mb-4">Adjust your search or filters to see more results.</p>
                                        @endif
                                        <button wire:click="openAddExpenseModal" class="inline-flex items-center px-4 py-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest">
                                            <i class="fa-solid fa-plus mr-2"></i> Add Expense
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>

    <!-- Add Expense Modal -->
    <x-dialog-modal wire:model="showAddExpenseModal">
        <x-slot name="title">
            Add New Expense
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-label for="expense_type_id">
                    {!! __('Expense Type') !!} <span class='text-red-500'>*</span>
                </x-label>
                <select id="expense_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring focus:ring-custom-orange focus:ring-opacity-50" wire:model.live="expense_type_id">
                    <option value="">Select Expense Type</option>
                    @foreach($expenseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->expense_type_name }}</option>
                    @endforeach
                </select>
                <x-input-error for="expense_type_id" class="mt-2" />
            </div>
            @if ($expense_type_id == 8)
                <div class="mt-4">
                    <x-label for="expense_type_specify" value="{{ __('Specify (if applicable)') }}" />
                    <x-input id="expense_type_specify" type="text" class="mt-1 block w-full" wire:model.defer="expense_type_specify" />
                    <x-input-error for="expense_type_specify" class="mt-2" />
                </div>
            @endif
            <div class="mt-4">
                <x-label for="amount">
                    {!! __('Amount') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="amount" type="number" class="mt-1 block w-full" wire:model.defer="amount" min="0" step="0.01" />
                <x-input-error for="amount" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showAddExpenseModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="addExpense" wire:loading.attr="disabled">
                Save Expense
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Edit Expense Modal -->
    <x-dialog-modal wire:model="showEditExpenseModal">
        <x-slot name="title">
            Edit Expense
        </x-slot>

        <x-slot name="content">
            <div class="mt-4">
                <x-label for="edit_expense_type_id">
                    {!! __('Expense Type') !!} <span class='text-red-500'>*</span>
                </x-label>
                <select id="edit_expense_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-custom-orange focus:ring focus:ring-custom-orange focus:ring-opacity-50" wire:model.defer="edit_expense_type_id">
                    <option value="">Select Expense Type</option>
                    @foreach($expenseTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->expense_type_name }}</option>
                    @endforeach
                </select>
                <x-input-error for="edit_expense_type_id" class="mt-2" />
            </div>
            @if ($edit_expense_type_id == 8)
                <div class="mt-4">
                    <x-label for="edit_expense_type_specify" value="{{ __('Specify (if applicable)') }}" />
                    <x-input id="edit_expense_type_specify" type="text" class="mt-1 block w-full" wire:model.defer="edit_expense_type_specify" />
                    <x-input-error for="edit_expense_type_specify" class="mt-2" />
                </div>
            @endif
            <div class="mt-4">
                <x-label for="edit_amount">
                    {!! __('Amount') !!} <span class='text-red-500'>*</span>
                </x-label>
                <x-input id="edit_amount" type="number" class="mt-1 block w-full" wire:model.defer="edit_amount" min="0" step="0.01" />
                <x-input-error for="edit_amount" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showEditExpenseModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <button class="inline-flex items-center px-4 py-2 ml-2 bg-custom-orange hover:bg-orange-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest" wire:click="updateExpense" wire:loading.attr="disabled">
                Save Changes
            </button>
        </x-slot>
    </x-dialog-modal>

    <!-- Delete Confirmation Modal -->
    <x-dialog-modal wire:model="showDeleteConfirmationModal">
        <x-slot name="title">
            Confirm Delete Expense
        </x-slot>

        <x-slot name="content">
            <p class="text-gray-700">Are you sure you want to delete this expense? This action cannot be undone.</p>
            <div class="mt-4">
                <x-label for="adminPassword" value="{{ __('Admin Password') }}" />
                <x-input id="adminPassword" type="password" class="mt-1 block w-full" wire:model.defer="adminPassword" />
                <x-input-error for="adminPassword" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$set('showDeleteConfirmationModal', false)" wire:loading.attr="disabled">
                Cancel
            </x-secondary-button>

            <x-danger-button class="ms-2" wire:click="deleteExpense" wire:loading.attr="disabled">
                Delete Expense
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
