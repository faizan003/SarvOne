#!/usr/bin/env python3
"""
Debug script to test contract loading and function access
"""
import json
import os
from web3 import Web3

# Configuration
RPC_URL = "https://young-indulgent-patron.matic-amoy.quiknode.pro/4e4a6988984084051912a9832446801add7dffaf/"
CONTRACT_ADDRESS = "0x959387840a40b3bc065033a5da73c75C42c46919"
ABI_FILE = "fastapi_blockchain_service/SarvOneABI.json"

def main():
    print("🔍 Debugging Contract and ABI Loading...")
    print("=" * 50)
    
    # Test Web3 connection
    print("1. Testing Web3 connection...")
    w3 = Web3(Web3.HTTPProvider(RPC_URL))
    
    if w3.is_connected():
        print("✅ Web3 connected successfully")
        print(f"   - Latest block: {w3.eth.block_number}")
        print(f"   - Chain ID: {w3.eth.chain_id}")
    else:
        print("❌ Web3 connection failed")
        return
    
    # Test ABI loading
    print("\n2. Testing ABI loading...")
    try:
        with open(ABI_FILE, 'r') as f:
            abi = json.load(f)
        print(f"✅ ABI loaded successfully")
        print(f"   - ABI has {len(abi)} functions/events")
        
        # Check for approveOrganization function
        approve_func = None
        for item in abi:
            if item.get('name') == 'approveOrganization':
                approve_func = item
                break
        
        if approve_func:
            print("✅ approveOrganization function found in ABI")
            print(f"   - Inputs: {len(approve_func['inputs'])}")
            for i, inp in enumerate(approve_func['inputs']):
                print(f"     {i+1}. {inp['name']}: {inp['type']}")
        else:
            print("❌ approveOrganization function not found in ABI")
            return
            
    except Exception as e:
        print(f"❌ Failed to load ABI: {e}")
        return
    
    # Test contract loading
    print("\n3. Testing contract loading...")
    try:
        contract = w3.eth.contract(address=CONTRACT_ADDRESS, abi=abi)
        print("✅ Contract loaded successfully")
        print(f"   - Contract address: {contract.address}")
        
        # Test function access
        print("\n4. Testing function access...")
        try:
            func = contract.functions.approveOrganization
            print("✅ approveOrganization function accessible")
            print(f"   - Function object: {type(func)}")
            
            # Test building a sample transaction
            print("\n5. Testing transaction building...")
            try:
                # Sample parameters
                org_did = "did:sarvone:test:12345"
                org_address = "0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e"
                scopes = ["kyc_verification"]
                
                # Try to build transaction
                tx = func(org_did, org_address, scopes).buildTransaction({
                    'from': '0x4778eC77AC034d25687fAf8d9457b3f1FC4bB8De',
                    'nonce': 0,
                    'gas': 200000,
                    'gasPrice': w3.to_wei(30, 'gwei'),
                    'chainId': 80002
                })
                print("✅ Transaction building successful")
                print(f"   - Transaction data length: {len(tx['data'])}")
                
            except Exception as e:
                print(f"❌ Transaction building failed: {e}")
                
        except Exception as e:
            print(f"❌ Function access failed: {e}")
            
    except Exception as e:
        print(f"❌ Contract loading failed: {e}")

if __name__ == "__main__":
    main() 