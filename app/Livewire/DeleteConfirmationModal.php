<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class DeleteConfirmationModal extends Component
{
    public $show = false;
    public $password;
    public $model;
    public $modelId;

    protected $listeners = ['showDeleteModal'];

    public function showDeleteModal($model, $id)
    {
        $this->model = $model;
        $this->modelId = $id;
        $this->show = true;
    }

    public function confirmDelete()
    {
        $this->validate([
            'password' => 'required',
        ]);

        $admins = User::where('user_level_id', 1)->get();
        $passwordCorrect = false;

        foreach ($admins as $admin) {
            if (Hash::check($this->password, $admin->password)) {
                $passwordCorrect = true;
                break;
            }
        }

        if ($passwordCorrect) {
            $modelClass = "App\\Models\\" . $this->model;
            $item = $modelClass::findOrFail($this->modelId);
            $item->delete();

            $this->show = false;
            $this->password = null;
            $this->dispatch('itemDeleted');
            $this->dispatch('notify', 'Item deleted successfully!');
        } else {
            $this->addError('password', 'The provided password does not match an administrator\'s password.');
        }
    }

    public function render()
    {
        return view('livewire.delete-confirmation-modal');
    }
}
