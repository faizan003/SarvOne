#!/usr/bin/env python3
"""
Test script for SarvOne Blockchain Service
Run this to test the FastAPI service endpoints
"""

import requests
import json
import time
from dotenv import load_dotenv
import os

# Load environment variables
load_dotenv()

# Service configuration
BASE_URL = "http://localhost:8001"
TEST_ORG_DID = "did:sarvone:testbank:00001"
TEST_ORG_ADDRESS = "0x742d35Cc6B00Cc73C4b6DEBaa9f3f7b8c7B58E2B"  # Example address
TEST_SCOPES = ["kyc_verification", "loan_approval", "credit_check"]

def test_health_check():
    """Test the health check endpoint"""
    print("🔍 Testing health check...")
    try:
        response = requests.get(f"{BASE_URL}/health", timeout=10)
        if response.status_code == 200:
            data = response.json()
            print("✅ Health check passed")
            print(f"   - Status: {data['status']}")
            print(f"   - Latest Block: {data['latest_block']}")
            print(f"   - Admin Balance: {data['admin_balance_eth']} ETH")
            print(f"   - Contract: {data['contract_address']}")
            return True
        else:
            print(f"❌ Health check failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Health check error: {e}")
        return False

def test_contract_info():
    """Test the contract info endpoint"""
    print("\n🔍 Testing contract info...")
    try:
        response = requests.get(f"{BASE_URL}/contract/info", timeout=10)
        if response.status_code == 200:
            data = response.json()
            print("✅ Contract info retrieved")
            print(f"   - Contract: {data['contract_address']}")
            print(f"   - Admin: {data['admin_address']}")
            print(f"   - Chain ID: {data['chain_id']}")
            print(f"   - Gas Limit: {data['gas_limit']}")
            return True
        else:
            print(f"❌ Contract info failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Contract info error: {e}")
        return False

def test_approve_organization():
    """Test the organization approval endpoint"""
    print("\n🔍 Testing organization approval...")
    
    # Check if we have admin private key configured
    if not os.getenv('ADMIN_PRIVATE_KEY') or os.getenv('ADMIN_PRIVATE_KEY') == 'your_admin_private_key_here':
        print("⚠️  Skipping approval test - ADMIN_PRIVATE_KEY not configured")
        print("   Set a real private key in .env to test blockchain transactions")
        return True
    
    try:
        payload = {
            "orgDID": TEST_ORG_DID,
            "orgAddress": TEST_ORG_ADDRESS,
            "scopes": TEST_SCOPES
        }
        
        print(f"   - Approving: {TEST_ORG_DID}")
        print(f"   - Address: {TEST_ORG_ADDRESS}")
        print(f"   - Scopes: {TEST_SCOPES}")
        
        response = requests.post(
            f"{BASE_URL}/approve_org",
            json=payload,
            timeout=60  # Blockchain calls can take time
        )
        
        if response.status_code == 200:
            data = response.json()
            print("✅ Organization approval successful")
            print(f"   - TX Hash: {data['tx_hash']}")
            print(f"   - Explorer: {data['explorer_url']}")
            
            # Test transaction status endpoint
            time.sleep(2)  # Wait a bit for transaction to propagate
            return test_transaction_status(data['tx_hash'])
        else:
            print(f"❌ Approval failed: {response.status_code}")
            try:
                error_data = response.json()
                print(f"   - Error: {error_data.get('detail', 'Unknown error')}")
            except:
                print(f"   - Response: {response.text}")
            return False
            
    except Exception as e:
        print(f"❌ Approval error: {e}")
        return False

def test_transaction_status(tx_hash):
    """Test the transaction status endpoint"""
    print(f"\n🔍 Testing transaction status for {tx_hash[:10]}...")
    try:
        response = requests.get(f"{BASE_URL}/transaction/{tx_hash}", timeout=10)
        if response.status_code == 200:
            data = response.json()
            print("✅ Transaction status retrieved")
            print(f"   - Status: {data['status']}")
            if data.get('block_number'):
                print(f"   - Block: {data['block_number']}")
                print(f"   - Gas Used: {data['gas_used']}")
            return True
        else:
            print(f"❌ Transaction status failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Transaction status error: {e}")
        return False

def test_input_validation():
    """Test input validation"""
    print("\n🔍 Testing input validation...")
    
    # Test invalid address
    try:
        payload = {
            "orgDID": "did:sarvone:test:001",
            "orgAddress": "invalid_address",
            "scopes": ["test"]
        }
        
        response = requests.post(f"{BASE_URL}/approve_org", json=payload, timeout=10)
        if response.status_code == 422:  # Validation error
            print("✅ Address validation working")
        else:
            print(f"❌ Address validation failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Validation test error: {e}")
        return False
    
    # Test invalid DID
    try:
        payload = {
            "orgDID": "invalid:did",
            "orgAddress": "0x742d35Cc6B00Cc73C4b6DEBaa9f3f7b8c7B58E2B",
            "scopes": ["test"]
        }
        
        response = requests.post(f"{BASE_URL}/approve_org", json=payload, timeout=10)
        if response.status_code == 422:  # Validation error
            print("✅ DID validation working")
            return True
        else:
            print(f"❌ DID validation failed: {response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Validation test error: {e}")
        return False

def main():
    print("🧪 SarvOne Blockchain Service Test Suite")
    print("=" * 50)
    
    # Check if service is running
    try:
        response = requests.get(f"{BASE_URL}/health", timeout=5)
        if response.status_code != 200:
            print(f"❌ Service not responding properly at {BASE_URL}")
            print("💡 Make sure the service is running: python run.py")
            return
    except requests.exceptions.ConnectionError:
        print(f"❌ Cannot connect to service at {BASE_URL}")
        print("💡 Make sure the service is running: python run.py")
        return
    except Exception as e:
        print(f"❌ Connection error: {e}")
        return
    
    # Run tests
    tests = [
        test_health_check,
        test_contract_info,
        test_input_validation,
        test_approve_organization
    ]
    
    passed = 0
    total = len(tests)
    
    for test in tests:
        try:
            if test():
                passed += 1
        except Exception as e:
            print(f"❌ Test failed with exception: {e}")
    
    print(f"\n📊 Test Results: {passed}/{total} tests passed")
    
    if passed == total:
        print("🎉 All tests passed! Service is working correctly.")
    else:
        print("⚠️  Some tests failed. Check the output above for details.")
    
    print(f"\n🔗 Service Documentation: {BASE_URL}/docs")

if __name__ == "__main__":
    main() 