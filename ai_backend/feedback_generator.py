"""
============================================================
AI Feedback Generator Module
============================================================
Integrates with Google Generative AI (Gemini) to produce
constructive, encouraging coaching feedback based on the
vision and audio analysis scores.
============================================================
"""

import google.generativeai as genai
from dataclasses import dataclass, field
from typing import Optional
import os
import logging
import json

logger = logging.getLogger(__name__)


@dataclass
class CoachingFeedback:
    """Structured AI coaching feedback."""
    overall_score: float = 0.0
    summary: str = ""
    eye_contact_feedback: str = ""
    posture_feedback: str = ""
    speech_feedback: str = ""
    filler_word_feedback: str = ""
    actionable_tips: list = field(default_factory=list)
    encouragement: str = ""
    generation_successful: bool = False
    error_message: str = ""


class FeedbackGenerator:
    """
    Uses Google Gemini to generate constructive coaching feedback
    based on presentation analysis scores.
    """

    def __init__(self, api_key: Optional[str] = None):
        """Initialize with Google AI Studio API key."""
        self.api_key = api_key or os.getenv("GOOGLE_AI_API_KEY", "")
        if self.api_key:
            genai.configure(api_key=self.api_key)
            self.model = genai.GenerativeModel("gemini-2.0-flash")
        else:
            self.model = None
            logger.warning("No Google AI API key provided. Feedback will be basic.")

    def _build_prompt(
        self,
        eye_contact_score: float,
        posture_score: float,
        transcription: str,
        filler_count: int,
        filler_details: dict,
        words_per_minute: float,
        word_count: int,
        duration_seconds: float,
    ) -> str:
        """Build a detailed prompt for the LLM."""
        return f"""You are an expert public speaking coach. Analyze the following 
presentation practice session data and provide constructive, encouraging feedback.

## Session Analysis Data

**Eye Contact Score:** {eye_contact_score}/100
(Measures how consistently the speaker looked at the camera/audience)

**Posture Score:** {posture_score}/100
(Measures upright posture and body stability)

**Speech Transcription:**
"{transcription[:2000]}"

**Speech Statistics:**
- Total Words: {word_count}
- Duration: {duration_seconds:.1f} seconds
- Speaking Pace: {words_per_minute:.0f} words per minute
- Total Filler Words: {filler_count}
- Filler Word Breakdown: {json.dumps(filler_details)}

## Instructions
Please provide your response as a JSON object with these exact keys:
{{
  "overall_score": <0-100 weighted overall score>,
  "summary": "<2-3 sentence overall assessment>",
  "eye_contact_feedback": "<specific feedback on eye contact>",
  "posture_feedback": "<specific feedback on posture and body language>",
  "speech_feedback": "<feedback on speech clarity, pace, and content>",
  "filler_word_feedback": "<specific feedback on filler word usage>",
  "actionable_tips": ["<tip 1>", "<tip 2>", "<tip 3>"],
  "encouragement": "<motivational closing message>"
}}

Be constructive, specific, and encouraging. Focus on growth."""

    def generate_feedback(
        self,
        eye_contact_score: float,
        posture_score: float,
        transcription: str,
        filler_count: int = 0,
        filler_details: dict = None,
        words_per_minute: float = 0,
        word_count: int = 0,
        duration_seconds: float = 0,
    ) -> CoachingFeedback:
        """Generate AI coaching feedback using Gemini."""
        feedback = CoachingFeedback()

        if not self.model:
            # Fallback: basic feedback without AI
            return self._generate_basic_feedback(
                eye_contact_score, posture_score, filler_count
            )

        try:
            prompt = self._build_prompt(
                eye_contact_score, posture_score, transcription,
                filler_count, filler_details or {}, words_per_minute,
                word_count, duration_seconds,
            )

            response = self.model.generate_content(
                prompt,
                generation_config=genai.types.GenerationConfig(
                    response_mime_type="application/json",
                    temperature=0.7,
                    max_output_tokens=1024,
                ),
            )

            # Parse the JSON response
            result = json.loads(response.text)

            feedback.overall_score = float(result.get("overall_score", 0))
            feedback.summary = result.get("summary", "")
            feedback.eye_contact_feedback = result.get("eye_contact_feedback", "")
            feedback.posture_feedback = result.get("posture_feedback", "")
            feedback.speech_feedback = result.get("speech_feedback", "")
            feedback.filler_word_feedback = result.get("filler_word_feedback", "")
            feedback.actionable_tips = result.get("actionable_tips", [])
            feedback.encouragement = result.get("encouragement", "")
            feedback.generation_successful = True

            logger.info(f"AI feedback generated. Overall score: {feedback.overall_score}")

        except Exception as e:
            logger.error(f"AI feedback generation failed: {e}")
            feedback = self._generate_basic_feedback(
                eye_contact_score, posture_score, filler_count
            )
            feedback.error_message = f"AI generation failed, using basic feedback: {e}"

        return feedback

    def _generate_basic_feedback(
        self, eye_score: float, posture_score: float, filler_count: int
    ) -> CoachingFeedback:
        """Fallback feedback without AI when API is unavailable."""
        overall = (eye_score * 0.3 + posture_score * 0.3 +
                   max(0, 100 - filler_count * 5) * 0.4)

        tips = []
        if eye_score < 70:
            tips.append("Practice looking directly at your camera lens.")
        if posture_score < 70:
            tips.append("Stand or sit upright with shoulders back.")
        if filler_count > 5:
            tips.append("Pause instead of using filler words like 'um' or 'uh'.")
        if not tips:
            tips.append("Keep practicing to maintain your excellent performance!")

        return CoachingFeedback(
            overall_score=round(overall, 1),
            summary="Analysis complete. See detailed feedback below.",
            eye_contact_feedback=f"Eye contact score: {eye_score}/100.",
            posture_feedback=f"Posture score: {posture_score}/100.",
            speech_feedback="Review your transcription for clarity.",
            filler_word_feedback=f"You used {filler_count} filler words.",
            actionable_tips=tips,
            encouragement="Every practice session makes you a better speaker!",
            generation_successful=True,
        )
