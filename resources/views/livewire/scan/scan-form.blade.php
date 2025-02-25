<?php

use Livewire\Volt\Component;

new class extends Component {
    public function scan($url)
    {
        try {
            return redirect()->to($url);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to process QR code.');
        }
    }
};
?>

<div>
    @if (session()->has('success'))
        <div class="flex min-h-screen flex-col items-center justify-center bg-gray-100 p-4 dark:bg-gray-900">
            <div class="w-full max-w-md rounded-lg bg-white p-8 text-center shadow-lg dark:bg-gray-800">
                <div class="mb-6">
                    <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Success!</h2>
                <p class="mb-6 text-gray-600 dark:text-gray-400">{{ session('success') }}</p>
                <a class="inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    href="{{ route('dashboard') }}">
                    Return to Dashboard
                </a>
            </div>
        </div>
    @else
        <div class="p-4 text-center">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 md:text-2xl">QR Code Scanner</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 md:text-base">Scan QR Code for Attendance</p>
        </div>

        <div class="mx-auto mt-4 w-full max-w-sm px-4 md:mt-6">
            <div class="overflow-hidden rounded-lg bg-white shadow-lg dark:bg-gray-800">
                <div class="relative aspect-square">
                    <div class="h-full w-full" id="reader"></div>
                </div>
            </div>

            <div class="mx-auto mt-4 w-full max-w-sm px-4">
                <select
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
                    id="cameraSelection">
                    <option value="">Select a camera</option>
                </select>
            </div>

            <div class="mt-4 flex justify-center gap-3 md:gap-4">
                <x-primary-button class="text-sm md:text-base" id="startButton" onclick="startScanner()">
                    Start Scanner
                </x-primary-button>
                <x-secondary-button class="hidden text-sm md:text-base" id="stopButton" onclick="stopScanner()">
                    Stop Scanner
                </x-secondary-button>
            </div>

            <div class="mt-4 px-2 text-center text-xs text-gray-600 dark:text-gray-400 md:mt-6 md:text-sm">
                <p>Position the QR code within the camera frame to scan</p>
                <p class="mt-1 md:mt-2">Make sure you have good lighting and a steady hand</p>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="animate-fade-up fixed bottom-4 left-4 right-4 z-50 md:left-auto md:right-4">
            <div class="transform rounded-lg bg-red-500 px-4 py-2 text-sm text-white shadow-lg transition-all duration-500 ease-in-out hover:scale-105 dark:bg-red-600 md:px-6 md:py-3 md:text-base"
                x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-2">
                {{ session('error') }}
            </div>
        </div>
    @endif



    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
        let html5QrcodeScanner = null;
        let currentCamera = null;
        let cameras = [];

        document.addEventListener('DOMContentLoaded', async function() {
            try {
                await loadQRScanner();
            } catch (error) {
                console.error('Failed to initialize:', error);
            }
        });

        async function loadQRScanner() {
            try {
                cameras = await Html5Qrcode.getCameras();
                if (cameras && cameras.length) {
                    await populateCameraSelect();
                    const defaultCamera = cameras[0].id;
                    document.getElementById('cameraSelection').value = defaultCamera;
                }
            } catch (err) {
                console.error('QR Scanner init failed:', err);
            }
        }

        async function populateCameraSelect() {
            const select = document.getElementById('cameraSelection');
            select.innerHTML = '<option value="">Select camera</option>';

            cameras.forEach(camera => {
                const option = document.createElement('option');
                option.value = camera.id;
                option.text = camera.label || `Camera ${camera.id}`;
                select.appendChild(option);
            });

            // Add camera change event listener
            select.addEventListener('change', async function() {
                if (html5QrcodeScanner && this.value) {
                    const isScanning = await html5QrcodeScanner.isScanning;
                    if (isScanning) {
                        await switchCamera(this.value);
                    }
                }
            });
        }

        async function switchCamera(newCameraId) {
            try {
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.stop();
                    await startScanningWithCamera(newCameraId);
                }
            } catch (err) {
                console.error('Camera switch error:', err);
                alert('Failed to switch camera: ' + err.message);
            }
        }

        async function startScanningWithCamera(cameraId) {
            try {
                await html5QrcodeScanner.start(
                    cameraId, {
                        fps: 10,
                        qrbox: {
                            width: 250,
                            height: 250
                        },
                        aspectRatio: 1.0
                    },
                    (decodedText) => handleScan(decodedText),
                    (error) => console.warn(error)
                );
            } catch (err) {
                console.error('Start scanning error:', err);
                throw err;
            }
        }

        async function startScanner() {
            const startBtn = document.getElementById('startButton');
            const stopBtn = document.getElementById('stopButton');
            const cameraSelect = document.getElementById('cameraSelection');

            try {
                startBtn.disabled = true;
                const cameraId = cameraSelect.value || cameras[0]?.id;

                if (!cameraId) {
                    throw new Error('No camera selected');
                }

                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.stop();
                }

                html5QrcodeScanner = new Html5Qrcode("reader");
                await startScanningWithCamera(cameraId);

                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');

            } catch (err) {
                console.error('Scanner error:', err);
                alert('Failed to start scanner: ' + err.message);
            } finally {
                startBtn.disabled = false;
            }
        }

        async function stopScanner() {
            if (html5QrcodeScanner) {
                try {
                    await html5QrcodeScanner.stop();
                    html5QrcodeScanner = null;
                } catch (err) {
                    console.error('Stop scanner error:', err);
                }
            }
            document.getElementById('startButton').classList.remove('hidden');
            document.getElementById('stopButton').classList.add('hidden');
        }

        async function handleScan(decodedText) {
            await stopScanner();
            @this.scan(decodedText);
        }

        // Cleanup
        window.addEventListener('beforeunload', stopScanner);
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopScanner();
        });
    </script>
</div>
