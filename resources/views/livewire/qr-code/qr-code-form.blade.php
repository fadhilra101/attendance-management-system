<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\QrCode;
use App\Models\Office;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;

new class extends Component {
    public ?QrCode $qrCode = null;
    public string $type = '';
    public int $timeRemaining = 120;
    public $selectedOffice = null;
    public $offices = [];
    public $qrCodeImage = '';

    public function mount()
    {
        $this->offices = Office::all();
    }

    public function selectOffice($officeId)
    {
        $this->selectedOffice = $officeId;
    }

    public function generateQrCode($type)
    {
        if (!$this->selectedOffice) {
            session()->flash('error', 'Please select an office first.');
            return;
        }

        // Create QR Code record
        $this->qrCode = new QrCode();
        $this->qrCode->save(); // Save the model to generate the ID
        $this->type = $type;

        $office = Office::find($this->selectedOffice);

        // Generate API URL with parameters
        $apiUrl =
            config('app.url') .
            '/attendances/scan?' .
            http_build_query([
                'qr_code_id' => $this->qrCode->id,
                'lat' => $office->gps_lat,
                'lng' => $office->gps_lng,
                'type' => $type,
                'office_id' => $office->id,
            ]);

        // Generate QR code image
        $this->qrCodeImage = base64_encode(
            QrCodeGenerator::format('svg') // Changed from 'png' to 'svg'
                ->size(300)
                ->errorCorrection('H')
                ->generate($apiUrl),
        );
    }

    public function updateTimer()
    {
        if ($this->timeRemaining > 0) {
            $this->timeRemaining--;

            // Check for attendance every 2 seconds (when timer is even)
            if ($this->timeRemaining % 2 === 0 && $this->qrCode) {
                // Use exists() for better performance instead of get() or all()
                $hasAttendance = \App\Models\Attendance::select('id')->where('qr_code_checkin_id', $this->qrCode->id)->orWhere('qr_code_checkout_id', $this->qrCode->id)->exists();

                if ($hasAttendance) {
                    session()->flash('success', 'Attendance recorded successfully.');
                    $this->deleteQrCode();
                    return;
                }
            }

            if ($this->timeRemaining === 0) {
                $this->deleteQrCode();
            }
        }
    }

    public function deleteQrCode()
    {
        if ($this->qrCode) {
            $this->qrCode->delete();
        }
        $this->qrCode = null;
        $this->type = '';
        $this->qrCodeImage = '';
        $this->timeRemaining = 120;
    }

    public function resetQrCode()
    {
        $this->deleteQrCode();
    }
};
?>

<div class="space-y-6">
    {{-- Title --}}
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">QR Code Generator</h2>
        <p class="text-gray-600 dark:text-gray-400">Generate QR Code for Attendance</p>
    </div>

    @if (!$this->qrCode)
        @if (session()->has('error'))
            <div class="mb-4 text-center" wire:poll.5000ms="$refresh">
                <p class="text-red-600 dark:text-red-400">{{ session('error') }}</p>
            </div>
        @endif
        @if (session()->has('success'))
            <div class="mb-4 text-center" wire:poll.5000ms="$refresh">
                <p class="text-green-600 dark:text-green-400">{{ session('success') }}</p>
            </div>
        @endif
        @if (!$selectedOffice)
            {{-- Office Selection --}}
            <div class="mx-auto max-w-md">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Select Office</label>
                <div class="grid gap-3">
                    @foreach ($offices as $office)
                        <button
                            class="{{ $selectedOffice === $office->id
                                ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/50 dark:border-indigo-400'
                                : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800' }} w-full rounded-lg border p-4 text-left transition dark:border-gray-700 dark:bg-gray-800"
                            wire:click="selectOffice({{ $office->id }})">
                            <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $office->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $office->address }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            {{-- Selection Buttons --}}
            <div class="flex flex-col items-center gap-4">
                <div class="mb-4 text-center">
                    <h3 class="font-medium text-gray-900 dark:text-gray-100">Selected Office:</h3>
                    <p class="text-indigo-600 dark:text-indigo-400">{{ $offices->find($selectedOffice)->name }}</p>
                    <button class="mt-2 text-sm text-gray-500 hover:underline dark:text-gray-400" wire:click="reset">
                        Change Office
                    </button>
                </div>
                <div class="flex gap-4">
                    <x-green-button wire:click="generateQrCode('check_in')">
                        Generate Check-in QR
                    </x-green-button>
                    <x-danger-button wire:click="generateQrCode('check_out')">
                        Generate Check-out QR
                    </x-danger-button>
                </div>
            </div>
        @endif
    @else
        {{-- QR Code Display --}}
        <div class="flex flex-col items-center space-y-4">
            <div class="mb-2 text-center">
                <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $offices->find($selectedOffice)->name }}
                </h3>
            </div>
            <div class="rounded-lg bg-white p-4 shadow">
                <img class="h-64 w-64" src="data:image/svg+xml;base64,{{ $qrCodeImage }}" alt="QR Code">
            </div>


            {{-- Timer Display --}}
            <div class="text-center">
                <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">QR Code will expire in:</p>
                <p class="{{ $this->timeRemaining <= 10 ? 'text-red-600' : 'text-indigo-600' }} text-2xl font-bold dark:text-indigo-400"
                    wire:poll.1000ms="updateTimer">
                    {{ $this->timeRemaining }} seconds
                </p>
            </div>

            {{-- Type Display --}}
            <div class="text-center">
                <span
                    class="{{ $this->type === 'check-in'
                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                        : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' }} rounded-full px-4 py-2">
                    {{ ucfirst($this->type) }}
                </span>
            </div>

            {{-- Reset Button --}}
            <x-primary-button wire:click="resetQrCode">
                Generate New QR Code
            </x-primary-button>
        </div>
    @endif

    {{-- Flash Messages --}}
    <x-action-message on="saved">
        {{ session('success') }}
    </x-action-message>

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4">
            <div class="rounded-lg bg-red-500 px-6 py-3 text-white shadow-lg dark:bg-red-600">
                {{ session('error') }}
            </div>
        </div>
    @endif
</div>
