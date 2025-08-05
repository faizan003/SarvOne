# SarvOne Blockchain Service

FastAPI microservice for SarvOne smart contract operations on Polygon Amoy testnet.

## Features

- ✅ **Organization Approval**: Call `approveOrganization` function on SarvOne smart contract
- ✅ **Transaction Monitoring**: Track transaction status and receipts
- ✅ **Health Checks**: Monitor service and blockchain connectivity
- ✅ **Comprehensive Logging**: Full request/response logging
- ✅ **Error Handling**: Detailed error messages and validation
- ✅ **Auto-retry**: RPC failover support

## Setup Instructions

### 1. Virtual Environment Setup

```bash
# Navigate to the blockchain service directory
cd fastapi_blockchain_service

# Create virtual environment
python -m venv blockchain_env

# Activate virtual environment
# On Windows:
blockchain_env\Scripts\activate
# On macOS/Linux:
source blockchain_env/bin/activate

# Install dependencies
pip install -r requirements.txt
```

### 2. Environment Configuration

```bash
# Copy the example environment file
cp env_example.txt .env

# Edit .env with your actual values
# Required variables:
# - CONTRACT_ADDRESS=0x959387840a40b3bc065033a5da73c75C42c46919
# - ADMIN_PRIVATE_KEY=your_admin_private_key_here
# - BLOCKCHAIN_RPC_URL=https://rpc-amoy.polygon.technology
```

### 3. Run the Service

```bash
# Using the run script (recommended)
python run.py

# Or directly with uvicorn
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
```

### 4. Verify Service

```bash
# Health check
curl http://localhost:8001/health

# API documentation
# Open: http://localhost:8001/docs
```

## API Endpoints

### POST /approve_org
Approve an organization on the SarvOne smart contract.

**Request Body:**
```json
{
  "orgDID": "did:sarvone:sbi:00012345",
  "orgAddress": "0xAbc123456789DefGhi123456789AbcDef12345678",
  "scopes": ["education", "loan_approval", "kyc_verification"]
}
```

**Response:**
```json
{
  "success": true,
  "tx_hash": "0xabcdef1234567890...",
  "explorer_url": "https://amoy.polygonscan.com/tx/0xabcdef1234567890...",
  "timestamp": "2025-01-28T12:00:00.000Z"
}
```

### GET /health
Service health check with blockchain connectivity status.

### GET /transaction/{tx_hash}
Get transaction status and receipt information.

### GET /contract/info
Get smart contract and service configuration details.

## Laravel Integration

### 1. Add FastAPI Service Configuration

Add to `config/services.php`:

```php
'blockchain_service' => [
    'url' => env('BLOCKCHAIN_SERVICE_URL', 'http://localhost:8001'),
    'timeout' => env('BLOCKCHAIN_SERVICE_TIMEOUT', 30),
],
```

Add to `.env`:
```
BLOCKCHAIN_SERVICE_URL=http://localhost:8001
BLOCKCHAIN_SERVICE_TIMEOUT=30
```

### 2. Update AdminController

Replace the current blockchain call with FastAPI service call:

```php
use Illuminate\Support\Facades\Http;

public function approveOrganization(Request $request, Organization $organization)
{
    // ... existing validation and DID generation ...

    // Prepare credential scopes for smart contract
    $writeScopes = json_decode($organization->write_scopes, true) ?? [];
    $readScopes = json_decode($organization->read_scopes, true) ?? [];
    $contractScopes = CredentialScopeService::mapScopesForContract($writeScopes, $readScopes);

    try {
        // Call FastAPI blockchain service
        $response = Http::timeout(config('services.blockchain_service.timeout'))
            ->post(config('services.blockchain_service.url') . '/approve_org', [
                'orgDID' => $sarvoneDID,
                'orgAddress' => $organization->wallet_address,
                'scopes' => $contractScopes
            ]);

        if (!$response->successful()) {
            throw new \Exception('Blockchain service error: ' . $response->body());
        }

        $blockchainResult = $response->json();
        
        if (!$blockchainResult['success']) {
            throw new \Exception('Blockchain approval failed: ' . ($blockchainResult['error'] ?? 'Unknown error'));
        }

        // Update organization in database
        $organization->update([
            'verification_status' => 'approved',
            'verified_at' => now(),
            'verification_notes' => $request->remarks,
            'blockchain_tx_hash' => $blockchainResult['tx_hash'],
            'did' => $sarvoneDID,
            'trust_score' => 100
        ]);

        Log::info('Organization approved successfully via FastAPI', [
            'organization_id' => $organization->id,
            'did' => $sarvoneDID,
            'wallet_address' => $organization->wallet_address,
            'contract_scopes' => $contractScopes,
            'tx_hash' => $blockchainResult['tx_hash'],
            'admin_remarks' => $request->remarks,
            'explorer_url' => $blockchainResult['explorer_url']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Organization approved successfully on blockchain!',
            'did' => $sarvoneDID,
            'tx_hash' => $blockchainResult['tx_hash'],
            'explorer_url' => $blockchainResult['explorer_url']
        ]);

    } catch (\Exception $e) {
        Log::error('FastAPI blockchain service call failed', [
            'error' => $e->getMessage(),
            'organization_id' => $organization->id,
            'did' => $sarvoneDID,
            'wallet_address' => $organization->wallet_address
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Blockchain approval failed: ' . $e->getMessage()
        ], 500);
    }
}
```

## Environment Variables Reference

| Variable | Description | Example |
|----------|-------------|---------|
| `BLOCKCHAIN_RPC_URL` | Primary Polygon Amoy RPC endpoint | `https://rpc-amoy.polygon.technology` |
| `BLOCKCHAIN_RPC_FALLBACK_URL` | Backup RPC endpoint | `https://your-backup-rpc.com` |
| `CONTRACT_ADDRESS` | SarvOne smart contract address | `0x959387840a40b3bc065033a5da73c75C42c46919` |
| `ADMIN_PRIVATE_KEY` | Government admin private key | `0xabcdef123...` |
| `CHAIN_ID` | Polygon Amoy chain ID | `80002` |
| `GAS_LIMIT` | Transaction gas limit | `200000` |
| `GAS_PRICE_GWEI` | Gas price in Gwei | `30` |
| `EXPLORER_URL` | Block explorer base URL | `https://amoy.polygonscan.com/tx/` |
| `API_HOST` | FastAPI service host | `0.0.0.0` |
| `API_PORT` | FastAPI service port | `8001` |

## Security Considerations

1. **Private Key Security**: Store admin private key securely, never in version control
2. **Network Security**: Run FastAPI service behind firewall/VPN in production
3. **API Authentication**: Add authentication middleware for production deployment
4. **Rate Limiting**: Implement rate limiting to prevent abuse
5. **Input Validation**: All inputs are validated using Pydantic models

## Monitoring & Logging

- All requests/responses are logged to `blockchain_service.log`
- Transaction hashes and explorer URLs provided for verification
- Health check endpoint for monitoring service status
- Detailed error messages for debugging

## Production Deployment

1. **Use HTTPS**: Configure SSL/TLS for API endpoints
2. **Environment Separation**: Use separate .env files for staging/production
3. **Process Management**: Use systemd/supervisor for service management
4. **Load Balancing**: Deploy multiple instances behind load balancer
5. **Database**: Consider adding database for transaction tracking
6. **Monitoring**: Integrate with monitoring tools (Prometheus, Grafana, etc.)

## Troubleshooting

### Common Issues

1. **Connection Refused**: Check if service is running on correct port
2. **RPC Errors**: Verify RPC URLs and network connectivity
3. **Gas Errors**: Ensure admin account has sufficient MATIC balance
4. **Contract Errors**: Verify contract address and ABI file

### Debug Mode

Set environment variable for debug logging:
```bash
export LOG_LEVEL=DEBUG
python run.py
```

## Support

For issues or questions:
1. Check the logs: `tail -f blockchain_service.log`
2. Verify health endpoint: `curl http://localhost:8001/health`
3. Test with API docs: `http://localhost:8001/docs` 