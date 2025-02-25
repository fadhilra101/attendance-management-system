<?php

use Livewire\Volt\Component;
use App\Models\Office;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

new class extends Component {
    public $name, $address, $gps_lat, $gps_lng;

    public function store(): void
    {
        if (!Auth::user()->hasPermission('Create Offices')) {
            abort(403, 'Unauthorized');
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:offices,name'],
            'address' => ['required', 'string', 'max:255'],
            'gps_lat' => ['required', 'numeric', 'between:-90,90'],
            'gps_lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $office = Office::create($validated);

        $this->dispatch('saved');
        $this->resetFields();
        $this->isCreating = false;
    }

    public function getGPS(): void
    {
        $this->js(
            <<<'JS'
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        $wire.set('gps_lat', position.coords.latitude);
                        $wire.set('gps_lng', position.coords.longitude);
                    },
                    (error) => {
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                alert('User denied the request for Geolocation');
                                break;
                            case error.POSITION_UNAVAILABLE:
                                alert('Location information is unavailable');
                                break;
                            case error.TIMEOUT:
                                alert('The request to get user location timed out');
                                break;
                            default:
                                alert('An unknown error occurred');
                                break;
                        }
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    }
                );
            JS
            ,
        );
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
        <x-text-input id="name" type="text" wire:model="name" class="w-full" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="address" value="Address" class="mb-2" />
        <x-text-input id="address" type="text" wire:model="address" class="w-full" />
        <x-input-error :messages="$errors->get('address')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="gps_lat" value="GPS Lat" class="mb-2" />
        <x-text-input id="gps_lat" type="text" wire:model="gps_lat" class="w-full" />
        <x-input-error :messages="$errors->get('gps_lat')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="gps_lng" value="GPS Lng" class="mb-2" />
        <x-text-input id="gps_lng" type="text" wire:model="gps_lng" class="w-full" />
        <x-input-error :messages="$errors->get('gps_lng')" class="mt-2" />
    </div>

    <x-blue-button type="button" wire:click="getGPS" class="mb-2">
        {{ __('Get GPS') }}
    </x-blue-button>
    <div wire:loading wire:target="getGPS" class="flex items-center gap-2 text-blue-600 animate-pulse mt-1 mb-3">
        <svg class="w-5 h-5 animate-spin" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <span class="text-sm font-medium">Getting your location...</span>
    </div>

    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Create') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideCreate')">{{ __('Cancel') }}</x-secondary-button>
    </div>

</form>
