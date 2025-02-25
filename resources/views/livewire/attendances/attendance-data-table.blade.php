<?php

use App\Models\Attendance;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $attendanceId;
    public $filterStatus = '';
    public $filterDate = '';
    public $filterEmployee = '';
    public $statusColors = [
        // Single status (check-in only)
        Attendance::STATUS_PRESENT => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        Attendance::STATUS_LATE => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',

        // Combined status (check-in and check-out)
        Attendance::STATUS_LATE_AND_EARLY => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        Attendance::STATUS_LATE_AND_OVERTIME => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        Attendance::STATUS_ONTIME_AND_EARLY => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        Attendance::STATUS_ONTIME_AND_OVERTIME => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        Attendance::STATUS_ONTIME_AND_NORMAL => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',

        // Other statuses
        Attendance::STATUS_ABSENT => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        Attendance::STATUS_LEAVE => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
        Attendance::STATUS_HOLIDAY => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    ];

    public function with(): array
    {
        return [
            'attendances' => $this->getAttendances(),
        ];
    }

    private function getAttendances()
    {
        $query = Attendance::with(['user', 'office']);

        // Check user permissions
        if (!auth()->user()->hasPermission('View All Attendances')) {
            // Users can only see their own attendance records
            $query->where('user_id', auth()->id());
        }

        // Apply filters
        $query
            ->when($this->filterEmployee, function ($query) {
                // Only allow employee filter for users with permission
                if (auth()->user()->hasPermission('View All Attendances')) {
                    $query->where('user_id', $this->filterEmployee);
                }
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterDate, function ($query) {
                $query->whereDate('check_in_time', $this->filterDate);
            });

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function confirmAttendanceDeletion($attendanceId)
    {
        $this->attendanceId = $attendanceId;
    }

    public function deleteAttendance()
    {
        Attendance::find($this->attendanceId)->delete();

        // Reset the attendanceId after deletion
        $this->reset('attendanceId');
        $this->dispatch('saved');
    }

    public function downloadCsv()
    {
        $attendances = Attendance::with(['user', 'office'])
            ->when($this->filterEmployee, function ($query) {
                if (auth()->user()->hasPermission('View All Attendances')) {
                    $query->where('user_id', $this->filterEmployee);
                }
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->when($this->filterDate, function ($query) {
                $query->whereDate('check_in_time', $this->filterDate);
            })
            ->get()
            ->map(function ($attendance) {
                return [
                    'Employee' => $attendance->user->name,
                    'Office' => $attendance->office->name,
                    'Check In' => $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s - d M Y') : '-',
                    'Check Out' => $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s - d M Y') : '-',
                    'Status' => Attendance::getStatuses()[$attendance->status],
                ];
            });

        return \App\Services\CsvExporter::exportToCsv($attendances, 'attendance_records.csv');
    }

    public function resetFilters()
    {
        $this->reset(['filterEmployee', 'filterStatus', 'filterDate']);
    }
};
?>

<div class="w-full">
    <div class="mb-4 w-full">
        <div class="mb-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-900">
            <div class="flex flex-wrap items-center gap-6">
                <!-- Employee Filter -->
                @if (auth()->user()->hasPermission('View All Attendances') &&
                        in_array(auth()->user()->role->name, ['Super Admin', 'Admin', 'HRD']))
                    <div class="min-w-[200px] flex-1">
                        <select
                            class="focus:ring-primary-500 w-full rounded-md border-0 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 transition-all focus:ring-2 dark:bg-gray-800 dark:text-gray-300"
                            wire:model.live="filterEmployee">
                            <option value="">{{ __('All Employees') }}</option>
                            @foreach (App\Models\User::all() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Status Filter -->
                @php
                    $statuses = Attendance::getStatuses();
                @endphp
                <div class="min-w-[200px] flex-1">
                    <select
                        class="focus:ring-primary-500 w-full rounded-md border-0 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 transition-all focus:ring-2 dark:bg-gray-800 dark:text-gray-300"
                        wire:model.live="filterStatus">
                        <option value="">{{ __('All Attendance Status') }}</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter -->
                <div class="relative min-w-[200px] flex-1">
                    <div class="relative">
                        <input
                            class="focus:ring-primary-500 w-full cursor-pointer rounded-md border-0 bg-gray-50 px-4 py-2.5 pl-10 text-sm text-gray-600 transition-all hover:bg-gray-100 focus:ring-2 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0"
                            type="date" wire:model.live="filterDate" max="{{ date('Y-m-d') }}">

                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>

                        @if ($filterDate)
                            <button class="absolute inset-y-0 right-0 flex items-center pr-3" type="button"
                                wire:click="$set('filterDate', '')">
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-500"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Reset Button -->
                <button
                    class="px-4 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700 focus:outline-none dark:text-gray-400 dark:hover:text-gray-200"
                    wire:click="resetFilters">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>

                <!-- Download CSV Button -->
                <button
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700 focus:outline-none dark:text-gray-400 dark:hover:text-gray-200"
                    wire:click="downloadCsv">
                    <svg class="mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('Export CSV') }}
                </button>
            </div>
        </div>
        <!-- Success Message -->
        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        <!-- Main Content -->
        <div class="w-full overflow-x-auto rounded-lg bg-white shadow-sm dark:bg-gray-900">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Table Header -->
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Employee') }}
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Office') }}
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Check In') }}
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Check Out') }}
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                            {{ __('Status') }}
                        </th>
                        @if (auth()->user()->hasPermission('Delete Attendances') &&
                                in_array(auth()->user()->role->name, ['Super Admin', 'Admin', 'HRD']))
                            <th
                                class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                {{ __('Actions') }}
                            </th>
                    </tr>
                </thead>

                <x-modal name="confirm-attendance-deletion">
                    <div class="bg-white p-6 dark:bg-gray-800">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('Are you sure you want to delete this attendance record?') }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Once deleted, this attendance record will be permanently removed.') }}
                        </p>
                        <div class="mt-6 flex justify-end space-x-3">
                            <x-secondary-button
                                x-on:click="$dispatch('close-modal', { name: 'confirm-attendance-deletion' })">
                                {{ __('Cancel') }}
                            </x-secondary-button>
                            <x-danger-button wire:click="deleteAttendance"
                                x-on:click="$dispatch('close-modal', { name: 'confirm-attendance-deletion' })">
                                {{ __('Delete Attendance') }}
                            </x-danger-button>
                        </div>
                    </div>
                </x-modal>
                @endif
                </tr>
                </thead>

                <!-- Table Body -->
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @php
                        $attendances = $this->getAttendances();
                    @endphp

                    @if ($attendances->count() > 0)
                        @foreach ($attendances as $attendance)
                            <tr class="transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $attendance->user->name }}
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $attendance->office->name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-4 w-4 text-green-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                        {{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s - d M Y') : '-' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-4 w-4 text-red-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                        {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s - d M Y') : '-' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    <span
                                        class="{{ $statusColors[$attendance->status] }} rounded-full px-3 py-1 text-xs font-semibold">
                                        {{ Attendance::getStatuses()[$attendance->status] }}
                                    </span>
                                </td>
                                @if (auth()->user()->hasPermission('Delete Attendances') &&
                                        in_array(auth()->user()->role->name, ['Super Admin', 'Admin', 'HRD']))
                                    <td class="space-x-2 whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        <x-danger-button wire:click="confirmAttendanceDeletion({{ $attendance->id }})"
                                            x-on:click="$dispatch('open-modal', { name: 'confirm-attendance-deletion'})">
                                            {{ __('Delete') }}
                                        </x-danger-button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="whitespace-nowrap bg-gray-50 px-6 py-8 text-center text-sm text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                                colspan="5">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="mb-4 h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('No attendance records found') }}
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-700">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>
