<?php

use Livewire\Volt\Component;

new class extends Component {
    public $faceEmbedding;

    public function mount()
    {
        $this->faceEmbedding = auth()->user()->face_embeddings;
    }
};
?>

<div>
    <div class="mx-auto w-full max-w-md p-4 dark:bg-gray-900">
        <div class="mb-6 text-center">
            <h2 class="mb-3 text-2xl font-bold text-gray-800 dark:text-white">Face Verification</h2>
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Please prepare:</p>
            <div class="mb-4 flex flex-wrap justify-center gap-3">
                <span
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 transition-colors duration-200 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Clear face
                </span>
                <span
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 transition-colors duration-200 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Good light
                </span>
                <span
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 transition-colors duration-200 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Face forward
                </span>
                <span
                    class="flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-700 transition-colors duration-200 dark:bg-gray-800 dark:text-gray-300">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    No mask
                </span>
            </div>
        </div>
        <div
            class="relative overflow-hidden rounded-2xl bg-white shadow-lg ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
            <div class="absolute inset-0 flex items-center justify-center bg-gray-900/50" id="loadingIndicator">
                <div class="text-white">Loading...</div>
            </div>
            <video class="aspect-[3/4] w-full object-cover" id="video" autoplay playsinline></video>
            <canvas class="hidden" id="canvas"></canvas>
        </div>
        <div class="mt-4 text-center text-sm text-gray-700 dark:text-gray-300" id="result"></div>
    </div>

    <script src="{{ asset('js/face-api.js') }}"></script>
    <script>
        class FaceVerificationSystem {
            constructor() {
                this.video = document.getElementById('video');
                this.canvas = document.getElementById('canvas');
                this.resultDiv = document.getElementById('result');
                this.loadingIndicator = document.getElementById('loadingIndicator');
                this.storedEmbeddings = @json($faceEmbedding);
                this.currentStream = null;
                this.isProcessing = false;
                this.verificationActive = true;
                this.modelPath = '/models';

                this.init();
            }

            async init() {
                try {
                    console.log('Initializing face verification system...');
                    await this.loadModels();
                    await this.startCamera();
                    this.startVerification();
                } catch (error) {
                    this.handleError('Initialization failed', error);
                }
            }

            async loadModels() {
                try {
                    this.showMessage('Loading face detection models...', 'info');
                    await Promise.all([
                        faceapi.nets.faceLandmark68Net.loadFromUri(this.modelPath),
                        faceapi.nets.faceRecognitionNet.loadFromUri(this.modelPath),
                        faceapi.nets.tinyFaceDetector.loadFromUri(this.modelPath), // Remove this line
                    ]);
                    console.log('Face detection models loaded successfully');
                } catch (error) {
                    throw new Error(`Model loading failed: ${error.message}`);
                }
            }

            async startCamera() {
                try {
                    this.currentStream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: 'user',
                            width: {
                                ideal: 1280
                            },
                            height: {
                                ideal: 720
                            }
                        }
                    });

                    this.video.srcObject = this.currentStream;

                    await new Promise(resolve => {
                        this.video.onloadedmetadata = () => {
                            this.canvas.width = this.video.videoWidth;
                            this.canvas.height = this.video.videoHeight;
                            resolve();
                        };
                    });
                } catch (error) {
                    throw new Error('Camera access denied or not available');
                }
            }

            async verifyFace() {
                if (this.isProcessing || !this.verificationActive) return;

                this.isProcessing = true;
                try {
                    const detection = await faceapi.detectSingleFace(
                        this.video,
                        new faceapi.TinyFaceDetectorOptions({
                            inputSize: 512,
                            scoreThreshold: 0.5
                        })
                    ).withFaceLandmarks().withFaceDescriptor();

                    if (!detection) {
                        this.showMessage('No face detected - please center your face', 'warning');
                        this.drawEmpty();
                        return;
                    }

                    const brightness = this.checkBrightness();
                    if (brightness < 50) {
                        this.showMessage('Environment too dark - please improve lighting', 'warning');
                        return;
                    }

                    await this.processDetection(detection);

                } catch (error) {
                    this.handleError('Verification error', error);
                } finally {
                    this.isProcessing = false;
                    if (this.verificationActive) {
                        requestAnimationFrame(() => this.verifyFace());
                    }
                }
            }

            async processDetection(detection) {
                const currentDescriptor = detection.descriptor;
                const storedDescriptor = new Float32Array(JSON.parse(this.storedEmbeddings));
                const distance = faceapi.euclideanDistance(currentDescriptor, storedDescriptor);

                this.drawDetection(detection);

                if (distance < 0.6) {
                    this.showMessage('✅ Face verified successfully!', 'success');
                    this.verificationActive = false;
                    await this.handleSuccess();
                } else {
                    this.showMessage('❌ Face verification failed - please try again', 'error');
                }
            }

            startVerification() {
                this.hideLoadingIndicator();
                this.verifyFace();
            }

            checkBrightness() {
                const ctx = this.canvas.getContext('2d');
                ctx.drawImage(this.video, 0, 0);
                const imageData = ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
                const data = imageData.data;
                let brightness = 0;

                for (let i = 0; i < data.length; i += 4) {
                    brightness += (data[i] + data[i + 1] + data[i + 2]) / 3;
                }

                return brightness / (data.length / 4);
            }

            drawDetection(detection) {
                const ctx = this.canvas.getContext('2d');
                ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                const dims = faceapi.matchDimensions(this.canvas, {
                    width: this.video.videoWidth,
                    height: this.video.videoHeight
                }, true);

                const resizedDetection = faceapi.resizeResults(detection, dims);
                faceapi.draw.drawDetections(this.canvas, [resizedDetection]);
                faceapi.draw.drawFaceLandmarks(this.canvas, [resizedDetection]);
            }

            drawEmpty() {
                const ctx = this.canvas.getContext('2d');
                ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            }

            showMessage(message, type = 'info') {
                const colors = {
                    info: 'text-blue-500',
                    success: 'text-green-500',
                    warning: 'text-yellow-500',
                    error: 'text-red-500'
                };

                this.resultDiv.className = `mt-4 text-center ${colors[type]}`;
                this.resultDiv.textContent = message;
            }

            hideLoadingIndicator() {
                this.loadingIndicator.style.display = 'none';
            }

            handleError(context, error) {
                console.error(`${context}:`, error);
                this.showMessage(`Error: ${error.message}`, 'error');
            }

            async handleSuccess() {
                document.getElementById('redirectForm').submit();
            }

            cleanup() {
                this.verificationActive = false;
                if (this.currentStream) {
                    this.currentStream.getTracks().forEach(track => track.stop());
                }
            }
        }

        // Initialize the system when face-api is ready
        const initSystem = async () => {
            while (typeof faceapi === 'undefined') {
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            const system = new FaceVerificationSystem();
            window.addEventListener('beforeunload', () => system.cleanup());
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSystem);
        } else {
            initSystem();
        }
    </script>
</div>
