<?php

namespace App\Livewire;

use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use App\Models\ClientPromo; // Import ClientPromo model
use App\Models\ClientSpecialPrice; // Import ClientSpecialPrice model
use App\Models\PromoPackage; // Import PromoPackage model
use App\Models\SpecialPriceSet; // Import SpecialPriceSet model
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SpecialPrice; // Import SpecialPrice model
use Illuminate\Support\Facades\DB; // Import DB for transactions

class ClientList extends Component
{
    use WithPagination;

    public $search = '';
    public $showAddClientModal = false;
    public $showEditClientModal = false;
    public $name;
    public $company;
    public $contact_no;
    public $email;
    public $address;
    public $google_map_pin;
    public $clientId;

    public $promoPackages;
    public $specialPriceSets;
    public $clientPromoPackageId;
    public $clientSpecialPriceSetId;

    protected $listeners = ['itemDeleted' => '$refresh'];

    public function openAddClientModal()
    {
        $this->reset(['name', 'company', 'contact_no', 'email', 'address', 'google_map_pin', 'clientId', 'clientPromoPackageId', 'clientSpecialPriceSetId']);
        $this->name = $this->search;
        $this->showAddClientModal = true;
    }

    public function addClient()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'google_map_pin' => 'nullable|string|max:255',
        ]);

        $client = Client::create([
            'client_status_id' => 1, // Assuming 1 is "Active"
            'name' => $this->name,
            'company' => $this->company,
            'contact_no' => $this->contact_no,
            'email' => $this->email,
            'address' => $this->address,
            'google_map_pin' => $this->google_map_pin,
            'created_by' => Auth::id(),
        ]);

        if (!empty($this->clientPromoPackageId)) {
            ClientPromo::create([
                'client_id' => $client->id,
                'promo_package_id' => $this->clientPromoPackageId,
            ]);
        }

        if (!empty($this->clientSpecialPriceSetId)) {
            ClientSpecialPrice::create([
                'client_id' => $client->id,
                'special_price_set_id' => $this->clientSpecialPriceSetId,
            ]);
        }

        $this->showAddClientModal = false;
        $this->dispatch('notify', 'Client added successfully!');
        $this->reset(['search']); // Reset search filter
        $this->resetPage(); // Reset pagination to show new client
    }

    public function openEditClientModal($id)
    {
        $client = Client::with(['clientPromo', 'clientSpecialPrice.specialPriceSet'])->findOrFail($id);
        $this->clientId = $client->id;
        $this->name = $client->name;
        $this->company = $client->company;
        $this->contact_no = $client->contact_no;
        $this->email = $client->email;
        $this->address = $client->address;
        $this->google_map_pin = $client->google_map_pin;
        $this->clientPromoPackageId = $client->clientPromo->promo_package_id ?? '';
        $this->clientSpecialPriceSetId = $client->clientSpecialPrice->specialPriceSet->id ?? '';
        $this->showEditClientModal = true;
    }

    public function updateClient()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'google_map_pin' => 'nullable|string|max:255',
        ]);

        $client = Client::findOrFail($this->clientId);
        $client->update([
            'name' => $this->name,
            'company' => $this->company,
            'contact_no' => $this->contact_no,
            'email' => $this->email,
            'address' => $this->address,
            'google_map_pin' => $this->google_map_pin,
            'updated_by' => Auth::id(),
        ]);

        $this->showEditClientModal = false;
        $this->dispatch('notify', 'Client updated successfully!');

        // Update ClientPromo
        ClientPromo::where('client_id', $client->id)->delete();
        if (!empty($this->clientPromoPackageId)) {
            ClientPromo::create([
                'client_id' => $client->id,
                'promo_package_id' => $this->clientPromoPackageId,
            ]);
        }

        // Update ClientSpecialPrice
        ClientSpecialPrice::where('client_id', $client->id)->delete();
        if (!empty($this->clientSpecialPriceSetId)) {
            ClientSpecialPrice::create([
                'client_id' => $client->id,
                'special_price_set_id' => $this->clientSpecialPriceSetId,
            ]);
        }
    }

    public function deleteClient($id)
    {
        $this->dispatch('showDeleteModal', 'Client', $id);
    }

    public function render()
    {
        $clients = Client::where('name', 'like', '%'.$this->search.'%')
            ->orWhere('company', 'like', '%'.$this->search.'%')
            ->paginate(10);

        $this->promoPackages = PromoPackage::all();
        $this->specialPriceSets = SpecialPriceSet::all();

        return view('livewire.client-list', [
            'clients' => $clients,
            'userLevel' => Auth::user()->user_level_id,
            'promoPackages' => $this->promoPackages,
            'specialPriceSets' => $this->specialPriceSets,
        ]);
    }
}
