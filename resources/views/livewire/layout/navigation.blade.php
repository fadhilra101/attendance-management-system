<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirectIntended(default: route('login'), navigate: true);
    }
}; ?>

<nav class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800" x-data="{ open: false }">
    <x-auth-session-status :status="session('status')" />

    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @if (Auth::user()->hasPermission('View Users'))
                        <x-nav-link :href="route('users')" :active="request()->routeIs('users')" wire:navigate>
                            {{ __('Users') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPermission('View Roles'))
                        <x-nav-link :href="route('roles')" :active="request()->routeIs('roles')" wire:navigate>
                            {{ __('Roles') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPermission('View Offices'))
                        <x-nav-link :href="route('offices')" :active="request()->routeIs('offices')" wire:navigate>
                            {{ __('Offices') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPermission('View Permissions'))
                        <x-nav-link :href="route('permissions')" :active="request()->routeIs('permissions')" wire:navigate>
                            {{ __('Permissions') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasPermission('View All Attendances'))
                        <x-nav-link :href="route('attendances')" :active="request()->routeIs('attendances')" wire:navigate>
                            {{ __('Attendances') }}
                        </x-nav-link>
                    @endif

                    @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'))
                        <x-nav-link :href="route('qr-code')" :active="request()->routeIs('qr-code')" wire:navigate>
                            {{ __('QR Code') }}
                        </x-nav-link>
                    @endif

                    <x-nav-link :href="route('scan')" :active="request()->routeIs('scan')">
                        {{ __('Scan') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden space-x-4 sm:ms-6 sm:flex sm:items-center">
                <!-- Theme Toggle - Desktop -->
                <button
                    class="rounded-lg p-2 transition-colors duration-200 hover:bg-gray-100 focus:outline-none dark:hover:bg-gray-700"
                    aria-label="Toggle dark mode" x-data @click="$store.darkMode.toggle()">
                    <svg class="h-5 w-5 text-gray-500" x-cloak x-show="$store.darkMode.on" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg class="h-5 w-5 text-gray-400" x-cloak x-show="!$store.darkMode.on" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M12 12m-4 0a4 4 0 118 0 4 4 0 01-8 0" />
                    </svg>
                </button>

                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
                                x-on:profile-updated.window="name = $event.detail.name"></div>
                            <div class="ms-1">
                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button class="w-full text-start" wire:click="logout">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Controls -->
            <div class="flex items-center space-x-2 sm:hidden">
                <!-- Theme Toggle - Mobile -->
                <button
                    class="rounded-lg p-2 transition-colors duration-200 hover:bg-gray-100 focus:outline-none dark:hover:bg-gray-700"
                    aria-label="Toggle dark mode" x-data @click="$store.darkMode.toggle()">
                    <svg class="h-5 w-5 text-gray-700" x-cloak x-show="$store.darkMode.on" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <svg class="h-5 w-5 text-gray-200" x-cloak x-show="!$store.darkMode.on" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M12 12m-4 0a4 4 0 118 0 4 4 0 01-8 0" />
                    </svg>
                </button>

                <!-- Hamburger -->
                <button
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                    @click="open = ! open">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path class="inline-flex" :class="{ 'hidden': open, 'inline-flex': !open }"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path class="hidden" :class="{ 'hidden': !open, 'inline-flex': open }" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div class="hidden sm:hidden" :class="{ 'block': open, 'hidden': !open }">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @if (Auth::user()->hasPermission('View Users'))
                <x-responsive-nav-link :href="route('users')" :active="request()->routeIs('users')" wire:navigate>
                    {{ __('Users') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPermission('View Roles'))
                <x-responsive-nav-link :href="route('roles')" :active="request()->routeIs('roles')" wire:navigate>
                    {{ __('Roles') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPermission('View Offices'))
                <x-responsive-nav-link :href="route('offices')" :active="request()->routeIs('offices')" wire:navigate>
                    {{ __('Offices') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPermission('View Permissions'))
                <x-responsive-nav-link :href="route('permissions')" :active="request()->routeIs('permissions')" wire:navigate>
                    {{ __('Permissions') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasPermission('View All Attendances'))
                <x-responsive-nav-link :href="route('attendances')" :active="request()->routeIs('attendances')" wire:navigate>
                    {{ __('Attendances') }}
                </x-responsive-nav-link>
            @endif

            @if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Super Admin'))
                <x-responsive-nav-link :href="route('qr-code')" :active="request()->routeIs('qr-code')" wire:navigate>
                    {{ __('QR Code') }}
                </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('scan')" :active="request()->routeIs('scan')">
                {{ __('Scan') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600">
            <div class="px-4">
                <div class="text-base font-medium text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                    x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button class="w-full text-start" wire:click="logout">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
