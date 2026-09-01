@extends('layouts.app')
@section('title', 'Record Session')
@section('breadcrumb', 'New Session')

@section('content')

<div class="anim-in" style="max-width:960px; margin:0 auto;">

    {{-- Header --}}
    <div style="margin-bottom:22px;">
        <h1 style="font-size:22px; font-weight:700; color:#f3f4f6; margin:0 0 4px;">Practice Session</h1>
        <p style="font-size:13px; color:#6b7280; margin:0;">Set up your camera and start recording whenever you're ready.</p>
    </div>

    <div style="display:grid; grid-template-columns:1fr 280px; gap:18px; align-items:start;">

        {{-- ── Left: Camera + controls ── --}}
        <div>
            <div class="card" style="overflow:hidden;">
                {{-- Video area --}}
                <div id="videoWrap" style="position:relative; aspect-ratio:16/9; background:#111117;">
                    <video id="videoPreview" autoplay muted playsinline
                           style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1); display:none;"></video>

                    {{-- Placeholder --}}
                    <div id="placeholder" style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px;">
                        <div style="width:60px; height:60px; border-radius:50%; background:#18181f; border:1px solid rgba(255,255,255,.07); display:flex; align-items:center; justify-content:center;">
                            <svg width="26" height="26" fill="none" stroke="#4b5563" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p style="font-size:13px; color:#4b5563;">Click "Enable Camera" to begin</p>
                    </div>

                    {{-- Countdown overlay --}}
                    <div id="countdownOverlay" style="position:absolute; inset:0; display:none; align-items:center; justify-content:center; background:rgba(12,12,15,.7); z-index:10; backdrop-filter:blur(4px);">
                        <div style="text-align:center;">
                            <div id="countdownNum" style="font-size:80px; font-weight:800; color:#fff; line-height:1; margin-bottom:8px;">3</div>
                            <div style="font-size:14px; color:#9ca3af;">Get ready…</div>
                        </div>
                    </div>

                    {{-- REC badge --}}
                    <div id="recBadge" style="position:absolute; top:14px; left:14px; display:none; align-items:center; gap:6px; background:rgba(12,12,15,.85); border:1px solid rgba(248,113,113,.3); border-radius:99px; padding:5px 10px; font-size:12px; font-weight:600; color:#f87171; backdrop-filter:blur(8px);">
                        <span id="recDot" style="width:8px; height:8px; border-radius:50%; background:#f87171;" class="rec-pulse"></span>
                        REC &nbsp;<span id="recTimer" style="font-family:'JetBrains Mono',monospace; font-size:11px;">00:00</span>
                    </div>

                    {{-- Status pill --}}
                    <div id="statusPill" style="position:absolute; top:14px; right:14px; display:flex; align-items:center; gap:6px; background:rgba(12,12,15,.8); border:1px solid rgba(255,255,255,.1); border-radius:99px; padding:5px 12px; font-size:12px; color:#6b7280; backdrop-filter:blur(8px);">
                        <span id="sDot" style="width:7px; height:7px; border-radius:50%; background:#4b5563; display:inline-block;"></span>
                        <span id="sText">Inactive</span>
                    </div>
                </div>

                {{-- Controls bar --}}
                <div style="padding:16px 20px; display:flex; align-items:center; gap:12px; border-top:1px solid rgba(255,255,255,.06);">
                    <button id="btnCamera" onclick="enableCamera()"
                            style="display:inline-flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; font-size:13px; font-weight:500; background:#18181f; border:1px solid rgba(255,255,255,.09); color:#9ca3af; cursor:pointer; transition:all .2s;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Enable Camera
                    </button>

                    <button id="btnRecord" onclick="startCountdown()" disabled
                            class="btn-primary" style="opacity:.4; cursor:not-allowed; transition:all .2s;">
                        <svg id="recIcon" width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8"/></svg>
                        <span id="recLabel">Start Recording</span>
                    </button>

                    <div style="flex:1;"></div>

                    <input type="text" id="sessionTitle" placeholder="Session title (optional)"
                           style="background:#18181f; border:1px solid rgba(255,255,255,.08); border-radius:8px; padding:8px 12px; font-size:13px; color:#d1d5db; width:180px; outline:none; font-family:inherit;"
                           onfocus="this.style.borderColor='rgba(124,106,247,.5)'"
                           onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                </div>
            </div>
        </div>

        {{-- ── Right: Info panels ── --}}
        <div style="display:flex; flex-direction:column; gap:14px;">

            {{-- Checklist --}}
            <div id="tipCard" class="card anim-up" style="padding:18px;">
                <div style="font-size:12px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px;">Before You Start</div>
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @php $tips = [
                        ['icon'=>'M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z', 'icon2'=>'circle cx="12" cy="12" r="3"', 'text'=>'Look directly at camera lens'],
                        ['icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'text'=>'Sit upright, shoulders back'],
                        ['icon'=>'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'text'=>'Good lighting on your face'],
                        ['icon'=>'M15.536 8.464a5 5 0 010 7.072M12 9v3m0 0v3m0-3h3m-3 0H9', 'text'=>'Speak at a moderate pace'],
                        ['icon'=>'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636', 'text'=>'Minimize "um", "uh", "like"'],
                    ]; @endphp
                    @foreach($tips as $tip)
                    <div style="display:flex; align-items:flex-start; gap:9px;">
                        <div style="width:20px; height:20px; border-radius:6px; background:rgba(124,106,247,.12); border:1px solid rgba(124,106,247,.2); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px;">
                            <svg width="11" height="11" fill="none" stroke="#a498fb" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tip['icon'] }}"/>
                            </svg>
                        </div>
                        <span style="font-size:12.5px; color:#9ca3af; line-height:1.4;">{{ $tip['text'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Analysis progress --}}
            <div id="analysisCard" class="card anim-up-1" style="padding:18px; display:none;">
                <div style="font-size:12px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:14px;">Analyzing…</div>
                <div style="display:flex; flex-direction:column; gap:10px;" id="stepList">
                    @php $steps = [
                        ['id'=>'s-upload', 'label'=>'Uploading video'],
                        ['id'=>'s-vision', 'label'=>'Eye contact & posture'],
                        ['id'=>'s-audio',  'label'=>'Speech transcription'],
                        ['id'=>'s-ai',     'label'=>'AI feedback'],
                    ]; @endphp
                    @foreach($steps as $step)
                    <div id="{{ $step['id'] }}" style="display:flex; align-items:center; gap:10px; font-size:12.5px; color:#4b5563;">
                        <div style="width:22px; height:22px; border-radius:50%; border:1.5px solid #21212b; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                            <span class="step-icon" style="font-size:10px;">{{ $loop->iteration }}</span>
                        </div>
                        <span>{{ $step['label'] }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="progress-bar" style="margin-top:14px;">
                    <div id="progressFill" class="progress-fill" style="width:0%;"></div>
                </div>
                <div id="progressPct" style="font-size:11px; color:#4b5563; text-align:right; margin-top:5px;">0%</div>
            </div>

            {{-- How it works --}}
            <div id="howCard" class="card anim-up-2" style="padding:18px;">
                <div style="font-size:12px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px;">How It Works</div>
                <ol style="padding-left:16px; margin:0; display:flex; flex-direction:column; gap:7px;">
                    @foreach(['Enable your camera','Start the countdown','Deliver your speech','Stop & submit for AI analysis','Review your detailed report'] as $i => $step)
                    <li style="font-size:12.5px; color:#6b7280; line-height:1.4;">{{ $step }}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// ── State ─────────────────────────────────────────────────
let stream = null, recorder = null, chunks = [], recording = false;
let timerHandle = null, startedAt = null;

// ── Camera ────────────────────────────────────────────────
async function enableCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { width: 1280, height: 720, facingMode: 'user' },
            audio: { echoCancellation: true, noiseSuppression: true }
        });
        const vid = document.getElementById('videoPreview');
        vid.srcObject = stream;
        vid.style.display = 'block';
        document.getElementById('placeholder').style.display = 'none';

        const btn = document.getElementById('btnCamera');
        btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="#34d399" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg> Camera On';
        btn.style.color = '#34d399'; btn.style.borderColor = 'rgba(52,211,153,.3)';
        btn.disabled = true;

        const rec = document.getElementById('btnRecord');
        rec.disabled = false; rec.style.opacity = '1'; rec.style.cursor = 'pointer';

        setStatus('ready', '#34d399', 'Ready');
        showToast('Camera enabled — ready to record!', 'success');
    } catch (e) {
        showToast('Camera access denied. Please allow permissions.', 'error');
        setStatus('error', '#f87171', 'Error');
    }
}

// ── Countdown ─────────────────────────────────────────────
function startCountdown() {
    if (recording) { stopRecording(); return; }

    const overlay = document.getElementById('countdownOverlay');
    const num = document.getElementById('countdownNum');
    overlay.style.display = 'flex';

    let count = 3;
    num.textContent = count;

    const id = setInterval(() => {
        count--;
        if (count <= 0) {
            clearInterval(id);
            overlay.style.display = 'none';
            startRecording();
        } else {
            num.textContent = count;
            num.style.transform = 'scale(1.2)';
            setTimeout(() => { num.style.transform = 'scale(1)'; }, 150);
        }
    }, 1000);
}

// ── Recording ─────────────────────────────────────────────
function startRecording() {
    chunks = [];
    const mime = ['video/webm;codecs=vp9,opus','video/webm;codecs=vp8,opus','video/webm']
                 .find(t => MediaRecorder.isTypeSupported(t)) || 'video/webm';

    recorder = new MediaRecorder(stream, { mimeType: mime, videoBitsPerSecond: 2_500_000 });
    recorder.ondataavailable = e => { if (e.data.size > 0) chunks.push(e.data); };
    recorder.onstop = () => upload(new Blob(chunks, { type: mime }));
    recorder.start(1000);
    recording = true;
    startedAt = Date.now();

    document.getElementById('recBadge').style.display = 'flex';
    document.getElementById('recLabel').textContent = 'Stop Recording';
    document.getElementById('recIcon').innerHTML = '<rect x="6" y="6" width="12" height="12" rx="1.5"/>';

    const btn = document.getElementById('btnRecord');
    btn.style.background = '#ef4444';
    btn.style.boxShadow = '0 4px 20px rgba(239,68,68,.4)';

    timerHandle = setInterval(updateTimer, 1000);
    setStatus('recording', '#f87171', 'Recording');
    showToast('Recording started — speak naturally!', 'info');
}

function stopRecording() {
    if (recorder && recorder.state !== 'inactive') recorder.stop();
    recording = false;
    clearInterval(timerHandle);

    document.getElementById('recBadge').style.display = 'none';
    document.getElementById('recLabel').textContent = 'Start Recording';
    document.getElementById('recIcon').innerHTML = '<circle cx="12" cy="12" r="8"/>';

    const btn = document.getElementById('btnRecord');
    btn.style.background = '#7c6af7';
    btn.style.boxShadow = '0 4px 20px rgba(124,106,247,.35)';
    btn.disabled = true; btn.style.opacity = '.4'; btn.style.cursor = 'not-allowed';

    setStatus('processing', '#f59e0b', 'Processing…');
}

function updateTimer() {
    const s = Math.floor((Date.now() - startedAt) / 1000);
    document.getElementById('recTimer').textContent =
        String(Math.floor(s/60)).padStart(2,'0') + ':' + String(s%60).padStart(2,'0');
}

// ── Upload ────────────────────────────────────────────────
async function upload(blob) {
    // Show analysis panel, hide how-it-works & tip cards
    document.getElementById('analysisCard').style.display = 'block';
    document.getElementById('howCard').style.display = 'none';

    setStep('s-upload', 'active');
    setProgress(10);

    const fd = new FormData();
    fd.append('video', blob, 'recording.webm');
    fd.append('title', document.getElementById('sessionTitle').value || '');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    try {
        setProgress(20);
        const res = await fetch('/analyze', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: fd
        });

        setStep('s-upload', 'done'); setStep('s-vision', 'active'); setProgress(45);
        await delay(900);
        setStep('s-vision', 'done'); setStep('s-audio', 'active'); setProgress(65);
        await delay(700);
        setStep('s-audio', 'done'); setStep('s-ai', 'active'); setProgress(82);

        const data = await res.json();
        setStep('s-ai', 'done'); setProgress(100);

        if (data.success) {
            showToast('Analysis complete! Loading results…', 'success');
            setTimeout(() => {
                window.location.href = data.session_id ? `/session/${data.session_id}` : '/';
            }, 1200);
        } else {
            throw new Error(data.message || 'Analysis failed');
        }
    } catch (err) {
        showToast(err.message || 'Upload failed. Please try again.', 'error');
        setStatus('error', '#f87171', 'Error');
        const btn = document.getElementById('btnRecord');
        btn.disabled = false; btn.style.opacity = '1'; btn.style.cursor = 'pointer';
    }
}

// ── Helpers ───────────────────────────────────────────────
function setStatus(_, color, text) {
    document.getElementById('sDot').style.background = color;
    document.getElementById('sText').textContent = text;
    document.getElementById('sText').style.color = color;
}
function setProgress(pct) {
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('progressPct').textContent = pct + '%';
}
function setStep(id, state) {
    const el = document.getElementById(id);
    if (!el) return;
    const icon = el.querySelector('.step-icon');
    if (state === 'active') {
        el.style.color = '#a498fb';
        icon.innerHTML = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" fill="currentColor" opacity=".4"/></svg>';
    } else if (state === 'done') {
        el.style.color = '#34d399';
        icon.textContent = '✓';
        el.querySelector('div').style.borderColor = 'rgba(52,211,153,.4)';
    }
}
function delay(ms) { return new Promise(r => setTimeout(r, ms)); }
</script>
@endsection
