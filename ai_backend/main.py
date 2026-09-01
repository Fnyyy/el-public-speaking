"""
============================================================
AI Presentation Coach - FastAPI Main Application
============================================================
REST API server that orchestrates video/audio analysis and
AI feedback generation for public speaking coaching.

Endpoints:
  POST /api/analyze     - Full analysis pipeline
  GET  /api/health      - Health check
  GET  /                - API info
============================================================
"""

from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
import tempfile
import os
import shutil
import logging
import time
from dotenv import load_dotenv

from vision_analyzer import VisionAnalysisPipeline
from audio_analyzer import AudioAnalyzer
from feedback_generator import FeedbackGenerator

# Load environment variables
load_dotenv()

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s - %(name)s - %(levelname)s - %(message)s",
)
logger = logging.getLogger(__name__)

# ============================================================
# FastAPI Application Setup
# ============================================================

app = FastAPI(
    title="AI Presentation & Public Speaking Coach API",
    description="Analyzes video recordings for eye contact, posture, "
                "filler words, and generates AI coaching feedback.",
    version="1.0.0",
    docs_url="/docs",
    redoc_url="/redoc",
)

# ============================================================
# CORS Configuration - Allow Laravel frontend access
# ============================================================

FRONTEND_URL = os.getenv("FRONTEND_URL", "http://localhost:8000")

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        FRONTEND_URL,
        "http://localhost:8000",    # Laravel default
        "http://localhost:3000",    # Alternative dev
        "http://127.0.0.1:8000",
        "*",                        # Dev convenience (restrict in production)
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ============================================================
# Initialize Analysis Modules
# ============================================================

feedback_gen = FeedbackGenerator(api_key=os.getenv("GOOGLE_AI_API_KEY"))


# ============================================================
# API Endpoints
# ============================================================

@app.get("/")
async def root():
    """API information endpoint."""
    return {
        "service": "AI Presentation & Public Speaking Coach",
        "version": "1.0.0",
        "endpoints": {
            "analyze": "POST /api/analyze - Upload video for full analysis",
            "health": "GET /api/health - Service health check",
            "docs": "GET /docs - Interactive API documentation",
        },
    }


@app.get("/api/health")
async def health_check():
    """Health check endpoint for Cloud Run and monitoring."""
    return {
        "status": "healthy",
        "service": "ai-presentation-coach",
        "timestamp": time.time(),
        "gemini_configured": bool(os.getenv("GOOGLE_AI_API_KEY")),
    }


@app.post("/api/analyze")
async def analyze_presentation(video: UploadFile = File(...)):
    """
    Full presentation analysis pipeline.
    
    Accepts a video file upload and returns:
    - Eye contact score and analysis
    - Posture score and analysis
    - Speech transcription
    - Filler word analysis
    - AI-generated coaching feedback
    """
    # Validate file type
    allowed_types = [
        "video/webm", "video/mp4", "video/avi",
        "video/x-matroska", "video/quicktime",
        "application/octet-stream",  # Some browsers send this for webm
    ]
    
    if video.content_type not in allowed_types:
        raise HTTPException(
            status_code=400,
            detail=f"Invalid file type: {video.content_type}. "
                   f"Accepted: {', '.join(allowed_types)}",
        )

    # Save uploaded file to temp directory
    temp_dir = tempfile.mkdtemp()
    ext = os.path.splitext(video.filename or "video.webm")[1] or ".webm"
    temp_path = os.path.join(temp_dir, f"upload{ext}")

    try:
        logger.info(f"Received video: {video.filename} ({video.content_type})")

        # Write uploaded file to disk
        with open(temp_path, "wb") as f:
            content = await video.read()
            f.write(content)

        file_size_mb = os.path.getsize(temp_path) / (1024 * 1024)
        logger.info(f"Video saved: {file_size_mb:.1f} MB")

        # ---- Step 1: Vision Analysis (Eye Contact + Posture) ----
        logger.info("Starting vision analysis...")
        vision_pipeline = VisionAnalysisPipeline(sample_rate=5)
        vision_result = vision_pipeline.analyze_video(temp_path)
        vision_pipeline.close()

        # ---- Step 2: Audio Analysis (Transcription + Fillers) ----
        logger.info("Starting audio analysis...")
        audio_analyzer = AudioAnalyzer()
        audio_result = audio_analyzer.analyze_video(temp_path)

        # ---- Step 3: AI Feedback Generation ----
        logger.info("Generating AI feedback...")
        ai_feedback = feedback_gen.generate_feedback(
            eye_contact_score=vision_result.eye_contact.score,
            posture_score=vision_result.posture.score,
            transcription=audio_result.transcription,
            filler_count=audio_result.filler_analysis.total_filler_count,
            filler_details=audio_result.filler_analysis.filler_frequency,
            words_per_minute=audio_result.words_per_minute,
            word_count=audio_result.word_count,
            duration_seconds=audio_result.duration_seconds,
        )

        # ---- Build Response ----
        response = {
            "success": True,
            "analysis": {
                "vision_analysis_mode": vision_result.analysis_mode,  # "real" or "mock"
                "eye_contact": {
                    "score": vision_result.eye_contact.score,
                    "frames_analyzed": vision_result.eye_contact.frames_analyzed,
                    "frames_with_contact": vision_result.eye_contact.frames_with_contact,
                    "feedback": vision_result.eye_contact.feedback,
                },
                "posture": {
                    "score": vision_result.posture.score,
                    "frames_analyzed": vision_result.posture.frames_analyzed,
                    "slouch_count": vision_result.posture.slouch_detected_count,
                    "movement_score": vision_result.posture.excessive_movement_score,
                    "feedback": vision_result.posture.feedback,
                },
                "speech": {
                    "transcription": audio_result.transcription,
                    "word_count": audio_result.word_count,
                    "duration_seconds": round(audio_result.duration_seconds, 1),
                    "words_per_minute": audio_result.words_per_minute,
                    "filler_words": {
                        "total_count": audio_result.filler_analysis.total_filler_count,
                        "percentage": audio_result.filler_analysis.filler_percentage,
                        "breakdown": audio_result.filler_analysis.top_fillers,
                    },
                },
                "ai_feedback": {
                    "overall_score": ai_feedback.overall_score,
                    "summary": ai_feedback.summary,
                    "eye_contact_feedback": ai_feedback.eye_contact_feedback,
                    "posture_feedback": ai_feedback.posture_feedback,
                    "speech_feedback": ai_feedback.speech_feedback,
                    "filler_word_feedback": ai_feedback.filler_word_feedback,
                    "actionable_tips": ai_feedback.actionable_tips,
                    "encouragement": ai_feedback.encouragement,
                },
                "video_info": {
                    "duration_seconds": round(vision_result.video_duration_seconds, 1),
                    "total_frames": vision_result.total_frames,
                },
            },
        }

        logger.info("Analysis pipeline complete!")
        return JSONResponse(content=response)

    except Exception as e:
        logger.error(f"Analysis failed: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Analysis failed: {str(e)}")

    finally:
        # Cleanup temporary files
        shutil.rmtree(temp_dir, ignore_errors=True)


# ============================================================
# Run with: uvicorn main:app --host 0.0.0.0 --port 8080
# ============================================================

if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("API_PORT", "8080"))
    uvicorn.run("main:app", host="0.0.0.0", port=port, reload=True)
