# How to Fix the ABI Mismatch Issue

## Problem
Your smart contract has been updated but the ABI file is outdated.

**Smart Contract Function:**
```solidity
function approveOrganization(string memory orgDID, address orgAddress, string[] memory scopes)
```

**ABI File Function:**
```json
"inputs": [
    {"internalType": "address", "name": "_org", "type": "address"},
    {"internalType": "string[]", "name": "_scopes", "type": "string[]"}
]
```

## Solution

1. **Recompile your smart contract** to generate the correct ABI
2. **Replace the old ABI file** with the new one
3. The correct ABI should have these inputs:
   ```json
   "inputs": [
       {"internalType": "string", "name": "orgDID", "type": "string"},
       {"internalType": "address", "name": "orgAddress", "type": "address"}, 
       {"internalType": "string[]", "name": "scopes", "type": "string[]"}
   ]
   ```

## Steps to Fix

1. Go to Remix IDE or your Solidity compiler
2. Compile your updated smart contract 
3. Copy the new ABI
4. Replace the content in `fastapi_blockchain_service/SarvOneABI.json`
5. Restart the FastAPI service

## Verification
After updating the ABI, test with:
```bash
curl -X POST "http://localhost:8003/approve_org" \
  -H "Content-Type: application/json" \
  -d '{"orgDID":"did:sarvone:test:00001","orgAddress":"0x742d35Cc6634C0532925a3b8D46a02948d9A4f6e","scopes":["kyc_verification"]}'
```

This should return a successful response instead of a 400 error. 