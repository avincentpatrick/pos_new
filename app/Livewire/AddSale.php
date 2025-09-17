<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Sale;
use App\Models\Transaction;
use App\Models\Payment; // Import the new Payment model
use App\Models\CashierDutyLog;
use App\Models\ClientPromo;
use App\Models\ClientSpecialPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache; // Import Cache facade
use Livewire\Component;
use Illuminate\Validation\ValidationException;

class AddSale extends Component
{
    public $search = '';
    public $selectedCustomerId;
    public $quantities = [];
    public $total_amount = 0; // This is the total for the sale, not the payment
    public $selectedOrderType;
    public $notes = '';

    // New properties for Payment model
    public $paymentMethodId;
    public $paymentAmountReceived = 0;
    public $paymentAmountChange = 0;
    public $paymentReferenceNumber;
    public $paymentCheckNumber;

    public $showAddClientModal = false;
    public $showCheckoutModal = false;
    public $showReceiptModal = false;
    public $lastTransaction = null;

    public $name; // For adding new client
    public $company;
    public $contact_no;
    public $email;
    public $address;
    public $google_map_pin;

    public $clientPromos = [];
    public $clientSpecialPrices = [];
    public $freeQuantities = [];

    public function openAddClientModal()
    {
        $this->reset(['name', 'company', 'contact_no', 'email', 'address', 'google_map_pin']);
        $this->name = $this->search;
        $this->showAddClientModal = true;
    }

    public function openCheckoutModal()
    {
        $this->paymentAmountReceived = 0; // Reset amount received
        $this->paymentAmountChange = 0; // Reset amount change
        $this->resetValidation(['paymentAmountReceived', 'paymentReferenceNumber', 'paymentCheckNumber']);
        $this->showCheckoutModal = true;
    }

    public function checkout()
    {
        try {
            $this->validate([
                'selectedCustomerId' => 'required',
                'paymentMethodId' => 'required', // Use new paymentMethodId
                'selectedOrderType' => 'required',
                'quantities' => ['required', 'array', function ($attribute, $value, $fail) {
                    $productAdded = false;
                    foreach ($value as $quantity) {
                        if ($quantity > 0) {
                            $productAdded = true;
                            break;
                        }
                    }
                    if (!$productAdded) {
                        $fail('At least one product must be added to the sale.');
                    }
                }],
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('notify', 'Please fill out all required fields before checking out.');
            throw $e;
        }

        if (in_array($this->paymentMethodId, [3, 7])) { // Use new paymentMethodId
            $this->finalizeSale();
        } else {
            $this->openCheckoutModal();
        }
    }

    public function updatedPaymentAmountReceived($value) // Updated method name
    {
        $received = (float)$value;
        $total = (float)$this->total_amount; // Use total_amount from transaction

        if ($received >= $total) {
            $this->paymentAmountChange = $received - $total;
        } else {
            $this->paymentAmountChange = 0;
        }
    }

    public function finalizeSale()
    {
        // Validation for cash payments
        if ($this->paymentMethodId == 1 && $this->paymentAmountReceived < $this->total_amount) {
            $this->dispatch('notify', 'Amount received cannot be less than the total amount for cash payments.');
            return;
        }

        $activeDutyLog = CashierDutyLog::where('user_id', auth()->id())
            ->where('cashier_duty_log_status_id', 1)
            ->first();

        if (!$activeDutyLog) {
            $this->dispatch('notify', 'You must start a duty shift before making a sale.');
            return;
        }

        $transaction = null;
        DB::transaction(function () use (&$transaction, $activeDutyLog) {
            $transaction = Transaction::create([
                'cashier_duty_log_id' => $activeDutyLog->id,
                'transaction_status_id' => 1, // Assuming 1 is "Completed"
                'client_id' => $this->selectedCustomerId,
                'order_type_id' => $this->selectedOrderType,
                'total_amount' => $this->total_amount, // Add total_amount to transaction
                'note' => $this->notes,
                'created_by' => auth()->id(),
            ]);

            // Adjust payment details for "Other Payment Methods" before creation
            if (!in_array($this->paymentMethodId, [1, 3, 7])) { // Not Cash, Credit, or COD
                $this->paymentAmountReceived = $this->total_amount; // Use total_amount
                $this->paymentAmountChange = 0;
            }

            Payment::create([
                'cashier_duty_log_id' => $activeDutyLog->id,
                'client_id' => $this->selectedCustomerId,
                'transaction_id' => $transaction->id,
                'payment_method_id' => $this->paymentMethodId,
                'amount_received' => $this->paymentAmountReceived,
                'amount_change' => $this->paymentAmountChange,
                'reference_number' => $this->paymentReferenceNumber,
                'check_number' => $this->paymentCheckNumber,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->quantities as $productId => $quantity) {
                if ($quantity > 0) {
                    $product = Product::find($productId);
                    $price = isset($this->clientSpecialPrices[$productId])
                        ? $this->clientSpecialPrices[$productId]->special_price
                        : $product->retail_price;
                    
                    $freeQuantity = $this->freeQuantities[$productId] ?? 0;
                    $totalQuantity = $quantity + $freeQuantity;

                    Sale::create([
                        'sale_status_id' => 1, // Assuming 1 is "Completed"
                        'transaction_id' => $transaction->id,
                        'product_id' => $productId,
                        'quantity' => $totalQuantity, // Paid + Free
                        'price' => $price,
                        'total' => $price * $quantity, // Charge for paid quantity only
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        });

        $this->lastTransaction = Transaction::with(['client', 'sales.product', 'payments'])->find($transaction->id); // Load payments relationship
        $this->showReceiptModal = true;
        $this->dispatch('notify', 'Sale finalized successfully!');
    }

    public function resetForm()
    {
        $this->reset([
            'search',
            'selectedCustomerId',
            'clientPromos',
            'clientSpecialPrices',
            'freeQuantities',
            'quantities',
            'total_amount',
            'selectedOrderType',
            'notes',
            'paymentMethodId',
            'paymentAmountReceived',
            'paymentAmountChange',
            'paymentReferenceNumber',
            'paymentCheckNumber',
            'showCheckoutModal',
            'showReceiptModal',
            'lastTransaction',
        ]);
    }

    public function addClient()
    {
        try {
            $this->validate([
                'name' => 'required|string|max:255',
                'company' => 'nullable|string|max:255',
                'contact_no' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'address' => 'nullable|string|max:255',
                'google_map_pin' => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch('notify', 'Please fill out all required fields for the new client.');
            throw $e;
        }

        $client = Client::create([
            'client_status_id' => 1, // Assuming 1 is "Active"
            'name' => $this->name,
            'company' => $this->company,
            'contact_no' => $this->contact_no,
            'email' => $this->email,
            'address' => $this->address,
            'google_map_pin' => $this->google_map_pin,
            'created_by' => auth()->id(),
        ]);

        $this->selectCustomer($client->id);
        $this->showAddClientModal = false;
        $this->dispatch('notify', 'Client added successfully!');
    }

    public function selectCustomer($id)
    {
        $this->selectedCustomerId = $id;
        $client = Client::find($id);
        $this->search = $client->name;

        $clientPromo = ClientPromo::where('client_id', $id)->with('promoPackage.promos.product')->first();
        if ($clientPromo && $clientPromo->promoPackage->validity_date >= now()) {
            $this->clientPromos = $clientPromo->promoPackage->promos->keyBy('product_id');
        } else {
            $this->clientPromos = [];
        }

        $clientSpecialPrice = ClientSpecialPrice::where('client_id', $id)->with('specialPriceSet.specialPrices.product')->first();
        if ($clientSpecialPrice && $clientSpecialPrice->specialPriceSet->validity_date >= now()) {
            $this->clientSpecialPrices = $clientSpecialPrice->specialPriceSet->specialPrices->keyBy('product_id');
        } else {
            $this->clientSpecialPrices = [];
        }

        $this->calculateTotal();
    }

    public function clearSelectedCustomer()
    {
        $this->selectedCustomerId = null;
        $this->search = '';
        $this->clientPromos = [];
        $this->clientSpecialPrices = [];
        $this->freeQuantities = [];
        $this->calculateTotal();
    }


    public function updatedQuantities($value, $key)
    {
        $productId = $key;
        $product = Product::with(['stocks', 'sales'])->find($productId);
        $remaining_stock = $product->stocks->sum('quantity') - $product->sales->sum('quantity');
        $value = (int)$value;

        if ($value < 0) {
            $this->quantities[$productId] = 0;
        } elseif ($value > $remaining_stock) {
            $this->quantities[$productId] = $remaining_stock;
        } else {
            $this->quantities[$productId] = $value;
        }

        // Calculate free quantities based on promo
        if (isset($this->clientPromos[$productId])) {
            $promo = $this->clientPromos[$productId];
            if ($promo->minimum_buy > 0) {
                $freeCount = floor($this->quantities[$productId] / $promo->minimum_buy) * $promo->get_free;
                $this->freeQuantities[$productId] = $freeCount;
            }
        }

        $this->calculateTotal();
    }


    public function calculateTotal()
    {
        $this->total_amount = 0;
        foreach ($this->quantities as $productId => $quantity) {
            if ($quantity > 0) {
                $product = Product::find($productId);
                $price = isset($this->clientSpecialPrices[$productId])
                    ? $this->clientSpecialPrices[$productId]->special_price
                    : $product->retail_price;
                $this->total_amount += $price * $quantity;
            }
        }
    }

    public function render()
    {
        $customers = [];
        if (strlen($this->search) > 1) {
            $customers = Client::where(function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('company', 'like', '%'.$this->search.'%')
                    ->orWhere('contact_no', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('address', 'like', '%'.$this->search.'%');
            })->get();
        }

        $products = Product::with(['stocks', 'sales'])->whereNull('deleted_at')->get();

        foreach ($products as $product) {
            $totalStock = $product->stocks->sum('quantity');
            $totalSold = $product->sales->sum('quantity');
            $product->remaining_stock = $totalStock - $totalSold;
        }

        $paymentMethods = Cache::remember('paymentMethods', 60*60, function () { // Cache for 1 hour
            return PaymentMethod::all();
        });
        $orderTypes = Cache::remember('orderTypes', 60*60, function () { // Cache for 1 hour
            return OrderType::all();
        });

        return view('livewire.add-sale', [
            'customers' => $customers,
            'products' => $products,
            'paymentMethods' => $paymentMethods,
            'orderTypes' => $orderTypes,
        ]);
    }
}
