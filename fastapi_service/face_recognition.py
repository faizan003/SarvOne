import numpy as np
import tempfile
import os
import logging
from typing import Dict, Any, Optional, Tuple, List
from PIL import Image, ImageFilter, ImageEnhance
import io
import hashlib
from config import settings
import cv2

# Try to import DeepFace, but don't fail if it's not available
try:
    from deepface import DeepFace
    DEEPFACE_AVAILABLE = True
    logger = logging.getLogger(__name__)
    logger.info("DeepFace is available for advanced face recognition")
except ImportError:
    DEEPFACE_AVAILABLE = False
    logger = logging.getLogger(__name__)
    logger.warning("DeepFace not available, using OpenCV-based face recognition only")

logger = logging.getLogger(__name__)

class FaceRecognitionEngine:
    def __init__(self):
        self.threshold = settings.face_recognition_threshold
        self.similarity_threshold = 0.7  # Threshold for face similarity
        
        # Initialize OpenCV face detection with multiple cascades for better accuracy
        self.face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')
        self.face_cascade_alt = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_alt.xml')
        self.face_cascade_alt2 = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_alt2.xml')
        self.eye_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_eye.xml')
        
        # Verify cascades loaded successfully
        if self.face_cascade.empty():
            logger.error("Failed to load face cascade classifier")
        if self.face_cascade_alt.empty():
            logger.error("Failed to load face cascade alt classifier")
        if self.face_cascade_alt2.empty():
            logger.error("Failed to load face cascade alt2 classifier")
        
        # Available models for fallback
        self.available_models = ["OpenCV_Face", "Template_Matching", "Feature_Comparison"]
        self.available_metrics = ["euclidean", "cosine", "hash_similarity"]
        
        logger.info(f"Initialized Face Recognition Engine with OpenCV-based models")
    
    def verify_faces(self, img1_path: str, img2_path: str) -> Dict[str, Any]:
        """Verify if two images contain the same person using OpenCV-based methods"""
        try:
            # Validate input files
            if not os.path.exists(img1_path):
                raise ValueError(f"First image file not found: {img1_path}")
            if not os.path.exists(img2_path):
                raise ValueError(f"Second image file not found: {img2_path}")
            
            logger.info(f"Starting face verification: {img1_path} vs {img2_path}")
            
            # Load images using OpenCV
            img1 = cv2.imread(img1_path)
            img2 = cv2.imread(img2_path)
            
            if img1 is None or img2 is None:
                raise ValueError("Failed to load one or both images")
            
            # Convert to grayscale for face detection
            gray1 = cv2.cvtColor(img1, cv2.COLOR_BGR2GRAY)
            gray2 = cv2.cvtColor(img2, cv2.COLOR_BGR2GRAY)
            
            # Detect faces using OpenCV with multiple cascades
            faces1 = []
            faces2 = []
            
            # Try primary cascade for first image
            if not self.face_cascade.empty():
                faces1 = self.face_cascade.detectMultiScale(gray1, 1.1, 5, minSize=(50, 50), maxSize=(300, 300))
            
            # If no faces found, try alternative cascade
            if len(faces1) == 0 and not self.face_cascade_alt.empty():
                faces1 = self.face_cascade_alt.detectMultiScale(gray1, 1.1, 4, minSize=(50, 50), maxSize=(300, 300))
            
            # If still no faces found, try second alternative cascade
            if len(faces1) == 0 and not self.face_cascade_alt2.empty():
                faces1 = self.face_cascade_alt2.detectMultiScale(gray1, 1.1, 4, minSize=(50, 50), maxSize=(300, 300))
            
            # Try primary cascade for second image
            if not self.face_cascade.empty():
                faces2 = self.face_cascade.detectMultiScale(gray2, 1.1, 5, minSize=(50, 50), maxSize=(300, 300))
            
            # If no faces found, try alternative cascade
            if len(faces2) == 0 and not self.face_cascade_alt.empty():
                faces2 = self.face_cascade_alt.detectMultiScale(gray2, 1.1, 4, minSize=(50, 50), maxSize=(300, 300))
            
            # If still no faces found, try second alternative cascade
            if len(faces2) == 0 and not self.face_cascade_alt2.empty():
                faces2 = self.face_cascade_alt2.detectMultiScale(gray2, 1.1, 4, minSize=(50, 50), maxSize=(300, 300))
            
            # Check if faces are detected
            if len(faces1) == 0 or len(faces2) == 0:
                return {
                    "verified": False,
                    "confidence": 0.0,
                    "distance": 1.0,
                    "threshold": self.similarity_threshold,
                    "trust_score": 0.0,
                    "model_used": "OpenCV_Face",
                    "metric_used": "face_detection",
                    "status": "error",
                    "message": "No faces detected in one or both images"
                }
            
            # Extract face regions for comparison
            face1_region = self._extract_face_region(img1, faces1[0])
            face2_region = self._extract_face_region(img2, faces2[0])
            
            if face1_region is None or face2_region is None:
                return {
                    "verified": False,
                    "confidence": 0.0,
                    "distance": 1.0,
                    "threshold": self.similarity_threshold,
                    "trust_score": 0.0,
                    "model_used": "OpenCV_Face",
                    "metric_used": "face_extraction",
                    "status": "error",
                    "message": "Failed to extract face regions"
                }
            
            # Compare faces using multiple methods
            similarity_scores = []
            
            # Method 1: Feature-based comparison
            feature_score = self._feature_comparison_similarity(face1_region, face2_region)
            similarity_scores.append(feature_score)
            
            # Method 2: Histogram comparison
            histogram_score = self._histogram_similarity(face1_region, face2_region)
            similarity_scores.append(histogram_score)
            
            # Method 3: Template matching
            template_score = self._template_matching_similarity(face1_region, face2_region)
            similarity_scores.append(template_score)
            
            # Calculate weighted average similarity
            weights = [0.4, 0.3, 0.3]  # Feature, Histogram, Template weights
            final_similarity = sum(score * weight for score, weight in zip(similarity_scores, weights))
            
            # Determine verification result
            verified = final_similarity >= self.similarity_threshold
            confidence = min(1.0, final_similarity)
            distance = 1.0 - final_similarity
            
            # Calculate trust score (0-100 scale)
            trust_score = round(confidence * 100, 2)
            
            # Additional analysis for quality and liveness
            quality_analysis = self.analyze_face_quality(img1_path)
            liveness_analysis = self.detect_liveness_indicators(img1_path)
            
            return {
                "verified": verified,
                "confidence": round(confidence, 4),
                "distance": round(distance, 4),
                "threshold": self.similarity_threshold,
                "trust_score": trust_score,
                "model_used": "OpenCV_MultiMethod",
                "metric_used": "weighted_similarity",
                "similarity_scores": {
                    "feature_comparison": round(similarity_scores[0], 4),
                    "histogram": round(similarity_scores[1], 4),
                    "template_matching": round(similarity_scores[2], 4)
                },
                "quality_analysis": quality_analysis,
                "liveness_analysis": liveness_analysis,
                "status": "success",
                "message": "Face verification completed successfully"
            }
            
        except Exception as e:
            logger.error(f"Face verification error: {str(e)}")
            return {
                "verified": False,
                "confidence": 0.0,
                "distance": 1.0,
                "threshold": self.similarity_threshold,
                "trust_score": 0.0,
                "model_used": "OpenCV_MultiMethod",
                "metric_used": "weighted_similarity",
                "status": "error",
                "message": f"Face verification failed: {str(e)}"
            }
    
    def _extract_face_region(self, image, face_coords):
        """Extract face region from image"""
        try:
            x, y, w, h = face_coords
            # Add some padding around the face
            padding = int(min(w, h) * 0.1)
            x1 = max(0, x - padding)
            y1 = max(0, y - padding)
            x2 = min(image.shape[1], x + w + padding)
            y2 = min(image.shape[0], y + h + padding)
            
            face_region = image[y1:y2, x1:x2]
            if face_region.size == 0:
                return None
            
            # Resize to standard size for comparison
            face_region = cv2.resize(face_region, (100, 100))
            return face_region
            
        except Exception as e:
            logger.error(f"Face region extraction error: {str(e)}")
            return None
    
    def _feature_comparison_similarity(self, face1, face2):
        """Calculate similarity using feature comparison"""
        try:
            # Convert to grayscale if needed
            if len(face1.shape) == 3:
                face1_gray = cv2.cvtColor(face1, cv2.COLOR_BGR2GRAY)
            else:
                face1_gray = face1
                
            if len(face2.shape) == 3:
                face2_gray = cv2.cvtColor(face2, cv2.COLOR_BGR2GRAY)
            else:
                face2_gray = face2
            
            # Calculate mean and std
            mean1, std1 = cv2.meanStdDev(face1_gray)
            mean2, std2 = cv2.meanStdDev(face2_gray)
            
            # Compare statistics
            mean_diff = abs(mean1[0] - mean2[0]) / 255.0
            std_diff = abs(std1[0] - std2[0]) / 255.0
            
            # Calculate similarity based on differences
            similarity = 1.0 - (mean_diff + std_diff) / 2.0
            
            return float(max(0.0, similarity))
            
        except Exception as e:
            logger.error(f"Feature comparison error: {str(e)}")
            return 0.0
    
    def _histogram_similarity(self, face1, face2):
        """Calculate similarity using histogram comparison"""
        try:
            # Convert to grayscale if needed
            if len(face1.shape) == 3:
                face1_gray = cv2.cvtColor(face1, cv2.COLOR_BGR2GRAY)
            else:
                face1_gray = face1
                
            if len(face2.shape) == 3:
                face2_gray = cv2.cvtColor(face2, cv2.COLOR_BGR2GRAY)
            else:
                face2_gray = face2
            
            # Calculate histograms
            hist1 = cv2.calcHist([face1_gray], [0], None, [256], [0, 256])
            hist2 = cv2.calcHist([face2_gray], [0], None, [256], [0, 256])
            
            # Normalize histograms
            hist1 = hist1 / np.sum(hist1)
            hist2 = hist2 / np.sum(hist2)
            
            # Calculate histogram intersection
            intersection = np.minimum(hist1, hist2)
            similarity = np.sum(intersection)
            
            return float(similarity)
            
        except Exception as e:
            logger.error(f"Histogram similarity error: {str(e)}")
            return 0.0
    
    def _template_matching_similarity(self, face1, face2):
        """Calculate similarity using template matching"""
        try:
            # Convert to grayscale if needed
            if len(face1.shape) == 3:
                face1_gray = cv2.cvtColor(face1, cv2.COLOR_BGR2GRAY)
            else:
                face1_gray = face1
                
            if len(face2.shape) == 3:
                face2_gray = cv2.cvtColor(face2, cv2.COLOR_BGR2GRAY)
            else:
                face2_gray = face2
            
            # Use template matching
            result = cv2.matchTemplate(face1_gray, face2_gray, cv2.TM_CCOEFF_NORMED)
            similarity = np.max(result)
            
            return float(similarity)
            
        except Exception as e:
            logger.error(f"Template matching error: {str(e)}")
            return 0.0
    
    def analyze_face_quality(self, image_path: str) -> Dict[str, Any]:
        """Analyze face quality for liveness detection"""
        try:
            # Load image using OpenCV
            img = cv2.imread(image_path)
            if img is None:
                return {
                    "face_detected": False,
                    "quality_score": 0.0,
                    "message": "Failed to load image"
                }
            
            # Convert to grayscale for face detection
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            
            # Calculate image statistics first
            brightness = np.mean(gray)
            contrast = np.std(gray)
            height, width = img.shape[:2]
            image_area = width * height
            
            # STRICT CHECKS FOR BLANK/UNIFORM IMAGES
            # Check for very low contrast (blank/uniform image)
            if contrast < 15:  # Increased threshold
                return {
                    "face_detected": False,
                    "quality_score": 0.0,
                    "image_size": image_area,
                    "brightness": round(brightness, 1),
                    "contrast": round(contrast, 1),
                    "message": "No face detected - image appears to be blank or uniform (low contrast)"
                }
            
            # Check for very high or very low brightness (likely not a face)
            if brightness < 30 or brightness > 225:  # Stricter thresholds
                return {
                    "face_detected": False,
                    "quality_score": 0.0,
                    "image_size": image_area,
                    "brightness": round(brightness, 1),
                    "contrast": round(contrast, 1),
                    "message": "No face detected - image brightness suggests no meaningful content"
                }
            
            # Check for very small images (likely not a proper face photo)
            if image_area < 15000:  # Increased minimum size
                return {
                    "face_detected": False,
                    "quality_score": 0.0,
                    "image_size": image_area,
                    "brightness": round(brightness, 1),
                    "contrast": round(contrast, 1),
                    "message": "No face detected - image too small for proper analysis"
                }
            
            # Check for uniform color distribution (indicates blank image)
            hist = cv2.calcHist([gray], [0], None, [256], [0, 256])
            hist_normalized = hist / np.sum(hist)
            max_hist_value = np.max(hist_normalized)
            
            # If any single color value has more than 80% of pixels, it's likely blank
            if max_hist_value > 0.8:
                return {
                    "face_detected": False,
                    "quality_score": 0.0,
                    "image_size": image_area,
                    "brightness": round(brightness, 1),
                    "contrast": round(contrast, 1),
                    "message": "No face detected - image appears to be a solid color"
                }
            
            # Use OpenCV to detect faces with multiple cascades for better accuracy
            faces = []
            
            # Try primary cascade
            if not self.face_cascade.empty():
                faces = self.face_cascade.detectMultiScale(
                    gray, 
                    scaleFactor=1.1, 
                    minNeighbors=5,
                    minSize=(50, 50),
                    maxSize=(300, 300)
                )
            
            # If no faces found, try alternative cascade
            if len(faces) == 0 and not self.face_cascade_alt.empty():
                faces = self.face_cascade_alt.detectMultiScale(
                    gray, 
                    scaleFactor=1.1, 
                    minNeighbors=4,
                    minSize=(50, 50),
                    maxSize=(300, 300)
                )
            
            # If still no faces found, try second alternative cascade
            if len(faces) == 0 and not self.face_cascade_alt2.empty():
                faces = self.face_cascade_alt2.detectMultiScale(
                    gray, 
                    scaleFactor=1.1, 
                    minNeighbors=4,
                    minSize=(50, 50),
                    maxSize=(300, 300)
                )
            
            if len(faces) == 0:
                return {
                    "face_detected": False,
                    "quality_score": 0.0,
                    "image_size": image_area,
                    "brightness": round(brightness, 1),
                    "contrast": round(contrast, 1),
                    "message": "No face detected in the image. Please ensure your face is clearly visible and well-lit."
                }
            
            # Additional validation: Check if detected face region has meaningful content
            for (x, y, w, h) in faces:
                face_roi = gray[y:y+h, x:x+w]
                face_brightness = np.mean(face_roi)
                face_contrast = np.std(face_roi)
                
                # If face region is too uniform, it's likely a false positive
                if face_contrast < 20:
                    return {
                        "face_detected": False,
                        "quality_score": 0.0,
                        "image_size": image_area,
                        "brightness": round(brightness, 1),
                        "contrast": round(contrast, 1),
                        "message": "No face detected - detected region appears to be uniform"
                    }
                
                # If face region brightness is extreme, it's likely not a real face
                if face_brightness < 40 or face_brightness > 200:
                    return {
                        "face_detected": False,
                        "quality_score": 0.0,
                        "image_size": image_area,
                        "brightness": round(brightness, 1),
                        "contrast": round(contrast, 1),
                        "message": "No face detected - detected region brightness is not typical of a face"
                    }
            
            # Quality score based on multiple factors
            size_score = min(1.0, image_area / 100000)  # Prefer larger images
            brightness_score = 1.0 - abs(brightness - 128) / 128  # Prefer medium brightness
            contrast_score = min(1.0, contrast / 50)  # Prefer good contrast
            
            quality_score = (size_score * 0.4 + brightness_score * 0.3 + contrast_score * 0.3)
            
            return {
                "face_detected": True,
                "quality_score": round(quality_score, 3),
                "image_size": image_area,
                "brightness": round(brightness, 1),
                "contrast": round(contrast, 1),
                "message": "Face quality analysis completed"
            }
            
        except Exception as e:
            logger.error(f"Face quality analysis error: {str(e)}")
            return {
                "face_detected": False,
                "quality_score": 0.0,
                "message": f"Face quality analysis failed: {str(e)}"
            }
    
    def detect_liveness_indicators(self, image_path: str) -> Dict[str, Any]:
        """Detect liveness indicators in the image"""
        try:
            # Load image using OpenCV
            img = cv2.imread(image_path)
            if img is None:
                return {
                    "liveness_score": 0.0,
                    "message": "Failed to load image"
                }
            
            # Convert to grayscale
            gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
            
            # Calculate image quality metrics for liveness
            img_array = np.array(gray)
            
            # Calculate metrics that might indicate liveness
            brightness = np.mean(img_array)
            contrast = np.std(img_array)
            sharpness = np.std(np.diff(img_array, axis=0)) + np.std(np.diff(img_array, axis=1))
            
            # Simple liveness scoring based on image quality
            brightness_score = 1.0 - abs(brightness - 128) / 128  # Prefer medium brightness
            contrast_score = min(1.0, contrast / 50)  # Prefer good contrast
            sharpness_score = min(1.0, sharpness / 100)  # Prefer sharp images
            
            liveness_score = (brightness_score * 0.4 + contrast_score * 0.3 + sharpness_score * 0.3)
            
            return {
                "liveness_score": round(liveness_score, 3),
                "brightness": round(brightness, 1),
                "contrast": round(contrast, 1),
                "sharpness": round(sharpness, 1),
                "message": "Liveness detection completed"
            }
            
        except Exception as e:
            logger.error(f"Liveness detection error: {str(e)}")
            return {
                "liveness_score": 0.0,
                "brightness": 0.0,
                "contrast": 0.0,
                "sharpness": 0.0,
                "message": f"Liveness detection failed: {str(e)}"
            }

# Global face recognition engine instance
face_engine = FaceRecognitionEngine() 