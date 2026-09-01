{{-- ============================================================
    Session Detail View - Full Analysis Results
    ============================================================ --}}

@extends('layouts.app')

@section('title', $session->title ?: 'Session Details')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Back Link & Header --}}
    <div class="mb-8 animate-fade-in">
        <a href="{{ route('dashboard') }}" class="text-xs text-gray-500 hover:text-brand-400 transition-colors flex items-center gap-1 mb-4">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Dashboard
        </a>
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $session->title ?: 'Practice Session' }}</h1>
                <p class="text-sm text-gray-400 mt-1">
                    {{ $session->created_at->format('F d, Y · h:i A') }} · {{ $session->formatted_duration }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-center">
                    <div class="text-3xl font-bold gradient-text">{{ number_format($session->overall_score, 0) }}</div>
                    <div class="text-[10px] text-gray-500 uppercase tracking-wider">Overall</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Score Cards Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        @php
            $scores = [
                ['label' => 'Eye Contact', 'score' => $session->eye_contact_score, 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'emerald'],
                ['label' => 'Posture', 'score' => $session->posture_score, 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'violet'],
                ['label' => 'Filler Words', 'score' => max(0, 100 - $session->filler_word_count * 5), 'icon' => 'M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z', 'color' => 'amber'],
            ];
        @endphp

        @foreach($scores as $s)
        <div class="glass rounded-2xl p-6 text-center hover-lift animate-slide-up">
            <div class="relative w-28 h-28 mx-auto mb-4">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="6" />
                    <circle cx="50" cy="50" r="42" fill="none" 
                            stroke="currentColor" stroke-width="6" stroke-linecap="round"
                            stroke-dasharray="{{ 2 * 3.14159 * 42 }}"
                            stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $s['score'] / 100) }}"
                            class="score-ring text-{{ $s['color'] }}-400" />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-2xl font-bold text-white">{{ number_format($s['score'], 0) }}</span>
                </div>
            </div>
            <h3 class="text-sm font-semibold text-gray-200">{{ $s['label'] }}</h3>
        </div>
        @endforeach
    </div>

    {{-- AI Feedback Section --}}
    <div class="glass rounded-2xl p-6 mb-8 animate-slide-up" style="animation-delay: 0.15s;">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500/20 to-brand-600/5 flex items-center justify-center">
                <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
            </div>
            AI Coach Feedback
        </h2>

        @if($session->ai_summary)
        <div class="bg-gradient-to-r from-brand-500/10 to-transparent border border-brand-500/10 rounded-xl p-4 mb-5">
            <p class="text-sm text-gray-200 leading-relaxed">{{ $session->ai_summary }}</p>
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @if($session->eye_contact_feedback)
            <div class="glass-light rounded-xl p-4">
                <h4 class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-2">Eye Contact</h4>
                <p class="text-sm text-gray-300 leading-relaxed">{{ $session->eye_contact_feedback }}</p>
            </div>
            @endif

            @if($session->posture_feedback)
            <div class="glass-light rounded-xl p-4">
                <h4 class="text-xs font-semibold text-violet-400 uppercase tracking-wider mb-2">Posture & Body Language</h4>
                <p class="text-sm text-gray-300 leading-relaxed">{{ $session->posture_feedback }}</p>
            </div>
            @endif

            @if($session->speech_feedback)
            <div class="glass-light rounded-xl p-4">
                <h4 class="text-xs font-semibold text-blue-400 uppercase tracking-wider mb-2">Speech & Clarity</h4>
                <p class="text-sm text-gray-300 leading-relaxed">{{ $session->speech_feedback }}</p>
            </div>
            @endif

            @if($session->filler_word_feedback)
            <div class="glass-light rounded-xl p-4">
                <h4 class="text-xs font-semibold text-amber-400 uppercase tracking-wider mb-2">Filler Words</h4>
                <p class="text-sm text-gray-300 leading-relaxed">{{ $session->filler_word_feedback }}</p>
            </div>
            @endif
        </div>

        {{-- Actionable Tips --}}
        @if($session->actionable_tips && count($session->actionable_tips) > 0)
        <div class="mt-5 glass-light rounded-xl p-4">
            <h4 class="text-xs font-semibold text-brand-400 uppercase tracking-wider mb-3">Actionable Tips</h4>
            <ul class="space-y-2">
                @foreach($session->actionable_tips as $tip)
                <li class="flex items-start gap-2 text-sm text-gray-300">
                    <span class="text-brand-400 mt-0.5 shrink-0">→</span>
                    {{ $tip }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Encouragement --}}
        @if($session->encouragement)
        <div class="mt-5 bg-gradient-to-r from-emerald-500/10 to-transparent border border-emerald-500/10 rounded-xl p-4">
            <p class="text-sm text-emerald-300 italic">"{{ $session->encouragement }}"</p>
        </div>
        @endif
    </div>

    {{-- Transcription --}}
    @if($session->transcription)
    <div class="glass rounded-2xl p-6 mb-8 animate-slide-up" style="animation-delay: 0.25s;">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-white">Speech Transcription</h2>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <span>{{ $session->word_count }} words</span>
                <span>{{ $session->words_per_minute }} WPM</span>
            </div>
        </div>
        <div class="bg-surface-800 rounded-xl p-4 max-h-64 overflow-y-auto">
            <p class="text-sm text-gray-300 leading-relaxed whitespace-pre-line">{{ $session->transcription }}</p>
        </div>

        @if($session->filler_word_breakdown && count($session->filler_word_breakdown) > 0)
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach($session->filler_word_breakdown as $filler)
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400 text-xs font-medium">
                "{{ $filler['word'] }}" × {{ $filler['count'] }}
            </span>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex items-center justify-between animate-fade-in">
        <a href="{{ route('record') }}"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 text-white text-sm font-semibold hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-500/25 transition-all">
            Practice Again
        </a>
        <form action="{{ route('session.delete', $session->id) }}" method="POST" onsubmit="return confirm('Delete this session?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-xs text-gray-500 hover:text-rose-400 transition-colors">
                Delete Session
            </button>
        </form>
    </div>
</div>
@endsection
