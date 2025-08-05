#!/usr/bin/env python3
"""
Startup script for SecureVerify Face Verification API
"""

import os
import sys
import uvicorn
from pathlib import Path

def main():
    """Main entry point"""
    # Add the current directory to Python path
    current_dir = Path(__file__).parent
    sys.path.insert(0, str(current_dir))
    
    # Configuration
    host = os.getenv('HOST', '0.0.0.0')
    port = int(os.getenv('PORT', 8000))
    reload = os.getenv('RELOAD', 'True').lower() == 'true'
    log_level = os.getenv('LOG_LEVEL', 'info')
    
    print(f"Starting SecureVerify Face Verification API on {host}:{port}")
    print(f"Reload: {reload}, Log Level: {log_level}")
    
    # Run the server
    uvicorn.run(
        "main:app",
        host=host,
        port=port,
        reload=reload,
        log_level=log_level,
        access_log=True
    )

if __name__ == "__main__":
    main() 