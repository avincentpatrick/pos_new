<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Delivery;
use App\Models\OrderType;
use App\Models\Personnel;
use App\Models\Route;
use App\Models\DeliveryBatch;

class FinalizeDeliveryBatch extends Component
{
    public $transactions;
    public $drivers;
    public $helpers;
    public $routes;

    public $showAddBatchModal = false;
    public $selectedDriver;
    public $selectedHelper;
    public $selectedRoute;
    public $currentBatch; // To hold the currently selected batch for interaction
    public $pendingTransactionsByBatch = []; // Associative array to hold transactions pending save, keyed by batch ID
    public $allDeliveryBatches; // To hold all active delivery batches
    public $finalizedDeliveryBatches; // To hold all finalized delivery batches
    public $unavailablePersonnelIds = []; // New property to track unavailable personnel

    public $showViewTicketModal = false;
    public $selectedFinalizedBatch;
    public $selectedBatchesForPrint = []; // Array to hold IDs of batches selected for print/download
    public $selectAllFinalizedBatches = false; // Property to handle select all checkbox

    protected $rules = [
        'selectedDriver' => 'required|exists:personnels,id',
        'selectedHelper' => 'nullable|exists:personnels,id',
        'selectedRoute' => 'required|exists:routes,id',
    ];

    protected $listeners = ['transactionDropped']; // Listener for drag and drop

    public function mount()
    {
        $this->loadDrivers();
        $this->loadHelpers();
        $this->loadRoutes();
        $this->loadActiveDeliveryBatches(); // Load all active batches
        $this->loadFinalizedDeliveryBatches(); // Load all finalized batches
        $this->loadUnavailablePersonnel(); // Load unavailable personnel on mount

        // Initialize pendingTransactionsByBatch for existing batches
        foreach ($this->allDeliveryBatches as $batch) {
            $this->pendingTransactionsByBatch[$batch->id] = [];
        }
    }

    public function loadUnavailablePersonnel()
    {
        // Get IDs of personnel (drivers and helpers) currently assigned to ongoing batches
        $unavailableDrivers = DeliveryBatch::where('delivery_batch_status_id', '!=', 2)
                                            ->pluck('driver_id')
                                            ->filter()
                                            ->toArray();

        $unavailableHelpers = DeliveryBatch::where('delivery_batch_status_id', '!=', 2)
                                            ->pluck('helper_id')
                                            ->filter()
                                            ->toArray();

        $this->unavailablePersonnelIds = array_unique(array_merge($unavailableDrivers, $unavailableHelpers));
    }

    public function loadTransactions()
    {
        $deliveryOrderTypeId = OrderType::where('order_type_name', 'Delivery')->first()->id;
        // Get all transaction IDs that are currently in any pending batch
        $pendingTransactionIds = collect($this->pendingTransactionsByBatch)
                                    ->flatten()
                                    ->map(fn($transaction) => $transaction->id) // Extract only the ID
                                    ->unique()
                                    ->toArray();

        $this->transactions = Transaction::where('order_type_id', $deliveryOrderTypeId)
            ->whereDoesntHave('deliveries') // Remove the "Returned" exception
            ->whereHas('sales.stockMovement') // Add condition for dispense record
            ->whereNotIn('id', $pendingTransactionIds) // Exclude transactions already assigned to a pending batch
            ->with('client', 'payments.paymentMethod', 'sales.product') // Eager load client, payments with method, and sales with product
            ->get();
    }

    public function loadDrivers()
    {
        $this->drivers = Personnel::where('personnel_type_id', 1)->get();
    }

    public function loadHelpers()
    {
        $this->helpers = Personnel::where('personnel_type_id', 2)->get();
    }

    public function loadRoutes()
    {
        $this->routes = Route::all();
    }

    public function openAddBatchModal()
    {
        $this->resetValidation();
        $this->reset(['selectedDriver', 'selectedHelper', 'selectedRoute']);
        $this->loadUnavailablePersonnel(); // Re-check unavailable personnel when opening modal
        $this->showAddBatchModal = true;
    }

    public function createDeliveryBatch()
    {
        $this->validate();

        $this->currentBatch = DeliveryBatch::create([
            'driver_id' => $this->selectedDriver,
            'helper_id' => $this->selectedHelper,
            'route_id' => $this->selectedRoute,
            'finalize_id' => 1, // Set to 1 when created (not finalized)
            'delivery_batch_status_id' => 1, // Set to ongoing by default
        ]);

        $this->showAddBatchModal = false;
        session()->flash('message', 'Delivery Batch created successfully!');
        $this->loadActiveDeliveryBatches(); // Refresh the list of active batches
        $this->loadFinalizedDeliveryBatches(); // Refresh the list of finalized batches
        $this->loadUnavailablePersonnel(); // Re-check unavailable personnel after creating a batch
        $this->currentBatch = DeliveryBatch::with(['driver', 'helper', 'route', 'deliveries.transaction'])->find($this->currentBatch->id);
    }

    public function transactionDropped($transactionId, $batchId)
    {
        // Ensure the pendingTransactionsByBatch array is initialized for this batch
        if (!isset($this->pendingTransactionsByBatch[$batchId])) {
            $this->pendingTransactionsByBatch[$batchId] = [];
        }

        // Find the transaction to add
        $transactionToAdd = Transaction::with('client', 'payments.paymentMethod', 'sales.product')->find($transactionId);

        if (!$transactionToAdd) {
            session()->flash('error', 'Transaction not found.');
            return;
        }

        // Check if the transaction is already in the pending list for this batch
        $alreadyInBatch = collect($this->pendingTransactionsByBatch[$batchId] ?? [])->contains('id', $transactionId);

        if (!$alreadyInBatch) {
            $this->pendingTransactionsByBatch[$batchId][] = $transactionToAdd;
            session()->flash('message', 'Transaction ' . $transactionId . ' added to pending list for Batch ' . $batchId . '!');
            $this->loadTransactions(); // Refresh the list of pending transactions
        } else {
            session()->flash('error', 'Transaction ' . $transactionId . ' is already in the pending list for Batch ' . $batchId . '.');
        }
    }

    public function finalizeBatchDelivery($batchId)
    {
        $batchToFinalize = DeliveryBatch::find($batchId);

        if (!$batchToFinalize) {
            session()->flash('error', 'Batch not found.');
            return;
        }

        // Get transactions pending for this specific batch
        $transactionsForThisBatch = $this->pendingTransactionsByBatch[$batchId] ?? [];

        if (empty($transactionsForThisBatch)) {
            session()->flash('error', 'No transactions to finalize in Batch ' . $batchId . '.');
            return;
        }

        foreach ($transactionsForThisBatch as $transaction) {
            Delivery::create([
                'transaction_id' => $transaction->id,
                'delivery_batch_id' => $batchToFinalize->id,
            ]);
        }

        $batchToFinalize->update([
            'finalize_id' => 2, 
            'delivery_batch_status_id' => 1
        ]);
        unset($this->pendingTransactionsByBatch[$batchId]); // Clear pending list for this batch
        $this->loadTransactions(); // Refresh the list of pending transactions
        $this->loadActiveDeliveryBatches(); // Refresh the list of active batches
        $this->loadFinalizedDeliveryBatches(); // Refresh the list of finalized batches
        $this->loadUnavailablePersonnel(); // Re-check unavailable personnel after finalizing a batch
        
        if ($this->currentBatch && $this->currentBatch->id === $batchId) {
            $this->currentBatch = null; // Clear current batch if it was the one finalized
        }
        session()->flash('message', 'All transactions finalized and added to Batch ' . $batchId . '!');
    }

    public function updatedSelectAllFinalizedBatches($value)
    {
        if ($value) {
            $this->selectedBatchesForPrint = $this->finalizedDeliveryBatches->pluck('id')->toArray();
        } else {
            $this->selectedBatchesForPrint = [];
        }
    }

    public function printSelectedBatches()
    {
        if (empty($this->selectedBatchesForPrint)) {
            session()->flash('error', 'No batches selected for printing.');
            return;
        }

        $batchesToPrint = DeliveryBatch::whereIn('id', $this->selectedBatchesForPrint)
            ->with(['driver', 'helper', 'route', 'deliveries.transaction.client', 'deliveries.transaction.sales.product', 'deliveries.transaction.payments.paymentMethod'])
            ->get();

        if ($batchesToPrint->isEmpty()) {
            session()->flash('error', 'Selected batches not found.');
            return;
        }

        // Render the Blade view to HTML
        $printHtml = view('livewire.delivery-batch-print', ['deliveryBatches' => $batchesToPrint])->render();

        // Return a JavaScript response to open a new window and print
        return $this->js(<<<JS
            const printWindow = window.open('', '_blank');
            if (printWindow) {
                printWindow.document.open();
                printWindow.document.write(`{$printHtml}`);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            } else {
                alert('Please allow pop-ups for printing.');
            }
        JS);
    }


    public function loadActiveDeliveryBatches()
    {
        // Load batches that are not yet finalized (finalize_id = 1) and are ongoing (delivery_batch_status_id = 1)
        $this->allDeliveryBatches = DeliveryBatch::where('finalize_id', 1)
            ->with(['driver', 'helper', 'route', 'deliveries.transaction'])
            ->get();

        // Set the first active batch as currentBatch if available
        if ($this->allDeliveryBatches->isNotEmpty()) {
            $this->currentBatch = $this->allDeliveryBatches->first();
        } else {
            $this->currentBatch = null;
        }
    }

    public function loadFinalizedDeliveryBatches()
    {
        // Load batches that are finalized (finalize_id = 2) and are ongoing (delivery_batch_status_id = 1)
        $this->finalizedDeliveryBatches = DeliveryBatch::where('finalize_id', 2)
            ->where('delivery_batch_status_id', 1)
            ->with(['driver', 'helper', 'route', 'deliveries.transaction.client', 'deliveries.transaction.sales.product', 'deliveries.transaction.payments.paymentMethod'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function markBatchAsCompleted($batchId)
    {
        $batch = DeliveryBatch::find($batchId);
        if ($batch) {
            $batch->update(['delivery_batch_status_id' => 2]); // 2 for Completed
            session()->flash('message', 'Delivery Batch #' . $batchId . ' marked as completed.');
            $this->loadFinalizedDeliveryBatches(); // Refresh the list
            $this->loadUnavailablePersonnel(); // Re-check unavailable personnel after marking batch as completed
            $this->showViewTicketModal = false; // Close the modal
        } else {
            session()->flash('error', 'Delivery Batch not found.');
        }
    }

    public function viewFinalizedBatch($batchId)
    {
        $this->selectedFinalizedBatch = DeliveryBatch::with(['driver', 'helper', 'route', 'deliveries.transaction.client', 'deliveries.transaction.sales.product', 'deliveries.transaction.payments.paymentMethod'])->find($batchId);
        $this->showViewTicketModal = true;
    }

    public function render()
    {
        $this->loadTransactions(); // Call loadTransactions in render to refresh on poll

        return view('livewire.finalize-delivery-batch', [
            'transactions' => $this->transactions,
            'drivers' => $this->drivers,
            'helpers' => $this->helpers,
            'routes' => $this->routes,
            'currentBatch' => $this->currentBatch,
            'pendingTransactionsByBatch' => $this->pendingTransactionsByBatch, // Pass to view
            'allDeliveryBatches' => $this->allDeliveryBatches, // Pass all active batches to view
            'finalizedDeliveryBatches' => $this->finalizedDeliveryBatches, // Pass all finalized batches to view
            'unavailablePersonnelIds' => $this->unavailablePersonnelIds, // Pass to view
        ]);
    }

    public function selectBatch($batchId)
    {
        $this->currentBatch = DeliveryBatch::with(['driver', 'helper', 'route', 'deliveries.transaction'])->find($batchId);
        $this->transactionsToAddToBatch = []; // Clear pending transactions when selecting a new batch
        session()->flash('message', 'Batch ' . $batchId . ' selected.');
    }

    public function removeTransactionFromBatch($batchId, $transactionId)
    {
        if (isset($this->pendingTransactionsByBatch[$batchId])) {
            $this->pendingTransactionsByBatch[$batchId] = collect($this->pendingTransactionsByBatch[$batchId])->reject(function ($transaction) use ($transactionId) {
                return $transaction->id == $transactionId;
            })->values()->all(); // Re-index the array

            session()->flash('message', 'Transaction ' . $transactionId . ' removed from Batch ' . $batchId . ' pending list.');
            $this->loadTransactions(); // Refresh the list of available transactions
        } else {
            session()->flash('error', 'Batch ' . $batchId . ' not found in pending list.');
        }
    }
}
