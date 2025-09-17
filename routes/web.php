<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\CashierModule;
use App\Livewire\StorageModule;
use App\Livewire\AdminModule;
use App\Livewire\ProductList;
use App\Livewire\ClientList;
use App\Livewire\AddStock;
use App\Livewire\AddSale;
use App\Livewire\TransactionList;
use App\Livewire\ClientDetail;
use App\Livewire\ProductDetail;
use App\Livewire\Remittances;
use App\Livewire\FinalizeDeliveryBatch;
use App\Livewire\FulfillOrdersModule;
use App\Livewire\UserList;
use App\Livewire\PersonnelList;
use App\Livewire\StockMovementMonitoring;
use App\Livewire\ExpenseList; // Import the new Livewire component
use App\Livewire\PromosAndDiscounts; // Import the new Livewire component

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware([
    'auth:web', // Changed from auth:sanctum to auth:web
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Role-based modules with middleware
    Route::get('/admin-module', AdminModule::class)->middleware('user.level:1')->name('admin-module');
    Route::get('/cashier-module', CashierModule::class)->middleware('user.level:2')->name('cashier-module');
    Route::get('/storage-module', StorageModule::class)->middleware('user.level:3')->name('storage-module');

    // Other routes (consider adding middleware if needed)
    Route::get('/products', ProductList::class)->name('products');
    Route::get('/clients', ClientList::class)->name('clients');
    Route::get('/add-stock', AddStock::class)->name('add-stock');
    Route::get('/add-sale', AddSale::class)->name('add-sale');
    Route::get('/transactions', TransactionList::class)->name('transactions');
    Route::get('/clients/{client}', ClientDetail::class)->name('client-detail');
    Route::get('/products/{product}', ProductDetail::class)->name('product-detail');
    Route::get('/remittances', Remittances::class)->name('remittances');
    Route::get('/finalize-delivery-batch', FinalizeDeliveryBatch::class)->name('finalize-delivery-batch');
    Route::get('/fulfill-orders', FulfillOrdersModule::class)->middleware('user.level:3')->name('fulfill-orders');
    Route::get('/users', UserList::class)->middleware('user.level:1')->name('users');
    Route::get('/personnel', PersonnelList::class)->middleware('user.level:1')->name('personnel');
    Route::get('/stock-movement-monitoring', StockMovementMonitoring::class)->middleware('user.level:1')->name('stock-movement-monitoring');
    Route::get('/expenses', ExpenseList::class)->middleware('user.level:1')->name('expenses');
    Route::get('/promos-and-discounts', PromosAndDiscounts::class)->middleware('user.level:1')->name('promos-and-discounts');
});
