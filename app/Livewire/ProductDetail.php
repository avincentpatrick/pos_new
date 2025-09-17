<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use Livewire\Component;

class ProductDetail extends Component
{
    public Product $product;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function render()
    {
        $totalStock = Stock::where('product_id', $this->product->id)->sum('quantity');
        $soldProductsCount = Sale::where('product_id', $this->product->id)->sum('quantity');

        $remainingStock = $totalStock - $soldProductsCount;

        $salesLogs = Sale::with('transaction')->where('product_id', $this->product->id)->latest()->paginate(5, ['*'], 'salesPage');
        $stockLogs = Stock::where('product_id', $this->product->id)->latest()->paginate(5, ['*'], 'stockPage');

        $soldProductsKilos = $soldProductsCount * ($this->product->kilogram ?? 0);
        $totalSales = Sale::where('product_id', $this->product->id)->sum('total');

        return view('livewire.product-detail', [
            'totalStock' => $totalStock,
            'remainingStock' => $remainingStock,
            'salesLogs' => $salesLogs,
            'stockLogs' => $stockLogs,
            'soldProductsCount' => $soldProductsCount,
            'soldProductsKilos' => $soldProductsKilos,
            'totalSales' => $totalSales,
        ]);
    }
}
