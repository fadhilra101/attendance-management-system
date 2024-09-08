<?php

use App\Models\Office;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $isCreating = false;
    public $isEditing = false;
    public $officeId;

    protected $listeners = [
        'hideCreate' => 'handleHideCreate',
        'hideEdit' => 'handleHideEdit',
    ];

    public function create(): void
    {
        $this->isCreating = true;
    }

    public function edit($id): void
    {
        if (!Auth::user()->hasPermission('Edit Offices')) {
            abort(403, 'Unauthorized');
        }

        $this->officeId = $id;
        $this->isEditing = true;
    }

    public function confirmOfficeDeletion($id)
    {
        // Set ID office yang akan dihapus
        $this->officeId = $id;
    }

    public function deleteOffice(): void
    {
        Office::find($this->officeId)->delete();

        // Reset the officeId after deletion
        $this->reset('officeId');
        $this->dispatch('saved');
    }

    public function handleHideCreate(): void
    {
        $this->isCreating = false;
    }

    public function handleHideEdit(): void
    {
        $this->isEditing = false;
    }

}
?>

<div class="w-full">
    <div class="w-full mb-4">
        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        @if($isCreating && Auth::user()->hasPermission('Create Offices'))

            <livewire:offices.create-office-table />

        @elseif($isEditing && Auth::user()->hasPermission('Edit Offices'))

            <livewire:offices.edit-office-table :officeId="$officeId" />

        @else

            @if(Auth::user()->hasPermission('Create Offices'))

                <x-green-button wire:click="$set('isCreating', true)" class="mb-4">
                    {{ __('Create Office') }}
                </x-green-button>

            @endif

            <div class="overflow-x-auto bg-white dark:bg-gray-900 w-full">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-500 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Address') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('GPS Latitude') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('GPS Longitude') }}</th>
                            @if(Auth::user()->hasPermission('Edit Offices') || Auth::user()->hasPermission('Delete Offices'))
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @php
                            $offices = Office::paginate(10);
                        @endphp

                        @if($offices->count() > 0)
                            @foreach($offices as $office)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $office->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $office->address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $office->gps_lat }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $office->gps_lng }}
                                    </td>
                                    @if(Auth::user()->hasPermission('Edit Offices') || Auth::user()->hasPermission('Delete Offices'))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            @if(Auth::user()->hasPermission('Edit Offices'))
                                                <x-secondary-button wire:click="edit({{ $office->id }})" class="mr-2">
                                                    {{ __('Edit') }}
                                                </x-secondary-button>
                                            @endif
                                            @if(Auth::user()->hasPermission('Delete Offices'))
                                                <x-danger-button
                                                    wire:click="confirmOfficeDeletion({{ $office->id }})"
                                                    x-on:click="$dispatch('open-modal', { name: 'confirm-office-deletion'})">
                                                    {{ __('Delete') }}
                                                </x-danger-button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            <x-modal name="confirm-office-deletion">
                                <div class="p-6">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Are you sure you want to delete this office?') }}</h2>

                                    <div class="mt-4 flex justify-end">
                                        <!-- Cancel Button -->
                                        <x-secondary-button x-on:click="$dispatch('close-modal', { name: 'confirm-office-deletion' })">
                                            {{ __('Cancel') }}
                                        </x-secondary-button>

                                        <!-- Confirm Delete Button -->
                                        <x-danger-button class="ml-3"
                                        wire:click="deleteOffice({{ $officeId }})"
                                        x-on:click="$dispatch('close-modal', { name: 'confirm-office-deletion' })">
                                            {{ __('Delete') }}
                                        </x-danger-button>
                                    </div>
                                </div>
                            </x-modal>

                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ __('No Data Available') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>

                </table>
                {{ $offices->links() }} <!-- Pagination Links -->
            </div>

        @endif

    </div>
</div>
