<?php

use Livewire\Volt\Component;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

new class extends Component {
    public $name, $email, $password, $password_confirmation, $role_id;
    public $roles = [];
    public $showPassword = false; // To toggle password visibility

    public function mount(): void
    {
        // Check if the authenticated user has the 'Super Admin' role
        if (Auth::user()->hasRole('Super Admin')) {
            // Fetch all roles if the user is a Super Admin
            $this->roles = Role::all()->pluck('name', 'id');
        } else {
            // Fetch all roles except 'Super Admin' if the user is not a Super Admin
            $this->roles = Role::where('name', '!=', 'Super Admin')->pluck('name', 'id');
        }
    }


    public function store(): void
    {
        if (!Auth::user()->hasPermission('Create Users')) {
            abort(403, 'Unauthorized');
        }
        // Define validation rules and custom messages
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:users,name'
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'lowercase'
            ],
            'password' => [
                'required',
                'string',
                'confirmed', // This will validate 'password_confirmation' automatically
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#.,]/'
            ],
            'role_id' => [
                'required',
                'exists:roles,id'
            ]
        ];

        $messages = [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'role_id.exists' => 'The selected role is invalid.',
        ];

        // Validate input data
        $validated = $this->validate($rules, $messages);

        // Check if the current user is not a Super Admin and is trying to assign the Super Admin role
        if (Auth::user()->hasRole('Super Admin') === false && $validated['role_id'] === Role::where('name', 'Super Admin')->first()->id) {
            // If the current user is not a Super Admin and is trying to assign the Super Admin role, throw an exception
            $this->addError('role_id', 'You are not authorized to assign the Super Admin role.');
            return;
        }

        // Hash the password before saving
        $validated['password'] = Hash::make($validated['password']);

        // Create the new user with the validated data
        $user = User::create($validated);

        // Dispatch a saved event for any post-save actions
        event(new Registered($user));

        // Notify that the save operation was successful
        $this->dispatch('saved');

        // Reset form fields after saving
        $this->resetFields();
    }


    private function resetFields(): void
    {
        $this->reset(['name', 'email', 'role_id']);
        $this->dispatch('hideCreate');
        $this->showPassword = false;
    }

    public function togglePasswordVisibility(): void
    {
        $this->showPassword = !$this->showPassword;
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
        <x-input-label for="email" value="Email" class="mb-2" />
        <x-text-input id="email" type="email" wire:model.defer="email" class="w-full" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="password" value="Password" class="mb-2" />
        <div class="relative">
            <x-text-input
                id="password"
                type="{{ $showPassword ? 'text' : 'password' }}"
                wire:model.defer="password"
                class="w-full pr-12"
            />
            <button
                type="button"
                wire:click="togglePasswordVisibility"
                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-300"
            >
                @if ($showPassword)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7z" />
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12s3-7 9-7 9 7 9 7-3 7-9 7-9-7-9-7z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1l22 22" />
                    </svg>
                @endif
            </button>

        </div>
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div class="w-full">
        <x-input-label for="password_confirmation" value="Confirm Password" class="mb-2" />
        <x-text-input
            id="password_confirmation"
            type="{{ $showPassword ? 'text' : 'password' }}"
            wire:model.defer="password_confirmation"
            class="w-full"
        />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>


    <div class="w-full">
        <x-input-label for="role_id" value="Role" class="mb-2" />
        <x-select-input
            id="role_id"
            name="role_id"
            wire:model.defer="role_id"
            :options="$roles"
            value="{{ old('role_id', $role_id) }}"
            class="w-full"
        />
        <x-input-error :messages="$errors->get('role_id')" class="mt-2" />
    </div>



    <div class="flex gap-2 mt-2">
        <x-primary-button type="submit">{{ __('Create') }}</x-primary-button>
        <x-secondary-button type="button" wire:click="$dispatch('hideCreate')">{{ __('Cancel') }}</x-secondary-button>
    </div>

</form>

