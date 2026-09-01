<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AI-powered public speaking coach - Practice and improve your presentation skills with real-time feedback">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SpeakAI Coach') - AI Presentation Coach</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f4ff',
                            100: '#dbe4ff',
                            200: '#bac8ff',
                            300: '#91a7ff',
                            400: '#748ffc',
                            500: '#5c7cfa',
                            600: '#4c6ef5',
                            700: '#4263eb',
                            800: '#3b5bdb',
                            900: '#364fc7',
                        },
                        surface: {
                            900: '#0a0e1a',
                            800: '#0f1629',
                            700: '#151d35',
                            600: '#1a2440',
                            500: '#1e2b4d',
                        }
                    },
                    animation: {
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'fade-in': 'fadeIn 0.6s ease-out',
                    },
                    keyframes: {
                        glow: {
                            '0%': { boxShadow: '0 0 20px rgba(92, 124, 250, 0.3)' },
                            '100%': { boxShadow: '0 0 40px rgba(92, 124, 250, 0.6)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                    },
                },
            },
        }
    </script>

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0f1629; }
        ::-webkit-scrollbar-thumb { background: #4c6ef5; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #5c7cfa; }

        /* Glassmorphism utilities */
        .glass {
            background: rgba(15, 22, 41, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glass-light {
            background: rgba(30, 43, 77, 0.5);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* Score ring animation */
        .score-ring {
            transition: stroke-dashoffset 1.5s ease-in-out;
        }

        /* Gradient text */
        .gradient-text {
            background: linear-gradient(135deg, #5c7cfa, #748ffc, #91a7ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Hover card effect */
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        }

        /* Recording pulse */
        .recording-pulse {
            animation: recordingPulse 1.5s infinite;
        }
        @keyframes recordingPulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            50% { opacity: 0.8; box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
        }
    </style>
</head>
<body class="bg-surface-900 text-gray-100 font-sans antialiased min-h-screen">

    {{-- Navigation --}}
    <nav class="glass fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-lg shadow-brand-500/25 group-hover:shadow-brand-500/50 transition-shadow">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-xl font-bold gradient-text">SpeakAI</span>
                        <span class="text-xs text-gray-400 block -mt-1">Coach</span>
                    </div>
                </a>

                {{-- Nav Links --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('dashboard') }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                              {{ request()->routeIs('dashboard') ? 'bg-brand-600/20 text-brand-300' : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </span>
                    </a>
                    <a href="{{ route('record') }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium transition-all
                              {{ request()->routeIs('record') ? 'bg-brand-600/20 text-brand-300' : 'bg-gradient-to-r from-brand-600 to-brand-500 text-white hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-500/25' }}">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            New Session
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="pt-20 pb-12">
        @yield('content')
    </main>

    {{-- Toast Notifications --}}
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-3"></div>

    <script>
        // Global toast notification system
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const colors = {
                success: 'from-emerald-500/20 to-emerald-600/5 border-emerald-500/30',
                error: 'from-rose-500/20 to-rose-600/5 border-rose-500/30',
                info: 'from-brand-500/20 to-brand-600/5 border-brand-500/30',
                warning: 'from-amber-500/20 to-amber-600/5 border-amber-500/30',
            };
            toast.className = `glass px-5 py-3 rounded-xl bg-gradient-to-r ${colors[type]} animate-slide-up max-w-sm`;
            toast.innerHTML = `<p class="text-sm font-medium text-gray-100">${message}</p>`;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s';
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    </script>

    @yield('scripts')
</body>
</html>
