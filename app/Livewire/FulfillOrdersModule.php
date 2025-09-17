<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\OrderType;
use App\Models\DispenseStatusType;
use App\Models\ReturnReason;
use App\Models\StorageDutyLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class FulfillOrdersModule extends Component
{
    use WithPagination;

    public $showDetailsModal = false;
    public $selectedSale;
    public $productFilter = '';
    public $orderTypeFilter = '';

    // Properties for the update modal
    public $returnReasons = [];
    public $selectedStatusId;
    public $returnReasonId;
    public $specifyReason = '';
    public $actualQuantityReturned;
    public $return_remarks = '';
    public $actualQuantityDispensed;

    // Filters for Fulfilled Orders
    public $fulfilledStatusFilter = '';
    public $fulfilledProductFilter = '';
    public $fulfilledOrderTypeFilter = '';
    public $fulfilledStartDate = '';
    public $fulfilledEndDate = '';

    public $selectedOrders = [];
    public $showConfirmModal = false;
    public $ordersToConfirm = [];

    public function mount()
    {
        // The loadOrders method is no longer needed as queries are in render()
    }

    public function dispenseOrder($saleId)
    {
        $sale = Sale::with('product', 'transaction', 'stockMovement')->find($saleId);

        if (!$sale) {
            session()->flash('error', 'Sale not found.');
            return;
        }

        if ($sale->stockMovement) {
            session()->flash('error', 'This order has already been dispensed.');
            return;
        }

        $activeStorageDutyLog = StorageDutyLog::where('user_id', Auth::id())
            ->where('storage_duty_log_status_id', 1)
            ->first();

        if (!$activeStorageDutyLog) {
            session()->flash('error', 'No active storage shift found. Please start a shift first.');
            return;
        }

        DB::transaction(function () use ($sale, $activeStorageDutyLog) {
            $dispenseStatusTypeId = null;
            $orderTypeId = $sale->transaction->order_type_id;

            if ($orderTypeId == 1) { // Pick-up
                $dispenseStatusTypeId = 3; // Completed
            } elseif ($orderTypeId == 2) { // Delivery
                $dispenseStatusTypeId = 2; // Ongoing Delivery
            }

            // Create a stock movement record for dispensing
            StockMovement::create([
                'dispense_status_type_id' => $dispenseStatusTypeId,
                'sales_id' => $sale->id,
                'product_id' => $sale->product_id,
                'quantity' => $sale->quantity,
                'actual_quantity_dispensed' => $orderTypeId == 1 ? $sale->quantity : null,
                'storage_duty_log_id' => $activeStorageDutyLog->id,
            ]);


            session()->flash('message', 'Order dispensed successfully!');
        });
        $this->dispatch('stockMovementUpdated'); // Dispatch event for real-time update
    }

    public function viewOrder($saleId)
    {
        $this->selectedSale = Sale::with('product', 'transaction.orderType', 'transaction.client', 'stockMovement.dispenseStatusType')->find($saleId);
        $this->returnReasons = ReturnReason::all();
        $this->selectedStatusId = $this->selectedSale->stockMovement->dispense_status_type_id;
        $this->returnReasonId = '';
        $this->specifyReason = '';
        $this->actualQuantityReturned = '';
        $this->return_remarks = '';
        $this->actualQuantityDispensed = $this->selectedSale->quantity;
        $this->showDetailsModal = true;
    }

    public function updateFulfilledOrder()
    {
        $this->validate([
            'selectedStatusId' => 'required|integer',
            'actualQuantityReturned' => 'required_if:selectedStatusId,1|integer|min:0|max:' . $this->selectedSale->quantity,
            'returnReasonId' => 'required_if:selectedStatusId,1',
            'specifyReason' => 'required_if:returnReasonId,5',
            'actualQuantityDispensed' => 'required_if:selectedStatusId,1|integer|min:0|max:' . $this->selectedSale->quantity,
        ]);

        DB::transaction(function () {
            $stockMovement = $this->selectedSale->stockMovement;

            if ($this->selectedStatusId == 1) { // Returned
                // Update existing movement to 'Returned'
                $stockMovement->update([
                    'dispense_status_type_id' => 1, // Returned
                    'return_reason_id' => $this->returnReasonId,
                    'return_reason_specify' => $this->returnReasonId == 5 ? $this->specifyReason : null,
                    'return_remarks' => $this->return_remarks,
                    'actual_quantity_dispensed' => $this->actualQuantityDispensed,
                    'actual_quantity_returned' => $this->actualQuantityReturned,
                ]);
                $this->dispatch('transactionUpdated'); // Dispatch event to notify Remittances page

                // Update DeliveryBatch status
                $delivery = $this->selectedSale->transaction->deliveries->first();
                if ($delivery && $delivery->deliveryBatch) {
                    $delivery->deliveryBatch->update(['delivery_batch_status_id' => 2]); // 2 for Completed
                    $this->dispatch('deliveryBatchUpdated'); // Notify FinalizeDeliveryBatch
                }

            } else {
                // Update the status, and set actual_quantity_dispensed to the original quantity
                $stockMovement->update([
                    'dispense_status_type_id' => 3, // Completed
                    'actual_quantity_dispensed' => $this->selectedSale->quantity,
                    'actual_quantity_returned' => 0,
                    'return_reason_id' => null,
                    'return_reason_specify' => null,
                    'return_remarks' => null,
                ]);

                // Update DeliveryBatch status
                $delivery = $this->selectedSale->transaction->deliveries->first();
                if ($delivery && $delivery->deliveryBatch) {
                    $delivery->deliveryBatch->update(['delivery_batch_status_id' => 2]); // 2 for Completed
                    $this->dispatch('deliveryBatchUpdated'); // Notify FinalizeDeliveryBatch
                }
            }
        });

        $this->showDetailsModal = false;
        session()->flash('message', 'Order has been updated successfully.');
        $this->dispatch('stockMovementUpdated'); // Dispatch event for real-time update
    }

    public function openConfirmModal()
    {
        $this->ordersToConfirm = Sale::with('product', 'transaction.client')
            ->whereIn('id', $this->selectedOrders)
            ->get();
        $this->showConfirmModal = true;
    }

    public function confirmTagSelectedAsCompleted()
    {
        DB::transaction(function () {
            $salesToComplete = Sale::whereIn('id', $this->selectedOrders)->get();

            foreach ($salesToComplete as $sale) {
                $stockMovement = $sale->stockMovement;
                if ($stockMovement) {
                    $stockMovement->update([
                        'dispense_status_type_id' => 3, // Completed
                        'actual_quantity_dispensed' => $sale->quantity,
                        'actual_quantity_returned' => 0, // Set to 0 for completed orders
                        'return_reason_id' => null,
                        'return_reason_specify' => null,
                        'return_remarks' => null,
                    ]);

                    // Update DeliveryBatch status
                    $delivery = $sale->transaction->deliveries->first();
                    if ($delivery && $delivery->deliveryBatch) {
                        $delivery->deliveryBatch->update(['delivery_batch_status_id' => 2]); // 2 for Completed
                        $this->dispatch('deliveryBatchUpdated'); // Notify FinalizeDeliveryBatch
                    }
                }
            }
        });

        $this->selectedOrders = [];
        $this->showConfirmModal = false;
        session()->flash('message', 'Selected orders have been tagged as completed.');
        $this->dispatch('stockMovementUpdated'); // Dispatch event for real-time update
    }

    public function render()
    {
        // Query for Pending Transactions
        $pendingTransactionsQuery = Sale::with(['product', 'transaction.orderType', 'transaction.client'])
            ->where(function ($query) {
                $query->whereDoesntHave('stockMovement');
            })
            ->when($this->productFilter, function ($query) {
                $query->where('product_id', $this->productFilter);
            })
            ->when($this->orderTypeFilter, function ($query) {
                $query->whereHas('transaction', function ($subQuery) {
                    $subQuery->where('order_type_id', $this->orderTypeFilter);
                });
            })
            ->join('transactions as t1', 'sales.transaction_id', '=', 't1.id')
            ->orderBy('t1.created_at', 'asc')
            ->select('sales.*');

        $pendingTransactions = $pendingTransactionsQuery->paginate(10, ['*'], 'pendingPage');

        // Query for Fulfilled Orders
        $fulfilledOrdersQuery = Sale::with(['product', 'transaction.orderType', 'transaction.client', 'stockMovement.dispenseStatusType'])
            ->whereHas('stockMovement')
            ->when($this->fulfilledStatusFilter, function ($query) {
                $query->whereHas('stockMovement', function ($subQuery) {
                    $subQuery->where('dispense_status_type_id', $this->fulfilledStatusFilter);
                });
            })
            ->when($this->fulfilledProductFilter, function ($query) {
                $query->where('product_id', $this->fulfilledProductFilter);
            })
            ->when($this->fulfilledOrderTypeFilter, function ($query) {
                $query->whereHas('transaction', function ($subQuery) {
                    $subQuery->where('order_type_id', $this->fulfilledOrderTypeFilter);
                });
            })
            ->when($this->fulfilledStartDate, function ($query) {
                $query->whereHas('stockMovement', function ($subQuery) {
                    $subQuery->where('created_at', '>=', $this->fulfilledStartDate);
                });
            })
            ->when($this->fulfilledEndDate, function ($query) {
                $query->whereHas('stockMovement', function ($subQuery) {
                    $subQuery->where('created_at', '<=', $this->fulfilledEndDate);
                });
            })
            ->join('stock_movements', 'sales.id', '=', 'stock_movements.sales_id')
            ->orderBy('stock_movements.created_at', 'desc')
            ->select('sales.*');

        $fulfilledOrders = $fulfilledOrdersQuery->paginate(10, ['*'], 'fulfilledPage');

        // Data for filters
        $products = Product::orderBy('product_name')->get();
        $orderTypes = OrderType::all();
        $dispenseStatusTypes = DispenseStatusType::whereIn('id', [2, 3])->get();

        return view('livewire.fulfill-orders-module', [
            'pendingTransactions' => $pendingTransactions,
            'fulfilledOrders' => $fulfilledOrders,
            'products' => $products,
            'orderTypes' => $orderTypes,
            'dispenseStatusTypes' => $dispenseStatusTypes,
        ]);
    }
}
