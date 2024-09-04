<?php

use Livewire\Volt\Component;

use App\Models\Role;

new class extends Component {
    public $roleId, $name;
    public $roles = [];

    protected $rules = [
        'name' => 'required|string|max:255'
    ];

    public function mount($roleId): void
    {
        $role = Role::findOrFail($roleId);

        $this->roleId = $role->id;
        $this->name = $role->name;
    }

    public function update(): void
    {
        if (!Auth::user()->hasPermission('Edit Roles')) {
            abort(403, 'Unauthorized');
        }
        // Validate
        $this->validate();

        $role = Role::findOrFail($this->roleId);

        if ($role->name === 'Super Admin') {
        // Batalkan perubahan dan tampilkan pesan error
        session()->flash('error', 'Cannot edit the Super Admin role.');
        return;
    }

        $role->update(['name' => $this->name]);

        // Dispatch the "saved" event
        $this->dispatch('saved');
        $this->resetFields();
    }

    private function resetFields(): void
    {
        $this->reset(['roleId', 'name']);
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

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideEdit')">{{ __('Cancel') }}</x-secondary-button>
    </div>
</form>
