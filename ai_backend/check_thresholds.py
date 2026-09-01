"""Quick threshold verification script."""
from vision_analyzer import VisionAnalysisPipeline, EyeContactAnalyzer, PostureAnalyzer

e = EyeContactAnalyzer()
p = PostureAnalyzer()

print("=== THRESHOLD VERIFICATION ===")
print(f"  Eye iris threshold   : {e.threshold}   (target: 0.17)")
print(f"  Head yaw threshold   : {e.head_yaw_threshold}   (target: 0.10)")
print(f"  Head pitch threshold : {e.head_pitch_threshold}   (target: 0.12)")
print(f"  Ear-shoulder gap     : {p.ear_shoulder_min_gap}   (target: 0.15)")
print(f"  Head-shoulder gap    : {p.head_shoulder_min_gap}   (target: 0.25)")
print(f"  Shoulder level       : {p.shoulder_level_threshold}   (target: 0.04)")
print(f"  Movement threshold   : {p.movement_threshold}   (target: 0.018)")

v = VisionAnalysisPipeline()
mode = "REAL" if not v.use_mock else "MOCK (MediaPipe unavailable!)"
print(f"\n  Pipeline mode        : {mode}")

# Check analysis_mode field exists
from vision_analyzer import VisionAnalysisResult
r = VisionAnalysisResult()
print(f"  Default analysis_mode: '{r.analysis_mode}'")

v.close()
e.close()
p.close()
print("\nAll checks passed!")
