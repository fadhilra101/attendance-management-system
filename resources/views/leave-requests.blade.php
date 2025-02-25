<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Leave Requests') }} <!-- Updated header to reflect leave requests -->
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="bg-white p-4 shadow dark:bg-gray-800 sm:rounded-lg sm:p-8">
                    <!-- Removed max-w-xl to allow full width -->
                    <div class="w-full">
                        <livewire:leave-requests.leave-request-data-table />
                        <!-- Updated component to reflect leave requests -->
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
