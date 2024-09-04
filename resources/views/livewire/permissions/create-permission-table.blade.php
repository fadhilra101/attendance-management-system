<?php

use Livewire\Volt\Component;
use App\Models\Permission;

new class extends Component
{
    public $name, $desc;

    public function store(): void
    {
        if (!Auth::user()->hasPermission('Create Permissions')) {
            abort(403, 'Unauthorized');
        }

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:permissions,name'
            ],
            'desc' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        Permission::create($validated);

        $this->dispatch('saved');
        $this->resetFields();
    }

    private function resetFields(): void
    {
        $this->reset('name', 'desc');
        $this->dispatch('hideCreate');
    }
};
?>

<form wire:submit="store" class="space-y-4 w-full">
    <div class="w-full">
        <x-input-label for="name" value="Name" class="mb-2" />
        <x-text-input id="name" type="text" wire:model.defer="name" class="w-full" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="desc" value="Desc" class="mb-2" />
        <x-text-input id="desc" type="text" wire:model.defer="desc" class="w-full" />
        <x-input-error :messages="$errors->get('desc')" class="mt-2" />
    </div>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Create') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideCreate')">{{ __('Cancel') }}</x-secondary-button>
    </div>
</form>
