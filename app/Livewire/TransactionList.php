<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Models\PaymentMethod;
use App\Models\OrderType; // Import OrderType model
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class TransactionList extends Component
{
    use WithPagination;

    public $showReceiptModal = false;
    public $selectedTransaction = null;
    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $paymentMethodFilter = '';
    public $orderTypeFilter = ''; // New property for order type filter
    public $statusFilter = ''; // 'all', 'unpaid', or 'paid'
    public $paymentMethods;
    public $orderTypes; // New property for order types

    public $showDeleteModal = false;
    public $transactionIdToDelete = null;
    public $adminPassword = '';

    public function confirmDelete($transactionId)
    {
        $this->transactionIdToDelete = $transactionId;
        $this->showDeleteModal = true;
        $this->adminPassword = '';
    }

    public function deleteTransaction()
    {
        if (!\Hash::check($this->adminPassword, Auth::user()->password)) {
            $this->dispatch('notify', 'Invalid admin password.');
            return;
        }

        DB::transaction(function () {
            $transaction = Transaction::findOrFail($this->transactionIdToDelete);
            $transaction->sales()->delete();
            $transaction->payments()->delete();
            $transaction->delete();
        });

        $this->showDeleteModal = false;
        $this->dispatch('notify', 'Transaction deleted successfully!');
        $this->resetPage();
    }

    public function mount()
    {
        $this->paymentMethods = PaymentMethod::all();
        $this->orderTypes = OrderType::all(); // Fetch all order types
    }

    public function viewTransaction($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['client', 'sales.product', 'sales.stockMovement', 'payments.paymentMethod', 'orderType'])->find($transactionId);
        $this->showReceiptModal = true;
    }

    public function render()
    {
        $user = Auth::user();
        $query = Transaction::with(['client', 'payments.paymentMethod', 'orderType', 'sales.stockMovement']); // Eager load orderType

        if ($user->user_level_id == 2) { // Cashier
            $activeDutyLog = \App\Models\CashierDutyLog::where('user_id', $user->id)
                ->where('cashier_duty_log_status_id', 1) // Assuming 1 is active
                ->first();

            if ($activeDutyLog) {
                $query->where('cashier_duty_log_id', $activeDutyLog->id);
            } else {
                // If no active session, show no transactions
                $query->whereRaw('1 = 0');
            }
        } else { // Admin
            if ($this->startDate) {
                $query->whereDate('created_at', '>=', $this->startDate);
            }

            if ($this->endDate) {
                $query->whereDate('created_at', '<=', $this->endDate);
            }
        }

        $query->where(function ($query) {
            $query->whereHas('client', function ($subQuery) {
                $subQuery->where('name', 'like', '%' . $this->search . '%');
            })
            ->orWhere('id', 'like', '%' . $this->search . '%');
        });

        if ($this->paymentMethodFilter) {
            $query->whereHas('payments', function ($subQuery) {
                $subQuery->where('payment_method_id', $this->paymentMethodFilter);
            });
        }

        if ($this->orderTypeFilter) { // New filter for order type
            $query->where('order_type_id', $this->orderTypeFilter);
        }

        if ($this->statusFilter === 'unpaid') {
            $query->whereHas('payments', function ($subQuery) {
                $subQuery->where('payment_method_id', 7); // Only COD
            })
            ->whereRaw('(transactions.total_amount - (SELECT SUM(amount_received) FROM payments WHERE transaction_id = transactions.id)) > 0');
        } elseif ($this->statusFilter === 'paid') {
            $query->whereDoesntHave('payments', function ($subQuery) {
                $subQuery->where('payment_method_id', 3); // Exclude Client Credit
            })
            ->whereRaw('(transactions.total_amount - (SELECT SUM(amount_received) FROM payments WHERE transaction_id = transactions.id)) <= 0');
        } elseif ($this->statusFilter === 'not_applicable') {
            $query->whereHas('payments', function ($subQuery) {
                $subQuery->where('payment_method_id', 3); // Only Client Credit
            });
        }

        $transactions = $query->latest()->paginate(10);

        return view('livewire.transaction-list', [
            'transactions' => $transactions,
            'userLevel' => Auth::user()->user_level_id,
            'orderTypes' => $this->orderTypes, // Pass order types to the view
        ]);
    }
}
