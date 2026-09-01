"""
============================================================
Audio Analyzer Module
============================================================
Uses SpeechRecognition to transcribe audio and detect filler words.
============================================================
"""

import speech_recognition as sr
from pydub import AudioSegment
from dataclasses import dataclass, field
from typing import Dict, List
import tempfile
import os
import re
import logging

logger = logging.getLogger(__name__)

FILLER_WORDS = {
    "uh": "uh", "um": "um", "uhm": "um", "umm": "um",
    "like": "like", "so": "so", "basically": "basically",
    "actually": "actually", "literally": "literally",
    "right": "right", "well": "well", "anyway": "anyway",
    "okay": "okay", "ok": "okay",
}

FILLER_PHRASES = ["you know", "i mean", "kind of", "sort of"]


@dataclass
class FillerWordAnalysis:
    total_filler_count: int = 0
    filler_frequency: Dict[str, int] = field(default_factory=dict)
    filler_percentage: float = 0.0
    top_fillers: List[Dict] = field(default_factory=list)


@dataclass
class AudioAnalysisResult:
    transcription: str = ""
    word_count: int = 0
    duration_seconds: float = 0.0
    words_per_minute: float = 0.0
    filler_analysis: FillerWordAnalysis = field(default_factory=FillerWordAnalysis)
    analysis_successful: bool = False
    error_message: str = ""


class AudioAnalyzer:
    """Analyzes audio from video files for speech content and filler words."""

    def __init__(self, language: str = "en-US"):
        self.language = language
        self.recognizer = sr.Recognizer()
        self.recognizer.energy_threshold = 300
        self.recognizer.dynamic_energy_threshold = True

    def extract_audio_from_video(self, video_path: str) -> str:
        """Extract audio from video file, return path to WAV."""
        temp_dir = tempfile.mkdtemp()
        audio_path = os.path.join(temp_dir, "extracted_audio.wav")
        video_audio = AudioSegment.from_file(video_path)
        video_audio = video_audio.set_channels(1).set_frame_rate(16000).set_sample_width(2)
        video_audio.export(audio_path, format="wav")
        return audio_path

    def transcribe_audio(self, audio_path: str) -> dict:
        """Transcribe audio file using Google Speech Recognition."""
        try:
            with sr.AudioFile(audio_path) as source:
                audio_data = self.recognizer.record(source)
                duration = source.DURATION
            text = self.recognizer.recognize_google(audio_data, language=self.language)
            return {"text": text, "duration": duration, "success": True}
        except sr.UnknownValueError:
            return {"text": "", "duration": 0, "success": False,
                    "error": "Could not understand audio."}
        except sr.RequestError as e:
            return {"text": "", "duration": 0, "success": False,
                    "error": f"Service unavailable: {e}"}

    def analyze_filler_words(self, text: str) -> FillerWordAnalysis:
        """Count filler words and phrases in transcribed text."""
        if not text:
            return FillerWordAnalysis()

        text_lower = text.lower()
        words = text_lower.split()
        total_words = len(words)
        filler_counts: Dict[str, int] = {}

        # Detect multi-word phrases
        for phrase in FILLER_PHRASES:
            count = text_lower.count(phrase)
            if count > 0:
                filler_counts[phrase] = filler_counts.get(phrase, 0) + count

        # Detect single-word fillers
        single_fillers = [w for w in FILLER_WORDS.keys() if " " not in w]
        for word in words:
            clean = re.sub(r'[^\w]', '', word)
            if clean in single_fillers:
                canonical = FILLER_WORDS[clean]
                filler_counts[canonical] = filler_counts.get(canonical, 0) + 1

        total_fillers = sum(filler_counts.values())
        pct = (total_fillers / total_words * 100) if total_words > 0 else 0
        sorted_f = sorted(filler_counts.items(), key=lambda x: x[1], reverse=True)
        top = [{"word": w, "count": c} for w, c in sorted_f[:10]]

        return FillerWordAnalysis(
            total_filler_count=total_fillers,
            filler_frequency=filler_counts,
            filler_percentage=round(pct, 1),
            top_fillers=top,
        )

    def analyze_video(self, video_path: str) -> AudioAnalysisResult:
        """Full audio analysis pipeline for a video file."""
        result = AudioAnalysisResult()
        try:
            audio_path = self.extract_audio_from_video(video_path)
            tx = self.transcribe_audio(audio_path)
            if not tx["success"]:
                result.error_message = tx.get("error", "Transcription failed")
                return result
            result.transcription = tx["text"]
            result.duration_seconds = tx.get("duration", 0)
            result.word_count = len(result.transcription.split())
            if result.duration_seconds > 0:
                result.words_per_minute = round(
                    (result.word_count / result.duration_seconds) * 60, 1)
            result.filler_analysis = self.analyze_filler_words(result.transcription)
            result.analysis_successful = True
            try:
                os.remove(audio_path)
                os.rmdir(os.path.dirname(audio_path))
            except OSError:
                pass
        except Exception as e:
            result.error_message = f"Audio analysis failed: {e}"
            logger.error(result.error_message)
        return result
