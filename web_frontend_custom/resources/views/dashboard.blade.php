{{-- ============================================================
    Dashboard - Historical Scores & AI Feedback
    ============================================================
    Displays:
    - Average score overview cards
    - Score trend visualization
    - Session history with detailed metrics
    ============================================================ --}}

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8 animate-fade-in">
        <div>
            <h1 class="text-3xl font-bold gradient-text">Dashboard</h1>
            <p class="text-gray-400 text-sm mt-1">Track your public speaking progress over time</p>
        </div>
        <a href="{{ route('record') }}"
           class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 text-white text-sm font-semibold hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-500/25 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            New Session
        </a>
    </div>

    {{-- Score Overview Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

        {{-- Overall Score --}}
        <div class="glass rounded-2xl p-5 hover-lift animate-slide-up">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Overall Score</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500/20 to-brand-600/5 flex items-center justify-center">
                    <svg class="w-4 h-4 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ number_format($averages['overall'], 1) }}</div>
            <div class="text-xs text-gray-500 mt-1">Average across sessions</div>
        </div>

        {{-- Eye Contact --}}
        <div class="glass rounded-2xl p-5 hover-lift animate-slide-up" style="animation-delay: 0.05s;">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Eye Contact</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500/20 to-emerald-600/5 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold {{ \App\Models\PresentationSession::getScoreColor($averages['eye_contact']) }}">
                {{ number_format($averages['eye_contact'], 1) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Avg. eye contact score</div>
        </div>

        {{-- Posture --}}
        <div class="glass rounded-2xl p-5 hover-lift animate-slide-up" style="animation-delay: 0.10s;">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Posture</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500/20 to-violet-600/5 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold {{ \App\Models\PresentationSession::getScoreColor($averages['posture']) }}">
                {{ number_format($averages['posture'], 1) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Avg. posture score</div>
        </div>

        {{-- Filler Words --}}
        <div class="glass rounded-2xl p-5 hover-lift animate-slide-up" style="animation-delay: 0.15s;">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">Filler Words</span>
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500/20 to-amber-600/5 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-amber-400">{{ number_format($averages['filler_words'], 0) }}</div>
            <div class="text-xs text-gray-500 mt-1">Avg. filler words per session</div>
        </div>
    </div>

    {{-- Session History --}}
    <div class="glass rounded-2xl overflow-hidden animate-slide-up" style="animation-delay: 0.2s;">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-gray-100">Session History</h2>
        </div>

        @if($sessions->isEmpty())
            {{-- Empty State --}}
            <div class="px-6 py-16 text-center">
                <div class="w-20 h-20 rounded-2xl bg-surface-700 flex items-center justify-center mx-auto mb-5 animate-float">
                    <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-300 mb-2">No sessions yet</h3>
                <p class="text-gray-500 text-sm mb-6 max-w-md mx-auto">
                    Start your first practice session to begin tracking your public speaking progress.
                </p>
                <a href="{{ route('record') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-brand-500 text-white font-semibold hover:from-brand-500 hover:to-brand-400 shadow-lg shadow-brand-500/25 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Start Your First Session
                </a>
            </div>
        @else
            {{-- Session Table --}}
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-gray-500 uppercase tracking-wider">
                            <th class="text-left px-6 py-3 font-medium">Session</th>
                            <th class="text-center px-4 py-3 font-medium">Overall</th>
                            <th class="text-center px-4 py-3 font-medium">Eye Contact</th>
                            <th class="text-center px-4 py-3 font-medium">Posture</th>
                            <th class="text-center px-4 py-3 font-medium">Fillers</th>
                            <th class="text-center px-4 py-3 font-medium">Duration</th>
                            <th class="text-right px-6 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($sessions as $session)
                        <tr class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-gray-200 group-hover:text-white transition-colors">
                                        {{ $session->title ?: 'Untitled Session' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $session->created_at->format('M d, Y · h:i A') }}
                                    </p>
                                </div>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span class="text-sm font-bold {{ \App\Models\PresentationSession::getScoreColor($session->overall_score) }}">
                                    {{ number_format($session->overall_score, 0) }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span class="text-sm font-semibold {{ \App\Models\PresentationSession::getScoreColor($session->eye_contact_score) }}">
                                    {{ number_format($session->eye_contact_score, 0) }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span class="text-sm font-semibold {{ \App\Models\PresentationSession::getScoreColor($session->posture_score) }}">
                                    {{ number_format($session->posture_score, 0) }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                                    {{ $session->filler_word_count <= 3 ? 'bg-emerald-500/10 text-emerald-400' : ($session->filler_word_count <= 8 ? 'bg-amber-500/10 text-amber-400' : 'bg-rose-500/10 text-rose-400') }}">
                                    {{ $session->filler_word_count }}
                                </span>
                            </td>
                            <td class="text-center px-4 py-4">
                                <span class="text-xs text-gray-400">{{ $session->formatted_duration }}</span>
                            </td>
                            <td class="text-right px-6 py-4">
                                <a href="{{ route('session.show', $session->id) }}"
                                   class="text-xs text-brand-400 hover:text-brand-300 font-medium transition-colors">
                                    View Details →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
