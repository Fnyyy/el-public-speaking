<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PresentationSession Model
 * 
 * Represents a single practice session with all analysis
 * results from the AI backend.
 */
class PresentationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'duration_seconds',
        'eye_contact_score',
        'posture_score',
        'movement_score',
        'transcription',
        'word_count',
        'words_per_minute',
        'filler_word_count',
        'filler_word_percentage',
        'filler_word_breakdown',
        'overall_score',
        'ai_summary',
        'eye_contact_feedback',
        'posture_feedback',
        'speech_feedback',
        'filler_word_feedback',
        'actionable_tips',
        'encouragement',
        'raw_response',
    ];

    protected $casts = [
        'filler_word_breakdown' => 'array',
        'actionable_tips' => 'array',
        'raw_response' => 'array',
        'eye_contact_score' => 'float',
        'posture_score' => 'float',
        'movement_score' => 'float',
        'overall_score' => 'float',
        'words_per_minute' => 'float',
        'filler_word_percentage' => 'float',
        'duration_seconds' => 'float',
    ];

    /**
     * Get a formatted duration string (e.g., "2m 30s").
     */
    public function getFormattedDurationAttribute(): string
    {
        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        
        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        }
        return sprintf('%ds', $seconds);
    }

    /**
     * Get the score color class based on value.
     */
    public static function getScoreColor(float $score): string
    {
        if ($score >= 80) return 'text-emerald-400';
        if ($score >= 60) return 'text-amber-400';
        return 'text-rose-400';
    }

    /**
     * Get the score background gradient based on value.
     */
    public static function getScoreBg(float $score): string
    {
        if ($score >= 80) return 'from-emerald-500/20 to-emerald-600/5';
        if ($score >= 60) return 'from-amber-500/20 to-amber-600/5';
        return 'from-rose-500/20 to-rose-600/5';
    }
}
