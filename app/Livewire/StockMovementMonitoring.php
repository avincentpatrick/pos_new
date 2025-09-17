<?php

namespace App\Livewire;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\StorageDutyLog;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StockMovementMonitoring extends Component
{
    use WithPagination;

    public $selectedProductId = '';
    public $selectedStorageDutyLogId = '';
    public $selectedDate; // New property for the date picker
    public $selectedStorageDutyLog; // New property to store the selected duty log details
    public $startDate; // Keep startDate for historical 'Start Count' calculation
    public $endDate; // Keep endDate for historical 'Start Count' calculation
    public $currentActiveStorageDutyLogId; // To store the ID of the current active shift

    public $products;
    public $storageDutyLogs;
    public $productSummary = []; // New property for the summary table

    // Listeners to reset pagination when filters change
    public function updatedSelectedProductId()
    {
        $this->resetPage();
    }

    public function updatedSelectedStorageDutyLogId()
    {
        $this->resetPage();
    }

    public function updatedSelectedDate($value)
    {
        $this->resetPage();
        $this->selectedDate = $value;

        $dutyLog = StorageDutyLog::with('user')
            ->whereDate('time_in', $this->selectedDate)
            ->first();

        if ($dutyLog) {
            $this->selectedStorageDutyLogId = $dutyLog->id;
            $this->selectedStorageDutyLog = $dutyLog;
        } else {
            $this->selectedStorageDutyLogId = null;
            $this->selectedStorageDutyLog = null;
        }
    }

    public function mount()
    {
        $this->products = Product::all();
        $this->storageDutyLogs = StorageDutyLog::with('user')->orderBy('time_in', 'desc')->get();
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Set default selectedStorageDutyLogId and selectedDate to the current active shift if one exists
        $activeShift = StorageDutyLog::with('user')
            ->where('storage_duty_log_status_id', 1) // Assuming 1 is "Ongoing"
            ->first();

        if ($activeShift) {
            $this->currentActiveStorageDutyLogId = $activeShift->id;
            $this->selectedStorageDutyLogId = $activeShift->id;
            $this->selectedDate = Carbon::parse($activeShift->time_in)->format('Y-m-d');
            $this->selectedStorageDutyLog = $activeShift;
        } else {
            $this->currentActiveStorageDutyLogId = null;
            $this->selectedStorageDutyLogId = null;
            $this->selectedDate = Carbon::now()->format('Y-m-d'); // Default to today if no active shift
            $this->selectedStorageDutyLog = null; // No active shift, so no selected duty log
        }
    }

    public function render()
    {
        // Determine the actual storage duty log ID to filter by
        $filterStorageDutyLogId = $this->selectedStorageDutyLogId;
        // If no specific shift is selected, and there's an active shift, use the active shift's ID
        if (empty($this->selectedStorageDutyLogId) && $this->currentActiveStorageDutyLogId) {
            $filterStorageDutyLogId = $this->currentActiveStorageDutyLogId;
        }

        $queryStockAdditions = Stock::with('product', 'storageDutyLog.user')
            ->when($this->selectedProductId, function ($query) {
                $query->where('product_id', $this->selectedProductId);
            })
            ->when($filterStorageDutyLogId, function ($query) use ($filterStorageDutyLogId) {
                $query->where('storage_duty_log_id', $filterStorageDutyLogId);
            });

        $queryStockDispensing = StockMovement::with('product', 'storageDutyLog.user')
            ->when($this->selectedProductId, function ($query) {
                $query->where('product_id', $this->selectedProductId);
            })
            ->when($filterStorageDutyLogId, function ($query) use ($filterStorageDutyLogId) {
                $query->where('storage_duty_log_id', $filterStorageDutyLogId);
            });

        $stockAdditions = $queryStockAdditions->get();
        $stockDispensing = $queryStockDispensing->get();

        $this->calculateProductSummary($stockAdditions, $stockDispensing, $filterStorageDutyLogId);

        $combinedMovements = collect([]);

        foreach ($stockAdditions as $addition) {
            $combinedMovements->push([
                'date' => $addition->created_at,
                'product_name' => $addition->product->product_name,
                'type' => 'Addition',
                'quantity' => $addition->quantity,
                'user' => $addition->storageDutyLog->user->name ?? 'N/A',
            ]);
        }

        foreach ($stockDispensing as $movement) {
            if ($movement->actual_quantity_dispensed > 0) {
                $combinedMovements->push([
                    'date' => $movement->created_at,
                    'product_name' => $movement->product->product_name,
                    'type' => 'Dispense',
                    'quantity' => $movement->quantity, // Use original order quantity
                    'user' => $movement->storageDutyLog->user->name ?? 'N/A',
                ]);
            }
            if ($movement->actual_quantity_returned > 0) {
                $combinedMovements->push([
                    'date' => $movement->created_at,
                    'product_name' => $movement->product->product_name,
                    'type' => 'Return',
                    'quantity' => $movement->actual_quantity_returned,
                    'user' => $movement->storageDutyLog->user->name ?? 'N/A',
                ]);
            }
        }

        $sortedMovements = $combinedMovements->sortByDesc('date');

        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $currentPageItems = $sortedMovements->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedMovements = new \Illuminate\Pagination\LengthAwarePaginator($currentPageItems, count($sortedMovements), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);

        return view('livewire.stock-movement-monitoring', [
            'movements' => $paginatedMovements,
            'productSummary' => $this->productSummary, // Pass the summary data to the view
        ]);
    }

    private function calculateProductSummary($stockAdditions, $stockDispensing, $filterStorageDutyLogId)
    {
        $this->productSummary = [];
        $filteredProducts = $this->products->when($this->selectedProductId, function ($collection) {
            return $collection->where('id', $this->selectedProductId);
        });

        $storageDutyLog = null;
        if ($filterStorageDutyLogId) {
            $storageDutyLog = StorageDutyLog::with(['startStockCount.stockCountItems', 'endStockCount.stockCountItems'])
                ->find($filterStorageDutyLogId);
        }

        // Determine the reference date for finding the previous closing stock
        $referenceDate = null;
        if ($storageDutyLog) {
            $referenceDate = $storageDutyLog->time_in;
        } elseif ($this->selectedDate) {
            $referenceDate = Carbon::parse($this->selectedDate)->startOfDay();
        }

        $previousClosingStocks = [];
        if ($referenceDate) {
            $previousDutyLog = StorageDutyLog::with('endStockCount.stockCountItems')
                ->where('time_out', '<', $referenceDate)
                ->whereNotNull('time_out') // Ensure the shift was ended
                ->orderByDesc('time_out')
                ->first();

            if ($previousDutyLog && $previousDutyLog->endStockCount) {
                foreach ($previousDutyLog->endStockCount->stockCountItems as $item) {
                    $previousClosingStocks[$item->product_id] = $item->quantity;
                }
            }
        }

        foreach ($filteredProducts as $product) {
            $startCount = 0;
            if ($storageDutyLog && $storageDutyLog->startStockCount) {
                $startCount = $storageDutyLog->startStockCount->stockCountItems
                    ->where('product_id', $product->id)
                    ->first()
                    ->quantity ?? 0;
            } elseif (!$filterStorageDutyLogId) {
                // Fallback if no specific shift is selected and no active shift
                $startCount = Stock::where('product_id', $product->id)
                    ->whereDate('created_at', '<', $this->startDate) // Use fixed start of month
                    ->sum('quantity');
            }

            $restocks = $stockAdditions->where('product_id', $product->id)->sum('quantity');
            $initialDispensed = $stockDispensing->where('product_id', $product->id)->sum('quantity'); // Use original order quantity
            $actualReceived = $stockDispensing->where('product_id', $product->id)->sum('actual_quantity_dispensed');
            $returns = $stockDispensing->where('product_id', $product->id)->sum('actual_quantity_returned');
            $runningStock = $restocks - $initialDispensed + $returns;

            $loss = $initialDispensed - ($actualReceived + $returns);

            $closingCount = 'N/A';
            $discrepancy = 'N/A';

            if ($storageDutyLog && $storageDutyLog->storage_duty_log_status_id != 1) { // Not ongoing
                $endCountItem = optional($storageDutyLog->endStockCount)->stockCountItems
                    ->where('product_id', $product->id)
                    ->first();
                
                $closingCount = $endCountItem->quantity ?? 0;
                $discrepancy = $closingCount - $runningStock;
            }

            $this->productSummary[] = [
                'product_name' => $product->product_name,
                'start_count' => $startCount,
                'restocks' => $restocks,
                'initial_dispensed' => $initialDispensed,
                'actual_received' => $actualReceived,
                'returns' => $returns,
                'running_stock' => $runningStock,
                'loss' => $loss,
                'high_level' => $product->high_level,
                'running_low_level' => $product->running_low_level,
                'critical_level' => $product->critical_level,
                'closing_count' => $closingCount,
                'discrepancy' => $discrepancy,
                'previous_closing_stock' => $previousClosingStocks[$product->id] ?? 0, // Add previous closing stock
            ];
        }
    }
}
