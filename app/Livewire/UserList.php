<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\UserLevel;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public $userLevels;
    public $selectedUser;
    public $selectedUserLevel;
    public $showActivateModal = false;
    public $search = '';
    public $userLevelFilter = '';

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->userLevels = UserLevel::all();
    }

    public function openActivateModal(User $user)
    {
        $this->selectedUser = $user;
        $this->selectedUserLevel = $user->user_level_id;
        $this->showActivateModal = true;
    }

    public function updateUserLevel()
    {
        $this->validate([
            'selectedUserLevel' => 'required|exists:user_levels,id',
        ]);

        $this->selectedUser->update([
            'user_level_id' => $this->selectedUserLevel,
        ]);

        $this->showActivateModal = false;
        session()->flash('message', 'User activated successfully.');
    }

    public function render()
    {
        $query = User::query();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        }

        if ($this->userLevelFilter) {
            $query->where('user_level_id', $this->userLevelFilter);
        }

        $users = $query->paginate(10);

        return view('livewire.user-list', [
            'users' => $users,
        ]);
    }
}
