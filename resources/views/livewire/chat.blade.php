<div class="chat-root">
  <div class="relative mb-6 w-full">
    <flux:heading size="xl" level="1">{{ __('Chat') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading>
    <flux:separator variant="subtle" />
  </div>

  <div class="flex h-[550px] text-sm border rounded-x1 shadow overflow-hidden bg-black">
    <!-- User List -->
    <aside class="w-1/4 border-r bg-gray-50 flex flex-col">
      <div class="p-4 font-semibold text-gray-700 border-b text-base">Users</div>
      <div class="flex-1 overflow-y-auto divide-y">
        <ul>
          @foreach ($users as $user)
            <li
              wire:click="selectUser({{ $user->id }})"
              wire:key="user-{{ $user->id }}"
              class="p-2 cursor-pointer {{ $selectedUser && $selectedUser->id === $user->id ? 'font-bold bg-blue-200' : '' }}"
            >
              {{ $user->name }}
            </li>
          @endforeach
        </ul>
        @if ($selectedUser)
          <div class="mt-4 p-4 border rounded">
            <h2>Chatting with: {{ $selectedUser->name }}</h2>
            <p class="text-gray-900 font-medium">{{ $selectedUser->name }}</p>
            <p class="text-xs text-gray-500">{{ $selectedUser->email }}</p>
          </div>
        @else
          <p class="mt-4 italic">Select a User to start chatting.</p>
        @endif
      </div>
    </aside>

    <!-- Chat Section -->
    <main class="w-3/4 flex flex-col">
      <header class="p-4 border-b bg-gray-50">
        @if ($selectedUser)
          <h2 class="text-lg font-semibold text-gray-800">{{ $selectedUser->name }}</h2>
          <p class="text-xs text-gray-500">{{ $selectedUser->email }}</p>
        @else
          <h2 class="text-lg font-semibold text-gray-800">No user selected</h2>
        @endif
      </header>

      <section class="flex-1 px-6 py-4 overflow-y-auto space-y-4 bg-gray-50">
        @if($selectedUser)
          <img
            src="{{ $selectedUser->avatar
              ? asset('storage/' . $selectedUser->avatar)
              : 'https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png' }}"
            alt="{{ $selectedUser->name }}"
            class="w-10 h-10 rounded-full bg-gray-200 object-cover"
            onerror="this.onerror=null; this.src='https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png';"
          >
        @endif

        <!-- Messages -->
@foreach ($messages as $msg)
  @php $isMine = $msg->sender_id === auth()->id(); @endphp

  <div class="chat-message flex items-end {{ $isMine ? 'justify-end' : 'justify-start' }}">
    <img
        src="{{ $selectedUser->avatar
          ? asset('storage/' . $selectedUser->avatar)
          : 'https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png' }}"
        alt="{{ $selectedUser->name }}"
        class="w-8 h-8 rounded-full object-cover mr-2 bg-gray-100"
        onerror="this.onerror=null; this.src='https://img.freepik.com/premium-vector/default-avatar-profile-icon-social-media-user-image-gray-avatar-icon-blank-profile-silhouette-vector-illustration_561158-3396.jpg';"
      >
    @unless ($isMine)
      
    @endunless

    <div class="chat-bubble px-4 py-2 rounded-2xl shadow max-w-sm
                {{ $isMine ? 'bg-blue-600 text-white rounded-bl-none' : 'bg-gray-200 text-black rounded-br-none' }}">
      {{ $msg->message }}
    </div>

    <span class="text-xs text-gray-500 ml-2 timestamp" data-time="{{ $msg->created_at->format('c') }}">
      {{ $msg->created_at->format('g:i A') }}
    </span>
  </div>
@endforeach

        <div id="typing-indicator" class="px-4 pb-1 text-xs text-gray-400 italic"></div>
      </section>

      <!-- Message Input -->
      <form wire:submit.prevent="submit" class="p-4 border-t bg-white flex items-center gap-2">
        <input
          wire:model.live="newMessage"
          type="text"
          placeholder="Type your message…"
          class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none"
        />
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-full transition">
          Send
        </button>
      </form>
    </main>
  </div>
</div>

<script>
  function updateTimestamps() {
    document.querySelectorAll('.timestamp').forEach(el => {
      const dt = new Date(el.getAttribute('data-time'));
      el.innerText = dt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour: true });
    });
  }

  document.addEventListener('livewire:load', () => {
    updateTimestamps();
    setInterval(updateTimestamps, 60000); // Update every minute
    Livewire.hook('message.processed', () => updateTimestamps());
  });

  document.addEventListener('livewire:initialized', () => {
    Livewire.on('userTyping', (event) => {
      window.Echo.private(`chat.${event.selectedUserID}`).whisper("typing", {
        userID: event.userID,
        userName: event.userName,
      });
    });

    window.Echo.private(`chat.{{ $loggedID }}`).listenForWhisper('typing', (event) => {
      const t = document.getElementById('typing-indicator');
      t.innerText = `${event.userName} is typing...`;
      setTimeout(() => t.innerText = '', 3000);
    });
  });
  // 1. Function to format all timestamps
  function updateTimestamps() {
    document.querySelectorAll('.timestamp').forEach(el => {
      const dt = new Date(el.dataset.time);
      el.textContent = dt.toLocaleTimeString([], {
        hour: 'numeric', minute: '2-digit', hour: true
      });
    });
  }

  // 2. Run on Livewire load
  document.addEventListener('livewire:load', () => {
    updateTimestamps();                          // initial formatting
    setInterval(updateTimestamps, 60000);       // refresh every minute

    // 3. Hook into Livewire Lifecycle for updates (sending/receiving messages)
    Livewire.hook('message.processed', () => {
      updateTimestamps();
    });
  });

</script>
