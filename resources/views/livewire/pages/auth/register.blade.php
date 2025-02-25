<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role_id = '';
    public $embeddings = null; // Add embeddings property

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate(
            [
                'name' => ['required', 'string', 'max:255', 'unique:users,name'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', 'lowercase'],
                'password' => ['required', 'string', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[a-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*?&#.,]/'],
                'password_confirmation' => ['required', 'same:password'],
                'role_id' => ['required', 'exists:roles,id'],
                'embeddings' => ['required', 'string'], // Add validation for embeddings
            ],
            [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
                'role_id.exists' => 'The key is invalid.',
                'embeddings.required' => 'Face capture is required.',
            ],
        );

        $validated['password'] = Hash::make($validated['password']);
        $validated['face_embeddings'] = $validated['embeddings']; // Save embeddings to face_embeddings column
        unset($validated['embeddings']); // Remove embeddings from validated data

        $user = User::create($validated);

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit.prevent="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input class="mt-1 block w-full" id="name" name="name" type="text" wire:model="name" required
                autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input class="mt-1 block w-full" id="email" name="email" type="email" wire:model="email"
                required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input class="mt-1 block w-full" id="password" name="password" type="password" wire:model="password"
                required autocomplete="new-password" />
            <x-input-error class="mt-2" :messages="$errors->get('password')" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input class="mt-1 block w-full" id="password_confirmation" name="password_confirmation"
                type="password" wire:model="password_confirmation" required autocomplete="new-password" />
            <x-input-error class="mt-2" :messages="$errors->get('password_confirmation')" />
        </div>

        <!-- Role -->
        <div class="mt-4">
            <x-input-label for="role_id" :value="__('Register Key')" />

            <!-- Input field for manual entry -->
            <x-text-input class="mt-1 block w-full" id="role_id" type="text" wire:model="role_id" required />
            <x-input-error class="mt-2" :messages="$errors->get('role_id')" />
        </div>


        <!-- Face Capture -->
        <div class="relative mt-2" id="faceCaptureContainer">
            <video class="w-full" id="videoElement"></video>
            <canvas class="absolute left-0 top-0" id="canvas"></canvas>

            <div class="mt-2 flex gap-2">
                <button
                    class="rounded-md bg-gray-200 px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-300"
                    id="switchCameraBtn" type="button">
                    {{ __('Switch Camera') }}
                </button>
            </div>
        </div>
        <!-- Success Display Container -->
        <div class="mt-2">
            <div class="hidden rounded-md bg-green-50 p-4" id="successContainer">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700" id="successMessage">Face captured successfully! go ahead and
                            continue</p>
                        <button
                            class="mt-2 rounded-md bg-green-200 px-3 py-2 text-sm font-semibold text-green-900 shadow-sm hover:bg-green-300"
                            id="retryFaceBtn" type="button">
                            {{ __('Capture New Face') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Error Display Container -->
        <div class="mt-2">
            <div class="hidden rounded-md bg-red-50 p-4" id="errorContainer">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700" id="errorMessage"></p>
                    </div>
                </div>
            </div>
        </div>

        <div wire:ignore>
            <input id="embeddings" type="hidden" wire:model="embeddings">

            <script src="{{ asset('js/face-api.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', async () => {
                    const video = document.getElementById('videoElement');
                    const canvas = document.getElementById('canvas');
                    const switchCameraBtn = document.getElementById('switchCameraBtn');
                    const retryFaceBtn = document.getElementById('retryFaceBtn');
                    const errorContainer = document.getElementById('errorContainer');
                    const errorMessage = document.getElementById('errorMessage');
                    const successContainer = document.getElementById('successContainer');
                    const faceCaptureElements = document.getElementById('faceCaptureContainer');
                    let currentStream = null;
                    let facingMode = 'user';
                    let isProcessing = false;
                    let consecutiveGoodDetections = 0;

                    video.autoplay = true;
                    video.muted = true;
                    video.playsInline = true;

                    function showError(message) {
                        errorMessage.textContent = message;
                        errorContainer.classList.remove('hidden');
                        setTimeout(() => {
                            errorContainer.classList.add('hidden');
                        }, 5000);
                    }

                    async function loadModels() {
                        try {
                            await Promise.all([
                                faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
                                faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
                                faceapi.nets.faceRecognitionNet.loadFromUri('/models')
                            ]);
                        } catch (err) {
                            console.error('Error loading models:', err);
                            showError('Error loading face detection models.');
                        }
                    }

                    async function startCamera() {
                        if (currentStream) {
                            currentStream.getTracks().forEach(track => track.stop());
                        }

                        try {
                            currentStream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: facingMode,
                                    width: {
                                        ideal: 1280
                                    },
                                    height: {
                                        ideal: 720
                                    },
                                    brightness: {
                                        ideal: 100
                                    },
                                    contrast: {
                                        ideal: 100
                                    }
                                }
                            });
                            video.srcObject = currentStream;
                        } catch (err) {
                            console.error('Camera error:', err);
                            showError('Error accessing camera. Please ensure camera permissions are granted.');
                        }
                    }

                    function resetFaceCapture() {
                        consecutiveGoodDetections = 0;
                        faceCaptureElements.style.display = 'block';
                        successContainer.classList.add('hidden');
                        const embeddingsInput = document.getElementById('embeddings');
                        embeddingsInput.value = '';
                        embeddingsInput.dispatchEvent(new Event('input'));
                        startCamera();
                        processFaceDetection();
                    }

                    await loadModels();
                    await startCamera();

                    async function processFaceDetection() {
                        if (video.readyState === 4 && !isProcessing) {
                            isProcessing = true;
                            try {
                                const detection = await faceapi.detectSingleFace(
                                    video,
                                    new faceapi.TinyFaceDetectorOptions({
                                        inputSize: 512,
                                        scoreThreshold: 0.5
                                    })
                                ).withFaceLandmarks().withFaceDescriptor();

                                canvas.width = video.clientWidth;
                                canvas.height = video.clientHeight;
                                const dims = {
                                    width: video.clientWidth,
                                    height: video.clientHeight
                                };

                                const ctx = canvas.getContext('2d');
                                ctx.clearRect(0, 0, canvas.width, canvas.height);

                                if (detection) {
                                    const resizedDetection = faceapi.resizeResults(detection, dims);
                                    faceapi.draw.drawDetections(canvas, resizedDetection);
                                    faceapi.draw.drawFaceLandmarks(canvas, resizedDetection);

                                    // Check for good quality detection
                                    if (detection.detection.score > 0.8 &&
                                        detection.landmarks.positions.length === 68) {
                                        consecutiveGoodDetections++;

                                        if (consecutiveGoodDetections >= 5) {
                                            const embeddings = Array.from(detection.descriptor);
                                            const embeddingsInput = document.getElementById('embeddings');
                                            embeddingsInput.value = JSON.stringify(embeddings);
                                            embeddingsInput.dispatchEvent(new Event('input'));

                                            faceCaptureElements.style.display = 'none';
                                            successContainer.classList.remove('hidden');

                                            if (currentStream) {
                                                currentStream.getTracks().forEach(track => track.stop());
                                            }
                                            return;
                                        }
                                    } else {
                                        consecutiveGoodDetections = 0;
                                    }
                                } else {
                                    consecutiveGoodDetections = 0;
                                }
                            } catch (err) {
                                console.error('Detection error:', err);
                            } finally {
                                isProcessing = false;
                            }
                        }
                        requestAnimationFrame(processFaceDetection);
                    }

                    processFaceDetection();

                    switchCameraBtn.addEventListener('click', async () => {
                        facingMode = facingMode === 'user' ? 'environment' : 'user';
                        consecutiveGoodDetections = 0;
                        await startCamera();
                    });

                    retryFaceBtn.addEventListener('click', resetFaceCapture);

                    window.addEventListener('beforeunload', () => {
                        if (currentStream) {
                            currentStream.getTracks().forEach(track => track.stop());
                        }
                    });
                });
            </script>
        </div>

        <div class="mt-4 flex items-center justify-end">
            <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
