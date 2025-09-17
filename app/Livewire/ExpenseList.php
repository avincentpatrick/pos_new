<?php

namespace App\Livewire;

use App\Models\Expense;
use App\Models\ExpenseType;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Import Hash facade
use Illuminate\Validation\ValidationException;

class ExpenseList extends Component
{
    use WithPagination;

    public $search = '';
    public $expenseTypeFilter = '';
    public $showAddExpenseModal = false;
    public $showEditExpenseModal = false;
    public $showDeleteConfirmationModal = false;

    // Properties for adding new expense
    public $expense_type_id;
    public $expense_type_specify;
    public $amount;

    // Properties for editing expense
    public $selectedExpenseId;
    public $edit_expense_type_id;
    public $edit_expense_type_specify;
    public $edit_amount;

    // Properties for delete confirmation
    public $expenseToDeleteId;
    public $adminPassword;

    protected $rules = [
        'expense_type_id' => 'required|exists:expense_types,id',
        'expense_type_specify' => 'nullable|string|max:255',
        'amount' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'expense_type_id.required' => 'The expense type is required.',
        'expense_type_id.exists' => 'The selected expense type is invalid.',
        'amount.required' => 'The amount is required.',
        'amount.numeric' => 'The amount must be a number.',
        'amount.min' => 'The amount must be at least 0.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedExpenseTypeFilter()
    {
        $this->resetPage();
    }

    public function openAddExpenseModal()
    {
        $this->resetValidation();
        $this->reset(['expense_type_id', 'expense_type_specify', 'amount']);
        $this->showAddExpenseModal = true;
    }

    public function addExpense()
    {
        $this->validate();

        Expense::create([
            'expense_type_id' => $this->expense_type_id,
            'expense_type_specify' => $this->expense_type_id == 8 ? $this->expense_type_specify : null,
            'amount' => $this->amount,
        ]);

        session()->flash('message', 'Expense added successfully.');
        $this->showAddExpenseModal = false;
        $this->resetPage();
    }

    public function openEditExpenseModal($id)
    {
        $this->resetValidation();
        $expense = Expense::findOrFail($id);
        $this->selectedExpenseId = $expense->id;
        $this->edit_expense_type_id = $expense->expense_type_id;
        $this->edit_expense_type_specify = $expense->expense_type_specify;
        $this->edit_amount = $expense->amount;
        $this->showEditExpenseModal = true;
    }

    public function updateExpense()
    {
        $this->validate([
            'edit_expense_type_id' => 'required|exists:expense_types,id',
            'edit_expense_type_specify' => 'nullable|string|max:255',
            'edit_amount' => 'required|numeric|min:0',
        ]);

        $expense = Expense::findOrFail($this->selectedExpenseId);
        $expense->update([
            'expense_type_id' => $this->edit_expense_type_id,
            'expense_type_specify' => $this->edit_expense_type_id == 8 ? $this->edit_expense_type_specify : null,
            'amount' => $this->edit_amount,
        ]);

        session()->flash('message', 'Expense updated successfully.');
        $this->showEditExpenseModal = false;
        $this->resetPage();
    }

    public function confirmDeleteExpense($id)
    {
        $this->resetValidation();
        $this->expenseToDeleteId = $id;
        $this->adminPassword = '';
        $this->showDeleteConfirmationModal = true;
    }

    public function deleteExpense()
    {
        $this->validate([
            'adminPassword' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($this->adminPassword, $user->password)) {
            $this->addError('adminPassword', 'Incorrect password.');
            return;
        }

        Expense::destroy($this->expenseToDeleteId);

        session()->flash('message', 'Expense deleted successfully.');
        $this->showDeleteConfirmationModal = false;
        $this->resetPage();
    }

    public function render()
    {
        $expenseTypes = ExpenseType::all();

        $expenses = Expense::with('expenseType')
            ->when($this->search, function ($query) {
                $query->where('expense_type_specify', 'like', '%' . $this->search . '%')
                      ->orWhereHas('expenseType', function ($subQuery) {
                          $subQuery->where('expense_type_name', 'like', '%' . $this->search . '%');
                      });
            })
            ->when($this->expenseTypeFilter, function ($query) {
                $query->where('expense_type_id', $this->expenseTypeFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.expense-list', [
            'expenses' => $expenses,
            'expenseTypes' => $expenseTypes,
        ]);
    }
}
