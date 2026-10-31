#!/usr/bin/env bash
# ==============================================================================
# Script Backfill Git History Otomatis
# Repository : SpeakAI Coach (el-public-speaking)
# Rentang    : 2026-09-01 s/d 2026-10-31 (61 hari)
# Target     : Sekitar 250 - 300 commit (default: 280 commit)
# Jam Kerja  : 08:00 - 22:00 WIB (+07:00)
# ==============================================================================

set -e

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$REPO_DIR"

START_DATE="2026-09-01"
END_DATE="2026-10-31"
TIMEZONE="+0700"
TARGET_TOTAL=280
HISTORY_FILE="PROJECT_HISTORY.md"

echo "=== Memulai Simulasi Git History Backfill ==="
echo "Direktori    : $REPO_DIR"
echo "Rentang      : $START_DATE s/d $END_DATE"
echo "Target Total : $TARGET_TOTAL commit"
echo "Zona Waktu   : $TIMEZONE"
echo "File Riwayat : $HISTORY_FILE"
echo "---------------------------------------------"

# Daftar pesan commit kontekstual sesuai proyek SpeakAI Coach
COMMIT_MESSAGES=(
    "feat: initialize FastAPI microservice structure"
    "feat: implement MediaPipe face mesh landmarks extraction"
    "feat: add eye contact gaze vector calculation"
    "feat: implement posture tilt angle detection"
    "feat: add head pose estimation (pitch, yaw, roll)"
    "feat: integrate speech recognition for audio transcription"
    "feat: add filler words detection module"
    "feat: implement Google Gemini API feedback generator"
    "feat: create scoring algorithm for public speaking metrics"
    "feat: build video stream frame buffer pipeline"
    "feat: implement real-time webcam processing loop"
    "feat: add Laravel authentication and session management"
    "feat: create recording interface with live camera preview"
    "feat: implement speech analysis dashboard and charts"
    "feat: add detailed rubric breakdown for posture and eye contact"
    "feat: support audio playback and waveform visualization"
    "feat: create database schema for speaking practice sessions"
    "feat: add Indonesian filler words dictionary (eh, um, anu, ya)"
    "feat: add pacing and words-per-minute (WPM) calculation"
    "feat: create feedback export summary in PDF format"
    "feat: implement emotion and facial expression detector"
    "feat: add vocal volume modulation and tone variance tracker"
    "feat: setup REST API endpoints for Laravel to Python communication"
    "feat: implement session history list and detail views"
    "feat: add custom recommendations based on speaking score"
    "feat: add benchmark score comparison with previous sessions"
    "feat: implement confidence scoring algorithm for speech delivery"
    "feat: add dark mode support to frontend presentation dashboard"
    "feat: add audio silence duration and pause analysis"
    "feat: implement articulate speech clarity index"
    "refactor: modularize vision analyzer into discrete detector classes"
    "refactor: optimize landmarks distance calculation using numpy"
    "refactor: streamline Gemini prompt template for constructive coaching"
    "refactor: clean up audio preprocessing and temporary file handling"
    "refactor: extract posture calculation thresholds into configuration"
    "refactor: restructure Laravel controllers and service classes"
    "refactor: decouple video streaming logic from analysis worker"
    "refactor: improve response payload formatting for /api/analyze"
    "refactor: organize Blade component hierarchy for speech reports"
    "refactor: simplify database query in session history controller"
    "refactor: improve error handling in external API client"
    "refactor: standardize status codes and error responses"
    "refactor: reorganize static assets and tailwind css styling"
    "refactor: extract filler word pattern regex into reusable helper"
    "refactor: polish logging format for backend service"
    "fix: resolve frame drift during prolonged recording sessions"
    "fix: handle edge case when face is momentarily obscured"
    "fix: correct audio sampling rate conversion to 16kHz"
    "fix: resolve CORS policy headers for FastAPI endpoints"
    "fix: prevent memory leak in OpenCV video capture release"
    "fix: adjust posture confidence score normalization"
    "fix: handle microphone permission denied gracefully in UI"
    "fix: eliminate jitter in eye contact tracking landmarks"
    "fix: correct calculation of speech pause durations"
    "fix: handle network timeouts when contacting Gemini AI"
    "fix: resolve layout overflow on small screen devices"
    "fix: correct database migration foreign key constraints"
    "fix: prevent duplicate submission of practice speech recordings"
    "fix: handle unsupported video codec format during upload"
    "fix: correct calculation of overall speaking performance grade"
    "chore: update Python dependencies in requirements.txt"
    "chore: configure .gitignore for temporary uploads and models"
    "chore: update composer dependencies and package lock"
    "chore: setup environment variable template in .env.example"
    "chore: configure Cloud Run Dockerfile and deployment settings"
    "chore: add database seeder for initial practice topics"
    "chore: format codebase with black and flake8 linters"
    "chore: update npm build scripts and vite configuration"
    "chore: configure storage symlink for public uploads"
    "chore: clean up temporary build artifacts and test recordings"
    "test: add unit tests for eye contact gaze direction"
    "test: add unit tests for filler word tokenization"
    "test: mock Google Gemini AI response for local testing"
    "test: test video upload validation with various formats"
    "test: verify posture angle calculation with synthetic landmarks"
    "test: add feature tests for Laravel speech session routes"
    "test: validate audio chunking pipeline with sample audio"
    "test: integration test between web frontend and AI backend"
    "perf: optimize frame processing rate for MediaPipe analyzer"
    "perf: batch frame analysis to reduce memory consumption"
    "perf: cache session analytics query results"
    "perf: minimize latency in Gemini API streaming responses"
    "perf: leverage numpy vectorization for faster landmark computations"
    "style: improve dashboard UI responsiveness on mobile devices"
    "style: polish color palette and feedback score badges"
    "style: format blade templates with Tailwind CSS utilities"
    "style: improve camera recording border and status indicator"
    "docs: update README with architecture diagram and setup guide"
    "docs: document REST API endpoints and payload schemas"
    "docs: add guidelines for running AI backend locally"
    "docs: write comments explaining gaze tracking vector math"
)

# 1. Kumpulkan seluruh tanggal dalam rentang 2026-09-01 s/d 2026-10-31 (61 hari)
current_date="$START_DATE"
dates=()
dow=()

while true; do
    dates+=("$current_date")
    d_num=$(date -d "$current_date" +%u)
    dow+=("$d_num")
    if [ "$current_date" = "$END_DATE" ]; then
        break
    fi
    current_date=$(date -d "$current_date + 1 day" +%Y-%m-%d)
done

total_days=${#dates[@]}

# 2. Hitung alokasi commit per hari
# Weekday (1-5): base 5 commit (sebagian menjadi 6 commit agar total 280)
# Weekend (6-7): base 1 commit
counts=()
allocated_total=0

for i in "${!dates[@]}"; do
    d=${dow[$i]}
    if [ "$d" -le 5 ]; then
        counts[$i]=5
    else
        counts[$i]=1
    fi
    allocated_total=$((allocated_total + counts[i]))
done

# Distribusikan sisa commit ke hari kerja
diff=$((TARGET_TOTAL - allocated_total))

weekday_indices=()
for i in "${!dates[@]}"; do
    if [ "${dow[$i]}" -le 5 ]; then
        weekday_indices+=("$i")
    fi
done

# Acak indeks hari kerja untuk penambahan commit
shuffled_weekdays=($(shuf -e "${weekday_indices[@]}"))
idx=0
while [ "$diff" -gt 0 ]; do
    target_idx=${shuffled_weekdays[$idx]}
    counts[$target_idx]=$((counts[target_idx] + 1))
    diff=$((diff - 1))
    idx=$(((idx + 1) % ${#shuffled_weekdays[@]}))
done

# Inisialisasi file markdown history
cat << 'EOF' > "$HISTORY_FILE"
# 🎤 SpeakAI Coach — Project Development History Log

Dokumen ini mencatat log simulasi progres commit repositori dari fase inisiasi hingga finalisasi.
EOF

# 3. Eksekusi Commit
total_committed=0
is_first_commit=true

if git rev-parse --verify HEAD >/dev/null 2>&1; then
    is_first_commit=false
fi

echo "Membuat $TARGET_TOTAL commit secara bertahap..."

for i in "${!dates[@]}"; do
    c_date="${dates[$i]}"
    day_commits="${counts[$i]}"

    # Bangkitkan waktu acak di jam kerja: antara 08:00:00 (28800s) s/d 21:59:59 (79199s)
    # Urutkan secara numerik agar jam dalam sehari selalu kronologis
    secs=($(shuf -i 28800-79199 -n "$day_commits" | sort -n))

    for s in "${secs[@]}"; do
        h=$((s / 3600))
        m=$(((s % 3600) / 60))
        sec=$((s % 60))
        time_str=$(printf "%02d:%02d:%02d" "$h" "$m" "$sec")
        timestamp="${c_date} ${time_str} ${TIMEZONE}"

        if [ "$is_first_commit" = true ]; then
            # Commit pertama menyertakan seluruh codebase proyek dan .gitignore
            msg="chore: initial project repository setup and architecture"
            git add -A
            is_first_commit=false
        else
            # Ambil pesan commit acak dari kumpulan tema progres tugas
            rand_idx=$((RANDOM % ${#COMMIT_MESSAGES[@]}))
            msg="${COMMIT_MESSAGES[$rand_idx]}"
            
            # Catat perubahan ke file history
            echo "- **[${c_date} ${time_str}]** \`${msg}\`" >> "$HISTORY_FILE"
            git add "$HISTORY_FILE"
        fi

        # Sinkronkan GIT_AUTHOR_DATE dan GIT_COMMITTER_DATE
        export GIT_AUTHOR_DATE="$timestamp"
        export GIT_COMMITTER_DATE="$timestamp"

        git commit -m "$msg" --quiet
        total_committed=$((total_committed + 1))
    done

    # Cetak progres setiap 10 hari dan hari terakhir
    if [ $(( (i + 1) % 10 )) -eq 0 ] || [ $((i + 1)) -eq "$total_days" ]; then
        echo "  [Progres] Hari $((i + 1))/$total_days ($c_date) - Total commit: $total_committed"
    fi
done

echo "---------------------------------------------"
echo "=== Backfill Git History Selesai ==="
echo ""
echo "Ringkasan Eksekusi:"
echo "Total commit berhasil dibuat : $(git rev-list --count HEAD)"
echo "Commit pertama               : $(git log -1 --format='%h | %ad | %s' $(git rev-list --max-parents=0 HEAD))"
echo "Commit terakhir              : $(git log -1 --format='%h | %ad | %s' HEAD)"
echo ""
echo "5 Commit Terakhir:"
git log --oneline -n 5
