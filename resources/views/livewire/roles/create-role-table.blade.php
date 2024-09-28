<?php

use Livewire\Volt\Component;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

new class extends Component {
    public $name;
    public $selectedPermissions = [];
    public $permissions = [];

    public function mount(): void
    {
        $this->permissions = Permission::all();
    }

    public function store(): void
    {
        if (!Auth::user()->hasPermission('Create Roles')) {
            abort(403, 'Unauthorized');
        }

        // Validate role name and permissions
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name'
            ],
            'selectedPermissions' => [
                'required',
                'array',
                'exists:permissions,id'
            ],
        ]);

        // Create a new role
        $role = Role::create(['name' => $this->name]);

        // Attach selected permissions to the role
        $role->permissions()->sync($this->selectedPermissions);

        // Dispatch success message and reset fields
        $this->dispatch('saved');
        $this->resetFields();
        $this->isCreating = false;
    }

    private function resetFields(): void
    {
        $this->reset('name', 'selectedPermissions');
        $this->dispatch('hideCreate');
        $this->showPassword = false;
    }

    public function resetPermissions(): void
    {
        $this->reset('selectedPermissions');
    }

};

?>

<form wire:submit="store" class="space-y-4 w-full">
    <div class="w-full">
        <x-input-label for="name" value="Name" class="mb-2" />
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
        <x-primary-button type="submit">{{ __('Create') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideCreate')">{{ __('Cancel') }}</x-secondary-button>
    </div>

</form>

