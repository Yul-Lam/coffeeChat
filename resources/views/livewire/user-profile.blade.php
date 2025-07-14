<div>
    <form wire:submit.prevent="updatedAvatar">
    <input type="file" wire:model="avatar">
    @error('avatar') <span class="error">{{ $message }}</span> @enderror
    <button type="submit">Upload Avatar</button>
</form>

</div>
