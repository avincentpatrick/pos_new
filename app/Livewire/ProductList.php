<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $showAddProductModal = false;
    public $showEditProductModal = false;
    public $product_name;
    public $product_description;
    public $kilogram;
    public $retail_price;
    public $high_level;
    public $running_low_level;
    public $critical_level;
    public $productId;

    protected $listeners = ['itemDeleted' => '$refresh'];

    public function openAddProductModal()
    {
        $this->reset(['product_name', 'product_description', 'kilogram', 'retail_price', 'high_level', 'running_low_level', 'critical_level', 'productId']);
        $this->product_name = $this->search; // Pre-fill with search term
        $this->showAddProductModal = true;
    }

    public function addProduct()
    {
        $this->validate([
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'kilogram' => 'nullable|integer',
            'retail_price' => 'required|numeric|min:0',
            'high_level' => 'required|integer|min:0',
            'running_low_level' => 'required|integer|min:0',
            'critical_level' => 'required|integer|min:0',
        ]);

        Product::create([
            'product_status_id' => 1, // Assuming 1 is "Active"
            'product_name' => $this->product_name,
            'product_description' => $this->product_description,
            'kilogram' => $this->kilogram,
            'retail_price' => $this->retail_price,
            'high_level' => $this->high_level,
            'running_low_level' => $this->running_low_level,
            'critical_level' => $this->critical_level,
            'created_by' => Auth::id(),
        ]);

        $this->showAddProductModal = false;
        $this->dispatch('notify', 'Product added successfully!');
        $this->reset(['search']); // Reset search filter
        $this->resetPage(); // Reset pagination to show new product
    }

    public function openEditProductModal($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $product->id;
        $this->product_name = $product->product_name;
        $this->product_description = $product->product_description;
        $this->kilogram = $product->kilogram;
        $this->retail_price = $product->retail_price;
        $this->high_level = $product->high_level;
        $this->running_low_level = $product->running_low_level;
        $this->critical_level = $product->critical_level;
        $this->showEditProductModal = true;
    }

    public function updateProduct()
    {
        $this->validate([
            'product_name' => 'required|string|max:255',
            'product_description' => 'nullable|string',
            'kilogram' => 'nullable|integer',
            'retail_price' => 'required|numeric|min:0',
            'high_level' => 'required|integer|min:0',
            'running_low_level' => 'required|integer|min:0',
            'critical_level' => 'required|integer|min:0',
        ]);

        $product = Product::findOrFail($this->productId);
        $product->update([
            'product_name' => $this->product_name,
            'product_description' => $this->product_description,
            'kilogram' => $this->kilogram,
            'retail_price' => $this->retail_price,
            'high_level' => $this->high_level,
            'running_low_level' => $this->running_low_level,
            'critical_level' => $this->critical_level,
            'updated_by' => Auth::id(),
        ]);

        $this->showEditProductModal = false;
        $this->dispatch('notify', 'Product updated successfully!');
    }

    public function deleteProduct($id)
    {
        $this->dispatch('showDeleteModal', 'Product', $id);
    }

    public function render()
    {
        $products = Product::where('product_name', 'like', '%'.$this->search.'%')
            ->paginate(10);

        return view('livewire.product-list', [
            'products' => $products,
        ]);
    }
}
