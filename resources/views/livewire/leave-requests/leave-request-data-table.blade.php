<?php

use App\Models\LeaveRequest;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $isCreating = false;
    public $isReading = false;
    public $isEditing = false;
    public $leaveRequestId;
    public $leaveRequests; // Add this line

    protected $listeners = [
        'hideCreate' => 'handleHideCreate',
        'hideRead' => 'handleHideRead',
        'hideEdit' => 'handleHideEdit',
    ];

    public function mount(): void
    {
        $this->isCreating = false;
        $this->isEditing = false;
        $this->leaveRequests = Auth::user()->hasPermission('View All Leave Requests') ? LeaveRequest::with('user')->orderBy('created_at', 'desc')->paginate(10) : LeaveRequest::where('user_id', Auth::id())->with('user')->orderBy('created_at', 'desc')->paginate(10);
    }

    public function create(): void
    {
        $this->isCreating = true;
    }

    public function read($id): void
    {
        $this->leaveRequestId = $id;
        $this->isReading = true;
    }

    public function edit($id): void
    {
        if (!Auth::user()->hasPermission('Edit Leave Requests')) {
            abort(403, 'Unauthorized');
        }

        $this->leaveRequestId = $id;
        $this->isEditing = true;
    }

    public function confirmApprove($id): void
    {
        $this->leaveRequestId = $id;
    }

    public function approve($id): void
    {
        $leaveRequest = LeaveRequest::find($id);
        $leaveRequest->update(['status' => 'approved']);
        $this->dispatch('saved');
        $this->reset('leaveRequestId');
    }

    public function confirmReject($id): void
    {
        $this->leaveRequestId = $id;
    }

    public function reject($id): void
    {
        $leaveRequest = LeaveRequest::find($id);
        $leaveRequest->update(['status' => 'rejected']);
        $this->dispatch('saved');
        $this->reset('leaveRequestId');
    }

    public function confirmLeaveRequestDeletion($id)
    {
        $this->leaveRequestId = $id;
    }

    public function deleteLeaveRequest(): void
    {
        LeaveRequest::find($this->leaveRequestId)->delete();
        $this->reset('leaveRequestId');
        $this->dispatch('saved');
    }

    public function handleHideCreate(): void
    {
        $this->isCreating = false;
    }

    public function handleHideRead(): void
    {
        $this->isReading = false;
    }

    public function handleHideEdit(): void
    {
        $this->isEditing = false;
    }
};
?>

<div class="w-full">
    <div class="mb-4 w-full">
        <x-success-message on="saved">
            {{ __('Your changes have been saved successfully.') }}
        </x-success-message>

        @if ($isCreating && Auth::user()->hasPermission('Create Leave Requests'))
            <livewire:leave-requests.create-leave-request-table />
        @elseif($isEditing && Auth::user()->hasPermission('Edit Leave Requests'))
            <livewire:leave-requests.edit-leave-request-table :leaveRequestId="$leaveRequestId" />
        @elseif($isReading)
            <livewire:leave-requests.read-leave-request-table :leaveRequestId="$leaveRequestId" />
        @else
            @if (Auth::user()->hasPermission('Create Leave Requests'))
                <x-green-button class="mb-4" wire:click="$set('isCreating', true)">
                    {{ __('Create Leave Request') }}
                </x-green-button>
            @endif

            <div class="w-full overflow-x-auto rounded-lg bg-white shadow-sm dark:bg-gray-900">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                {{ __('Employee') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                {{ __('Leave Type') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                {{ __('Start Date') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                {{ __('End Date') }}
                            </th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                {{ __('Status') }}
                            </th>
                            @if (Auth::user()->hasPermission('Edit Leave Requests') || Auth::user()->hasPermission('Delete Leave Requests'))
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                    {{ __('Actions') }}
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">

                        @if ($leaveRequests->count() > 0)
                            @foreach ($leaveRequests as $leaveRequest)
                                <tr class="transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ $leaveRequest->user->name }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $leaveRequest->leave_type }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $leaveRequest->start_date }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        {{ $leaveRequest->end_date }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        <span
                                            class="{{ $leaveRequest->status === 'approved'
                                                ? 'bg-green-100 text-green-800'
                                                : ($leaveRequest->status === 'rejected'
                                                    ? 'bg-red-100 text-red-800'
                                                    : 'bg-yellow-100 text-yellow-800') }} inline-flex rounded-full px-2 text-xs font-semibold leading-5">
                                            {{ ucfirst($leaveRequest->status) }}
                                        </span>
                                    </td>
                                    @if (Auth::user()->hasPermission('Edit Leave Requests') || Auth::user()->hasPermission('Delete Leave Requests'))
                                        <td class="space-x-2 whitespace-nowrap px-6 py-4 text-sm font-medium">
                                            @if (Auth::user()->hasPermission('Approve and Reject Leave Requests'))
                                                <x-secondary-button
                                                    class="transition duration-150 ease-in-out hover:scale-105"
                                                    wire:click="confirmApprove({{ $leaveRequest->id }})">
                                                    x-on:click="$dispatch('open-modal', { name: 'confirm-leave-request-approval'})">
                                                    {{ __('Approve') }}
                                                </x-secondary-button>
                                                <x-danger-button
                                                    class="transition duration-150 ease-in-out hover:scale-105"
                                                    wire:click="confirmReject({{ $leaveRequest->id }})">
                                                    x-on:click="$dispatch('open-modal', { name: 'confirm-leave-request-rejection'})">
                                                    {{ __('Reject') }}
                                                </x-danger-button>
                                            @endif
                                            @if (Auth::user()->hasPermission('Edit Leave Requests'))
                                                <x-secondary-button
                                                    class="transition duration-150 ease-in-out hover:scale-105"
                                                    wire:click="edit({{ $leaveRequest->id }})">
                                                    {{ __('Edit') }}
                                                </x-secondary-button>
                                            @endif
                                            @if (Auth::user()->hasPermission('Delete Leave Requests'))
                                                <x-danger-button
                                                    class="transition duration-150 ease-in-out hover:scale-105"
                                                    wire:click="confirmLeaveRequestDeletion({{ $leaveRequest->id }})"
                                                    x-on:click="$dispatch('open-modal', { name: 'confirm-leave-request-deletion'})">
                                                    {{ __('Delete') }}
                                                </x-danger-button>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @endforeach

                            <x-modal name="confirm-leave-request-deletion">
                                <div class="bg-white p-6 dark:bg-gray-800">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ __('Are you sure you want to delete this leave request?') }}
                                    </h2>
                                    <div class="mt-6 flex justify-end space-x-3">
                                        <x-secondary-button
                                            x-on:click="$dispatch('close-modal', { name: 'confirm-leave-request-deletion' })">
                                            {{ __('Cancel') }}
                                        </x-secondary-button>

                                        <x-danger-button wire:click="deleteLeaveRequest"
                                            x-on:click="$dispatch('close-modal', { name: 'confirm-leave-request-deletion' })">
                                            {{ __('Delete') }}
                                        </x-danger-button>
                                    </div>
                                </div>
                            </x-modal>
                        @else
                            <tr>
                                <td class="whitespace-nowrap bg-gray-50 px-6 py-8 text-center text-sm text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                                    colspan="6">
                                    {{ __('No leave requests available') }}
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $leaveRequests->links() }}
                </div>
            </div>
        @endif
    </div>
    {{-- Approve / Reject Modal --}}
    <x-modal name="confirm-leave-request-approval">
        <div class="bg-white p-6 dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to approve this leave request?') }}
            </h2>
            <div class="mt-6 flex justify-end space-x-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', { name: 'confirm-leave-request-approval' })">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-green-button wire:click="approve({{ $leaveRequestId }})"
                    x-on:click="$dispatch('close-modal', { name: 'confirm-leave-request-approval' })">
                    {{ __('Approve') }}
                </x-green-button>
            </div>
        </div>
    </x-modal>

    <x-modal name="confirm-leave-request-rejection">
        <div class="bg-white p-6 dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to reject this leave request?') }}
            </h2>
            <div class="mt-6 flex justify-end space-x-3">
                <x-secondary-button x-on:click="$dispatch('close-modal', { name: 'confirm-leave-request-rejection' })">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button wire:click="reject({{ $leaveRequestId }})"
                    x-on:click="$dispatch('close-modal', { name: 'confirm-leave-request-rejection' })">
                    {{ __('Reject') }}
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</div>
