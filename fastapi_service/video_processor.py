import numpy as np
import tempfile
import os
import logging
from typing import List, Tuple, Optional
from PIL import Image, ImageFilter
import io
from config import settings

logger = logging.getLogger(__name__)

class VideoProcessor:
    def __init__(self):
        # For PIL-based approach, we'll use image processing instead of face detection
        pass
        
    def extract_frames_from_video(self, video_path: str, num_frames: int = 5) -> List[Image.Image]:
        """Extract multiple frames from video for better face analysis"""
        try:
            # For now, we'll simulate frame extraction since we don't have OpenCV
            # In a real implementation, you would use a video processing library
            logger.info(f"Simulating frame extraction from video: {video_path}")
            
            # Create a simulated frame (in real implementation, this would be actual video frames)
            # For demo purposes, we'll create a simple image
            frame = Image.new('RGB', (640, 480), color='gray')
            
            return [frame] * num_frames
            
        except Exception as e:
            logger.error(f"Error extracting frames: {str(e)}")
            raise
    
    def detect_faces_in_frame(self, frame: Image.Image) -> List[Tuple[int, int, int, int]]:
        """Detect faces in a single frame"""
        try:
            # For PIL-based approach, we'll assume a face is present in the center
            # In a real implementation, you would use a face detection library
            width, height = frame.size
            
            # Simulate face detection in the center of the image
            face_size = min(width, height) // 3
            x = (width - face_size) // 2
            y = (height - face_size) // 2
            
            return [(x, y, face_size, face_size)]
            
        except Exception as e:
            logger.error(f"Error detecting faces: {str(e)}")
            return []
    
    def get_best_face_frame(self, frames: List[Image.Image]) -> Optional[Tuple[Image.Image, Tuple[int, int, int, int]]]:
        """Get the frame with the best quality face"""
        best_frame = None
        best_face = None
        best_score = 0
        
        for frame in frames:
            faces = self.detect_faces_in_frame(frame)
            
            for face in faces:
                x, y, w, h = face
                
                # Calculate face quality score
                # Larger faces with good aspect ratio get higher scores
                aspect_ratio = w / h
                size_score = w * h
                
                # Prefer faces that are reasonably sized and have good aspect ratio
                if 0.5 <= aspect_ratio <= 2.0 and size_score > 1000:
                    quality_score = size_score * (1.0 - abs(aspect_ratio - 1.0))
                    
                    if quality_score > best_score:
                        best_score = quality_score
                        best_frame = frame
                        best_face = face
        
        if best_frame is not None:
            logger.info(f"Selected best face frame with score: {best_score}")
            return best_frame, best_face
        
        return None
    
    def extract_face_region(self, frame: Image.Image, face_coords: Tuple[int, int, int, int]) -> Image.Image:
        """Extract and preprocess face region"""
        x, y, w, h = face_coords
        
        # Add some padding around the face
        padding = int(min(w, h) * 0.2)
        x1 = max(0, x - padding)
        y1 = max(0, y - padding)
        x2 = min(frame.width, x + w + padding)
        y2 = min(frame.height, y + h + padding)
        
        face_region = frame.crop((x1, y1, x2, y2))
        
        # Resize to standard size for comparison
        face_resized = face_region.resize((224, 224))
        
        return face_resized
    
    def process_video_for_face_verification(self, video_path: str) -> Optional[Image.Image]:
        """Process video and extract the best face for verification"""
        try:
            # Extract frames
            frames = self.extract_frames_from_video(video_path)
            
            # Find best face frame
            result = self.get_best_face_frame(frames)
            
            if result is None:
                logger.warning("No suitable face found in video")
                return None
            
            frame, face_coords = result
            
            # Extract face region
            face_region = self.extract_face_region(frame, face_coords)
            
            return face_region
            
        except Exception as e:
            logger.error(f"Error processing video: {str(e)}")
            raise
    
    def save_frame_as_image(self, frame: Image.Image, output_path: str) -> bool:
        """Save frame as image file"""
        try:
            frame.save(output_path, "JPEG", quality=95)
            return True
        except Exception as e:
            logger.error(f"Error saving frame: {str(e)}")
            return False

# Global video processor instance
video_processor = VideoProcessor() 