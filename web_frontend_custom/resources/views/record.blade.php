{{-- ============================================================
    Record Page - Webcam & Microphone Capture
    ============================================================
    Features:
    - Live webcam preview
    - MediaRecorder API for video+audio capture
    - Start/Stop recording controls
    - Upload recorded blob to Laravel backend
    ============================================================ --}}

@extends('layouts.app')

@section('title', 'Record Session')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Page Header --}}
    <div class="text-center mb-8 animate-fade-in">
        <h1 class="text-3xl font-bold gradient-text mb-2">Practice Session</h1>
        <p class="text-gray-400 text-sm max-w-lg mx-auto">
            Position yourself in front of the camera, speak clearly, and let our AI coach analyze your presentation skills.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Video Preview Panel (2/3 width) --}}
        <div class="lg:col-span-2 animate-slide-up">
            <div class="glass rounded-2xl overflow-hidden">
                {{-- Video Container --}}
                <div class="relative aspect-video bg-surface-800 rounded-t-2xl overflow-hidden">
                    {{-- Live Video Feed --}}
                    <video id="videoPreview" autoplay muted playsinline 
                           class="w-full h-full object-cover transform -scale-x-100">
                    </video>

                    {{-- No Camera Overlay --}}
                    <div id="noCameraOverlay" class="absolute inset-0 flex flex-col items-center justify-center bg-surface-800">
                        <div class="w-20 h-20 rounded-full bg-surface-700 flex items-center justify-center mb-4 animate-pulse-slow">
                            <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-400 text-sm">Click "Start Camera" to begin</p>
                    </div>

                    {{-- Recording Indicator --}}
                    <div id="recordingBadge" class="absolute top-4 left-4 hidden">
                        <div class="flex items-center gap-2 bg-red-500/20 backdrop-blur-sm border border-red-500/30 rounded-full px-3 py-1.5">
                            <div class="w-2.5 h-2.5 rounded-full bg-red-500 recording-pulse"></div>
                            <span class="text-red-300 text-xs font-semibold tracking-wider">REC</span>
                            <span id="recordingTimer" class="text-red-300 text-xs font-mono">00:00</span>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    <div id="statusBadge" class="absolute top-4 right-4">
                        <div class="flex items-center gap-2 glass-light rounded-full px-3 py-1.5">
                            <div id="statusDot" class="w-2 h-2 rounded-full bg-gray-500"></div>
                            <span id="statusText" class="text-xs font-medium text-gray-300">Inactive</span>
                        </div>
                    </div>
                </div>

                {{-- Controls Bar --}}
                <div class="p-5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        {{-- Start Camera Button --}}
                        <button id="btnStartCamera" onclick="startCamera()"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-surface-600 hover:bg-surface-500 text-sm font-medium transition-all hover:shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            Start Camera
                        </button>

                        {{-- Record Button --}}
                        <button id="btnRecord" onclick="toggleRecording()" disabled
                                class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 text-white text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-500/25 disabled:shadow-none">
                            <svg id="recordIcon" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8" />
                            </svg>
                            <span id="recordBtnText">Start Recording</span>
                        </button>
                    </div>

                    {{-- Session Title Input --}}
                    <input type="text" id="sessionTitle" 
                           placeholder="Session title (optional)"
                           class="bg-surface-700 border border-white/5 rounded-lg px-3 py-2 text-sm text-gray-300 placeholder-gray-500 focus:outline-none focus:border-brand-500/50 w-48">
                </div>
            </div>
        </div>

        {{-- Side Panel (1/3 width) --}}
        <div class="space-y-5 animate-slide-up" style="animation-delay: 0.1s;">

            {{-- Tips Card --}}
            <div class="glass rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-gray-200 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Quick Tips
                </h3>
                <ul class="space-y-2.5 text-xs text-gray-400">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 mt-0.5">✓</span>
                        Look directly at the camera lens for eye contact
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 mt-0.5">✓</span>
                        Sit or stand upright with shoulders back
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 mt-0.5">✓</span>
                        Speak clearly and at a moderate pace
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 mt-0.5">✓</span>
                        Avoid filler words like "um", "uh", "like"
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 mt-0.5">✓</span>
                        Ensure good lighting on your face
                    </li>
                </ul>
            </div>

            {{-- Analysis Status Card --}}
            <div id="analysisCard" class="glass rounded-2xl p-5 hidden">
                <h3 class="text-sm font-semibold text-gray-200 mb-4">Analysis Progress</h3>
                <div class="space-y-3">
                    <div id="step-upload" class="flex items-center gap-3 text-xs text-gray-500">
                        <div class="w-6 h-6 rounded-full border border-gray-600 flex items-center justify-center">
                            <span class="step-num">1</span>
                        </div>
                        <span>Uploading video...</span>
                    </div>
                    <div id="step-vision" class="flex items-center gap-3 text-xs text-gray-500">
                        <div class="w-6 h-6 rounded-full border border-gray-600 flex items-center justify-center">
                            <span class="step-num">2</span>
                        </div>
                        <span>Analyzing eye contact & posture...</span>
                    </div>
                    <div id="step-audio" class="flex items-center gap-3 text-xs text-gray-500">
                        <div class="w-6 h-6 rounded-full border border-gray-600 flex items-center justify-center">
                            <span class="step-num">3</span>
                        </div>
                        <span>Transcribing speech...</span>
                    </div>
                    <div id="step-ai" class="flex items-center gap-3 text-xs text-gray-500">
                        <div class="w-6 h-6 rounded-full border border-gray-600 flex items-center justify-center">
                            <span class="step-num">4</span>
                        </div>
                        <span>Generating AI feedback...</span>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-4 h-1.5 bg-surface-700 rounded-full overflow-hidden">
                    <div id="progressBar" class="h-full bg-gradient-to-r from-brand-600 to-brand-400 rounded-full transition-all duration-1000" style="width: 0%"></div>
                </div>
            </div>

            {{-- How It Works --}}
            <div class="glass rounded-2xl p-5">
                <h3 class="text-sm font-semibold text-gray-200 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    How It Works
                </h3>
                <ol class="space-y-2 text-xs text-gray-400 list-decimal list-inside">
                    <li>Start the camera and position yourself</li>
                    <li>Record your practice speech</li>
                    <li>Stop recording when done</li>
                    <li>AI analyzes your video & audio</li>
                    <li>Get detailed coaching feedback</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ============================================================
// MediaRecorder & Analysis JavaScript
// ============================================================

let mediaStream = null;
let mediaRecorder = null;
let recordedChunks = [];
let isRecording = false;
let recordingStartTime = null;
let timerInterval = null;

// ---- Camera Control ----

async function startCamera() {
    try {
        // Request webcam + microphone access
        mediaStream = await navigator.mediaDevices.getUserMedia({
            video: { width: 1280, height: 720, facingMode: 'user' },
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                sampleRate: 44100,
            },
        });

        // Display live preview
        const video = document.getElementById('videoPreview');
        video.srcObject = mediaStream;

        // Update UI state
        document.getElementById('noCameraOverlay').classList.add('hidden');
        document.getElementById('btnRecord').disabled = false;
        document.getElementById('btnStartCamera').innerHTML = `
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Camera Active
        `;
        document.getElementById('btnStartCamera').classList.add('bg-emerald-600/20', 'text-emerald-300');
        document.getElementById('btnStartCamera').disabled = true;

        updateStatus('ready', 'Ready');
        showToast('Camera and microphone connected!', 'success');

    } catch (err) {
        console.error('Camera access failed:', err);
        showToast('Failed to access camera/microphone. Please allow permissions.', 'error');
        updateStatus('error', 'Error');
    }
}

// ---- Recording Control ----

function toggleRecording() {
    if (isRecording) {
        stopRecording();
    } else {
        startRecording();
    }
}

function startRecording() {
    if (!mediaStream) {
        showToast('Please start the camera first.', 'warning');
        return;
    }

    recordedChunks = [];

    // Determine supported MIME type
    const mimeType = MediaRecorder.isTypeSupported('video/webm;codecs=vp9,opus')
        ? 'video/webm;codecs=vp9,opus'
        : MediaRecorder.isTypeSupported('video/webm;codecs=vp8,opus')
            ? 'video/webm;codecs=vp8,opus'
            : 'video/webm';

    mediaRecorder = new MediaRecorder(mediaStream, {
        mimeType: mimeType,
        videoBitsPerSecond: 2500000, // 2.5 Mbps
    });

    mediaRecorder.ondataavailable = (event) => {
        if (event.data.size > 0) {
            recordedChunks.push(event.data);
        }
    };

    mediaRecorder.onstop = () => {
        const blob = new Blob(recordedChunks, { type: mimeType });
        uploadRecording(blob);
    };

    // Start recording (collect data every second)
    mediaRecorder.start(1000);
    isRecording = true;
    recordingStartTime = Date.now();

    // Update UI
    document.getElementById('recordingBadge').classList.remove('hidden');
    document.getElementById('recordBtnText').textContent = 'Stop Recording';
    document.getElementById('recordIcon').innerHTML = '<rect x="6" y="6" width="12" height="12" rx="1" />';
    document.getElementById('btnRecord').classList.remove('from-brand-600', 'to-brand-500', 'shadow-brand-500/25');
    document.getElementById('btnRecord').classList.add('from-red-600', 'to-red-500', 'shadow-red-500/25');

    updateStatus('recording', 'Recording');

    // Start timer
    timerInterval = setInterval(updateTimer, 1000);

    showToast('Recording started. Speak naturally!', 'info');
}

function stopRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
    }

    isRecording = false;
    clearInterval(timerInterval);

    // Update UI
    document.getElementById('recordingBadge').classList.add('hidden');
    document.getElementById('recordBtnText').textContent = 'Start Recording';
    document.getElementById('recordIcon').innerHTML = '<circle cx="12" cy="12" r="8" />';
    document.getElementById('btnRecord').classList.add('from-brand-600', 'to-brand-500', 'shadow-brand-500/25');
    document.getElementById('btnRecord').classList.remove('from-red-600', 'to-red-500', 'shadow-red-500/25');
    document.getElementById('btnRecord').disabled = true;

    updateStatus('processing', 'Processing...');
    showToast('Recording stopped. Analyzing...', 'info');
}

function updateTimer() {
    const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
    const mins = String(Math.floor(elapsed / 60)).padStart(2, '0');
    const secs = String(elapsed % 60).padStart(2, '0');
    document.getElementById('recordingTimer').textContent = `${mins}:${secs}`;
}

// ---- Upload & Analysis ----

async function uploadRecording(blob) {
    const analysisCard = document.getElementById('analysisCard');
    analysisCard.classList.remove('hidden');

    // Animate progress steps
    animateStep('step-upload', true);
    updateProgress(15);

    const formData = new FormData();
    formData.append('video', blob, 'recording.webm');
    formData.append('title', document.getElementById('sessionTitle').value || '');

    try {
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        animateStep('step-upload', true);
        updateProgress(25);

        const response = await fetch('/analyze', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: formData,
        });

        animateStep('step-upload', 'done');
        animateStep('step-vision', true);
        updateProgress(50);

        // Simulate step progress (actual processing is server-side)
        setTimeout(() => {
            animateStep('step-vision', 'done');
            animateStep('step-audio', true);
            updateProgress(70);
        }, 1000);

        setTimeout(() => {
            animateStep('step-audio', 'done');
            animateStep('step-ai', true);
            updateProgress(85);
        }, 2000);

        const result = await response.json();

        if (result.success) {
            animateStep('step-ai', 'done');
            updateProgress(100);

            showToast('Analysis complete! Redirecting to results...', 'success');

            // Redirect to session detail or dashboard
            setTimeout(() => {
                if (result.session_id) {
                    window.location.href = `/session/${result.session_id}`;
                } else {
                    window.location.href = '/';
                }
            }, 1500);
        } else {
            throw new Error(result.message || 'Analysis failed');
        }

    } catch (error) {
        console.error('Upload failed:', error);
        showToast(error.message || 'Failed to analyze recording. Please try again.', 'error');
        updateStatus('error', 'Error');
        document.getElementById('btnRecord').disabled = false;
    }
}

// ---- UI Helpers ----

function updateStatus(state, text) {
    const dot = document.getElementById('statusDot');
    const label = document.getElementById('statusText');
    label.textContent = text;

    dot.className = 'w-2 h-2 rounded-full';
    const colors = {
        ready: 'bg-emerald-400',
        recording: 'bg-red-500 recording-pulse',
        processing: 'bg-amber-400 animate-pulse',
        error: 'bg-rose-500',
        inactive: 'bg-gray-500',
    };
    dot.classList.add(...(colors[state] || 'bg-gray-500').split(' '));
}

function animateStep(stepId, state) {
    const el = document.getElementById(stepId);
    if (state === true) {
        el.classList.remove('text-gray-500');
        el.classList.add('text-brand-300');
        el.querySelector('.step-num').innerHTML = `
            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>`;
    } else if (state === 'done') {
        el.classList.remove('text-brand-300');
        el.classList.add('text-emerald-400');
        el.querySelector('.step-num').innerHTML = '✓';
    }
}

function updateProgress(pct) {
    document.getElementById('progressBar').style.width = pct + '%';
}
</script>
@endsection
