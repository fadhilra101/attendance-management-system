<?php

use Livewire\Volt\Component;

use App\Models\Role;
use App\Models\Permission;

new class extends Component {
    public $roleId, $name;
    public $roles = [];
    public $permissions = [];
    public $selectedPermissions = [];

    protected $rules = [
        'name' => 'required|string|max:255'
    ];

    public function mount($roleId): void
    {
        $role = Role::findOrFail($roleId);

        $this->permissions = Permission::all();
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();

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

        $role->permissions()->sync($this->selectedPermissions);

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

        <!-- Permissions Multi-Select -->
        <div class="w-full">
            <x-input-label for="permissions" value="Permissions" class="mb-2" />
            <div class="relative flex flex-wrap gap-4">
                @foreach($permissions as $perm)
                <div class="flex items-center space-x-2">
                    <input
                        type="checkbox"
                        id="permission-{{ $perm->id }}"
                        wire:model.live="selectedPermissions"
                        value="{{ $perm->id }}"
                        class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <label for="permission-{{ $perm->id }}" class="text-sm font-thin text-gray-700 dark:text-gray-300">
                        {{ $perm->name }}
                    </label>
                </div>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('selectedPermissions')" class="mt-2" />
            @if(count($selectedPermissions) > 0)
            <div class="mt-4">
                <x-secondary-button type="button" wire:click="resetPermissions">{{ __('Reset Selection') }}</x-secondary-button>
            </div>
            @endif
        </div>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideEdit')">{{ __('Cancel') }}</x-secondary-button>
    </div>
</form>
