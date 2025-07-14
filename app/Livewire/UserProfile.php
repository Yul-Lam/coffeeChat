<?php

namespace App\Livewire;

use Livewire\Component;

class UserProfile extends Component
{
    
    use WithFileUploads;

    public $avatar;

    public function updatedAvatar()
    {
        $this->validate([
            'avatar' => 'image|max:1024', // 1MB Max
        ]);

        $path = $this->avatar->store('avatars', 'public');

        Auth::user()->update(['avatar' => $path]);
    }

    public function render()
    {
        return view('livewire.user-profile');
    }
}

