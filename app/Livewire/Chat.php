<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Auth;

class Chat extends Component
{
    public $users;
    public $selectedUser;
    public $newMessage = '';
    public $messages = [];
    public $loggedID;

    public function mount()
    {
        $this->loggedID = Auth::id();
        $this->users = User::where('id', '!=', $this->loggedID)->get();
        $this->selectedUser = $this->users->first();

        if ($this->selectedUser) {
            $this->loadMessages();
        }
    }

    public function selectUser($id)
    {
        $this->selectedUser = User::find($id);
        $this->loadMessages();
    }

    public function updatedNewMessage($value)
    {
        $this->dispatch(
            'userTyping',
            userID: $this->loggedID,
            userName: Auth::user()->name,
            selectedUserID: $this->selectedUser->id
        );
    }

    public function loadMessages()
    {
        if (!$this->selectedUser) {
            $this->messages = [];
            return;
        }

        $this->messages = ChatMessage::query()
            ->where(function ($q) {
                $q->where('sender_id', $this->loggedID)
                  ->where('receiver_id', $this->selectedUser->id);
            })
            ->orWhere(function ($q) {
                $q->where('sender_id', $this->selectedUser->id)
                  ->where('receiver_id', $this->loggedID);
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function submit()
    {
        if (!$this->newMessage || !$this->selectedUser) {
            return;
        }

        ChatMessage::create([
            'sender_id' => $this->loggedID,
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
