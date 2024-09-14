<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Role;

new class extends Component {
    public $userId, $name, $email, $role_id;
    public $roles = [];

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'role_id' => 'required|exists:roles,id',
    ];

    public function mount($userId): void
    {
        $user = User::findOrFail($userId);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_id = $user->role_id;

        if (auth()->user()->role->name === 'Super Admin') {
            // If the logged-in user is a Super Admin, retrieve all roles
            $this->roles = Role::all()->pluck('name', 'id');
        } else {
            // If the logged-in user is not a Super Admin, retrieve all roles except 'Super Admin'
            $this->roles = Role::where('name', '!=', 'Super Admin')->pluck('name', 'id');
        }

    }

    public function update(): void
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'role_id' => $this->role_id,
        ]);

        // Dispatch the "saved" event
        $this->dispatch('saved');
        $this->resetFields();
    }

    private function resetFields(): void
    {
        $this->reset(['userId', 'name', 'email', 'role_id']);
        $this->dispatch('hideEdit');
    }
};

?>

<form wire:submit="update" class="space-y-4 w-full">
    <div class="w-full">
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" type="text" wire:model.defer="name" class="w-full" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" type="email" wire:model.defer="email" class="w-full" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="role_id" value="Role" />
        <x-select-input
            id="role_id"
            name="role_id"
            wire:model.defer="role_id"
            :options="$roles"
            value="{{ old('role_id', $role_id) }}"
            class="w-full"
        />
        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
    </div>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">Update</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideEdit')">Cancel</x-secondary-button>
    </div>
</form>
