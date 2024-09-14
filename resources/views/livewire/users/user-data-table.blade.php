<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Role;

new class extends Component {
    use WithPagination;

    public $isCreating = false;
    public $isEditing = false;
    // public $confirmDelete = false;
    public $userId;

    protected $listeners = [
        'hideCreate' => 'handleHideCreate',
        'hideEdit' => 'handleHideEdit',
        // 'open-modal' => 'handleConfirmDelete'
    ];

    public function create(): void
    {
        $this->isCreating = true;
    }

    public function edit($id): void
    {
        // Pastikan pengguna memiliki izin saat komponen dimuat
        if (!Auth::user()->hasPermission('Edit Users')) {
            abort(403, 'Unauthorized');
        }

        $this->userId = $id;
        $this->isEditing = true;
    }

    public function verify($id): void
    {
        // Pastikan pengguna memiliki izin saat komponen dimuat
        if (!Auth::user()->hasPermission('Edit Users')) {
            abort(403, 'Unauthorized');
        }

        User::find($id)->update([
            'verified_at' => now()
        ]);

        $this->dispatch('saved');
    }

    public function deleteUser(): void
    {
        User::find($this->userId)->delete();

        // Reset the userId after deletion
        $this->reset('userId');
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

@php
$userCanEdit = Auth::user()->hasPermission('Edit Users');
$userCanDelete = Auth::user()->hasPermission('Delete Users');
@endphp

<div class="w-full">
    <div class="mb-4 w-full">
        <!-- Success Message -->
        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        @if($isCreating && Auth::user()->hasPermission('Create Users'))
            <!-- Livewire component for creating a user -->
            <livewire:users.create-user-table/>
        @elseif($isEditing && Auth::user()->hasPermission('Edit Users'))
            <!-- Livewire component for editing a user -->
            <livewire:users.edit-user-table :userId="$userId" />
        @else
            @if(Auth::user()->hasPermission('Create Users'))
                <!-- Button to create a new user -->
                <x-green-button wire:click="$set('isCreating', true)" class="mb-4">
                    {{ __('Create User') }}
                </x-green-button>
            @endif

            <!-- User table -->
            <div class="overflow-x-auto bg-white dark:bg-gray-900 w-full">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Email') }}</th>
                            <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Role') }}</th>
                            @if($userCanEdit || $userCanDelete)
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">{{ __('Actions') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @php
                            $users = User::with('role')->paginate(10);
                        @endphp

                        @if($users->count() > 0)
                            @foreach($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ optional($user->role)->name ?? '-' }}</td>
                                    @if(optional($user->role)->name !== 'Super Admin' || Auth::user()->hasRole('Super Admin'))
                                        @if($userCanEdit || $userCanDelete)
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @if($userCanEdit && $user->id !== Auth::user()->id)
                                                <x-primary-button
                                                    :disabled="!is_null($user->verified_at)"
                                                    wire:click="$dispatch('open-modal', { name: 'confirm-verify', userId: {{ $user->id }} })">
                                                    {{ __('Verify') }}
                                                </x-primary-button>

                                                    <x-secondary-button
                                                        class="ms-2"
                                                        wire:click="edit({{ $user->id }})">
                                                        {{ __('Edit') }}
                                                    </x-secondary-button>
                                                @endif

                                                @if($userCanDelete && $user->id !== Auth::user()->id)
                                                    <x-danger-button
                                                        x-on:click="$dispatch('open-modal', { name: 'confirm-user-deletion', userId: {{ $user->id }} })"
                                                        class="ms-2">
                                                        {{ __('Delete') }}
                                                    </x-danger-button>
                                                @endif
                                            </td>
                                        @endif
                                    @elseif(!$userCanEdit && !$userCanDelete)

                                    @else
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ __('Action not allowed') }}
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100" colspan="4">{{ __('No users found') }}</td>
                            </tr>
                        @endif
                        <!-- Confirmation Modal -->
                        <x-modal name="confirm-verify" :show="false" maxWidth="sm">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Are you sure you want to verify this user?') }}</h2>

                                <div class="mt-4 flex justify-end">
                                    <!-- Cancel Button -->
                                    <x-secondary-button x-on:click="$dispatch('close-modal', { name: 'confirm-verify' })">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>

                                    <!-- Confirm Verify Button -->
                                    <x-primary-button
                                        wire:click="verify({{ $user->id }})"
                                        class="ml-3"
                                        x-on:click="$dispatch('close-modal', { name: 'confirm-verify' })">
                                        {{ __('Confirm') }}
                                    </x-primary-button>
                                </div>
                            </div>
                        </x-modal>
                        <!-- Include Modal Component -->
                        <x-modal name="confirm-user-deletion">
                            <div class="p-6">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('Are you sure you want to delete this user?') }}</h2>

                                <div class="mt-4 flex justify-end">
                                    <!-- Cancel Button -->
                                    <x-secondary-button x-on:click="$dispatch('close-modal', { name: 'confirm-user-deletion' })">
                                        {{ __('Cancel') }}
                                    </x-secondary-button>

                                    <!-- Confirm Delete Button -->
                                    <x-danger-button class="ml-3" x-on:click="$wire.deleteUser({{ $user->id }}); $dispatch('close-modal', { name: 'confirm-user-deletion' })">
                                        {{ __('Delete') }}
                                    </x-danger-button>
                                </div>
                            </div>
                        </x-modal>

                    </tbody>
                </table>

                <!-- Pagination Links -->
                <div class="mt-4">
                    {{ User::paginate(10)->links() }}
                </div>
            </div>
        @endif


    </div>
</div>
