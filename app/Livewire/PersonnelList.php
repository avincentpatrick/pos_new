<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Personnel;
use App\Models\PersonnelType;
use Livewire\WithPagination;

class PersonnelList extends Component
{
    use WithPagination;

    public $personnelTypes;
    public $search = '';
    public $personnelTypeFilter = '';
    public $showAddPersonnelModal = false;

    // Properties for adding new personnel
    public $personnel_name;
    public $personnel_type_id;

    // Properties for editing personnel
    public $showEditPersonnelModal = false;
    public $selectedPersonnelId;
    public $edit_personnel_name;
    public $edit_personnel_type_id;

    // Properties for delete confirmation
    public $showDeleteConfirmationModal = false;
    public $personnelToDeleteId;

    protected $paginationTheme = 'tailwind';

    protected $rules = [
        'personnel_name' => 'required|string|max:255',
        'personnel_type_id' => 'required|exists:personnel_types,id',
    ];

    protected $messages = [
        'personnel_name.required' => 'The personnel name is required.',
        'personnel_type_id.required' => 'The personnel type is required.',
        'personnel_type_id.exists' => 'The selected personnel type is invalid.',
    ];

    public function mount()
    {
        $this->personnelTypes = PersonnelType::all();
    }

    public function openAddPersonnelModal()
    {
        $this->resetValidation();
        $this->reset(['personnel_name', 'personnel_type_id']);
        $this->personnel_name = $this->search; // Pre-fill with search term
        $this->showAddPersonnelModal = true;
    }

    public function addPersonnel()
    {
        $this->validate();

        Personnel::create([
            'personnel_name' => $this->personnel_name,
            'personnel_type_id' => $this->personnel_type_id,
        ]);

        $this->showAddPersonnelModal = false;
        session()->flash('message', 'Personnel added successfully.');
        $this->reset(['search', 'personnelTypeFilter']); // Reset filters
        $this->resetPage(); // Reset pagination to show new personnel
    }

    public function openEditPersonnelModal($id)
    {
        $this->resetValidation();
        $personnel = Personnel::findOrFail($id);
        $this->selectedPersonnelId = $personnel->id;
        $this->edit_personnel_name = $personnel->personnel_name;
        $this->edit_personnel_type_id = $personnel->personnel_type_id;
        $this->showEditPersonnelModal = true;
    }

    public function updatePersonnel()
    {
        $this->validate([
            'edit_personnel_name' => 'required|string|max:255',
            'edit_personnel_type_id' => 'required|exists:personnel_types,id',
        ]);

        $personnel = Personnel::findOrFail($this->selectedPersonnelId);
        $personnel->update([
            'personnel_name' => $this->edit_personnel_name,
            'personnel_type_id' => $this->edit_personnel_type_id,
        ]);

        session()->flash('message', 'Personnel updated successfully.');
        $this->showEditPersonnelModal = false;
        $this->resetPage();
    }

    public function confirmDeletePersonnel($id)
    {
        $this->resetValidation();
        $this->personnelToDeleteId = $id;
        $this->showDeleteConfirmationModal = true;
    }

    public function deletePersonnel()
    {
        Personnel::destroy($this->personnelToDeleteId);

        session()->flash('message', 'Personnel deleted successfully.');
        $this->showDeleteConfirmationModal = false;
        $this->resetPage();
    }

    public function render()
    {
        $query = Personnel::query();

        if ($this->search) {
            $query->where('personnel_name', 'like', '%' . $this->search . '%');
        }

        if ($this->personnelTypeFilter) {
            $query->where('personnel_type_id', $this->personnelTypeFilter);
        }

        $personnel = $query->paginate(10);

        return view('livewire.personnel-list', [
            'personnel' => $personnel,
        ]);
    }
}
