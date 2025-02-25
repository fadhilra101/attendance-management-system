<?php

use App\Models\Office;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
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
};
?>

<div class="w-full">
    <div class="w-full mb-4">
        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        @if ($isCreating && Auth::user()->hasPermission('Create Offices'))
            <livewire:offices.create-office-table />
        @elseif($isEditing && Auth::user()->hasPermission('Edit Offices'))
            <livewire:offices.edit-office-table :officeId="$officeId" />
        @else
            @if (Auth::user()->hasPermission('Create Offices'))
                <x-green-button wire:click="$set('isCreating', true)" class="mb-4">
                    {{ __('Create Office') }}
                </x-green-button>
            @endif

            <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-sm w-full">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Name') }}</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('Address') }}</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('GPS Latitude') }}</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                {{ __('GPS Longitude') }}</th>
                            @if (Auth::user()->hasPermission('Edit Offices') || Auth::user()->hasPermission('Delete Offices'))
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                        @php
                            $offices = Office::paginate(10);
                        @endphp

                        @if ($offices->count() > 0)
                            @foreach ($offices as $office)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $office->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $office->address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $office->gps_lat }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                        {{ $office->gps_lng }}
                                    </td>
                                    @if (Auth::user()->hasPermission('Edit Offices') || Auth::user()->hasPermission('Delete Offices'))
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                            @if (Auth::user()->hasPermission('Edit Offices'))
                                                <x-secondary-button wire:click="edit({{ $office->id }})"
                                                    class="transition ease-in-out duration-150 hover:scale-105">
                                                    {{ __('Edit') }}
                                                </x-secondary-button>
                                            @endif
                                            @if (Auth::user()->hasPermission('Delete Offices'))
                                                <x-danger-button
                                                    wire:click="confirmOfficeDeletion({{ $office->id }})"
                                                    x-on:click="$dispatch('open-modal', { name: 'confirm-office-deletion'})"
                                                    class="transition ease-in-out duration-150 hover:scale-105">
                                                    {{ __('Delete') }}
                                                </x-danger-button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            <x-modal name="confirm-office-deletion">
                                <div class="p-6 bg-white dark:bg-gray-800">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ __('Are you sure you want to delete this office?') }}
                                    </h2>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('Once deleted, all data associated with this office will be permanently removed.') }}
                                    </p>

                                    <div class="mt-6 flex justify-end space-x-3">
                                        <x-secondary-button
                                            x-on:click="$dispatch('close-modal', { name: 'confirm-office-deletion' })"
                                            class="transition ease-in-out duration-150 hover:scale-105">
                                            {{ __('Cancel') }}
                                        </x-secondary-button>

                                        <x-danger-button wire:click="deleteOffice"
                                            x-on:click="$dispatch('close-modal', { name: 'confirm-office-deletion' })"
                                            class="transition ease-in-out duration-150 hover:scale-105">
                                            {{ __('Delete Office') }}
                                        </x-danger-button>
                                    </div>
                                </div>
                            </x-modal>
                        @else
                            <tr>
                                <td colspan="5"
                                    class="px-6 py-8 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        {{ __('No Offices Available') }}
                                    </div>
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
