<?php

namespace App\Livewire;

use App\Models\Payment;
use App\Models\PaymentMethod; // Import PaymentMethod
use App\Models\Transaction;
use App\Models\CashierDutyLog;
use Illuminate\Support\Facades\Auth; // Import Auth for created_by
use Illuminate\Support\Facades\Cache; // Import Cache for static data
use Illuminate\Support\Facades\DB; // Import DB for transactions
use Livewire\Component;
use App\Models\Client; // Add this import
use Illuminate\Validation\ValidationException; // Import for validation

class Remittances extends Component
{
    protected $listeners = ['transactionUpdated' => '$refresh'];

    // Existing properties for COD Remittance Modal
    public $showAddPaymentModal = false;
    public $selectedCodPaymentId;
    public $remittancePaymentMethodId;
    public $remittanceAmountReceived = 0;
    public $remittanceReferenceNumber;
    public $remittanceCheckNumber;
    public $remittanceAmountChange = 0;

    // New properties for client credit payments
    public $showAddClientPaymentModal = false; // New modal for client credit
    public $selectedClientId; // To select client for credit payment
    public $clientPaymentAmountReceived = 0;
    public $clientPaymentReferenceNumber;
    public $clientPaymentCheckNumber;
    public $allClients; // To store all clients for dropdown
    public $searchClient = ''; // For searchable client dropdown
    public $filteredClients; // Clients matching search and unpaid credit criteria
    public $clientPaymentMethodId; // For payment method in client credit modal
    public $allPaymentMethods; // To store all payment methods for the new dropdown
    public $selectedClientName = ''; // To display selected client's name

    public $showClientCreditReceiptModal = false;
    public $selectedClientCreditPayment = null;

    // New properties for COD Transaction Receipt Modal
    public $showCodTransactionReceiptModal = false;
    public $selectedCodTransaction = null;

    public function mount()
    {
        $this->allClients = Cache::remember('clients', 60*60, function () {
            return Client::all();
        });
        $this->allPaymentMethods = Cache::remember('paymentMethods', 60*60, function () {
            return PaymentMethod::all();
        });
        $this->filteredClients = collect(); // Initialize as empty collection
    }

    public function openAddPaymentModal($transactionId) // For COD Remittance
    {
        $this->resetValidation();
        $this->selectedCodPaymentId = $transactionId;
        $transaction = Transaction::with('payments')->findOrFail($transactionId);

        $this->remittanceAmountReceived = $transaction->remaining_balance;
        $this->remittanceAmountChange = 0;

        $this->remittancePaymentMethodId = null;
        $this->remittanceReferenceNumber = null;
        $this->remittanceCheckNumber = null;

        $this->showAddPaymentModal = true;
    }

    public function openAddClientPaymentModal() // For Client Credit Payment
    {
        $this->resetValidation();
        $this->reset(['searchClient', 'selectedClientId', 'selectedClientName', 'clientPaymentAmountReceived', 'clientPaymentReferenceNumber', 'clientPaymentCheckNumber', 'clientPaymentMethodId']);
        $this->filteredClients = collect(); // Ensure it's an empty collection
        $this->showAddClientPaymentModal = true;
    }

    public function viewClientCreditPaymentReceipt($paymentId)
    {
        $this->selectedClientCreditPayment = Payment::with(['client', 'paymentMethod', 'transaction.sales.product'])->findOrFail($paymentId);
        $this->showClientCreditReceiptModal = true;
    }

    public function updatedRemittanceAmountReceived($value) // For COD Remittance
    {
        $received = (float)$value;
        $transaction = Transaction::with('payments')->findOrFail($this->selectedCodPaymentId);
        $originalRemainingBalance = (float)$transaction->remaining_balance;

        if ($received >= $originalRemainingBalance) {
            $this->remittanceAmountChange = $received - $originalRemainingBalance;
        } else {
            $this->remittanceAmountChange = 0;
        }
    }

    public function updatedSearchClient($value)
    {
        if (strlen($value) > 1) {
            $this->filteredClients = Client::where('name', 'like', '%' . $value . '%')
                ->whereHas('transactions', function ($query) {
                    $query->whereHas('payments', function ($subQuery) {
                        $subQuery->where('payment_method_id', 3); // Credit transactions
                    })
                    ->whereRaw('(transactions.total_amount - (SELECT SUM(amount_received) FROM payments WHERE transaction_id = transactions.id)) > 0');
                })
                ->get();
        } else {
            $this->filteredClients = collect();
        }
        $this->selectedClientId = null;
        $this->selectedClientName = '';
    }

    private function getFilteredClients()
    {
        return Client::where('name', 'like', '%' . $this->searchClient . '%')
            ->whereHas('transactions', function ($query) {
                $query->whereHas('payments', function ($subQuery) {
                    $subQuery->where('payment_method_id', 3); // Credit transactions
                })
                ->where('total_amount', '>', function ($subQuery) {
                    $subQuery->selectRaw('SUM(amount_received)')
                             ->from('payments')
                             ->whereColumn('transaction_id', 'transactions.id');
                });
            })
            ->get();
    }

    public function selectClient($clientId)
    {
        $client = Client::find($clientId);
        $this->selectedClientId = $clientId;
        $this->searchClient = $client->name;
        $this->selectedClientName = $client->name;
        $this->filteredClients = collect(); // Clear filtered clients after selection
    }

    public function clearSelectedClient()
    {
        $this->selectedClientId = null;
        $this->searchClient = '';
        $this->selectedClientName = '';
        $this->filteredClients = collect();
    }

    public function addCodRemittancePayment() // Renamed from addRemittancePayment
    {
        try {
            $this->validate([
                'remittancePaymentMethodId' => 'required',
                'remittanceAmountReceived' => 'required|numeric|min:0',
                'remittanceReferenceNumber' => 'nullable|string|max:255',
                'remittanceCheckNumber' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('notify', 'Please fill out all required fields for the remittance payment.');
            throw $e;
        }

        if ($this->remittancePaymentMethodId == 1) { // Assuming 1 is Cash
            $transaction = Transaction::with('payments')->findOrFail($this->selectedCodPaymentId);
            if ($this->remittanceAmountReceived < $transaction->remaining_balance) {
                $this->addError('remittanceAmountReceived', 'Amount received cannot be less than the remaining balance for cash payments.');
                return;
            }
        }

        DB::transaction(function () {
            $transaction = Transaction::with('payments')->findOrFail($this->selectedCodPaymentId);

            $newPaymentAmountReceived = $this->remittanceAmountReceived;
            $newPaymentAmountChange = $this->remittanceAmountChange;

            if ($this->remittancePaymentMethodId != 1) { // Not Cash
                $newPaymentAmountChange = 0;
            }

            Payment::create([
                'cashier_duty_log_id' => $transaction->cashier_duty_log_id,
                'client_id' => $transaction->client_id,
                'transaction_id' => $transaction->id,
                'payment_method_id' => $this->remittancePaymentMethodId,
                'amount_received' => $newPaymentAmountReceived,
                'amount_change' => $newPaymentAmountChange,
                'reference_number' => $this->remittanceReferenceNumber,
                'check_number' => $this->remittanceCheckNumber,
                'created_by' => Auth::id(),
            ]);
        });

        $this->showAddPaymentModal = false;
        $this->dispatch('notify', 'Remittance payment added successfully!');
        $this->dispatch('$refresh');
    }

    public function addClientCreditPayment() // New method for client credit payments
    {
        try {
            $this->validate([
                'selectedClientId' => 'required|exists:clients,id',
                'clientPaymentAmountReceived' => 'required|numeric|min:0',
                'clientPaymentMethodId' => 'required', // New validation
                'clientPaymentReferenceNumber' => 'nullable|string|max:255',
                'clientPaymentCheckNumber' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('notify', 'Please fill out all required fields for the client credit payment.');
            throw $e;
        }

        DB::transaction(function () {
            $newPaymentAmountChange = 0;
            if ($this->clientPaymentMethodId != 1) { // Not Cash
                $newPaymentAmountChange = 0;
            }

            $activeDutyLog = CashierDutyLog::where('user_id', Auth::id())
                ->where('cashier_duty_log_status_id', 1)
                ->first();

            if (!$activeDutyLog) {
                throw ValidationException::withMessages(['cashier_duty_log_id' => 'You must start a duty shift before adding a client credit payment.']);
            }

            Payment::create([
                'cashier_duty_log_id' => $activeDutyLog->id,
                'client_id' => $this->selectedClientId,
                'transaction_id' => null, // This is now nullable
                'payment_method_id' => $this->clientPaymentMethodId, // Use selected payment method
                'amount_received' => $this->clientPaymentAmountReceived,
                'amount_change' => $newPaymentAmountChange,
                'reference_number' => $this->clientPaymentReferenceNumber,
                'check_number' => $this->clientPaymentCheckNumber,
                'created_by' => Auth::id(),
            ]);
        });

        $this->showAddClientPaymentModal = false;
        $this->dispatch('notify', 'Client credit payment added successfully!');
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $codTransactions = Transaction::with(['client', 'payments', 'sales.stockMovement']) // Eager load sales.stockMovement (singular)
            ->whereHas('payments', function ($query) {
                $query->where('payment_method_id', 7); // Assuming 7 is COD
            })
            ->whereHas('sales.stockMovement', function ($query) { // Add condition for delivery status (singular)
                $query->whereIn('dispense_status_type_id', [1, 3]); // 1 for Returned, 3 for Completed
            })
            ->get()
            ->filter(function ($transaction) {
                return $transaction->remaining_balance > 0;
            });

        $clientCreditPayments = Payment::with(['client', 'paymentMethod']) // Eager load paymentMethod
            ->whereNull('transaction_id')
            ->latest()
            ->get();

        // Re-filter clients on render if searchClient is set and no client is selected
        if (strlen($this->searchClient) > 1 && is_null($this->selectedClientId)) {
            $this->filteredClients = $this->getFilteredClients();
        }

        return view('livewire.remittances', [
            'codTransactions' => $codTransactions,
            'clientCreditPayments' => $clientCreditPayments,
            'paymentMethods' => $this->allPaymentMethods, // Pass allPaymentMethods
            'clients' => $this->allClients, // Pass allClients
            'filteredClients' => $this->filteredClients, // Pass filteredClients
        ]);
    }
}
