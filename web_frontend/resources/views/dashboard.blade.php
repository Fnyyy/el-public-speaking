@extends('layouts.app')

@section('title', 'Overview')
@section('breadcrumb', 'Overview')

@section('content')

@php
    $totalSessions = $sessions->count();
    $best = $sessions->sortByDesc('overall_score')->first();
    $latest = $sessions->first();

    // Compute level
    $avgOverall = $averages['overall'];
    if ($avgOverall >= 80)      { $level = 'Expert';       $levelColor = '#34d399'; $levelBg = 'rgba(52,211,153,.1)'; }
    elseif ($avgOverall >= 60)  { $level = 'Intermediate'; $levelColor = '#f59e0b'; $levelBg = 'rgba(245,158,11,.1)'; }
    elseif ($avgOverall > 0)    { $level = 'Beginner';     $levelColor = '#f87171'; $levelBg = 'rgba(248,113,113,.1)'; }
    else                        { $level = 'New';          $levelColor = '#9ca3af'; $levelBg = 'rgba(156,163,175,.1)'; }
@endphp

{{-- ── Page header ── --}}
<div class="anim-in" style="display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:24px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:#f3f4f6; margin:0 0 4px;">Your Progress</h1>
        <p style="font-size:13px; color:#6b7280; margin:0;">
            {{ $totalSessions }} session{{ $totalSessions !== 1 ? 's' : '' }} recorded
            @if($totalSessions > 0)
                · Level: <span style="color:{{ $levelColor }}; font-weight:600;">{{ $level }}</span>
            @endif
        </p>
    </div>
    <a href="{{ route('record') }}" class="btn-primary">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        New Session
    </a>
</div>

{{-- ── Stat cards ── --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:24px;">

    {{-- Overall --}}
    <div class="card anim-up" style="padding:18px 20px;">
        <div style="font-size:11px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px;">Overall Avg</div>
        <div style="font-size:32px; font-weight:800; color:#f3f4f6; line-height:1; margin-bottom:4px;">
            {{ $totalSessions > 0 ? number_format($averages['overall'], 0) : '—' }}
        </div>
        <div class="progress-bar" style="margin-top:10px;">
            <div class="progress-fill" style="width:{{ $averages['overall'] }}%;"></div>
        </div>
    </div>

    {{-- Eye Contact --}}
    <div class="card anim-up-1" style="padding:18px 20px;">
        <div style="font-size:11px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
            <svg width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Eye Contact
        </div>
        <div style="font-size:32px; font-weight:800; color:#34d399; line-height:1; margin-bottom:4px;">
            {{ $totalSessions > 0 ? number_format($averages['eye_contact'], 0) : '—' }}
        </div>
        <div class="progress-bar" style="margin-top:10px;">
            <div class="progress-fill" style="width:{{ $averages['eye_contact'] }}%; background:linear-gradient(90deg,#059669,#34d399);"></div>
        </div>
    </div>

    {{-- Posture --}}
    <div class="card anim-up-2" style="padding:18px 20px;">
        <div style="font-size:11px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
            <svg width="13" height="13" fill="none" stroke="#818cf8" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            Posture
        </div>
        <div style="font-size:32px; font-weight:800; color:#818cf8; line-height:1; margin-bottom:4px;">
            {{ $totalSessions > 0 ? number_format($averages['posture'], 0) : '—' }}
        </div>
        <div class="progress-bar" style="margin-top:10px;">
            <div class="progress-fill" style="width:{{ $averages['posture'] }}%; background:linear-gradient(90deg,#4f46e5,#818cf8);"></div>
        </div>
    </div>

    {{-- Filler words --}}
    <div class="card anim-up-3" style="padding:18px 20px;">
        <div style="font-size:11px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px; display:flex; align-items:center; gap:6px;">
            <svg width="13" height="13" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            Filler Words
        </div>
        <div style="font-size:32px; font-weight:800; color:#f59e0b; line-height:1; margin-bottom:4px;">
            {{ $totalSessions > 0 ? number_format($averages['filler_words'], 0) : '—' }}
        </div>
        <div style="font-size:11px; color:#6b7280; margin-top:10px;">avg per session</div>
    </div>
</div>

@if($totalSessions === 0)
    {{-- Empty state --}}
    <div class="card anim-up" style="padding:64px 32px; text-align:center;">
        <div style="width:64px; height:64px; border-radius:16px; background:#18181f; border:1px solid rgba(255,255,255,.07); display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="28" height="28" fill="none" stroke="#4b5563" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
            </svg>
        </div>
        <h3 style="font-size:17px; font-weight:600; color:#d1d5db; margin:0 0 8px;">No sessions yet</h3>
        <p style="font-size:13px; color:#6b7280; margin:0 auto 24px; max-width:360px;">
            Record your first practice session. Our AI will analyze your eye contact, posture, and speech patterns.
        </p>
        <a href="{{ route('record') }}" class="btn-primary">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M6.343 6.343a8 8 0 1011.314 0"/></svg>
            Start Recording
        </a>
    </div>

@else

    {{-- ── Mini bar chart: last 8 sessions ── --}}
    @php
        $chartSessions = $sessions->take(8)->reverse()->values();
    @endphp
    <div class="card anim-up" style="padding:20px 24px; margin-bottom:24px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
            <div style="font-size:14px; font-weight:600; color:#d1d5db;">Score Trend</div>
            <div style="display:flex; align-items:center; gap:16px; font-size:11px; color:#6b7280;">
                <span style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:3px; border-radius:2px; background:#7c6af7; display:inline-block;"></span> Overall</span>
                <span style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:3px; border-radius:2px; background:#34d399; display:inline-block;"></span> Eye</span>
                <span style="display:flex; align-items:center; gap:5px;"><span style="width:10px; height:3px; border-radius:2px; background:#818cf8; display:inline-block;"></span> Posture</span>
            </div>
        </div>
        <div style="display:flex; align-items:flex-end; gap:10px; height:80px;">
            @foreach($chartSessions as $cs)
            @php
                $oh = max(4, ($cs->overall_score / 100) * 80);
                $eh = max(4, ($cs->eye_contact_score / 100) * 80);
                $ph = max(4, ($cs->posture_score / 100) * 80);
            @endphp
            <div style="flex:1; display:flex; align-items:flex-end; gap:2px; justify-content:center; position:relative; cursor:default;"
                 title="{{ $cs->title ?: 'Session' }}: {{ number_format($cs->overall_score,0) }}">
                <div style="width:6px; height:{{ $oh }}px; background:#7c6af7; border-radius:3px 3px 0 0; transition:opacity .2s;" onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'"></div>
                <div style="width:6px; height:{{ $eh }}px; background:#34d399; border-radius:3px 3px 0 0;" onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'"></div>
                <div style="width:6px; height:{{ $ph }}px; background:#818cf8; border-radius:3px 3px 0 0;" onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'"></div>
            </div>
            @endforeach
        </div>
        <div style="display:flex; gap:10px; margin-top:6px;">
            @foreach($chartSessions as $i => $cs)
            <div style="flex:1; text-align:center; font-size:10px; color:#4b5563;">{{ $cs->created_at->format('M d') }}</div>
            @endforeach
        </div>
    </div>

    {{-- ── Session list ── --}}
    <div class="card anim-up-1" style="overflow:hidden;">
        <div style="padding:16px 24px 14px; border-bottom:1px solid rgba(255,255,255,.06); display:flex; align-items:center; justify-content:space-between;">
            <div style="font-size:14px; font-weight:600; color:#d1d5db;">Session History</div>
            <span style="font-size:12px; color:#4b5563;">{{ $totalSessions }} total</span>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="font-size:11px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; background:#111117;">
                        <th style="padding:10px 24px; text-align:left; font-weight:600;">Session</th>
                        <th style="padding:10px 16px; text-align:center;">Overall</th>
                        <th style="padding:10px 16px; text-align:center;">Eye</th>
                        <th style="padding:10px 16px; text-align:center;">Posture</th>
                        <th style="padding:10px 16px; text-align:center;">Fillers</th>
                        <th style="padding:10px 16px; text-align:center;">Duration</th>
                        <th style="padding:10px 24px; text-align:right;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $s)
                    @php
                        $ocColor = $s->eye_contact_score >= 80 ? '#34d399' : ($s->eye_contact_score >= 60 ? '#f59e0b' : '#f87171');
                        $psColor = $s->posture_score >= 80 ? '#34d399' : ($s->posture_score >= 60 ? '#f59e0b' : '#f87171');
                        $ovColor = $s->overall_score >= 80 ? '#34d399' : ($s->overall_score >= 60 ? '#f59e0b' : '#f87171');
                        $fillerCls = $s->filler_word_count <= 3 ? 'badge-green' : ($s->filler_word_count <= 8 ? 'badge-yellow' : 'badge-red');
                    @endphp
                    <tr onclick="window.location='{{ route('session.show', $s->id) }}'"
                        style="cursor:pointer; border-bottom:1px solid rgba(255,255,255,.04); transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,.03)'"
                        onmouseout="this.style.background='transparent'">
                        <td style="padding:13px 24px;">
                            <div style="font-size:13.5px; font-weight:500; color:#e5e7eb;">{{ $s->title ?: 'Practice Session' }}</div>
                            <div style="font-size:11px; color:#4b5563; margin-top:2px;">{{ $s->created_at->format('M d, Y · g:i A') }}</div>
                        </td>
                        <td style="text-align:center; padding:13px 16px;">
                            <span style="font-size:15px; font-weight:700; color:{{ $ovColor }};">{{ number_format($s->overall_score, 0) }}</span>
                        </td>
                        <td style="text-align:center; padding:13px 16px;">
                            <span style="font-size:14px; font-weight:600; color:{{ $ocColor }};">{{ number_format($s->eye_contact_score, 0) }}</span>
                        </td>
                        <td style="text-align:center; padding:13px 16px;">
                            <span style="font-size:14px; font-weight:600; color:{{ $psColor }};">{{ number_format($s->posture_score, 0) }}</span>
                        </td>
                        <td style="text-align:center; padding:13px 16px;">
                            <span class="badge {{ $fillerCls }}">{{ $s->filler_word_count }}</span>
                        </td>
                        <td style="text-align:center; padding:13px 16px;">
                            <span style="font-size:12px; color:#6b7280; font-family:'JetBrains Mono',monospace;">{{ $s->formatted_duration }}</span>
                        </td>
                        <td style="text-align:right; padding:13px 24px;">
                            <span style="font-size:12px; color:#7c6af7; font-weight:500;">Details →</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
