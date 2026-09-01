@extends('layouts.app')
@section('title', $session->title ?: 'Session Results')
@section('breadcrumb', 'Session Results')

@section('content')

@php
    $eyeColor    = $session->eye_contact_score >= 80 ? '#34d399' : ($session->eye_contact_score >= 60 ? '#f59e0b' : '#f87171');
    $postColor   = $session->posture_score >= 80 ? '#34d399' : ($session->posture_score >= 60 ? '#f59e0b' : '#f87171');
    $ovColor     = $session->overall_score >= 80 ? '#34d399' : ($session->overall_score >= 60 ? '#f59e0b' : '#f87171');
    $speechScore = max(0, 100 - $session->filler_word_count * 5);
    $spColor     = $speechScore >= 80 ? '#34d399' : ($speechScore >= 60 ? '#f59e0b' : '#f87171');
@endphp

{{-- ── Header ── --}}
<div class="anim-in" style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:24px;">
    <div>
        <a href="{{ route('dashboard') }}" style="display:inline-flex; align-items:center; gap:5px; font-size:12px; color:#4b5563; text-decoration:none; margin-bottom:10px; transition:color .15s;"
           onmouseover="this.style.color='#a498fb'" onmouseout="this.style.color='#4b5563'">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Back to Overview
        </a>
        <h1 style="font-size:22px; font-weight:700; color:#f3f4f6; margin:0 0 4px;">{{ $session->title ?: 'Practice Session' }}</h1>
        <div style="font-size:12.5px; color:#6b7280; display:flex; align-items:center; gap:12px;">
            <span>{{ $session->created_at->format('F d, Y · g:i A') }}</span>
            <span style="width:3px; height:3px; border-radius:50%; background:#4b5563; display:inline-block;"></span>
            <span style="font-family:'JetBrains Mono',monospace;">{{ $session->formatted_duration }}</span>
        </div>
    </div>

    {{-- Overall score badge --}}
    <div style="text-align:center; flex-shrink:0;">
        <div style="font-size:42px; font-weight:800; color:{{ $ovColor }}; line-height:1;">{{ number_format($session->overall_score, 0) }}</div>
        <div style="font-size:10px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.08em; margin-top:3px;">Overall Score</div>
    </div>
</div>

{{-- ── Score cards ── --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px;">
    @php
        $scoreCards = [
            ['label'=>'Eye Contact',   'score'=>$session->eye_contact_score, 'color'=>$eyeColor,  'ring'=>263.89],
            ['label'=>'Posture',       'score'=>$session->posture_score,     'color'=>$postColor, 'ring'=>263.89],
            ['label'=>'Speech Fluency','score'=>$speechScore,                'color'=>$spColor,   'ring'=>263.89],
            ['label'=>'Movement',      'score'=>$session->movement_score ?? 0,'color'=>'#818cf8',  'ring'=>263.89],
        ];
    @endphp
    @foreach($scoreCards as $i => $sc)
    @php $offset = $sc['ring'] * (1 - $sc['score'] / 100); @endphp
    <div class="card anim-up" style="padding:20px; text-align:center; animation-delay:{{ $i * 0.07 }}s;">
        <div style="position:relative; width:76px; height:76px; margin:0 auto 12px;">
            <svg width="76" height="76" viewBox="0 0 100 100" style="transform:rotate(-90deg);">
                <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="7"/>
                <circle cx="50" cy="50" r="42" fill="none"
                        stroke="{{ $sc['color'] }}" stroke-width="7"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $sc['ring'] }}"
                        stroke-dashoffset="{{ $offset }}"
                        style="transition:stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1);"/>
            </svg>
            <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center;">
                <span style="font-size:18px; font-weight:700; color:#f3f4f6;">{{ number_format($sc['score'], 0) }}</span>
            </div>
        </div>
        <div style="font-size:12px; font-weight:600; color:#9ca3af;">{{ $sc['label'] }}</div>
    </div>
    @endforeach
</div>

{{-- ── AI Summary banner ── --}}
@if($session->ai_summary)
<div class="card anim-up-1" style="padding:18px 22px; margin-bottom:20px; border-left:3px solid #7c6af7; border-radius:12px 0 0 12px; border-radius:0 12px 12px 0;">
    <div style="font-size:11px; font-weight:600; color:#7c6af7; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">AI Coach Summary</div>
    <p style="font-size:13.5px; color:#d1d5db; line-height:1.6; margin:0;">{{ $session->ai_summary }}</p>
</div>
@endif

{{-- ── Feedback grid ── --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px;">
    @php
        $feedbacks = [
            ['label'=>'Eye Contact',         'color'=>$eyeColor,  'badge'=>'badge-green',  'text'=>$session->eye_contact_feedback],
            ['label'=>'Posture & Body',       'color'=>$postColor, 'badge'=>'badge-purple', 'text'=>$session->posture_feedback],
            ['label'=>'Speech & Clarity',     'color'=>'#818cf8',  'badge'=>'badge-purple', 'text'=>$session->speech_feedback],
            ['label'=>'Filler Words',         'color'=>'#f59e0b',  'badge'=>'badge-yellow', 'text'=>$session->filler_word_feedback],
        ];
    @endphp
    @foreach($feedbacks as $fb)
    @if($fb['text'])
    <div class="card-sm" style="padding:16px 18px;">
        <div style="font-size:11px; font-weight:600; color:{{ $fb['color'] }}; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px;">{{ $fb['label'] }}</div>
        <p style="font-size:13px; color:#9ca3af; line-height:1.55; margin:0;">{{ $fb['text'] }}</p>
    </div>
    @endif
    @endforeach
</div>

{{-- ── Actionable tips ── --}}
@if($session->actionable_tips && count($session->actionable_tips) > 0)
<div class="card anim-up-2" style="padding:18px 22px; margin-bottom:20px;">
    <div style="font-size:12px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em; margin-bottom:14px;">Action Plan</div>
    <div style="display:flex; flex-direction:column; gap:10px;">
        @foreach($session->actionable_tips as $i => $tip)
        <div style="display:flex; align-items:flex-start; gap:12px;">
            <div style="width:22px; height:22px; border-radius:6px; background:rgba(124,106,247,.12); border:1px solid rgba(124,106,247,.2); display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:11px; font-weight:700; color:#a498fb; margin-top:1px;">{{ $i+1 }}</div>
            <p style="font-size:13px; color:#d1d5db; line-height:1.5; margin:0;">{{ $tip }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Encouragement ── --}}
@if($session->encouragement)
<div style="padding:16px 20px; border-radius:10px; background:rgba(52,211,153,.07); border:1px solid rgba(52,211,153,.15); margin-bottom:20px;" class="anim-up-2">
    <p style="font-size:13.5px; color:#34d399; font-style:italic; margin:0; line-height:1.5;">"{{ $session->encouragement }}"</p>
</div>
@endif

{{-- ── Transcription ── --}}
@if($session->transcription)
<div class="card anim-up-3" style="padding:18px 22px; margin-bottom:20px;">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px;">
        <div style="font-size:12px; font-weight:600; color:#4b5563; text-transform:uppercase; letter-spacing:.06em;">Speech Transcript</div>
        <div style="display:flex; align-items:center; gap:14px; font-size:11.5px; color:#6b7280; font-family:'JetBrains Mono',monospace;">
            <span>{{ $session->word_count }}w</span>
            <span>{{ $session->words_per_minute }} wpm</span>
        </div>
    </div>
    <div style="background:#111117; border-radius:8px; padding:14px 16px; max-height:180px; overflow-y:auto;">
        <p style="font-size:13px; color:#9ca3af; line-height:1.7; margin:0; white-space:pre-wrap;">{{ $session->transcription }}</p>
    </div>

    @if($session->filler_word_breakdown && count($session->filler_word_breakdown) > 0)
    <div style="display:flex; flex-wrap:wrap; gap:7px; margin-top:12px;">
        @foreach($session->filler_word_breakdown as $fw)
        <span class="badge badge-yellow">"{{ $fw['word'] }}" ×{{ $fw['count'] }}</span>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- ── Actions ── --}}
<div class="anim-in" style="display:flex; align-items:center; justify-content:space-between; padding-top:4px;">
    <a href="{{ route('record') }}" class="btn-primary">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path stroke-linecap="round" d="M6.343 6.343a8 8 0 1011.314 0"/></svg>
        Practice Again
    </a>

    <form action="{{ route('session.delete', $session->id) }}" method="POST"
          onsubmit="return confirm('Delete this session? This cannot be undone.')">
        @csrf @method('DELETE')
        <button type="submit" class="btn-ghost" style="font-size:12px; color:#6b7280; padding:8px 14px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M10 11v6M14 11v6"/></svg>
            Delete Session
        </button>
    </form>
</div>

@endsection
