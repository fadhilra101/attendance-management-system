<?php

use App\Models\Permission;
use Livewire\Volt\Component;

new class extends Component
{
    public $permId, $name, $desc;

    public function mount($permId): void
    {
        $perm = Permission::findOrFail($permId);

        $this->permId = $perm->id;
        $this->name = $perm->name;
        $this->desc = $perm->desc;
    }

    public function update(): void
    {
        if (!Auth::user()->hasPermission('Edit Permissions')) {
            abort(403, 'Unauthorized');
        }

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'desc' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $perm = Permission::findOrFail($this->permId);

        $perm->update(['name' => $this->name, 'desc' => $this->desc]);

        // Dispatch the "saved" event
        $this->dispatch('saved');
        $this->resetFields();
    }

    private function resetFields(): void
    {
        $this->reset(['permId', 'name', 'desc']);
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
        <x-input-label for="desc" value="Desc" />
        <x-text-input id="desc" type="text" wire:model.defer="desc" class="w-full" />
        <x-input-error :messages="$errors->get('desc')" class="mt-2" />
    </div>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideEdit')">{{ __('Cancel') }}</x-secondary-button>
    </div>
</form>
