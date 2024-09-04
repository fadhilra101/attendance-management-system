<?php

use Livewire\Volt\Component;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

new class extends Component {
    public $name;

    public function store(): void
    {
        if (!Auth::user()->hasPermission('Create Roles')) {
            abort(403, 'Unauthorized');
        }

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name'
            ]]);

        $role = Role::create($validated);

        $this->dispatch('saved');
        $this->resetFields();
        $this->isCreating = false;
    }

    private function resetFields(): void
    {
        $this->reset('name');
        $this->dispatch('hideCreate');
        $this->showPassword = false;
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
    }
};

?>

<form wire:submit="store" class="space-y-4 w-full">
    <div class="w-full">
        <x-input-label for="name" value="Name" class="mb-2" />
        <x-text-input id="name" type="text" wire:model.defer="name" class="w-full" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Create') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideCreate')">{{ __('Cancel') }}</x-secondary-button>
    </div>

</form>

