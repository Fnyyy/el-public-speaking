"""
============================================================
Vision Analyzer Module (Tasks API)
============================================================
Uses OpenCV and MediaPipe Tasks API to analyze video frames for:
  1. Eye Contact Score  - Face Mesh landmark analysis
  2. Posture Score      - Pose estimation landmark analysis
============================================================
"""

import cv2
import numpy as np
import mediapipe as mp
from mediapipe.tasks import python
from mediapipe.tasks.python import vision
from dataclasses import dataclass, field
from typing import List, Tuple, Optional
import logging
import os

logger = logging.getLogger(__name__)


# ============================================================
# Data Classes for Clean Result Handling
# ============================================================

@dataclass
class EyeContactResult:
    score: float = 0.0                    # 0-100 scale
    frames_analyzed: int = 0
    frames_with_contact: int = 0
    looking_away_moments: List[float] = field(default_factory=list)  # timestamps
    feedback: str = ""

@dataclass
class PostureResult:
    score: float = 0.0                    # 0-100 scale
    frames_analyzed: int = 0
    slouch_detected_count: int = 0
    excessive_movement_score: float = 0.0  # 0-100 scale
    shoulder_alignment_avg: float = 0.0
    feedback: str = ""

@dataclass
class VisionAnalysisResult:
    eye_contact: EyeContactResult = field(default_factory=EyeContactResult)
    posture: PostureResult = field(default_factory=PostureResult)
    total_frames: int = 0
    video_duration_seconds: float = 0.0
    analysis_successful: bool = False
    error_message: str = ""
    analysis_mode: str = "real"            # "real" or "mock" — exposed in API response

# ============================================================
# Eye Contact Analyzer using MediaPipe FaceLandmarker Task
# ============================================================

class EyeContactAnalyzer:
    LEFT_IRIS = [468, 469, 470, 471, 472]
    RIGHT_IRIS = [473, 474, 475, 476, 477]

    LEFT_EYE_INNER = 133
    LEFT_EYE_OUTER = 33
    RIGHT_EYE_INNER = 362
    RIGHT_EYE_OUTER = 263

    LEFT_EYE_TOP = 159
    LEFT_EYE_BOTTOM = 145
    RIGHT_EYE_TOP = 386
    RIGHT_EYE_BOTTOM = 374

    # Landmarks for head-pose estimation
    NOSE_TIP = 1         # tip of the nose
    FOREHEAD_CENTER = 10 # top of forehead
    CHIN = 152           # bottom of chin

    def __init__(
        self,
        threshold: float = 0.17,             # iris must stay in centre 34% of eye socket (was 0.20)
        head_yaw_threshold: float = 0.10,    # tighter: head turn detected sooner (was 0.15)
        head_pitch_threshold: float = 0.12,  # tighter: head tilt detected sooner (was 0.18)
        model_path: str = "models/face_landmarker.task"
    ):
        self.threshold = threshold
        self.head_yaw_threshold = head_yaw_threshold
        self.head_pitch_threshold = head_pitch_threshold

        # Setup Face Landmarker Task
        base_options = python.BaseOptions(model_asset_path=model_path)
        options = vision.FaceLandmarkerOptions(
            base_options=base_options,
            output_face_blendshapes=False,
            output_facial_transformation_matrixes=False,
            num_faces=1)
        self.detector = vision.FaceLandmarker.create_from_options(options)

    def _calculate_iris_position_ratio(
        self, landmarks, iris_indices: list,
        inner_corner: int, outer_corner: int,
        top: int, bottom: int
    ) -> Tuple[float, float]:
        iris_x = np.mean([landmarks[i].x for i in iris_indices])
        iris_y = np.mean([landmarks[i].y for i in iris_indices])

        inner_x = landmarks[inner_corner].x
        outer_x = landmarks[outer_corner].x
        eye_width = abs(outer_x - inner_x)
        
        if eye_width < 1e-6:
            return 0.5, 0.5

        h_ratio = (iris_x - min(inner_x, outer_x)) / eye_width

        top_y = landmarks[top].y
        bottom_y = landmarks[bottom].y
        eye_height = abs(bottom_y - top_y)

        if eye_height < 1e-6:
            return h_ratio, 0.5

        v_ratio = (iris_y - min(top_y, bottom_y)) / eye_height

        return h_ratio, v_ratio

    def _estimate_head_direction(self, landmarks) -> Tuple[float, float]:
        """
        Estimate head yaw and pitch using facial geometry.
        Returns (yaw_deviation, pitch_deviation) — 0.0 each means perfectly centred.

        Yaw  : how much the nose deviates left/right from the eye-midpoint.
        Pitch: how much the nose deviates up/down from its expected position
               on a neutral face (≈ 55% down from forehead to chin).
        """
        left_x  = landmarks[self.LEFT_EYE_OUTER].x
        right_x = landmarks[self.RIGHT_EYE_OUTER].x
        nose_x  = landmarks[self.NOSE_TIP].x

        face_width = abs(right_x - left_x)
        if face_width < 1e-6:
            return 0.0, 0.0

        # Yaw: nose x vs horizontal midpoint between eye corners
        eye_mid_x   = (left_x + right_x) / 2
        yaw_deviation = abs(nose_x - eye_mid_x) / face_width

        # Pitch: nose y vs expected 55% down the forehead-to-chin axis
        forehead_y = landmarks[self.FOREHEAD_CENTER].y
        chin_y     = landmarks[self.CHIN].y
        nose_y     = landmarks[self.NOSE_TIP].y

        face_height = abs(chin_y - forehead_y)
        if face_height < 1e-6:
            return yaw_deviation, 0.0

        actual_ratio   = (nose_y - forehead_y) / face_height
        pitch_deviation = abs(actual_ratio - 0.55)   # 0.55 = neutral frontal ratio

        return yaw_deviation, pitch_deviation

    def analyze_frame(self, rgb_frame: np.ndarray) -> Optional[bool]:
        mp_image = mp.Image(image_format=mp.ImageFormat.SRGB, data=rgb_frame)
        detection_result = self.detector.detect(mp_image)

        if not detection_result.face_landmarks:
            return None

        landmarks = detection_result.face_landmarks[0]

        # Ensure iris landmarks exist
        if len(landmarks) <= max(self.RIGHT_IRIS):
            return None

        # ── Check 1: Head direction (yaw / pitch) ──────────────────────────────
        # If the head is turned or tilted, eye contact is impossible regardless
        # of where the iris sits inside the socket.
        yaw_dev, pitch_dev = self._estimate_head_direction(landmarks)
        if yaw_dev > self.head_yaw_threshold or pitch_dev > self.head_pitch_threshold:
            logger.debug(f"Head turned — yaw={yaw_dev:.3f}, pitch={pitch_dev:.3f}")
            return False

        # ── Check 2: Iris position within each eye socket ─────────────────────
        left_h, left_v = self._calculate_iris_position_ratio(
            landmarks, self.LEFT_IRIS,
            self.LEFT_EYE_INNER, self.LEFT_EYE_OUTER,
            self.LEFT_EYE_TOP, self.LEFT_EYE_BOTTOM
        )
        right_h, right_v = self._calculate_iris_position_ratio(
            landmarks, self.RIGHT_IRIS,
            self.RIGHT_EYE_INNER, self.RIGHT_EYE_OUTER,
            self.RIGHT_EYE_TOP, self.RIGHT_EYE_BOTTOM
        )

        avg_h = (left_h + right_h) / 2
        avg_v = (left_v + right_v) / 2

        h_deviation = abs(avg_h - 0.5)
        v_deviation = abs(avg_v - 0.5)

        # threshold 0.20 → iris must stay within centre 40% of eye width/height
        is_looking = h_deviation < self.threshold and v_deviation < self.threshold
        return is_looking

    def close(self):
        self.detector.close()

# ============================================================
# Posture Analyzer using MediaPipe PoseLandmarker Task
# ============================================================

class PostureAnalyzer:
    NOSE = 0
    LEFT_SHOULDER = 11
    RIGHT_SHOULDER = 12
    LEFT_EAR = 7
    RIGHT_EAR = 8
    LEFT_HIP = 23
    RIGHT_HIP = 24

    def __init__(
        self,
        # Minimum vertical gap (normalised) expected between ears and shoulders.
        # Larger gap required → slouching detected earlier.
        ear_shoulder_min_gap: float = 0.15,     # was 0.12 — stricter
        # Minimum vertical gap between nose and shoulder centre.
        # Larger gap required → drooping head detected earlier.
        head_shoulder_min_gap: float = 0.25,    # was 0.20 — stricter
        # Max tolerated shoulder height asymmetry (normalised y-diff).
        shoulder_level_threshold: float = 0.04, # was 0.05 — stricter
        # Frame-to-frame movement of shoulder centre that counts as fidgeting.
        movement_threshold: float = 0.018,      # was 0.025 — stricter
        model_path: str = "models/pose_landmarker.task"
    ):
        self.ear_shoulder_min_gap    = ear_shoulder_min_gap
        self.head_shoulder_min_gap   = head_shoulder_min_gap
        self.shoulder_level_threshold = shoulder_level_threshold
        self.movement_threshold      = movement_threshold

        # Setup Pose Landmarker Task
        base_options = python.BaseOptions(model_asset_path=model_path)
        options = vision.PoseLandmarkerOptions(
            base_options=base_options,
            output_segmentation_masks=False)
        self.detector = vision.PoseLandmarker.create_from_options(options)

        self.prev_shoulder_center = None

    def _get_landmark_coords(self, landmarks, index: int) -> Tuple[float, float]:
        lm = landmarks[index]
        return lm.x, lm.y

    def analyze_frame(self, rgb_frame: np.ndarray) -> Optional[dict]:
        mp_image = mp.Image(image_format=mp.ImageFormat.SRGB, data=rgb_frame)
        detection_result = self.detector.detect(mp_image)

        if not detection_result.pose_landmarks:
            return None

        landmarks = detection_result.pose_landmarks[0]

        left_shoulder  = self._get_landmark_coords(landmarks, self.LEFT_SHOULDER)
        right_shoulder = self._get_landmark_coords(landmarks, self.RIGHT_SHOULDER)
        left_ear       = self._get_landmark_coords(landmarks, self.LEFT_EAR)
        right_ear      = self._get_landmark_coords(landmarks, self.RIGHT_EAR)
        nose           = self._get_landmark_coords(landmarks, self.NOSE)

        shoulder_center_y = (left_shoulder[1] + right_shoulder[1]) / 2
        shoulder_center_x = (left_shoulder[0] + right_shoulder[0]) / 2
        ear_center_y      = (left_ear[1] + right_ear[1]) / 2

        # ── Slouch detection (two complementary criteria) ─────────────────────
        # 1. Ear-to-shoulder vertical gap: in normalised coords y grows downward,
        #    so shoulder_center_y > ear_center_y when posture is good.
        ear_shoulder_gap  = shoulder_center_y - ear_center_y   # positive = ears above shoulders
        is_ears_too_low   = ear_shoulder_gap < self.ear_shoulder_min_gap

        # 2. Nose-to-shoulder gap: nose should sit well above shoulder level.
        head_shoulder_gap = shoulder_center_y - nose[1]         # positive = nose above shoulders
        is_head_too_low   = head_shoulder_gap < self.head_shoulder_min_gap

        is_slouching = is_ears_too_low or is_head_too_low

        # ── Shoulder levelness ────────────────────────────────────────────────
        shoulder_y_diff      = abs(left_shoulder[1] - right_shoulder[1])
        is_uneven_shoulders  = shoulder_y_diff > self.shoulder_level_threshold

        # ── Frame-to-frame movement (fidgeting / swaying) ─────────────────────
        current_center = (shoulder_center_x, shoulder_center_y)
        movement = 0.0
        if self.prev_shoulder_center is not None:
            dx = current_center[0] - self.prev_shoulder_center[0]
            dy = current_center[1] - self.prev_shoulder_center[1]
            movement = np.sqrt(dx**2 + dy**2)
        self.prev_shoulder_center = current_center
        is_excessive_movement = movement > self.movement_threshold

        shoulder_width = abs(left_shoulder[0] - right_shoulder[0])

        return {
            "shoulder_y_diff":     shoulder_y_diff,
            "ear_shoulder_gap":    ear_shoulder_gap,
            "head_shoulder_gap":   head_shoulder_gap,
            "is_slouching":        is_slouching,
            "is_uneven_shoulders": is_uneven_shoulders,
            "movement":            movement,
            "is_excessive_movement": is_excessive_movement,
            "shoulder_width":      shoulder_width,
        }

    def close(self):
        self.detector.close()

# ============================================================
# Main Vision Analysis Pipeline
# ============================================================

class VisionAnalysisPipeline:
    def __init__(self, sample_rate: int = 3):
        self.sample_rate = sample_rate
        self.use_mock = False
        
        try:
            self.eye_analyzer = EyeContactAnalyzer()
            self.posture_analyzer = PostureAnalyzer()
            logger.info("VisionAnalysisPipeline initialized in REAL mode — MediaPipe models loaded.")
        except Exception as e:
            logger.warning(
                f"⚠️  MOCK MODE ACTIVE — MediaPipe init failed: {e}\n"
                "    Scores will be randomised and do NOT reflect actual performance!"
            )
            self.use_mock = True

    def analyze_video(self, video_path: str) -> VisionAnalysisResult:
        if self.use_mock:
            return self._mock_analyze(video_path)

        result = VisionAnalysisResult()

        cap = cv2.VideoCapture(video_path)
        if not cap.isOpened():
            result.error_message = f"Failed to open video file: {video_path}"
            logger.error(result.error_message)
            return result

        fps = cap.get(cv2.CAP_PROP_FPS) or 30.0
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        result.video_duration_seconds = total_frames / fps
        result.total_frames = total_frames

        eye_contact_frames = 0
        eye_total_analyzed = 0
        posture_good_frames = 0
        posture_total_analyzed = 0
        total_movement = []
        slouch_count = 0
        looking_away_times = []
        shoulder_alignments = []

        frame_idx = 0
        
        # Analyze max 300 frames to ensure fast response (for a demo)
        max_frames_to_process = 300
        processed_count = 0

        logger.info(f"Starting video analysis: {total_frames} frames, {fps:.1f} FPS")

        while True:
            ret, frame = cap.read()
            if not ret or processed_count >= max_frames_to_process:
                break

            if frame_idx % self.sample_rate != 0:
                frame_idx += 1
                continue
                
            processed_count += 1
            timestamp = frame_idx / fps
            rgb_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)

            # Eye Contact Analysis
            eye_result = self.eye_analyzer.analyze_frame(rgb_frame)
            if eye_result is not None:
                eye_total_analyzed += 1
                if eye_result:
                    eye_contact_frames += 1
                else:
                    looking_away_times.append(round(timestamp, 2))

            # Posture Analysis
            posture_data = self.posture_analyzer.analyze_frame(rgb_frame)
            if posture_data is not None:
                posture_total_analyzed += 1
                total_movement.append(posture_data["movement"])
                shoulder_alignments.append(posture_data["shoulder_y_diff"])

                if posture_data["is_slouching"]:
                    slouch_count += 1

                # A frame is "good" only if ALL three posture criteria are met
                if (
                    not posture_data["is_slouching"]
                    and not posture_data["is_excessive_movement"]
                    and not posture_data["is_uneven_shoulders"]
                ):
                    posture_good_frames += 1

            frame_idx += 1

        cap.release()

        # Calculate Final Scores
        if eye_total_analyzed > 0:
            eye_score = (eye_contact_frames / eye_total_analyzed) * 100
            result.eye_contact = EyeContactResult(
                score=round(eye_score, 1),
                frames_analyzed=eye_total_analyzed,
                frames_with_contact=eye_contact_frames,
                looking_away_moments=looking_away_times[:20],
                feedback=self._generate_eye_feedback(eye_score),
            )

        if posture_total_analyzed > 0:
            posture_score = (posture_good_frames / posture_total_analyzed) * 100
            avg_movement = np.mean(total_movement) if total_movement else 0
            # movement_threshold=0.025, so avg_movement≈0.025 → movement_score≈80
            movement_score = max(0, 100 - (avg_movement * 800))
            avg_shoulder = np.mean(shoulder_alignments) if shoulder_alignments else 0

            result.posture = PostureResult(
                score=round(posture_score, 1),
                frames_analyzed=posture_total_analyzed,
                slouch_detected_count=slouch_count,
                excessive_movement_score=round(movement_score, 1),
                shoulder_alignment_avg=round(avg_shoulder, 4),
                feedback=self._generate_posture_feedback(posture_score, movement_score),
            )

        result.analysis_successful = True
        result.analysis_mode = "real"
        logger.info(
            f"[REAL] Analysis complete — "
            f"Eye={result.eye_contact.score} ({eye_contact_frames}/{eye_total_analyzed} frames), "
            f"Posture={result.posture.score} ({posture_good_frames}/{posture_total_analyzed} frames), "
            f"Slouch={slouch_count}"
        )
        return result

    def _generate_eye_feedback(self, score: float) -> str:
        if score >= 80:
            return "Excellent eye contact! You maintained strong engagement with the camera."
        elif score >= 60:
            return "Good eye contact overall, but you occasionally looked away."
        elif score >= 40:
            return "Your eye contact needs improvement. Try to look at the camera more consistently."
        else:
            return "Significant eye contact issues detected. Practice looking directly at the camera."

    def _generate_posture_feedback(self, posture_score: float, movement_score: float) -> str:
        feedback_parts = []
        if posture_score >= 80:
            feedback_parts.append("Your posture is excellent - upright and confident.")
        elif posture_score >= 60:
            feedback_parts.append("Your posture is generally good with some moments of slouching.")
        else:
            feedback_parts.append("Your posture needs work. Focus on sitting/standing upright.")

        if movement_score >= 80:
            feedback_parts.append("Body stability is great.")
        elif movement_score >= 50:
            feedback_parts.append("Some excessive movement detected - try to be more still.")
        else:
            feedback_parts.append("Significant swaying/movement detected. Work on staying centered.")

        return " ".join(feedback_parts)

    def _mock_analyze(self, video_path: str) -> VisionAnalysisResult:
        """
        Fallback used when MediaPipe models fail to load.
        ⚠️  Scores are randomised — they do NOT reflect real performance.
        The response will include analysis_mode='mock' so the caller is aware.
        """
        result = VisionAnalysisResult()
        
        cap = cv2.VideoCapture(video_path)
        if not cap.isOpened():
            result.error_message = f"Failed to open video file: {video_path}"
            return result
            
        fps = cap.get(cv2.CAP_PROP_FPS) or 30.0
        total_frames = int(cap.get(cv2.CAP_PROP_FRAME_COUNT))
        result.video_duration_seconds = total_frames / fps
        result.total_frames = total_frames
        cap.release()

        import random
        # ⚠️  Mock scores — intentionally mid-to-low range to avoid false positives.
        # Real analysis requires MediaPipe models to be loaded successfully.
        eye_score      = random.uniform(30.0, 60.0)   # was 45–85 (too optimistic)
        posture_score  = random.uniform(30.0, 60.0)   # was 45–85 (too optimistic)
        movement_score = random.uniform(35.0, 65.0)   # was 50–85 (too optimistic)

        result.eye_contact = EyeContactResult(
            score=round(eye_score, 1),
            frames_analyzed=min(100, total_frames),
            frames_with_contact=int(min(100, total_frames) * (eye_score / 100)),
            feedback=(  
                "⚠️ MOCK MODE: MediaPipe unavailable. "
                + self._generate_eye_feedback(eye_score)
            )
        )
        
        result.posture = PostureResult(
            score=round(posture_score, 1),
            frames_analyzed=min(100, total_frames),
            slouch_detected_count=random.randint(2, 8),  # was 0–3 (too optimistic)
            excessive_movement_score=round(movement_score, 1),
            feedback=(
                "⚠️ MOCK MODE: MediaPipe unavailable. "
                + self._generate_posture_feedback(posture_score, movement_score)
            )
        )
        
        result.analysis_successful = True
        result.analysis_mode = "mock"
        logger.warning("[MOCK] Returning randomised scores — install MediaPipe for real analysis.")
        return result

    def close(self):
        if not self.use_mock:
            self.eye_analyzer.close()
            self.posture_analyzer.close()
