<?php

namespace App\Livewire;

use App\Models\StorageDutyLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\StockCount;
use App\Models\StockCountItems;
use App\Models\Stock; // Import Stock model
use App\Models\StockMovement; // Import StockMovement model
use Livewire\Component;

class StorageModule extends Component
{
    public $shiftStarted = false;
    public $showStartShiftModal = false;
    public $showEndShiftModal = false;
    public $showShiftReportModal = false; // New property for report modal
    public $products = [];
    public $stockCountQuantities = [];
    public $endShiftStockCountQuantities = [];
    public $currentStorageDutyLogId;
    public $shiftReport = []; // New property for shift report data
    public $selectedStorageDutyLog; // To hold the duty log for the report

    public function mount()
    {
        $this->checkShiftStatus();
        $this->products = Product::all();
        foreach ($this->products as $product) {
            $this->stockCountQuantities[$product->id] = null;
            $this->endShiftStockCountQuantities[$product->id] = null;
        }
    }

    public function checkShiftStatus()
    {
        $activeShift = StorageDutyLog::where('user_id', Auth::id())
            ->where('storage_duty_log_status_id', 1) // 1 for Ongoing
            ->first();

        $this->shiftStarted = (bool) $activeShift;
        if ($activeShift) {
            $this->currentStorageDutyLogId = $activeShift->id;
        } else {
            $this->currentStorageDutyLogId = null;
        }
    }

    public function openStartShiftModal()
    {
        $this->resetValidation();
        foreach ($this->products as $product) {
            $this->stockCountQuantities[$product->id] = null;
        }
        $this->showStartShiftModal = true;
    }

    public function startShift()
    {
        $this->validate([
            'stockCountQuantities.*' => 'nullable|integer|min:0',
        ]);

        $storageDutyLog = StorageDutyLog::create([
            'user_id' => Auth::id(),
            'time_in' => Carbon::now(),
            'created_by' => Auth::id(),
            'storage_duty_log_status_id' => 1, // Ongoing
        ]);

        $this->currentStorageDutyLogId = $storageDutyLog->id;

        $stockCount = StockCount::create([
            'storage_duty_log_id' => $this->currentStorageDutyLogId,
            'count_type_id' => 1, // Start Shift Count
        ]);

        foreach ($this->stockCountQuantities as $productId => $quantity) {
            if ($quantity > 0) {
                StockCountItems::create([
                    'stock_count_id' => $stockCount->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }
        }

        // Add starting stock counts to the 'stocks' table
        foreach ($this->stockCountQuantities as $productId => $quantity) {
            if ($quantity > 0) {
                Stock::create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'created_by' => Auth::id(),
                    'storage_duty_log_id' => $this->currentStorageDutyLogId,
                ]);
            }
        }

        $this->showStartShiftModal = false;
        $this->checkShiftStatus();
        $this->dispatch('stockMovementUpdated'); // Dispatch event for real-time update
    }

    public function openEndShiftModal()
    {
        $this->resetValidation();
        foreach ($this->products as $product) {
            $this->endShiftStockCountQuantities[$product->id] = null;
        }
        $this->showEndShiftModal = true;
    }

    public function endShift()
    {
        $this->validate([
            'endShiftStockCountQuantities.*' => 'nullable|integer|min:0',
        ]);

        if (is_null($this->currentStorageDutyLogId)) {
            $this->dispatch('notify', 'No active shift found to end.');
            $this->showEndShiftModal = false;
            return;
        }

        $storageDutyLog = StorageDutyLog::with(['user', 'startStockCount.stockCountItems.product', 'endStockCount.stockCountItems.product'])
            ->find($this->currentStorageDutyLogId);

        if (!$storageDutyLog) {
            $this->dispatch('notify', 'Active storage duty log not found.');
            $this->showEndShiftModal = false;
            $this->checkShiftStatus(); // Re-check status in case of data inconsistency
            return;
        }

        $storageDutyLog->update([
            'time_out' => Carbon::now(),
            'storage_duty_log_status_id' => 2, // Ended
        ]);

            $endStockCount = StockCount::create([
                'storage_duty_log_id' => $this->currentStorageDutyLogId,
                'count_type_id' => 2, // End Shift Count
            ]);

            foreach ($this->endShiftStockCountQuantities as $productId => $quantity) {
                if ($quantity > 0) {
                    StockCountItems::create([
                        'stock_count_id' => $endStockCount->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ]);
                }
            }

            // Generate Shift Report
            $this->selectedStorageDutyLog = $storageDutyLog;
            $this->shiftReport = [];

            foreach ($this->products as $product) {
                $startQuantity = $storageDutyLog->startStockCount->stockCountItems->where('product_id', $product->id)->first()->quantity ?? 0;
                $endQuantityReported = $endStockCount->stockCountItems->where('product_id', $product->id)->first()->quantity ?? 0;

                // Calculate restocks within the shift
                $restocks = Stock::where('product_id', $product->id)
                    ->whereBetween('created_at', [$storageDutyLog->time_in, $storageDutyLog->time_out])
                    ->sum('quantity');

                // Calculate initial dispensed (original ordered quantity) within the shift
                $initialDispensed = StockMovement::where('product_id', $product->id)
                    ->whereBetween('created_at', [$storageDutyLog->time_in, $storageDutyLog->time_out])
                    ->sum('quantity');

                // Calculate actual received by client within the shift
                $actualReceived = StockMovement::where('product_id', $product->id)
                    ->whereBetween('created_at', [$storageDutyLog->time_in, $storageDutyLog->time_out])
                    ->sum('actual_quantity_dispensed');

                // Calculate returns within the shift
                $returns = StockMovement::where('product_id', $product->id)
                    ->whereBetween('created_at', [$storageDutyLog->time_in, $storageDutyLog->time_out])
                    ->sum('actual_quantity_returned');

                $systemComputedRemainingStock = $restocks - $initialDispensed + $returns;
                $loss = $initialDispensed - ($actualReceived + $returns);
                $discrepancy = $endQuantityReported - $systemComputedRemainingStock; // Discrepancy is Closing Count - Running Stock

                $this->shiftReport[] = [
                    'product_name' => $product->product_name,
                    'start_stock_count' => $startQuantity, // Keep for reporting, but not in calculation
                    'restocks' => $restocks,
                    'initial_dispensed' => $initialDispensed,
                    'actual_received' => $actualReceived,
                    'returns' => $returns,
                    'system_computed_remaining_stock' => $systemComputedRemainingStock,
                    'loss' => $loss,
                    'end_stock_count_reported' => $endQuantityReported,
                    'discrepancy' => $discrepancy,
                ];
            }

            $this->showEndShiftModal = false;
            $this->showShiftReportModal = true; // Show the report modal
            $this->checkShiftStatus();
            $this->dispatch('stockMovementUpdated'); // Dispatch event for real-time update
        
    } // Closing brace for endShift() method

    public function render()
    {
        return view('livewire.storage-module');
    }
}
