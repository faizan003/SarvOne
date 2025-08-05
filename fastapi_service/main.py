from fastapi import FastAPI, File, UploadFile, HTTPException, Request, Depends
from fastapi.security import HTTPAuthorizationCredentials
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware
import tempfile
import os
import logging
import time
from typing import Dict, Any, Optional
from PIL import Image
import io
import hashlib
import shutil

# Import our modules
from config import settings
from security import security_manager, check_rate_limit, auth_bearer
from video_processor import video_processor
from face_recognition import face_engine

# Configure logging
logging.basicConfig(
    level=getattr(logging, settings.log_level),
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(settings.log_file),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger(__name__)

app = FastAPI(
    title=settings.api_title,
    version=settings.api_version,
    description=settings.api_description
)

# Add CORS middleware
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://localhost:8000",  # Laravel frontend
        "http://127.0.0.1:8000",
        "http://localhost:3000",  # React frontend (if used)
        "http://127.0.0.1:3000",
    ],
    allow_credentials=True,
    allow_methods=["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    allow_headers=["*"],
)

# Note: Rate limiting is handled per-endpoint using dependencies

class FaceVerificationService:
    def __init__(self):
        self.threshold = settings.face_recognition_threshold
    
    def extract_frame_from_video(self, video_path: str, frame_number: int = 15):
        """Extract a specific frame from video for face analysis"""
        try:
            # Use the video processor to extract the best face frame
            face_frame = video_processor.process_video_for_face_verification(video_path)
            
            if face_frame is None:
                raise ValueError("No suitable face found in video")
            
            return face_frame
            
        except Exception as e:
            logger.error(f"Error extracting frame: {str(e)}")
            raise HTTPException(status_code=400, detail=f"Error processing video: {str(e)}")
    
    def verify_faces(self, img1_path: str, img2_path: str) -> Dict[str, Any]:
        """Verify if two images contain the same person using DeepFace"""
        try:
            # Use the face recognition engine
            result = face_engine.verify_faces(img1_path, img2_path)
            
            # Add additional analysis
            quality_analysis = face_engine.analyze_face_quality(img1_path)
            liveness_analysis = face_engine.detect_liveness_indicators(img1_path)
            
            # Combine results
            result.update({
                "quality_analysis": quality_analysis,
                "liveness_analysis": liveness_analysis
            })
            
            return result
            
        except Exception as e:
            logger.error(f"Face verification error: {str(e)}")
            raise HTTPException(status_code=400, detail=f"Face verification failed: {str(e)}")

# Initialize the service
face_service = FaceVerificationService()

@app.get("/")
async def root():
    return {"message": "SecureVerify Face Verification API", "status": "active"}

@app.get("/health")
async def health_check():
    return {
        "status": "healthy", 
        "service": "face_verification",
        "version": settings.api_version,
        "models_available": face_engine.available_models,
        "rate_limit": settings.rate_limit_per_minute
    }

@app.post("/auth/token")
async def create_access_token():
    """Create access token for API authentication"""
    try:
        logger.info("🔑 Auth token request received")
        # In production, validate credentials here
        access_token = security_manager.create_access_token(
            data={"sub": "secureverify_api", "permissions": ["face_verification"]}
        )
        logger.info("✅ Auth token created successfully")
        return {"access_token": access_token, "token_type": "bearer"}
    except Exception as e:
        logger.error(f"❌ Token creation error: {str(e)}")
        raise HTTPException(status_code=500, detail="Could not create access token")

@app.post("/verification/selfie")
async def verify_selfie(
    request: Request,
    selfie: UploadFile = File(..., description="Selfie image file"),
    auth: Optional[HTTPAuthorizationCredentials] = Depends(auth_bearer)
):
    """
    Verify selfie image quality and detect face
    """
    
    # Log request
    client_ip = request.client.host
    logger.info(f"📸 Selfie verification request from {client_ip}")
    logger.info(f"📁 File: {selfie.filename}, Size: {selfie.size} bytes, Type: {selfie.content_type}")
    
    # Validate file type
    if not security_manager.validate_file_type(selfie.content_type, settings.allowed_image_types):
        logger.error(f"❌ Invalid file type: {selfie.content_type}")
        raise HTTPException(status_code=400, detail=f"Selfie must be one of: {settings.allowed_image_types}")
    
    # Validate file size
    selfie_content = await selfie.read()
    
    if not security_manager.validate_file_size(len(selfie_content)):
        logger.error(f"❌ File too large: {len(selfie_content)} bytes")
        raise HTTPException(status_code=400, detail=f"Selfie file too large. Max size: {settings.max_file_size} bytes")
    
    # Create temporary file with sanitized name
    selfie_filename = security_manager.sanitize_filename(selfie.filename or "selfie.jpg")
    
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as selfie_temp:
        selfie_temp.write(selfie_content)
        selfie_path = selfie_temp.name
    
    try:
        logger.info("🔍 Starting face analysis...")
        # Analyze face quality
        quality_analysis = face_engine.analyze_face_quality(selfie_path)
        liveness_analysis = face_engine.detect_liveness_indicators(selfie_path)
        
        logger.info(f"📊 Quality analysis: {quality_analysis.get('quality_score', 0):.2f}")
        logger.info(f"📊 Liveness analysis: {liveness_analysis.get('liveness_score', 0):.2f}")
        
        # Calculate trust score
        quality_score = quality_analysis.get('quality_score', 0) * 100
        liveness_score = liveness_analysis.get('liveness_score', 0) * 100
        
        # Weighted trust score calculation
        final_trust_score = (
            quality_score * 0.7 +
            liveness_score * 0.3
        )
        
        logger.info(f"🎯 Final trust score: {final_trust_score:.2f}%")
        
        response = {
            "verified": quality_analysis.get('face_detected', False),
            "trust_score": round(final_trust_score, 2),
            "quality_analysis": quality_analysis,
            "liveness_analysis": liveness_analysis,
            "status": "success",
            "message": "Selfie verification completed successfully"
        }
        
        logger.info("✅ Selfie verification completed successfully")
        return JSONResponse(content=response)
        
    except Exception as e:
        logger.error(f"❌ Selfie verification error: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Selfie verification failed: {str(e)}")
    
    finally:
        # Cleanup temporary file
        try:
            os.unlink(selfie_path)
            logger.info("🧹 Temporary file cleaned up")
        except:
            pass

@app.post("/verify-selfie-video")
async def verify_selfie_video(
    request: Request,
    selfie: UploadFile = File(..., description="Selfie image file"),
    video: UploadFile = File(..., description="Video file for frame extraction"),
    auth: Optional[HTTPAuthorizationCredentials] = Depends(auth_bearer),
    _: None = Depends(check_rate_limit)
):
    """
    Verify if the person in the selfie matches the person in the video
    """
    
    # Log request
    client_ip = request.client.host
    logger.info(f"🎬 Selfie-video verification request from {client_ip}")
    logger.info(f"📁 Selfie: {selfie.filename}, Size: {selfie.size} bytes")
    logger.info(f"📁 Video: {video.filename}, Size: {video.size} bytes")
    
    # Validate file types
    if not security_manager.validate_file_type(selfie.content_type, settings.allowed_image_types):
        logger.error(f"❌ Invalid selfie file type: {selfie.content_type}")
        raise HTTPException(status_code=400, detail=f"Selfie must be one of: {settings.allowed_image_types}")
    
    if not security_manager.validate_file_type(video.content_type, settings.allowed_video_types):
        logger.error(f"❌ Invalid video file type: {video.content_type}")
        raise HTTPException(status_code=400, detail=f"Video must be one of: {settings.allowed_video_types}")
    
    # Validate file sizes
    selfie_content = await selfie.read()
    video_content = await video.read()
    
    if not security_manager.validate_file_size(len(selfie_content)):
        logger.error(f"❌ Selfie file too large: {len(selfie_content)} bytes")
        raise HTTPException(status_code=400, detail=f"Selfie file too large. Max size: {settings.max_file_size} bytes")
    
    if not security_manager.validate_file_size(len(video_content)):
        logger.error(f"❌ Video file too large: {len(video_content)} bytes")
        raise HTTPException(status_code=400, detail=f"Video file too large. Max size: {settings.max_file_size} bytes")
    
    # Create temporary files with sanitized names
    selfie_filename = security_manager.sanitize_filename(selfie.filename or "selfie.jpg")
    video_filename = security_manager.sanitize_filename(video.filename or "video.mp4")
    
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as selfie_temp:
        selfie_temp.write(selfie_content)
        selfie_path = selfie_temp.name
    
    with tempfile.NamedTemporaryFile(delete=False, suffix='.mp4') as video_temp:
        video_temp.write(video_content)
        video_path = video_temp.name
    
    try:
        logger.info("🔍 Starting face verification...")
        # Extract frame from video using video processor
        video_frame = face_service.extract_frame_from_video(video_path)
        
        if video_frame is None:
            logger.error("❌ No suitable face found in video")
            raise HTTPException(status_code=400, detail="No suitable face found in video")
        
        # Save frame as temporary image
        with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as frame_temp:
            video_processor.save_frame_as_image(video_frame, frame_temp.name)
            frame_path = frame_temp.name
        
        logger.info("📸 Video frame extracted successfully")
        
        # Perform face verification
        verification_result = face_service.verify_faces(selfie_path, frame_path)
        
        logger.info(f"📊 Verification result: {verification_result.get('verified', False)}")
        logger.info(f"📊 Trust score: {verification_result.get('trust_score', 0):.2f}")
        
        # Calculate comprehensive trust score
        base_score = 70
        face_match_score = verification_result.get('trust_score', 0)
        quality_score = verification_result.get('quality_analysis', {}).get('quality_score', 0) * 100
        liveness_score = verification_result.get('liveness_analysis', {}).get('liveness_score', 0) * 100
        
        # Weighted trust score calculation
        final_trust_score = (
            base_score * 0.3 +
            face_match_score * 0.4 +
            quality_score * 0.2 +
            liveness_score * 0.1
        )
        
        logger.info(f"🎯 Final trust score: {final_trust_score:.2f}%")
        
        response = {
            "verified": verification_result.get('verified', False),
            "trust_score": round(final_trust_score, 2),
            "face_match_score": verification_result.get('confidence', 0),
            "quality_score": quality_score,
            "liveness_score": liveness_score,
            "model_used": verification_result.get('model_used', 'Unknown'),
            "status": "success",
            "message": "Face verification completed successfully"
        }
        
        logger.info("✅ Selfie-video verification completed successfully")
        return JSONResponse(content=response)
        
    except Exception as e:
        logger.error(f"❌ Selfie-video verification error: {str(e)}")
        raise HTTPException(status_code=400, detail=f"Face verification failed: {str(e)}")
    
    finally:
        # Cleanup temporary files
        for path in [selfie_path, video_path]:
            try:
                if os.path.exists(path):
                    os.unlink(path)
                    logger.info(f"🧹 Temporary file cleaned up: {path}")
            except:
                pass

@app.post("/analyze-face-quality")
async def analyze_face_quality(
    request: Request,
    image: UploadFile = File(..., description="Image file for quality analysis"),
    auth: Optional[HTTPAuthorizationCredentials] = Depends(auth_bearer),
    _: None = Depends(check_rate_limit)
):
    """
    Analyze face quality and detect liveness indicators
    """
    
    # Log request
    client_ip = request.client.host
    logger.info(f"🔍 Face quality analysis request from {client_ip}")
    logger.info(f"📁 File: {image.filename}, Size: {image.size} bytes, Type: {image.content_type}")
    
    # Validate file type
    if not security_manager.validate_file_type(image.content_type, settings.allowed_image_types):
        logger.error(f"❌ Invalid file type: {image.content_type}")
        raise HTTPException(status_code=400, detail=f"Image must be one of: {settings.allowed_image_types}")
    
    # Validate file size
    image_content = await image.read()
    
    if not security_manager.validate_file_size(len(image_content)):
        logger.error(f"❌ File too large: {len(image_content)} bytes")
        raise HTTPException(status_code=400, detail=f"Image file too large. Max size: {settings.max_file_size} bytes")
    
    # Create temporary file
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as img_temp:
        img_temp.write(image_content)
        image_path = img_temp.name
    
    try:
        logger.info("🔍 Starting face quality analysis...")
        
        # Analyze face quality
        quality_analysis = face_engine.analyze_face_quality(image_path)
        liveness_analysis = face_engine.detect_liveness_indicators(image_path)
        
        logger.info(f"📊 Quality score: {quality_analysis.get('quality_score', 0):.2f}")
        logger.info(f"📊 Liveness score: {liveness_analysis.get('liveness_score', 0):.2f}")
        logger.info(f"👤 Face detected: {quality_analysis.get('face_detected', False)}")
        
        response = {
            "quality_analysis": quality_analysis,
            "liveness_analysis": liveness_analysis,
            "status": "success",
            "message": "Face quality analysis completed successfully"
        }
        
        logger.info("✅ Face quality analysis completed successfully")
        return JSONResponse(content=response)
        
    except Exception as e:
        logger.error(f"❌ Face quality analysis error: {str(e)}")
        raise HTTPException(status_code=400, detail=f"Face quality analysis failed: {str(e)}")
    
    finally:
        # Clean up temporary file
        try:
            os.unlink(image_path)
            logger.info("🧹 Temporary file cleaned up")
        except:
            pass

@app.post("/verify-images")
async def verify_images(
    request: Request,
    image1: UploadFile = File(..., description="First image"),
    image2: UploadFile = File(..., description="Second image"),
    auth: Optional[HTTPAuthorizationCredentials] = Depends(auth_bearer),
    _: None = Depends(check_rate_limit)
):
    """
    Verify if two images contain the same person
    """
    
    # Validate file types
    if not image1.content_type.startswith('image/') or not image2.content_type.startswith('image/'):
        raise HTTPException(status_code=400, detail="Both files must be image files")
    
    # Create temporary files
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as img1_temp:
        img1_content = await image1.read()
        img1_temp.write(img1_content)
        img1_path = img1_temp.name
    
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as img2_temp:
        img2_content = await image2.read()
        img2_temp.write(img2_content)
        img2_path = img2_temp.name
    
    try:
        # Perform face verification
        verification_result = face_service.verify_faces(img1_path, img2_path)
        
        # Calculate trust score (0-100 scale)
        trust_score = round(verification_result['confidence'] * 100, 2)
        
        response = {
            "verified": verification_result['verified'],
            "score": verification_result['confidence'],
            "trust_score": trust_score,
            "distance": verification_result['distance'],
            "threshold": verification_result['threshold'],
            "model_used": verification_result['model'],
            "status": "success",
            "message": "Face verification completed successfully"
        }
        
        return JSONResponse(content=response)
        
    except Exception as e:
        logger.error(f"Image verification error: {str(e)}")
        raise HTTPException(status_code=500, detail=f"Verification failed: {str(e)}")
    
    finally:
        # Cleanup temporary files
        try:
            os.unlink(img1_path)
            os.unlink(img2_path)
        except:
            pass

@app.post("/demo/verify-faces")
async def demo_verify_faces(
    request: Request,
    image1: UploadFile = File(..., description="First image"),
    image2: UploadFile = File(..., description="Second image")
):
    """
    Demo endpoint for face verification without authentication
    """
    
    # Log request
    client_ip = request.client.host
    logger.info(f"Demo face verification request from {client_ip}")
    
    # Validate file types
    if not security_manager.validate_file_type(image1.content_type, settings.allowed_image_types):
        raise HTTPException(status_code=400, detail=f"Image1 must be one of: {settings.allowed_image_types}")
    
    if not security_manager.validate_file_type(image2.content_type, settings.allowed_image_types):
        raise HTTPException(status_code=400, detail=f"Image2 must be one of: {settings.allowed_image_types}")
    
    # Validate file sizes
    image1_content = await image1.read()
    image2_content = await image2.read()
    
    if not security_manager.validate_file_size(len(image1_content)):
        raise HTTPException(status_code=400, detail=f"Image1 file too large. Max size: {settings.max_file_size} bytes")
    
    if not security_manager.validate_file_size(len(image2_content)):
        raise HTTPException(status_code=400, detail=f"Image2 file too large. Max size: {settings.max_file_size} bytes")
    
    # Create temporary files
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as image1_temp:
        image1_temp.write(image1_content)
        image1_path = image1_temp.name
    
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as image2_temp:
        image2_temp.write(image2_content)
        image2_path = image2_temp.name
    
    try:
        # Perform face verification
        verification_result = face_service.verify_faces(image1_path, image2_path)
        
        response = {
            "verified": verification_result['verified'],
            "confidence": verification_result['confidence'],
            "trust_score": verification_result['trust_score'],
            "distance": verification_result['distance'],
            "threshold": verification_result['threshold'],
            "model_used": verification_result['model_used'],
            "similarity_scores": verification_result.get('similarity_scores', {}),
            "quality_analysis": verification_result.get('quality_analysis', {}),
            "liveness_analysis": verification_result.get('liveness_analysis', {}),
            "status": "success",
            "message": "Face verification completed successfully"
        }
        
        return JSONResponse(content=response)
        
    except Exception as e:
        logger.error(f"Demo face verification error: {str(e)}")
        raise HTTPException(status_code=400, detail=f"Face verification failed: {str(e)}")
    
    finally:
        # Clean up temporary files
        for path in [image1_path, image2_path]:
            if os.path.exists(path):
                os.unlink(path)

@app.post("/demo/analyze-face-quality")
async def demo_analyze_face_quality(
    request: Request,
    image: UploadFile = File(..., description="Image file for quality analysis")
):
    """
    Demo endpoint for face quality analysis (no auth required)
    """
    
    # Log request
    client_ip = request.client.host
    logger.info(f"🎭 DEMO: Face quality analysis request from {client_ip}")
    logger.info(f"📁 File: {image.filename}, Size: {image.size} bytes, Type: {image.content_type}")
    
    # Validate file type
    if not image.content_type.startswith('image/'):
        logger.error(f"❌ Invalid file type: {image.content_type}")
        raise HTTPException(status_code=400, detail="File must be an image")
    
    # Create temporary file
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as img_temp:
        image_content = await image.read()
        img_temp.write(image_content)
        image_path = img_temp.name
    
    try:
        logger.info("🔍 DEMO: Starting face quality analysis...")
        
        # Analyze face quality
        quality_analysis = face_engine.analyze_face_quality(image_path)
        liveness_analysis = face_engine.detect_liveness_indicators(image_path)
        
        logger.info(f"📊 DEMO: Quality score: {quality_analysis.get('quality_score', 0):.2f}")
        logger.info(f"📊 DEMO: Liveness score: {liveness_analysis.get('liveness_score', 0):.2f}")
        logger.info(f"👤 DEMO: Face detected: {quality_analysis.get('face_detected', False)}")
        
        response = {
            "quality_analysis": quality_analysis,
            "liveness_analysis": liveness_analysis,
            "status": "success",
            "message": "Demo face quality analysis completed successfully"
        }
        
        logger.info("✅ DEMO: Face quality analysis completed successfully")
        return JSONResponse(content=response)
        
    except Exception as e:
        logger.error(f"❌ DEMO: Face quality analysis error: {str(e)}")
        raise HTTPException(status_code=400, detail=f"Demo face quality analysis failed: {str(e)}")
    
    finally:
        # Clean up temporary file
        try:
            os.unlink(image_path)
            logger.info("🧹 DEMO: Temporary file cleaned up")
        except:
            pass

@app.post("/demo/verification/selfie")
async def demo_verify_selfie(
    request: Request,
    selfie: UploadFile = File(..., description="Selfie image file")
):
    """
    Demo endpoint for selfie verification (no auth required)
    """
    
    # Log request
    client_ip = request.client.host
    logger.info(f"🎭 DEMO: Selfie verification request from {client_ip}")
    logger.info(f"📁 File: {selfie.filename}, Size: {selfie.size} bytes, Type: {selfie.content_type}")
    
    # Validate file type
    if not selfie.content_type.startswith('image/'):
        logger.error(f"❌ Invalid file type: {selfie.content_type}")
        raise HTTPException(status_code=400, detail="File must be an image")
    
    # Create temporary file
    with tempfile.NamedTemporaryFile(delete=False, suffix='.jpg') as selfie_temp:
        selfie_content = await selfie.read()
        selfie_temp.write(selfie_content)
        selfie_path = selfie_temp.name
    
    try:
        logger.info("🔍 DEMO: Starting selfie analysis...")
        
        # Analyze face quality
        quality_analysis = face_engine.analyze_face_quality(selfie_path)
        liveness_analysis = face_engine.detect_liveness_indicators(selfie_path)
        
        logger.info(f"📊 DEMO: Quality score: {quality_analysis.get('quality_score', 0):.2f}")
        logger.info(f"📊 DEMO: Liveness score: {liveness_analysis.get('liveness_score', 0):.2f}")
        
        # Calculate trust score
        quality_score = quality_analysis.get('quality_score', 0) * 100
        liveness_score = liveness_analysis.get('liveness_score', 0) * 100
        
        # Weighted trust score calculation
        final_trust_score = (
            quality_score * 0.7 +
            liveness_score * 0.3
        )
        
        logger.info(f"🎯 DEMO: Final trust score: {final_trust_score:.2f}%")
        
        response = {
            "verified": quality_analysis.get('face_detected', False),
            "trust_score": round(final_trust_score, 2),
            "quality_analysis": quality_analysis,
            "liveness_analysis": liveness_analysis,
            "status": "success",
            "message": "Demo selfie verification completed successfully"
        }
        
        logger.info("✅ DEMO: Selfie verification completed successfully")
        return JSONResponse(content=response)
        
    except Exception as e:
        logger.error(f"❌ DEMO: Selfie verification error: {str(e)}")
        raise HTTPException(status_code=400, detail=f"Demo selfie verification failed: {str(e)}")
    
    finally:
        # Cleanup temporary file
        try:
            os.unlink(selfie_path)
            logger.info("🧹 DEMO: Temporary file cleaned up")
        except:
            pass

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001) 