<?php

use Livewire\Volt\Component;
use App\Models\Office;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

new class extends Component {
    public $name, $address, $gps_lat, $gps_lng;

    public function store(): void
    {
        if (!Auth::user()->hasPermission('Create Office')) {
            abort(403, 'Unauthorized');
        }

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:offices,name'
            ],

            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'gps_lat' => [
                'required',
                'string',
                'max:255',
            ],

            'gps_lng' => [
                'required',
                'string',
                'max:255',
            ],

        ]);

        $office = Office::create($validated);

        $this->dispatch('saved');
        $this->resetFields();
        $this->isCreating = false;
    }

    private function resetFields(): void
    {
        $this->reset(['name', 'address', 'gps_lat', 'gps_lng']);
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
        <x-input-label for="address" value="Address" class="mb-2" />
        <x-text-input id="address" type="text" wire:model.defer="address" class="w-full" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="gps_lat" value="GPS Lat" class="mb-2" />
        <x-text-input id="gps_lat" type="text" wire:model.defer="gps_lat" class="w-full" />
        <x-input-error :messages="$errors->get('gps_lat')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="gps_lng" value="GPS Lng" class="mb-2" />
        <x-text-input id="gps_lng" type="text" wire:model.defer="gps_lng" class="w-full" />
        <x-input-error :messages="$errors->get('gps_lng')" class="mt-2" />
    </div>

    <x-blue-button
        type="button"
        wire:click="getGPS"
        class="mb-2">
        {{ __('Get GPS') }}
    </x-blue-button>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Create') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideCreate')">{{ __('Cancel') }}</x-secondary-button>
    </div>

</form>

