<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Permission;
use App\Models\Role;

new class extends Component
{
    use WithPagination;

    public $isCreating = false;
    public $isEditing = false;
    public $isAddRole = false;
    public $permId;
    public $permIdBeingDeleted;
    public $selectedRoles = [];

    protected $listeners = [
        'hideCreate' => 'handleHideCreate',
        'hideEdit' => 'handleHideEdit',
    ];

    public function mount()
    {
        $permissions = Permission::with('roles')->get();

        foreach ($permissions as $permission) {
            foreach ($permission->roles as $role) {
                $this->selectedRoles[$permission->id][$role->id] = true;
            }
        }
    }


    public function create(): void
    {
        if (!Auth::user()->hasPermission('Create Permissions')) {
            abort(403, 'Unauthorized');
        }

        $this->isCreating = true;
    }

    public function edit($id): void
    {
        if (!Auth::user()->hasPermission('Edit Permissions')) {
            abort(403, 'Unauthorized');
        }
        $this->isEditing = true;
        $this->permId = $id;
    }

    public function confirmDeletePerm($permId)
    {
        // Set ID perm yang akan dihapus
        $this->permIdBeingDeleted = $permId;
    }

    public function deleteConfirmedPerm()
    {
        if (!Auth::user()->hasPermission('Delete Permissions')) {
            abort(403, 'Unauthorized');
        }
        // Hapus perm setelah konfirmasi
        $perm = Permission::find($this->permIdBeingDeleted);

        $perm->delete();

        // Reset nilai setelah penghapusan
        $this->reset(['permIdBeingDeleted', 'permId']);

        // Trigger event untuk notifikasi atau action lain
        $this->dispatch('saved');
    }

    public function save()
    {
        if (!Auth::user()->hasPermission('Edit Permissions')) {
            abort(403, 'Unauthorized');
        }
        // Loop through each permission and its selected roles
        foreach ($this->selectedRoles as $permId => $roles) {
            // Get the Permission model
            $perm = Permission::find($permId);

            // Ensure that the permission exists
            if ($perm) {
                // Extract role IDs from the array of roles (filter only `true` values)
                $roleIds = array_keys(array_filter($roles, function($value) {
                    return $value === true;
                }));

                // Sync roles for the current permission
                // This will attach roles that are not in the pivot table
                // and detach roles that are no longer selected.
                $perm->roles()->sync($roleIds);
            }
        }

        // Trigger event to notify or perform other actions
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

        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        @if($isCreating && Auth::user()->hasPermission('Create Permissions'))
            <livewire:permissions.create-permission-table />
        @elseif($isEditing && Auth::user()->hasPermission('Edit Permissions'))
            <livewire:permissions.edit-permission-table :permId="$permId" />
        @else
        <div class="flex justify-between mb-4">
            @if(Auth::user()->hasPermission('Create Permissions'))
                <!-- Button Create Permission -->
                <x-green-button wire:click="create">
                    {{ __('Create Permission') }}
                </x-green-button>
            @endif

            <!-- Conditional Buttons: Save and Exit -->
            @if($isAddRole)
                <div class="flex gap-2">
                    <x-primary-button wire:click="save">
                        {{ __('Save') }}
                    </x-primary-button>

                    <x-secondary-button
                        wire:click="$set('isAddRole', false)">
                        {{ __('Exit') }}
                    </x-secondary-button>
                </div>
            @elseif(Auth::user()->hasPermission('Edit Permissions') || Auth::user()->hasPermission('Delete Permissions'))
                <x-secondary-button
                    wire:click="$set('isAddRole', true)"
                >
                    {{ __('Edit') }}
                </x-secondary-button>
            @else

            @endif
        </div>


            <div class="overflow-x-auto bg-white dark:bg-gray-900 w-full">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Name') }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Description') }}
                            </th>
                            @if($isAddRole)
                            <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider">
                                {{ __('Action') }}
                            </th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @foreach(Permission::with('roles')->paginate(10) as $perm)
                            <tr>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ $perm->name }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ $perm->desc }}
                                </td>
                                @if($isAddRole)
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <div class="flex flex-wrap gap-6">
                                        @foreach(Role::all() as $role)
                                            <div class="flex flex-col items-center">
                                                <input
                                                    type="checkbox"
                                                    wire:model.defer="selectedRoles.{{ $perm->id }}.{{ $role->id }}"
                                                    value="{{ $role->id }}"
                                                    class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                >
                                                <label class="text-sm font-thin text-gray-700 dark:text-gray-300">{{ $role->name }}</label>
                                            </div>
                                        @endforeach

                                        <x-secondary-button
                                            wire:click="edit('{{ $perm->id }}')">
                                            {{ __('Edit') }}
                                        </x-secondary-button>

                                        <x-danger-button
                                            wire:click="confirmDeletePerm({{ $perm->id }})"
                                            x-data
                                            x-on:click="$dispatch('open-modal', { name: 'confirm-perm-deletion' })"
                                            class="ms-2">
                                            {{ __('Delete') }}
                                        </x-danger-button>
                                    </div>
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        <!-- Include Modal Component -->
                        <x-modal name="confirm-perm-deletion">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Are you sure you want to delete this perm?') }}</h2>
                                <div class="mt-4 flex justify-end">
                                    <!-- Tombol Batal -->
                                    <x-secondary-button x-on:click="$dispatch('close-modal', { name: 'confirm-perm-deletion' })">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>

                                    <!-- Tombol Konfirmasi Delete -->
                                    <x-danger-button class="ml-3"
                                                     wire:click="deleteConfirmedPerm"
                                                     x-on:click="$dispatch('close-modal', { name: 'confirm-perm-deletion' })">
                                        {{ __('Delete') }}
                                    </x-danger-button>
                                </div>
                            </div>
                        </x-modal>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Links -->
            <div class="mt-2 px-3">
                {{ Permission::paginate(10)->links() }}
            </div>
        @endif
    </div>
</div>
