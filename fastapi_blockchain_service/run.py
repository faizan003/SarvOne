#!/usr/bin/env python3
"""
SarvOne Blockchain Service Runner
Run this script to start the FastAPI blockchain microservice
"""

import os
import sys
from dotenv import load_dotenv

# Load environment variables from .env file
load_dotenv()

def check_environment():
    """Check if all required environment variables are set"""
    required_vars = [
        'CONTRACT_ADDRESS',
        'ADMIN_PRIVATE_KEY',
        'BLOCKCHAIN_RPC_URL'
    ]
    
    missing_vars = []
    for var in required_vars:
        if not os.getenv(var):
            missing_vars.append(var)
    
    if missing_vars:
        print("❌ Missing required environment variables:")
        for var in missing_vars:
            print(f"   - {var}")
        print("\n📝 Please create a .env file based on env_example.txt")
        return False
    
    print("✅ Environment variables loaded successfully")
    return True

def main():
    print("🚀 Starting SarvOne Blockchain Service...")
    
    if not check_environment():
        sys.exit(1)
    
    # Print configuration info
    print(f"📋 Configuration:")
    print(f"   - Contract: {os.getenv('CONTRACT_ADDRESS')}")
    print(f"   - RPC URL: {os.getenv('BLOCKCHAIN_RPC_URL')}")
    print(f"   - Chain ID: {os.getenv('CHAIN_ID', '80002')}")
    print(f"   - Gas Limit: {os.getenv('GAS_LIMIT', '200000')}")
    print(f"   - Gas Price: {os.getenv('GAS_PRICE_GWEI', '30')} Gwei")
    
    # Import and run the FastAPI app
    try:
        import uvicorn
        from main import app
        
        host = os.getenv('API_HOST', '0.0.0.0')
        port = int(os.getenv('API_PORT', '8003'))
        
        print(f"🌐 Starting server on {host}:{port}")
        print(f"📖 API Documentation: http://{host}:{port}/docs")
        
        uvicorn.run(
            app, 
            host=host, 
            port=port,
            reload=False,  # Set to True for development
            log_level="info"
        )
        
    except ImportError as e:
        print(f"❌ Import error: {e}")
        print("💡 Make sure you've installed all requirements: pip install -r requirements.txt")
        sys.exit(1)
    except Exception as e:
        print(f"❌ Failed to start service: {e}")
        sys.exit(1)

if __name__ == "__main__":
    main() 