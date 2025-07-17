<div class="chat-root">
  <!-- Header -->
  <div class="relative mb-6 w-full">
    <flux:heading size="xl" level="1">{{ __('Chat Page') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading>
    <flux:separator variant="subtle" />
  </div>

  <div class="flex h-[550px] text-sm border rounded-x1 shadow overflow-hidden bg-black">
    <!-- User List -->
    <aside class="w-1/4 border-r bg-gray-50 flex flex-col">
      <div class="p-4 font-semibold text-gray-700 border-b text-base">CUSTOMERS</div>
      <div class="flex-1 overflow-y-auto divide-y">
        <ul>
          @foreach ($users as $user)
            <li
              wire:click="selectUser({{ $user->id }})"
              wire:key="user-{{ $user->id }}"
              class="p-2 cursor-pointer {{ $selectedUser && $selectedUser->id === $user->id ? 'font-bold bg-blue-200' : '' }}"
            >
              {{ $user->name }}
              <img
                src="{{ $selectedUser->avatar ? asset('storage/' . $selectedUser->avatar) :
                  'https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png' }}"
                alt="{{ $selectedUser->name }}"
                class="w-8 h-8 rounded-full object-cover mr-2 bg-gray-100"
              />
            </li>
          @endforeach
        </ul>
        @if ($selectedUser)
          <div class="mt-4 p-4 border rounded">
            <h2>Chatting with: {{ $selectedUser->name }}</h2>
            <p class="text-xs text-gray-500">{{ $selectedUser->email }}</p>
            <img
                src="{{ $selectedUser->avatar ? asset('storage/' . $selectedUser->avatar) :
                  'https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png' }}"
                alt="{{ $selectedUser->name }}"
                class="w-8 h-8 rounded-full object-cover mr-2 bg-gray-100"
              />
          </div>
        @else
          <p class="mt-4 italic">Select a User to start chatting.</p>
        @endif
      </div>
    </aside>

    <!-- Chat Section -->
    <main class="w-3/4 flex flex-col">
      <header class="p-4 border-b bg-gray-50">
        <h2 class="text-lg font-semibold text-gray-800">
          {{ $selectedUser ? $selectedUser->name : 'No user selected' }}
        </h2>
        @if ($selectedUser)
          <p class="text-xs text-gray-500">{{ $selectedUser->email }}</p>
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
            onerror="this.src='https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png';"
          >
        @endif

        @foreach ($messages as $msg)
          @php $isMine = $msg->sender_id === auth()->id(); @endphp
          <div class="chat-message flex items-end {{ $isMine ? 'justify-start' : 'justify-end' }}">
            @unless ($isMine)
            @endunless
            <img
                src="{{ $selectedUser->avatar ? asset('storage/' . $selectedUser->avatar) :
                  'https://cdn.pixabay.com/photo/2016/08/08/09/17/avatar-1577909_960_720.png' }}"
                alt="{{ $selectedUser->name }}"
                class="w-8 h-8 rounded-full object-cover mr-2 bg-gray-100"
              >
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

      <form wire:submit.prevent="submit" class="p-4 border-t bg-white flex items-center gap-2">
        <input
          wire:model.debounce.300ms="newMessage"
          @keydown="triggerTyping()"
          type="text"
          placeholder="Type your message…"
          class="flex-1 border border-gray-300 rounded-full px-4 py-2 text-sm focus:outline-none"
        />
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-full">
          Send
        </button>
      </form>
    </main>
  </div>
</div>

@push('scripts')
<script>
  function updateTimestamps() {
    document.querySelectorAll('.timestamp').forEach(el => {
      const dt = new Date(el.dataset.time);
      el.textContent = dt.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
    });
  }

  document.addEventListener('livewire:load', () => {
    updateTimestamps();
    setInterval(updateTimestamps, 60000);
    Livewire.hook('message.processed', updateTimestamps);

    let typingTimeout;

    window.triggerTyping = () => {
      Livewire.dispatch('typing');
      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(() => {
        Livewire.dispatch('stoppedTyping');
      }, 2000);
    };

    Livewire.on('user-typing', data => {
      Echo.private(`chat.${data.targetUser}`).whisper('typing', data);
    });

    Livewire.on('user-stopped-typing', data => {
      Echo.private(`chat.${data.targetUser}`).whisper('stopped-typing', data);
    });

    Echo.private(`chat.{{ $loggedID }}`)
      .listenForWhisper('typing', e => {
        document.getElementById('typing-indicator').innerText = `${e.userName} typing...`;
      })
      .listenForWhisper('stopped-typing', () => {
        document.getElementById('typing-indicator').innerText = '';
      });
  });
</script>
@endpush
