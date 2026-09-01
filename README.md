# 🎤 SpeakAI Coach — AI Presentation & Public Speaking Coach

> An intelligent web application that analyzes your practice speeches via webcam and microphone, providing real-time feedback on **posture**, **eye contact**, **filler words**, and constructive AI coaching.

![Architecture](https://img.shields.io/badge/Architecture-Microservices-blue)
![Python](https://img.shields.io/badge/Python-3.11-green)
![Laravel](https://img.shields.io/badge/Laravel-10+-red)
![Gemini](https://img.shields.io/badge/AI-Google%20Gemini-purple)

---

## 📐 Architecture Overview

```
┌─────────────────────┐     HTTP/REST      ┌─────────────────────┐
│                     │ ──────────────────► │                     │
│   Laravel Frontend  │   POST /api/analyze │  Python FastAPI     │
│   (PHP + Tailwind)  │ ◄────────────────── │  AI Backend         │
│                     │   JSON Response     │                     │
│   Port: 8000        │                     │   Port: 8080        │
└────────┬────────────┘                     └─────────┬───────────┘
         │                                            │
         │ MySQL                          ┌───────────┼───────────┐
         ▼                                │           │           │
   ┌───────────┐                    ┌─────▼─┐  ┌─────▼──┐  ┌─────▼──────┐
   │  Database │                    │OpenCV │  │Speech  │  │  Google    │
   │  (MySQL)  │                    │Media  │  │Recogn. │  │  Gemini    │
   │           │                    │Pipe   │  │        │  │  (AI)      │
   └───────────┘                    └───────┘  └────────┘  └────────────┘
```

---

## 📁 Project Structure

```
PUBLIC SPEAKING/
├── ai_backend/                    # Python FastAPI Microservice
│   ├── main.py                    # FastAPI server & endpoints
│   ├── vision_analyzer.py         # Eye contact & posture analysis (MediaPipe)
│   ├── audio_analyzer.py          # Speech transcription & filler words
│   ├── feedback_generator.py      # Google Gemini AI feedback
│   ├── requirements.txt           # Python dependencies
│   ├── Dockerfile                 # Cloud Run deployment
│   ├── .env.example               # Environment template
│   └── .dockerignore
│
├── web_frontend/                  # Laravel Frontend Application
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   └── PresentationController.php
│   │   └── Models/
│   │       └── PresentationSession.php
│   ├── database/migrations/
│   │   └── 2024_01_01_000001_create_presentation_sessions_table.php
│   ├── resources/views/
│   │   ├── layouts/app.blade.php  # Base layout (glassmorphism dark theme)
│   │   ├── dashboard.blade.php    # Score history dashboard
│   │   ├── record.blade.php       # Webcam recording page
│   │   └── session.blade.php      # Session detail view
│   └── routes/web.php
│
└── README.md                      # This file
```

---

## 🚀 Setup & Installation

### Prerequisites

| Tool | Version | Purpose |
|------|---------|---------|
| Python | 3.10+ | AI Backend |
| PHP | 8.1+ | Laravel Frontend |
| Composer | 2.x | PHP dependency manager |
| Node.js | 18+ | Frontend assets (optional) |
| MySQL | 8.0+ | Database |
| FFmpeg | Latest | Audio extraction from video |

---

### Step 1: Setup Python AI Backend

```bash
# Navigate to the AI backend directory
cd ai_backend

# Create and activate virtual environment
python -m venv venv

# Windows:
venv\Scripts\activate
# macOS/Linux:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Create .env file from template
copy .env.example .env    # Windows
# cp .env.example .env    # macOS/Linux

# Edit .env and add your Google AI Studio API key
# Get key at: https://aistudio.google.com/app/apikey
```

**Edit `.env`:**
```env
GOOGLE_AI_API_KEY=your_actual_api_key_here
API_PORT=8080
FRONTEND_URL=http://localhost:8000
```

**Start the FastAPI server:**
```bash
uvicorn main:app --host 0.0.0.0 --port 8080 --reload
```

Verify it's running: Open http://localhost:8080/docs for interactive API docs.

---

### Step 2: Setup Laravel Frontend

```bash
# Navigate to parent directory and create Laravel project
# (if starting fresh — copy our files into the Laravel project)
composer create-project laravel/laravel web_frontend_app

# Copy our custom files into the Laravel project:
# - app/Http/Controllers/PresentationController.php
# - app/Models/PresentationSession.php
# - database/migrations/2024_01_01_000001_create_presentation_sessions_table.php
# - resources/views/layouts/app.blade.php
# - resources/views/dashboard.blade.php
# - resources/views/record.blade.php
# - resources/views/session.blade.php
# - routes/web.php
```

**Configure `.env` for MySQL:**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=speakai_coach
DB_USERNAME=root
DB_PASSWORD=your_password

# Python AI Backend URL
AI_BACKEND_URL=http://localhost:8080
```

**Create database and run migrations:**
```bash
# Create MySQL database
mysql -u root -p -e "CREATE DATABASE speakai_coach;"

# Run migrations
cd web_frontend
php artisan migrate

# Start the Laravel development server
php artisan serve
```

The frontend will be at: http://localhost:8000

---

### Step 3: Test the Full Pipeline

1. Open http://localhost:8000 in your browser
2. Click **"New Session"** to go to the recording page
3. Click **"Start Camera"** and allow permissions
4. Click **"Start Recording"** and practice your speech
5. Click **"Stop Recording"** when done
6. Wait for AI analysis (30-120 seconds depending on video length)
7. View your results on the detail page
8. Check the **Dashboard** for historical scores

---

## 🐳 Docker Deployment (Cloud Run)

### Build and test locally:

```bash
cd ai_backend

# Build the Docker image
docker build -t speakai-coach-api .

# Run locally
docker run -p 8080:8080 \
  -e GOOGLE_AI_API_KEY=your_key_here \
  speakai-coach-api
```

### Deploy to Google Cloud Run:

```bash
# Authenticate with Google Cloud
gcloud auth login
gcloud config set project YOUR_PROJECT_ID

# Build and push to Container Registry
gcloud builds submit --tag gcr.io/YOUR_PROJECT_ID/speakai-coach-api

# Deploy to Cloud Run
gcloud run deploy speakai-coach-api \
  --image gcr.io/YOUR_PROJECT_ID/speakai-coach-api \
  --platform managed \
  --region us-central1 \
  --allow-unauthenticated \
  --set-env-vars GOOGLE_AI_API_KEY=your_key_here \
  --memory 2Gi \
  --timeout 300
```

After deployment, update your Laravel `.env` with the Cloud Run URL:
```env
AI_BACKEND_URL=https://speakai-coach-api-xxxxx-uc.a.run.app
```

---

## 🔧 API Reference

### `POST /api/analyze`
Upload a video file for full analysis.

**Request:** `multipart/form-data` with field `video` (file)

**Response:**
```json
{
  "success": true,
  "analysis": {
    "eye_contact": { "score": 78.5, "feedback": "..." },
    "posture": { "score": 85.2, "feedback": "..." },
    "speech": {
      "transcription": "...",
      "word_count": 150,
      "words_per_minute": 130.5,
      "filler_words": { "total_count": 5, "breakdown": [...] }
    },
    "ai_feedback": {
      "overall_score": 80,
      "summary": "...",
      "actionable_tips": ["..."],
      "encouragement": "..."
    }
  }
}
```

### `GET /api/health`
Health check endpoint.

### `GET /docs`
Interactive Swagger API documentation.

---

## 🛡 Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| AI Backend | FastAPI (Python) | REST API & orchestration |
| Vision | OpenCV + MediaPipe | Eye contact & posture analysis |
| Audio | SpeechRecognition + pydub | Transcription & filler detection |
| AI Coach | Google Gemini (via AI Studio) | Constructive feedback generation |
| Frontend | Laravel (PHP) + Tailwind CSS | Web UI & user management |
| Database | MySQL | Session history storage |
| Deployment | Docker + Cloud Run | Containerized cloud deployment |

---

## 📝 License

This project is for educational and personal development purposes.

---

Built with ❤️ for better public speaking
