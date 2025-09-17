<?php

namespace App\Livewire;

use App\Models\Stock;
use App\Models\Product;
use App\Models\StorageDutyLog; // Import StorageDutyLog model
use App\Models\StockMovement; // Import StockMovement model
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class AddStock extends Component
{
    use WithPagination;

    public $adjustments = [];
    public $total_stocks = [];
    public $currentStorageDutyLogId; // New property to store active storage duty log ID

    public function mount()
    {
        $this->loadProducts();
        $this->checkActiveStorageShift(); // Check for active storage shift on mount
    }

    public function checkActiveStorageShift()
    {
        $activeShift = StorageDutyLog::where('user_id', Auth::id())
            ->where('storage_duty_log_status_id', 1) // Assuming 1 is "Ongoing"
            ->first();

        if ($activeShift) {
            $this->currentStorageDutyLogId = $activeShift->id;
        }
    }

    public function loadProducts()
    {
        $products = Product::with('stocks')->get();
        foreach ($products as $product) {
            $this->total_stocks[$product->id] = $product->stocks->sum('quantity');
            $this->adjustments[$product->id] = null;
        }
    }

    public function saveStocks()
    {
        if (is_null($this->currentStorageDutyLogId)) {
            $this->dispatch('notify', 'No active storage shift found. Please start a shift first.');
            return;
        }

        foreach ($this->adjustments as $productId => $quantity) {
            // Only create a stock record if the quantity is not null and not 0
            if (!is_null($quantity) && $quantity != 0) {
                Stock::create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'storage_duty_log_id' => $this->currentStorageDutyLogId, // Add storage_duty_log_id
                    'created_by' => Auth::id(),
                ]);
            }
        }

        $this->loadProducts();
        $this->dispatch('notify', 'Stocks updated successfully!');
        $this->dispatch('stockMovementUpdated'); // Dispatch event for real-time update
    }

    public function incrementAdjustment($productId)
    {
        // Initialize to 0 if null before incrementing
        $this->adjustments[$productId] = ($this->adjustments[$productId] ?? 0) + 1;
    }

    public function decrementAdjustment($productId)
    {
        $currentAdjustment = $this->adjustments[$productId] ?? 0;
        $totalStock = $this->total_stocks[$productId] ?? 0;

        if ($totalStock + $currentAdjustment > 0) {
            $this->adjustments[$productId] = $currentAdjustment - 1;
        }
    }

    public function updatedAdjustments($value, $key)
    {
        $productId = $key;
        // Ensure value is treated as an integer, or null if empty
        $value = is_numeric($value) ? (int)$value : null;
        $totalStock = $this->total_stocks[$productId] ?? 0;

        if (!is_null($value) && $totalStock + $value < 0) {
            $this->adjustments[$productId] = -$totalStock;
        } else {
            $this->adjustments[$productId] = $value;
        }
    }

    public function render()
    {
        $products = Product::paginate(10);

        return view('livewire.add-stock', [
            'products' => $products,
        ]);
    }
}
