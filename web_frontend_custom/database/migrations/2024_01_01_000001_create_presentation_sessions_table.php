<?php

/*
|--------------------------------------------------------------------------
| Database Migration - Presentation Sessions Table
|--------------------------------------------------------------------------
|
| Stores each practice session's analysis results including
| vision scores, audio transcription, and AI feedback.
|
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentation_sessions', function (Blueprint $table) {
            $table->id();
            
            // Session metadata
            $table->string('title')->nullable();
            $table->float('duration_seconds')->default(0);
            
            // Vision Analysis Scores
            $table->float('eye_contact_score')->default(0);
            $table->float('posture_score')->default(0);
            $table->float('movement_score')->default(0);
            
            // Audio Analysis
            $table->text('transcription')->nullable();
            $table->integer('word_count')->default(0);
            $table->float('words_per_minute')->default(0);
            $table->integer('filler_word_count')->default(0);
            $table->float('filler_word_percentage')->default(0);
            $table->json('filler_word_breakdown')->nullable();
            
            // AI Feedback
            $table->float('overall_score')->default(0);
            $table->text('ai_summary')->nullable();
            $table->text('eye_contact_feedback')->nullable();
            $table->text('posture_feedback')->nullable();
            $table->text('speech_feedback')->nullable();
            $table->text('filler_word_feedback')->nullable();
            $table->json('actionable_tips')->nullable();
            $table->text('encouragement')->nullable();
            
            // Full raw API response for reference
            $table->json('raw_response')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentation_sessions');
    }
};
