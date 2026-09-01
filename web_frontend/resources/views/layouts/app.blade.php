<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SpeakCoach — Practice your presentations and get instant feedback on eye contact, posture, and speech clarity.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SpeakCoach') — Practice Smarter</title>

    <!-- Google Fonts: Outfit + JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        ink: {
                            950: '#0c0c0f',
                            900: '#111117',
                            800: '#18181f',
                            700: '#21212b',
                            600: '#2a2a38',
                            500: '#343447',
                        },
                        accent: {
                            DEFAULT: '#7c6af7',
                            light:   '#a498fb',
                            dim:     '#4a3fb5',
                        },
                        success: '#34d399',
                        warn:    '#f59e0b',
                        danger:  '#f87171',
                    },
                    borderRadius: {
                        'xl':  '12px',
                        '2xl': '16px',
                        '3xl': '24px',
                    },
                }
            }
        }
    </script>

    <style>
        /* ── Base resets ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        /* ── Scrollbar ───────────────────────────────────── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #111117; }
        ::-webkit-scrollbar-thumb { background: #343447; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #7c6af7; }

        /* ── Glass card ──────────────────────────────────── */
        .card {
            background: rgba(24, 24, 31, 0.85);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px;
            backdrop-filter: blur(16px);
        }
        .card-sm {
            background: rgba(33, 33, 43, 0.6);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 12px;
        }

        /* ── Accent pill / badge ─────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 99px; font-size: 11px; font-weight: 600;
        }
        .badge-green  { background: rgba(52,211,153,.12); color: #34d399; border: 1px solid rgba(52,211,153,.2); }
        .badge-yellow { background: rgba(245,158,11,.12); color: #f59e0b; border: 1px solid rgba(245,158,11,.2); }
        .badge-red    { background: rgba(248,113,113,.12); color: #f87171; border: 1px solid rgba(248,113,113,.2); }
        .badge-purple { background: rgba(124,106,247,.12); color: #a498fb; border: 1px solid rgba(124,106,247,.2); }

        /* ── Gradient text ───────────────────────────────── */
        .grad-text {
            background: linear-gradient(135deg, #a498fb 0%, #7c6af7 50%, #5b4de0 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Primary button ──────────────────────────────── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
            background: #7c6af7; color: #fff;
            box-shadow: 0 4px 20px rgba(124,106,247,.35);
            transition: all .2s ease;
            border: none; cursor: pointer;
        }
        .btn-primary:hover {
            background: #a498fb;
            box-shadow: 0 4px 28px rgba(124,106,247,.5);
            transform: translateY(-1px);
        }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 10px 20px; border-radius: 10px; font-size: 14px; font-weight: 500;
            background: rgba(255,255,255,.05); color: #9ca3af;
            border: 1px solid rgba(255,255,255,.08);
            transition: all .2s ease; cursor: pointer;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.09); color: #e5e7eb; }

        /* ── Score ring animation ────────────────────────── */
        .ring-track { stroke: rgba(255,255,255,0.06); }
        .ring-fill  { transition: stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1); }

        /* ── Sidebar nav item ────────────────────────────── */
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 10px; font-size: 13.5px; font-weight: 500;
            color: #6b7280; transition: all .18s ease; cursor: pointer; text-decoration: none;
        }
        .nav-item:hover { background: rgba(255,255,255,.05); color: #d1d5db; }
        .nav-item.active { background: rgba(124,106,247,.14); color: #a498fb; }
        .nav-item svg { width: 17px; height: 17px; flex-shrink: 0; }

        /* ── Animations ──────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes recPulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(248,113,113,.6); }
            50%      { box-shadow: 0 0 0 10px rgba(248,113,113,0); }
        }
        .anim-up   { animation: fadeUp .45s ease both; }
        .anim-in   { animation: fadeIn .4s ease both; }
        .anim-up-1 { animation: fadeUp .45s .07s ease both; }
        .anim-up-2 { animation: fadeUp .45s .14s ease both; }
        .anim-up-3 { animation: fadeUp .45s .21s ease both; }
        .rec-pulse { animation: recPulse 1.4s infinite; }

        /* ── Progress bar ────────────────────────────────── */
        .progress-bar { height: 4px; background: #21212b; border-radius: 99px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #7c6af7, #a498fb); border-radius: 99px; transition: width 0.8s ease; }

        /* ── Toast ───────────────────────────────────────── */
        .toast {
            padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 500;
            backdrop-filter: blur(12px);
            animation: fadeUp .3s ease;
            border: 1px solid rgba(255,255,255,.1);
            max-width: 320px;
        }
    </style>
</head>
<body style="background:#0c0c0f; color:#e5e7eb; font-family:'Outfit',sans-serif; min-height:100vh; margin:0;">

{{-- ── Layout wrapper: sidebar + main ── --}}
<div style="display:flex; min-height:100vh;">

    {{-- ── Sidebar ─────────────────────────────── --}}
    <aside id="sidebar" style="width:220px; flex-shrink:0; background:#111117; border-right:1px solid rgba(255,255,255,.06); display:flex; flex-direction:column; position:fixed; top:0; left:0; height:100vh; z-index:40; padding:20px 14px;">

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" style="display:flex; align-items:center; gap:10px; text-decoration:none; margin-bottom:28px; padding:4px 0;">
            <div style="width:34px; height:34px; border-radius:9px; background:linear-gradient(135deg,#7c6af7,#4a3fb5); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                </svg>
            </div>
            <div>
                <div style="font-size:15px; font-weight:700; color:#e5e7eb; line-height:1;">SpeakCoach</div>
                <div style="font-size:10px; color:#4b5563; margin-top:1px;">AI Practice Studio</div>
            </div>
        </a>

        {{-- Navigation --}}
        <nav style="flex:1; display:flex; flex-direction:column; gap:2px;">
            <a href="{{ route('dashboard') }}"
               class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
                </svg>
                Overview
            </a>
            <a href="{{ route('record') }}"
               class="nav-item {{ request()->routeIs('record') ? 'active' : '' }}">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M6.343 6.343a8 8 0 1011.314 0"/>
                </svg>
                New Session
            </a>
        </nav>

        {{-- Bottom section: backend status --}}
        <div style="padding:12px; background:#18181f; border-radius:10px; border:1px solid rgba(255,255,255,.06);">
            <div style="font-size:11px; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">System</div>
            <div id="backendStatus" style="display:flex; align-items:center; gap:6px; font-size:12px; color:#6b7280;">
                <span id="statusDot" style="width:7px; height:7px; border-radius:50%; background:#4b5563; flex-shrink:0; display:inline-block;"></span>
                <span id="statusLabel">Checking…</span>
            </div>
        </div>
    </aside>

    {{-- ── Main content ─────────────────────────── --}}
    <main style="flex:1; margin-left:220px; min-height:100vh; display:flex; flex-direction:column;">

        {{-- Top bar --}}
        <header style="height:56px; background:rgba(12,12,15,.8); border-bottom:1px solid rgba(255,255,255,.06); display:flex; align-items:center; padding:0 28px; position:sticky; top:0; z-index:30; backdrop-filter:blur(12px);">
            <div style="flex:1;">
                <span style="font-size:13px; font-weight:500; color:#4b5563;">@yield('breadcrumb', 'Overview')</span>
            </div>
            <a href="{{ route('record') }}" class="btn-primary" style="padding:7px 16px; font-size:13px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M6.343 6.343a8 8 0 1011.314 0"/></svg>
                Record
            </a>
        </header>

        {{-- Page content --}}
        <div style="flex:1; padding:28px;">
            @yield('content')
        </div>
    </main>
</div>

{{-- Toast container --}}
<div id="toast-container" style="position:fixed; bottom:22px; right:22px; z-index:100; display:flex; flex-direction:column; gap:10px; align-items:flex-end;"></div>

<script>
// ── Toast system ──────────────────────────────────────────
function showToast(msg, type = 'info') {
    const colors = {
        success: { bg: 'rgba(17,24,20,.95)', border: 'rgba(52,211,153,.25)', text: '#34d399', dot: '#34d399' },
        error:   { bg: 'rgba(24,17,17,.95)', border: 'rgba(248,113,113,.25)', text: '#f87171', dot: '#f87171' },
        warning: { bg: 'rgba(24,21,17,.95)', border: 'rgba(245,158,11,.25)', text: '#f59e0b', dot: '#f59e0b' },
        info:    { bg: 'rgba(17,17,24,.95)', border: 'rgba(124,106,247,.25)', text: '#a498fb', dot: '#7c6af7' },
    };
    const c = colors[type] || colors.info;
    const el = document.createElement('div');
    el.className = 'toast';
    el.style.cssText = `background:${c.bg}; border-color:${c.border}; color:${c.text};`;
    el.innerHTML = `<div style="display:flex;align-items:center;gap:8px;">
        <span style="width:7px;height:7px;border-radius:50%;background:${c.dot};flex-shrink:0;"></span>
        <span>${msg}</span>
    </div>`;
    document.getElementById('toast-container').prepend(el);
    setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .3s'; setTimeout(() => el.remove(), 300); }, 4000);
}

// ── Backend status check ──────────────────────────────────
async function checkBackend() {
    const dot = document.getElementById('statusDot');
    const label = document.getElementById('statusLabel');
    try {
        const r = await fetch('http://localhost:8080/api/health', { signal: AbortSignal.timeout(3000) });
        if (r.ok) {
            dot.style.background = '#34d399';
            label.textContent = 'AI Online';
            label.style.color = '#34d399';
        } else { throw new Error(); }
    } catch {
        dot.style.background = '#f87171';
        label.textContent = 'AI Offline';
        label.style.color = '#f87171';
    }
}
checkBackend();
</script>

@yield('scripts')
</body>
</html>
