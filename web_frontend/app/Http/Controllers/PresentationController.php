<?php

namespace App\Http\Controllers;

use App\Models\PresentationSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PresentationController
 * 
 * Handles the presentation coaching workflow:
 * 1. Dashboard view with historical sessions
 * 2. Recording page with webcam/mic capture
 * 3. Video upload → Python AI backend → Save results
 */
class PresentationController extends Controller
{
    /**
     * The Python AI backend URL.
     * Configure in .env as AI_BACKEND_URL
     */
    private string $aiBackendUrl;

    public function __construct()
    {
        $this->aiBackendUrl = env('AI_BACKEND_URL', 'http://localhost:8080');
    }

    /**
     * Dashboard - Display historical sessions and score trends.
     */
    public function dashboard()
    {
        $sessions = PresentationSession::orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        // Calculate averages for the score overview cards
        $averages = [
            'eye_contact' => $sessions->avg('eye_contact_score') ?? 0,
            'posture' => $sessions->avg('posture_score') ?? 0,
            'filler_words' => $sessions->avg('filler_word_count') ?? 0,
            'overall' => $sessions->avg('overall_score') ?? 0,
        ];

        return view('dashboard', compact('sessions', 'averages'));
    }

    /**
     * Record - Show the webcam/microphone recording page.
     */
    public function record()
    {
        return view('record');
    }

    /**
     * Analyze - Process uploaded video through AI backend.
     * 
     * Flow:
     * 1. Receive video blob from frontend JavaScript
     * 2. Forward to Python FastAPI backend via HTTP
     * 3. Parse the analysis response
     * 4. Save results to MySQL database
     * 5. Return JSON response to frontend
     */
    public function analyze(Request $request)
    {
        // Validate the uploaded video file
        $request->validate([
            'video' => 'required|file|max:102400', // 100MB max
            'title' => 'nullable|string|max:255',
        ]);

        $videoFile = $request->file('video');
        $title = $request->input('title', 'Practice Session ' . now()->format('M d, Y H:i'));

        try {
            Log::info('Sending video to AI backend for analysis...', [
                'file_size' => $videoFile->getSize(),
                'mime_type' => $videoFile->getMimeType(),
            ]);

            // Send video to the Python AI backend
            $response = Http::timeout(300) // 5 minute timeout for processing
                ->attach(
                    'video',
                    file_get_contents($videoFile->getRealPath()),
                    $videoFile->getClientOriginalName() ?? 'recording.webm'
                )
                ->post("{$this->aiBackendUrl}/api/analyze");

            // Check if the API call was successful
            if (!$response->successful()) {
                Log::error('AI backend returned error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'AI analysis service returned an error. Please try again.',
                ], 500);
            }

            $data = $response->json();

            // Verify the response structure
            if (!isset($data['success']) || !$data['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $data['detail'] ?? 'Analysis failed.',
                ], 500);
            }

            $analysis = $data['analysis'];

            // Save analysis results to MySQL database
            $session = PresentationSession::create([
                'title' => $title,
                'duration_seconds' => $analysis['video_info']['duration_seconds'] ?? 0,

                // Vision Scores
                'eye_contact_score' => $analysis['eye_contact']['score'] ?? 0,
                'posture_score' => $analysis['posture']['score'] ?? 0,
                'movement_score' => $analysis['posture']['movement_score'] ?? 0,

                // Audio Analysis
                'transcription' => $analysis['speech']['transcription'] ?? '',
                'word_count' => $analysis['speech']['word_count'] ?? 0,
                'words_per_minute' => $analysis['speech']['words_per_minute'] ?? 0,
                'filler_word_count' => $analysis['speech']['filler_words']['total_count'] ?? 0,
                'filler_word_percentage' => $analysis['speech']['filler_words']['percentage'] ?? 0,
                'filler_word_breakdown' => $analysis['speech']['filler_words']['breakdown'] ?? [],

                // AI Feedback
                'overall_score' => $analysis['ai_feedback']['overall_score'] ?? 0,
                'ai_summary' => $analysis['ai_feedback']['summary'] ?? '',
                'eye_contact_feedback' => $analysis['ai_feedback']['eye_contact_feedback'] ?? '',
                'posture_feedback' => $analysis['ai_feedback']['posture_feedback'] ?? '',
                'speech_feedback' => $analysis['ai_feedback']['speech_feedback'] ?? '',
                'filler_word_feedback' => $analysis['ai_feedback']['filler_word_feedback'] ?? '',
                'actionable_tips' => $analysis['ai_feedback']['actionable_tips'] ?? [],
                'encouragement' => $analysis['ai_feedback']['encouragement'] ?? '',

                // Raw response for debugging
                'raw_response' => $data,
            ]);

            Log::info('Analysis saved successfully', ['session_id' => $session->id]);

            return response()->json([
                'success' => true,
                'message' => 'Analysis complete!',
                'session_id' => $session->id,
                'data' => $analysis,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Cannot connect to AI backend', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Cannot connect to the AI analysis service. '
                           . 'Make sure the Python backend is running.',
            ], 503);

        } catch (\Exception $e) {
            Log::error('Analysis failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a single session's detailed results.
     */
    public function showSession($id)
    {
        $session = PresentationSession::findOrFail($id);
        return view('session', compact('session'));
    }

    /**
     * Delete a session.
     */
    public function deleteSession($id)
    {
        $session = PresentationSession::findOrFail($id);
        $session->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Session deleted successfully.');
    }
}
