<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class ClientDetail extends Component
{
    use WithPagination;

    public Client $client;
    public $showReceiptModal = false;
    public $selectedTransaction = null;

    public function mount(Client $client)
    {
        $this->client = $client;
    }

    public function viewTransaction($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['client', 'sales.product'])->find($transactionId);
        $this->showReceiptModal = true;
    }

    public function render()
    {
        $transactions = $this->client->transactions()->with(['sales.product', 'payments.paymentMethod'])->latest()->paginate(10);

        // Calculate Total Sales using adjusted_total
        $total_sales = $this->client->transactions()->get()->sum(function ($transaction) {
            return $transaction->adjusted_total;
        });
        
        // Calculate Total Credit using adjusted_total for credit payment methods
        $total_credit = $this->client->transactions()
            ->whereHas('payments', function ($query) {
                $query->where('payment_method_id', 3); // Only credit payment method
            })
            ->get()
            ->sum(function ($transaction) {
                return $transaction->adjusted_total;
            });
            
        $number_of_transactions = $this->client->transactions()->count();
        
        $total_kgs_ordered = $this->client->transactions()
            ->with('sales.product')
            ->get()
            ->flatMap(function ($transaction) {
                return $transaction->sales;
            })
            ->whereNotNull('product.kilogram')
            ->sum('quantity');

        return view('livewire.client-detail', [
            'transactions' => $transactions,
            'total_sales' => $total_sales,
            'total_credit' => $total_credit,
            'number_of_transactions' => $number_of_transactions,
            'total_kgs_ordered' => $total_kgs_ordered,
        ]);
    }
}
