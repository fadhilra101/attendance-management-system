<?php

use App\Models\Office;
use Livewire\Volt\Component;

new class extends Component
{
    public $officeId, $name, $address, $gps_lat, $gps_lng;

    public function mount($officeId): void
    {
        $office = Office::findOrFail($officeId);

        $this->officeId = $office->id;
        $this->name = $office->name;
        $this->address = $office->address;
        $this->gps_lat = $office->gps_lat;
        $this->gps_lng = $office->gps_lng;
    }

    public function update(): void
    {
        if (!Auth::user()->hasPermission('Edit Offices')) {
            abort(403, 'Unauthorized');
        }

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
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

        $office = Office::findOrFail($this->officeId);

        $office->update([
            'name' => $this->name,
            'address' => $this->address,
            'gps_lat' => $this->gps_lat,
            'gps_lng' => $this->gps_lng,
        ]);

        // Dispatch the "saved" event
        $this->dispatch('saved');
        $this->resetFields();
    }

    private function resetFields(): void
    {
        $this->reset(['officeId', 'name', 'address', 'gps_lat', 'gps_lng']);
        $this->dispatch('hideEdit');
    }
}
?>

<form wire:submit="update" class="space-y-4 w-full">
    <div class="w-full">
        <x-input-label for="name" value="Name" />
        <x-text-input id="name" type="text" wire:model.defer="name" class="w-full" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="address" value="Address" />
        <x-text-input id="address" type="text" wire:model.defer="address" class="w-full" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="gps_lat" value="Gps Latitude" />
        <x-text-input id="gps_lat" type="text" wire:model.defer="gps_lat" class="w-full" />
        <x-input-error :messages="$errors->get('gps_lat')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="gps_lng" value="Gps Longitude" />
        <x-text-input id="gps_lng" type="text" wire:model.defer="gps_lng" class="w-full" />
        <x-input-error :messages="$errors->get('gps_lng')" class="mt-2" />
    </div>

    <x-blue-button
        type="button"
        wire:click="getGPS"
        class="mb-2">
        {{ __('Get GPS ') }}
    </x-blue-button>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Update') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideEdit')">{{ __('Cancel') }}</x-secondary-button>
    </div>
