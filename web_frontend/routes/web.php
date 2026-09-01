<?php

/*
|--------------------------------------------------------------------------
| Web Routes - AI Presentation Coach
|--------------------------------------------------------------------------
|
| Routes for the dashboard, recording page, and analysis submission.
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PresentationController;

// Dashboard - View historical scores and AI feedback
Route::get('/', [PresentationController::class, 'dashboard'])->name('dashboard');

// Recording Page - Webcam/mic capture interface
Route::get('/record', [PresentationController::class, 'record'])->name('record');

// Upload & Analyze - Submit recorded video for AI analysis
Route::post('/analyze', [PresentationController::class, 'analyze'])->name('analyze');

// View single session details
Route::get('/session/{id}', [PresentationController::class, 'showSession'])->name('session.show');

// Delete a session
Route::delete('/session/{id}', [PresentationController::class, 'deleteSession'])->name('session.delete');
