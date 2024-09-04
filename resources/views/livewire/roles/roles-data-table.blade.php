<?php

use App\Models\Role;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
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
        
        // Hapus role setelah konfirmasi
        $role = Role::find($this->roleIdBeingDeleted);

        if ($role->name === 'Super Admin') {
        // Batalkan perubahan dan tampilkan pesan error
        session()->flash('error', 'Cannot edit the Super Admin role.');
        return;
    }

        $role->delete();

        // Reset nilai setelah penghapusan
        $this->reset(['roleIdBeingDeleted', 'roleId']);

        // Trigger event untuk notifikasi atau action lain
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
    <div class="mb-4 w-full">
        <!-- Success Message -->
        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        @if($isCreating && Auth::user()->hasPermission('Create Roles'))
            <!-- Livewire component for creating a role -->
            <livewire:roles.create-role-table/>
        @elseif($isEditing && Auth::user()->hasPermission('Edit Roles'))
            <!-- Livewire component for editing a role -->
            <livewire:roles.edit-role-table :roleId="$roleId" />
        @else

             @if(Auth::user()->hasPermission('Create Roles'))
                <!-- Button to create a new user -->
                <x-green-button
                    wire:click="create" class="mb-4">
                    {{ __('Create Role') }}
                </x-green-button>
             @endif

             <!-- Role Tables -->
             <div class="overflow-x-auto bg-white dark:bg-gray-900 w-full">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Name') }}
                            </th>
                            @if(Auth::user()->hasPermission('Edit Roles') || Auth::user()->hasPermission('Delete Roles'))
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach(Role::paginate(10) as $role)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $role->name }}
                                </td>
                                @if(Auth::user()->hasPermission('Edit Roles') || Auth::user()->hasPermission('Delete Roles'))
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(Auth::user()->hasPermission('Edit Roles'))
                                        <x-secondary-button
                                            wire:click="edit('{{ $role->id }}')">
                                            {{ __('Edit') }}
                                        </x-secondary-button>
                                        @endif

                                        @if(Auth::user()->hasPermission('Delete Roles'))
                                        <x-danger-button
                                            wire:click="confirmDeleteRole('{{ $role->id }}')"
                                            x-data
                                            x-on:click="$dispatch('open-modal', { name: 'confirm-role-deletion' })"
                                            class="ms-2">
                                            {{ __('Delete') }}
                                        </x-danger-button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        <!-- Include Modal Component -->
                        <x-modal name="confirm-role-deletion">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Are you sure you want to delete this role?') }}</h2>
                                <div class="mt-4 flex justify-end">
                                    <!-- Tombol Batal -->
                                    <x-secondary-button x-on:click="$dispatch('close-modal', { name: 'confirm-role-deletion' })">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>

                                    <!-- Tombol Konfirmasi Delete -->
                                    <x-danger-button class="ml-3"
                                                     wire:click="deleteConfirmedRole"
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
</div>
