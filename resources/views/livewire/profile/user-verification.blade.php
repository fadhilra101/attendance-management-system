<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public $isSuperAdmin = false;
    public $userVerified = false;

    public function mount()
    {
        $user = Auth::user();
        $this->userVerified = $user->verified_at !== null;
        $this->isSuperAdmin = $user->role->name === 'Super Admin';
    }

    public function verifyUser()
    {
        $user = Auth::user();
        $user->update(['verified_at' => now()]);
        $this->userVerified = true;
        $user->save();

        $this->dispatch('saved');
    }
}; ?>

<div>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Profile Verification') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __("Update your account's profile verification status.") }}
            </p>
        </header>
        <div class="gap-2">
            @if($this->userVerified)
            <x-green-button class="mt-6" disabled>
                {{ __('Verified') }}
            </x-green-button>
            @elseif($this->isSuperAdmin)
            <x-danger-button class="mt-6" wire:click="verifyUser">
                {{ __('Not Verified') }}
            </x-danger-button>
            @else
            <x-danger-button class="mt-6" disabled>
                {{ __('Not Verified') }}
            </x-danger-button>
            @endif
        </div>

        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>




    </section>
</div>
