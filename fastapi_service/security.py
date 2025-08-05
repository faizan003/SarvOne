import time
import hashlib
import jwt
from typing import Dict, Optional, List
from fastapi import HTTPException, Request, status
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from starlette.middleware.base import BaseHTTPMiddleware
from datetime import datetime, timedelta
import logging
from config import settings

logger = logging.getLogger(__name__)

class SecurityManager:
    def __init__(self):
        self.rate_limit_store: Dict[str, List[float]] = {}
        self.secret_key = settings.secret_key
        self.algorithm = settings.algorithm
    
    def create_access_token(self, data: dict, expires_delta: Optional[timedelta] = None):
        """Create JWT access token"""
        to_encode = data.copy()
        if expires_delta:
            expire = datetime.utcnow() + expires_delta
        else:
            expire = datetime.utcnow() + timedelta(minutes=settings.access_token_expire_minutes)
        
        to_encode.update({"exp": expire})
        encoded_jwt = jwt.encode(to_encode, self.secret_key, algorithm=self.algorithm)
        return encoded_jwt
    
    def verify_token(self, token: str) -> dict:
        """Verify JWT token"""
        try:
            payload = jwt.decode(token, self.secret_key, algorithms=[self.algorithm])
            return payload
        except jwt.ExpiredSignatureError:
            raise HTTPException(status_code=401, detail="Token expired")
        except jwt.JWTError:
            raise HTTPException(status_code=401, detail="Invalid token")
    
    def check_rate_limit(self, client_ip: str) -> bool:
        """Check rate limiting for client IP"""
        current_time = time.time()
        
        if client_ip not in self.rate_limit_store:
            self.rate_limit_store[client_ip] = []
        
        # Remove old requests (older than 1 minute)
        self.rate_limit_store[client_ip] = [
            req_time for req_time in self.rate_limit_store[client_ip]
            if current_time - req_time < 60
        ]
        
        # Check if limit exceeded
        if len(self.rate_limit_store[client_ip]) >= settings.rate_limit_per_minute:
            return False
        
        # Add current request
        self.rate_limit_store[client_ip].append(current_time)
        return True
    
    def validate_file_type(self, content_type: str, allowed_types: List[str]) -> bool:
        """Validate file type"""
        return content_type in allowed_types
    
    def validate_file_size(self, file_size: int) -> bool:
        """Validate file size"""
        return file_size <= settings.max_file_size
    
    def sanitize_filename(self, filename: str) -> str:
        """Sanitize filename for security"""
        # Remove path traversal attempts
        filename = filename.replace("..", "").replace("/", "").replace("\\", "")
        # Remove special characters
        filename = "".join(c for c in filename if c.isalnum() or c in ".-_")
        return filename

# Global security manager instance
security_manager = SecurityManager()

# Rate limiting middleware
async def rate_limit_middleware(request: Request, call_next):
    """Rate limiting middleware"""
    client_ip = request.client.host
    
    if not security_manager.check_rate_limit(client_ip):
        logger.warning(f"Rate limit exceeded for IP: {client_ip}")
        raise HTTPException(
            status_code=status.HTTP_429_TOO_MANY_REQUESTS,
            detail="Rate limit exceeded. Please try again later."
        )
    
    response = await call_next(request)
    return response

# Simple rate limiting dependency
async def check_rate_limit(request: Request):
    """Check rate limit for the request"""
    client_ip = request.client.host
    
    if not security_manager.check_rate_limit(client_ip):
        logger.warning(f"Rate limit exceeded for IP: {client_ip}")
        raise HTTPException(
            status_code=status.HTTP_429_TOO_MANY_REQUESTS,
            detail="Rate limit exceeded. Please try again later."
        )

# Authentication dependency
class AuthBearer(HTTPBearer):
    def __init__(self, auto_error: bool = True):
        super().__init__(auto_error=auto_error)
    
    async def __call__(self, request: Request) -> Optional[HTTPAuthorizationCredentials]:
        credentials: HTTPAuthorizationCredentials = await super().__call__(request)
        
        if not credentials:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Invalid authentication credentials",
                headers={"WWW-Authenticate": "Bearer"},
            )
        
        try:
            payload = security_manager.verify_token(credentials.credentials)
            request.state.user = payload
            return credentials
        except HTTPException:
            raise HTTPException(
                status_code=status.HTTP_401_UNAUTHORIZED,
                detail="Invalid authentication credentials",
                headers={"WWW-Authenticate": "Bearer"},
            )

# Optional authentication for development
auth_bearer = AuthBearer(auto_error=False) 