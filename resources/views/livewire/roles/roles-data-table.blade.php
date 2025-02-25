<?php

use App\Models\Role;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $isCreating = false;
    public $isEditing = false;
    public $roleId;
    public $roleIdBeingDeleted;

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
        $this->roleId = $id;
        $this->isEditing = true;
    }

    public function confirmDeleteRole($roleId)
    {
        // Set ID role yang akan dihapus
        $this->roleIdBeingDeleted = $roleId;
    }

    public function deleteConfirmedRole()
    {
        if (!Auth::user()->hasPermission('Delete Roles')) {
            abort(403, 'Unauthorized');
        }

        // Find the role to delete
        $role = Role::find($this->roleIdBeingDeleted);

        if ($role->name === 'Super Admin') {
            // Cancel deletion and show error message
            session()->flash('error', 'Cannot edit the Super Admin role.');
            return;
        }

        // Detach all permissions before deleting
        $role->permissions()->detach();
        $role->delete();

        // Reset values after deletion
        $this->reset(['roleIdBeingDeleted', 'roleId']);

        // Trigger event for notification
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
    <div class="mb-4 w-full">
        <!-- Success Message -->
        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        @if ($isCreating && Auth::user()->hasPermission('Create Roles'))
            <!-- Livewire component for creating a role -->
            <livewire:roles.create-role-table />
        @elseif($isEditing && Auth::user()->hasPermission('Edit Roles'))
            <!-- Livewire component for editing a role -->
            <livewire:roles.edit-role-table :roleId="$roleId" />
        @else
            @if (Auth::user()->hasPermission('Create Roles'))
                <!-- Button to create a new user -->
                <x-green-button class="mb-4" wire:click="create">
                    {{ __('Create Role') }}
                </x-green-button>
            @endif

            <!-- Role Tables -->
            <div class="w-full overflow-x-auto bg-white dark:bg-gray-900">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                scope="col">
                                {{ __('Name') }}
                            </th>
                            @if (Auth::user()->role->name === 'Super Admin' ||
                                    Auth::user()->role->name === 'Admin' ||
                                    Auth::user()->role->name === 'HRD')
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    scope="col">
                                    {{ __('ID') }}
                                </th>
                            @endif
                            @if (Auth::user()->hasPermission('Edit Roles') || Auth::user()->hasPermission('Delete Roles'))
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                    scope="col">
                                    {{ __('Actions') }}
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        @php
                            $roles = Role::paginate(10);
                        @endphp

                        @if ($roles->count() > 0)
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        {{ $role->name }}
                                    </td>
                                    @if (Auth::user()->role->name === 'Super Admin' ||
                                            Auth::user()->role->name === 'Admin' ||
                                            Auth::user()->role->name === 'HRD')
                                        <td class="cursor-pointer whitespace-nowrap px-6 py-4"
                                            onclick="copyToClipboard('{{ $role->id }}')">
                                            <div x-data="{ showNotif: false }"
                                                x-on:click="showNotif = true; setTimeout(() => showNotif = false, 2000)">
                                                <span>{{ $role->id }}</span>
                                                <div class="fixed left-1/2 top-4 -translate-x-1/2 transform rounded bg-green-500 px-4 py-2 text-white shadow"
                                                    x-show="showNotif"
                                                    x-transition:enter="transition ease-out duration-300"
                                                    x-transition:enter-start="opacity-0"
                                                    x-transition:enter-end="opacity-100"
                                                    x-transition:leave="transition ease-in duration-300"
                                                    x-transition:leave-start="opacity-100"
                                                    x-transition:leave-end="opacity-0">
                                                    Copied!
                                                </div>
                                            </div>
                                        </td>
                                    @endif
                                    @if (Auth::user()->hasPermission('Edit Roles') || Auth::user()->hasPermission('Delete Roles'))
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                            @if ($role->name !== 'Super Admin' && Auth::user()->hasPermission('Edit Roles'))
                                                <x-secondary-button wire:click="edit('{{ $role->id }}')">
                                                    {{ __('Edit') }}
                                                </x-secondary-button>
                                            @endif

                                            @if ($role->name !== 'Super Admin' && Auth::user()->hasPermission('Delete Roles'))
                                                <x-danger-button class="ms-2"
                                                    wire:click="confirmDeleteRole('{{ $role->id }}')" x-data
                                                    x-on:click="$dispatch('open-modal', { name: 'confirm-role-deletion' })">
                                                    {{ __('Delete') }}
                                                </x-danger-button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium text-gray-500 dark:text-gray-400"
                                    colspan="5">
                                    {{ __('No Data Available') }}
                                </td>
                            </tr>
                        @endif
                        <!-- Include Modal Component -->
                        <x-modal name="confirm-role-deletion">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('Are you sure you want to delete this role?') }}</h2>
                                <div class="mt-4 flex justify-end">
                                    <!-- Tombol Batal -->
                                    <x-secondary-button
                                        x-on:click="$dispatch('close-modal', { name: 'confirm-role-deletion' })">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>

                                    <!-- Tombol Konfirmasi Delete -->
                                    <x-danger-button class="ml-3" wire:click="deleteConfirmedRole"
                                        x-on:click="$dispatch('close-modal', { name: 'confirm-role-deletion' })">
                                        {{ __('Delete') }}
                                    </x-danger-button>
                                </div>
                            </div>
                        </x-modal>

                    </tbody>
                </table>
                <!-- Pagination Links -->
                <div class="mt-4">
                    {{ Role::paginate(10)->links() }}
                </div>
        @endif
    </div>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text);
        }
    </script>
</div>
