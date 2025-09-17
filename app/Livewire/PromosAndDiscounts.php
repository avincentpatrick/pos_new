<?php

namespace App\Livewire;

use App\Models\PromoPackage;
use App\Models\SpecialPriceSet;
use App\Models\Product; // Import Product model
use App\Models\SpecialPrice; // Import SpecialPrice model
use App\Models\Promo; // Import Promo model
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Client; // Import Client model
use App\Models\ClientPromo; // Import ClientPromo model
use App\Models\ClientSpecialPrice; // Import ClientSpecialPrice model
use Illuminate\Support\Facades\DB; // Import DB for transactions

class PromosAndDiscounts extends Component
{
    use WithPagination;

    public $searchPromoPackages = '';
    public $searchSpecialPriceSets = '';

    public $showAddPromoPackageModal = false;
    public $showAddSpecialPriceSetModal = false;

    // Properties for applying to clients
    public $showApplyToClientsModal = false;
    public $applyingToType = null; // 'promo_package' or 'special_price_set'
    public $applyingToId = null;
    public $selectedClientIds = [];
    public $clients = []; // For the modal list
    public $searchClients = '';
    public $initialSelectedClientIds = []; // To track clients initially selected

    // Properties for adding new Promo Package
    public $newPromoPackageName;
    public $newPromoPackageValidityDate;
    public $promoProducts = []; // New property for promos per product (product_id => ['minimum_buy', 'get_free'])

    // Properties for adding new Special Price Set
    public $newSpecialPriceSetName;
    public $newSpecialPriceSetValidityDate;
    public $specialPriceSetProducts = []; // New property for special prices per product in Special Price Set modal

    // Properties for editing Promo Package
    public $showEditPromoPackageModal = false;
    public $editingPromoPackageId = null;
    public $editingPromoPackageName;
    public $editingPromoPackageValidityDate;
    public $editingPromoProducts = [];

    // Properties for editing Special Price Set
    public $showEditSpecialPriceSetModal = false;
    public $editingSpecialPriceSetId = null;
    public $editingSpecialPriceSetName;
    public $editingSpecialPriceSetValidityDate;
    public $editingSpecialPriceSetProducts = [];

    protected $queryString = [
        'searchPromoPackages' => ['except' => ''],
        'searchSpecialPriceSets' => ['except' => ''],
    ];

    public function updatingSearchPromoPackages()
    {
        $this->resetPage('promoPackagesPage');
    }

    public function updatingSearchSpecialPriceSets()
    {
        $this->resetPage('specialPriceSetsPage');
    }

    public function render()
    {
        $promoPackages = PromoPackage::with('promos')
            ->where('promo_package_name', 'like', '%' . $this->searchPromoPackages . '%')
            ->paginate(5, ['*'], 'promoPackagesPage');

        $specialPriceSets = SpecialPriceSet::with('specialPrices')
            ->where('special_price_set_name', 'like', '%' . $this->searchSpecialPriceSets . '%')
            ->paginate(5, ['*'], 'specialPriceSetsPage');

        $products = Product::all(); // Fetch all products

        // Fetch clients for the "Apply to Clients" modal, eager load relationships
        $clients = Client::with(['clientPromo', 'clientSpecialPrice'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->searchClients . '%')
                    ->orWhere('company', 'like', '%' . $this->searchClients . '%');
            })
            ->get();
        $this->clients = $clients; // Update the clients property

        return view('livewire.promos-and-discounts', [
            'promoPackages' => $promoPackages,
            'specialPriceSets' => $specialPriceSets,
            'products' => $products, // Pass products to the view
            'clients' => $clients, // Pass clients to the view
        ]);
    }

    public function openAddPromoPackageModal()
    {
        $this->reset(['newPromoPackageName', 'newPromoPackageValidityDate', 'promoProducts']);
        $this->newPromoPackageName = $this->searchPromoPackages;

        // Initialize promoProducts for all products
        $products = Product::all();
        foreach ($products as $product) {
            $this->promoProducts[$product->id] = [
                'minimum_buy' => null,
                'get_free' => null,
            ];
        }

        $this->showAddPromoPackageModal = true;
    }

    public function closeAddPromoPackageModal()
    {
        $this->showAddPromoPackageModal = false;
        $this->reset(['newPromoPackageName', 'newPromoPackageValidityDate', 'promoProducts']);
        $this->resetValidation();
    }

    public function openAddSpecialPriceSetModal()
    {
        $this->reset(['newSpecialPriceSetName', 'newSpecialPriceSetValidityDate', 'specialPriceSetProducts']);
        $this->newSpecialPriceSetName = $this->searchSpecialPriceSets;

        // Initialize specialPriceSetProducts for all products
        $products = Product::all();
        foreach ($products as $product) {
            $this->specialPriceSetProducts[$product->id] = null;
        }

        $this->showAddSpecialPriceSetModal = true;
    }

    public function closeAddSpecialPriceSetModal()
    {
        $this->showAddSpecialPriceSetModal = false;
        $this->reset(['newSpecialPriceSetName', 'newSpecialPriceSetValidityDate', 'specialPriceSetProducts']);
        $this->resetValidation();
    }

    public function savePromoPackage()
    {
        $this->validate([
            'newPromoPackageName' => 'required|string|max:255',
            'newPromoPackageValidityDate' => 'required|date',
        ]);

        DB::transaction(function () {
            $promoPackage = PromoPackage::create([
                'promo_package_name' => $this->newPromoPackageName,
                'validity_date' => $this->newPromoPackageValidityDate,
            ]);

            foreach ($this->promoProducts as $productId => $promoData) {
                if ((!is_null($promoData['minimum_buy']) && $promoData['minimum_buy'] > 0) || (!is_null($promoData['get_free']) && $promoData['get_free'] > 0)) {
                    Promo::create([
                        'promo_package_id' => $promoPackage->id,
                        'product_id' => $productId,
                        'minimum_buy' => $promoData['minimum_buy'] ?? 0,
                        'get_free' => $promoData['get_free'] ?? 0,
                    ]);
                }
            }
        });

        $this->dispatch('notify', 'Promo Package and associated Promos added successfully!');
        $this->closeAddPromoPackageModal();
        $this->reset(['searchPromoPackages']);
        $this->resetPage('promoPackagesPage');
    }

    public function saveSpecialPriceSet()
    {
        $this->validate([
            'newSpecialPriceSetName' => 'required|string|max:255',
            'newSpecialPriceSetValidityDate' => 'required|date',
        ]);

        DB::transaction(function () {
            $specialPriceSet = SpecialPriceSet::create([
                'special_price_set_name' => $this->newSpecialPriceSetName,
                'validity_date' => $this->newSpecialPriceSetValidityDate,
            ]);

            foreach ($this->specialPriceSetProducts as $productId => $specialPrice) {
                if (!is_null($specialPrice) && $specialPrice > 0) {
                    SpecialPrice::create([
                        'special_price_set_id' => $specialPriceSet->id,
                        'product_id' => $productId,
                        'special_price' => $specialPrice,
                    ]);
                }
            }
        });

        $this->dispatch('notify', 'Special Price Set and associated Special Prices added successfully!');
        $this->closeAddSpecialPriceSetModal();
        $this->reset(['searchSpecialPriceSets']);
        $this->resetPage('specialPriceSetsPage');
    }

    public function openApplyToClientsModal($type, $id)
    {
        $this->reset(['selectedClientIds', 'searchClients', 'initialSelectedClientIds']);
        $this->applyingToType = $type;
        $this->applyingToId = $id;

        // Fetch clients with their current subscriptions
        $clients = Client::with(['clientPromo', 'clientSpecialPrice'])
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->searchClients . '%')
                    ->orWhere('company', 'like', '%' . $this->searchClients . '%');
            })
            ->get();

        $this->selectedClientIds = [];
        foreach ($clients as $client) {
            if ($type === 'promo_package' && $client->clientPromo && $client->clientPromo->promo_package_id === $id) {
                $this->selectedClientIds[] = (string)$client->id; // Cast to string for checkbox binding
            } elseif ($type === 'special_price_set' && $client->clientSpecialPrice && $client->clientSpecialPrice->special_price_set_id === $id) {
                $this->selectedClientIds[] = (string)$client->id; // Cast to string for checkbox binding
            }
        }
        $this->initialSelectedClientIds = $this->selectedClientIds; // Store initial state

        $this->showApplyToClientsModal = true;
    }

    public function closeApplyToClientsModal()
    {
        $this->showApplyToClientsModal = false;
        $this->reset(['applyingToType', 'applyingToId', 'selectedClientIds', 'searchClients']);
    }

    public function updatedSearchClients()
    {
        $this->resetPage('clientsPage'); // Assuming you might paginate clients in the modal
    }

    public function applyToClients()
    {
        $this->validate([
            'selectedClientIds' => 'array', // Can be empty if unselecting all
            'selectedClientIds.*' => 'exists:clients,id',
        ]);

        DB::transaction(function () {
            $currentClients = collect($this->initialSelectedClientIds)->map(fn($id) => (int)$id);
            $newClients = collect($this->selectedClientIds)->map(fn($id) => (int)$id);

            $clientsToAdd = $newClients->diff($currentClients);
            $clientsToRemove = $currentClients->diff($newClients);

            // Handle additions
            foreach ($clientsToAdd as $clientId) {
                $client = Client::with(['clientPromo', 'clientSpecialPrice'])->find($clientId);

                if ($this->applyingToType === 'promo_package') {
                    if ($client->clientPromo && $client->clientPromo->promo_package_id !== $this->applyingToId) {
                        $this->dispatch('notify', 'Client ' . $client->name . ' is already subscribed to another promo package.');
                        continue; // Skip this client
                    }
                    ClientPromo::updateOrCreate(
                        ['client_id' => $clientId],
                        ['promo_package_id' => $this->applyingToId]
                    );
                } elseif ($this->applyingToType === 'special_price_set') {
                    if ($client->clientSpecialPrice && $client->clientSpecialPrice->special_price_set_id !== $this->applyingToId) {
                        $this->dispatch('notify', 'Client ' . $client->name . ' is already subscribed to another special price set.');
                        continue; // Skip this client
                    }
                    ClientSpecialPrice::updateOrCreate(
                        ['client_id' => $clientId],
                        ['special_price_set_id' => $this->applyingToId]
                    );
                }
            }

            // Handle removals
            foreach ($clientsToRemove as $clientId) {
                if ($this->applyingToType === 'promo_package') {
                    ClientPromo::where('client_id', $clientId)
                        ->where('promo_package_id', $this->applyingToId)
                        ->delete();
                } elseif ($this->applyingToType === 'special_price_set') {
                    ClientSpecialPrice::where('client_id', $clientId)
                        ->where('special_price_set_id', $this->applyingToId)
                        ->delete();
                }
            }
        });

        $this->dispatch('notify', 'Client subscriptions updated successfully!');
        $this->closeApplyToClientsModal();
    }

    public function editPromoPackage($id)
    {
        $this->reset(['editingPromoPackageName', 'editingPromoPackageValidityDate', 'editingPromoProducts']);
        $promoPackage = PromoPackage::with('promos.product')->findOrFail($id);
        $this->editingPromoPackageId = $promoPackage->id;
        $this->editingPromoPackageName = $promoPackage->promo_package_name;
        $this->editingPromoPackageValidityDate = $promoPackage->validity_date;

        $products = Product::all();
        foreach ($products as $product) {
            $existingPromo = $promoPackage->promos->firstWhere('product_id', $product->id);
            $this->editingPromoProducts[$product->id] = [
                'minimum_buy' => $existingPromo->minimum_buy ?? null,
                'get_free' => $existingPromo->get_free ?? null,
            ];
        }
        $this->showEditPromoPackageModal = true;
    }

    public function updatePromoPackage()
    {
        $this->validate([
            'editingPromoPackageName' => 'required|string|max:255',
            'editingPromoPackageValidityDate' => 'required|date',
        ]);

        DB::transaction(function () {
            $promoPackage = PromoPackage::findOrFail($this->editingPromoPackageId);
            $promoPackage->update([
                'promo_package_name' => $this->editingPromoPackageName,
                'validity_date' => $this->editingPromoPackageValidityDate,
            ]);

            // Delete existing promos for this package
            Promo::where('promo_package_id', $promoPackage->id)->delete();

            // Create/update new promos
            foreach ($this->editingPromoProducts as $productId => $promoData) {
                if ((!is_null($promoData['minimum_buy']) && $promoData['minimum_buy'] > 0) || (!is_null($promoData['get_free']) && $promoData['get_free'] > 0)) {
                    Promo::create([
                        'promo_package_id' => $promoPackage->id,
                        'product_id' => $productId,
                        'minimum_buy' => $promoData['minimum_buy'] ?? 0,
                        'get_free' => $promoData['get_free'] ?? 0,
                    ]);
                }
            }
        });

        $this->dispatch('notify', 'Promo Package updated successfully!');
        $this->showEditPromoPackageModal = false;
        $this->resetPage('promoPackagesPage');
    }

    public function deletePromoPackage($id)
    {
        $this->dispatch('showDeleteModal', 'PromoPackage', $id);
    }

    public function editSpecialPriceSet($id)
    {
        $this->reset(['editingSpecialPriceSetName', 'editingSpecialPriceSetValidityDate', 'editingSpecialPriceSetProducts']);
        $specialPriceSet = SpecialPriceSet::with('specialPrices.product')->findOrFail($id);
        $this->editingSpecialPriceSetId = $specialPriceSet->id;
        $this->editingSpecialPriceSetName = $specialPriceSet->special_price_set_name;
        $this->editingSpecialPriceSetValidityDate = $specialPriceSet->validity_date;

        $products = Product::all();
        foreach ($products as $product) {
            $existingSpecialPrice = $specialPriceSet->specialPrices->firstWhere('product_id', $product->id);
            $this->editingSpecialPriceSetProducts[$product->id] = $existingSpecialPrice->special_price ?? null;
        }
        $this->showEditSpecialPriceSetModal = true;
    }

    public function updateSpecialPriceSet()
    {
        $this->validate([
            'editingSpecialPriceSetName' => 'required|string|max:255',
            'editingSpecialPriceSetValidityDate' => 'required|date',
        ]);

        DB::transaction(function () {
            $specialPriceSet = SpecialPriceSet::findOrFail($this->editingSpecialPriceSetId);
            $specialPriceSet->update([
                'special_price_set_name' => $this->editingSpecialPriceSetName,
                'validity_date' => $this->editingSpecialPriceSetValidityDate,
            ]);

            // Delete existing special prices for this set
            SpecialPrice::where('special_price_set_id', $specialPriceSet->id)->delete();

            // Create/update new special prices
            foreach ($this->editingSpecialPriceSetProducts as $productId => $specialPrice) {
                if (!is_null($specialPrice) && $specialPrice > 0) {
                    SpecialPrice::create([
                        'special_price_set_id' => $specialPriceSet->id,
                        'product_id' => $productId,
                        'special_price' => $specialPrice,
                    ]);
                }
            }
        });

        $this->dispatch('notify', 'Special Price Set updated successfully!');
        $this->showEditSpecialPriceSetModal = false;
        $this->resetPage('specialPriceSetsPage');
    }

    public function deleteSpecialPriceSet($id)
    {
        $this->dispatch('showDeleteModal', 'SpecialPriceSet', $id);
    }
}
