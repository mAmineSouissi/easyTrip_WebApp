from huggingface_hub import hf_hub_download
from ultralytics import YOLO
from PIL import Image
import sys

# Download the YOLOv8 face detection model
model_path = hf_hub_download(repo_id="arnabdhar/YOLOv8-Face-Detection", filename="model.pt")

# Load model and force it to run on CPU
model = YOLO(model_path)
model.to("cpu")  # 🔴 This line is important

def detect_face(image_path):
    image = Image.open(image_path).convert("RGB")
    results = model(image)  # Now runs on CPU
    detections = results[0].boxes

    if detections and len(detections) > 0:
        print("FACE_FOUND")
    else:
        print("NO_FACE")

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python detect_face.py ./public/img/profile/Face2.jpg")
        sys.exit(1)
    detect_face(sys.argv[1])
