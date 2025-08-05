import os
from typing import Optional
from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    # API Configuration
    api_title: str = "SecureVerify Face Verification API"
    api_version: str = "1.0.0"
    api_description: str = "AI-powered face verification service"
    
    # Security
    secret_key: str = os.getenv("SECRET_KEY", "your-secret-key-change-in-production")
    algorithm: str = "HS256"
    access_token_expire_minutes: int = 30
    
    # Rate Limiting
    rate_limit_per_minute: int = 60
    rate_limit_per_hour: int = 1000
    
    # File Upload Limits
    max_file_size: int = 10 * 1024 * 1024  # 10MB
    allowed_image_types: list = ["image/jpeg", "image/png", "image/jpg"]
    allowed_video_types: list = ["video/mp4", "video/webm", "video/avi"]
    
    # AI Model Configuration
    face_recognition_model: str = "Facenet"
    face_recognition_metric: str = "cosine"
    face_recognition_threshold: float = 0.6
    
    # Video Processing
    video_frame_extraction_interval: int = 15  # Extract every 15th frame
    max_video_duration: int = 30  # Maximum 30 seconds
    
    # Storage
    temp_file_cleanup_interval: int = 3600  # 1 hour
    max_temp_files: int = 100
    
    # Logging
    log_level: str = "INFO"
    log_file: str = "logs/face_verification.log"
    
    class Config:
        env_file = ".env"

settings = Settings() 