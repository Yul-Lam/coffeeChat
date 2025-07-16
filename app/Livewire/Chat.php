<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
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
    public $typingUsers = [];

    protected function getListeners(): array
    {
        return [
            'typing' => 'onTyping',
            'stoppedTyping' => 'onStoppedTyping',
            "echo-private:chat.{$this->loggedID},.client-typing"   => 'incomingTyping',
            "echo-private:chat.{$this->loggedID},.client-stopped-typing" => 'incomingStopped',
        ];
    }

    public function mount()
    {
        $this->loggedID = Auth::id();
        $this->users = User::where('id', '!=', $this->loggedID)->get();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
    }

    public function selectUser($id)
    {
        $this->selectedUser = User::find($id);
        $this->typingUsers = [];
        $this->loadMessages();
    }

    public function updatedNewMessage()
    {
        $this->dispatch('typing');
    }

    public function onTyping()
    {
        $this->dispatch('user-typing', [
            'targetUser' => $this->selectedUser->id,
            'userId'     => $this->loggedID,
            'userName'   => Auth::user()->name,
        ]);
    }

    public function onStoppedTyping()
    {
        $this->dispatch('user-stopped-typing', [
            'targetUser' => $this->selectedUser->id,
            'userId'     => $this->loggedID,
        ]);
    }

    public function incomingTyping($data)
    {
        $this->typingUsers[$data['userId']] = $data['userName'];
    }

    public function incomingStopped($data)
    {
        unset($this->typingUsers[$data['userId']]);
    }

    public function loadMessages()
    {
        if (!$this->selectedUser) {
            $this->messages = [];
            return;
        }

        $this->messages = ChatMessage::where(fn($q) => $q
                ->where('sender_id', $this->loggedID)
                ->where('receiver_id', $this->selectedUser->id))
            ->orWhere(fn($q) => $q
                ->where('sender_id', $this->selectedUser->id)
                ->where('receiver_id', $this->loggedID))
            ->orderBy('created_at')
            ->get();
    }

    public function submit()
    {
        if (! $this->newMessage || ! $this->selectedUser) return;

        ChatMessage::create([
            'sender_id'   => $this->loggedID,
            'receiver_id' => $this->selectedUser->id,
            'message'     => $this->newMessage,
        ]);

        $this->newMessage = '';
        $this->dispatch('stoppedTyping');
        $this->loadMessages();
    }

    public function render()
    {
        return view('livewire.chat');
    }
}
