# IPFS and Blockchain Integration - SecureVerify

## Overview

SecureVerify now implements a comprehensive decentralized storage and verification system using IPFS (InterPlanetary File System) for document storage and Polygon blockchain for hash verification. This ensures immutable, tamper-proof, and publicly verifiable credentials.

## Architecture

### 1. Three-Layer Storage System

```
┌─────────────────────────────────────────────────────────────┐
│                    SecureVerify Application                 │
├─────────────────────────────────────────────────────────────┤
│  1. Database Layer (MySQL)                                 │
│     - Metadata and quick access                            │
│     - Credential status and statistics                     │
│     - Issuer and subject information                       │
├─────────────────────────────────────────────────────────────┤
│  2. IPFS Layer (Decentralized Storage)                     │
│     - Full VC documents as JSON                            │
│     - Immutable and content-addressed                      │
│     - Distributed across IPFS nodes                        │
│     - Accessible via IPFS hash                             │
├─────────────────────────────────────────────────────────────┤
│  3. Blockchain Layer (Polygon)                             │
│     - Credential hashes stored on-chain                    │
│     - Immutable audit trail                                │
│     - Public verification                                  │
│     - Smart contract manages hash storage                  │
└─────────────────────────────────────────────────────────────┘
```

### 2. Data Flow

```
Issue VC → Sign with RSA → Store in IPFS → Store hash on Blockchain → Update Database
                ↓              ↓                   ↓                      ↓
        Digital Signature   IPFS Hash        Transaction Hash       Complete Record
```

## Implementation Details

### 1. IPFS Service (`app/Services/IPFSService.php`)

**Key Features:**
- Store VCs as JSON documents on IPFS
- Retrieve VCs from multiple IPFS gateways
- Automatic pinning to prevent garbage collection
- Fallback to multiple public gateways
- Caching for improved performance

**Methods:**
- `storeVC(array $vcData)` - Store VC on IPFS
- `retrieveVC(string $hash)` - Retrieve VC from IPFS
- `pinFile(string $hash)` - Pin file to prevent deletion
- `isAccessible()` - Check IPFS node availability

### 2. Blockchain Service (`app/Services/BlockchainService.php`)

**Key Features:**
- Store credential hashes on Polygon blockchain
- Verify credential existence and status
- Revoke credentials on-chain
- Gas optimization and error handling

**Methods:**
- `storeCredentialHash(string $hash, string $vcId, string $issuerDid)` - Store hash on blockchain
- `verifyCredentialHash(string $hash)` - Verify hash exists on blockchain
- `revokeCredential(string $hash, string $reason)` - Revoke credential on blockchain

### 3. Smart Contract (`contracts/CredentialRegistry.sol`)

**Key Features:**
- Solidity smart contract for Polygon blockchain
- Manages credential hashes and issuer registration
- Event logging for transparency
- Access control and security

**Main Functions:**
- `storeCredential()` - Store new credential hash
- `verifyCredential()` - Verify credential exists and status
- `revokeCredential()` - Revoke a credential
- `registerIssuer()` - Register new issuer organization

## Digital Signatures

### RSA-2048 Key Generation Process

```php
// 1. Generate RSA key pair for each organization
$organization->generateKeyPair();

// 2. Create W3C compliant VC structure
$vc = [
    '@context' => ['https://www.w3.org/2018/credentials/v1'],
    'id' => 'urn:uuid:12345...',
    'type' => ['VerifiableCredential', 'AccountVerificationCredential'],
    'issuer' => ['id' => 'did:secureverify:org:xyz...'],
    'credentialSubject' => ['id' => 'did:secureverify:user:abc...', 'data' => {...}],
    'issuanceDate' => '2025-01-11T...'
];

// 3. Convert to JSON string
$vcString = json_encode($vc, JSON_UNESCAPED_SLASHES);

// 4. Sign with organization's private key
$signature = openssl_sign($vcString, $signature, $privateKey, OPENSSL_ALGO_SHA256);

// 5. Add proof to VC
$vc['proof'] = [
    'type' => 'RsaSignature2018',
    'created' => now(),
    'verificationMethod' => $organization->did . '#key-1',
    'jws' => base64_encode($signature)
];
```

## API Endpoints

### Verification APIs

1. **Verify by VC ID**
   ```
   POST /api/verify/vc-id
   Body: { "vc_id": "urn:uuid:..." }
   ```

2. **Verify by IPFS Hash**
   ```
   POST /api/verify/ipfs-hash
   Body: { "ipfs_hash": "QmXXXXXX..." }
   ```

3. **Verify on Blockchain**
   ```
   POST /api/verify/blockchain
   Body: { "credential_hash": "sha256_hash..." }
   ```

4. **Get VC Status**
   ```
   GET /api/verify/status/{vcId}
   ```

### Response Format

```json
{
  "success": true,
  "data": {
    "vc_id": "urn:uuid:...",
    "overall_status": "verified",
    "verification_results": {
      "database_status": { "status": "verified" },
      "ipfs_verification": { "status": "verified", "accessible": true },
      "blockchain_verification": { "status": "verified", "exists_on_chain": true },
      "signature_verification": { "status": "verified", "is_valid": true }
    }
  }
}
```

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# IPFS Configuration
IPFS_GATEWAY_URL=https://ipfs.io/ipfs/
IPFS_API_URL=http://localhost:5001/api/v0/
IPFS_TIMEOUT=30

# Blockchain Configuration
BLOCKCHAIN_NETWORK=polygon
BLOCKCHAIN_PRIVATE_KEY=6001d9f1d7816625adbef25129e435495286ec2ed79f5e63540888789abb4aa5
BLOCKCHAIN_TIMEOUT=60

# Polygon Amoy Testnet Configuration
POLYGON_RPC_URL=https://rpc-amoy.polygon.technology
POLYGON_CHAIN_ID=80002
POLYGON_EXPLORER_URL=https://amoy.polygonscan.com
POLYGON_CONTRACT_ADDRESS=your_contract_address_here
POLYGON_GAS_LIMIT=200000
POLYGON_GAS_PRICE=30

# SecureVerify Configuration
SECUREVERIFY_DID_PREFIX=did:secureverify:
SECUREVERIFY_VC_CONTEXT=https://secureverify.in/credentials/v1
SECUREVERIFY_TIMEZONE=Asia/Kolkata
```

## Enhanced UI Features

### 1. Issued VCs Dashboard

- **Storage Status**: Visual indicators for IPFS and blockchain storage
- **IPFS Links**: Direct links to view VCs on IPFS gateways
- **Blockchain Links**: Links to view transactions on Polygon explorer
- **Detailed Modal**: Comprehensive VC information with verification status

### 2. Professional Design Elements

- Clean, modern interface following user preferences [[memory:2811418]]
- Consistent color scheme and typography
- Responsive design for all devices
- Professional icons and status indicators

## Security Features

### 1. Multi-Layer Verification

- **Database Integrity**: Status checks and metadata validation
- **IPFS Verification**: Content retrieval and hash validation
- **Blockchain Verification**: On-chain existence and status checks
- **Digital Signature**: RSA-2048 signature verification

### 2. Fallback Mechanisms

- **IPFS Fallback**: Multiple gateway support for reliability
- **Blockchain Fallback**: Graceful degradation if blockchain unavailable
- **Error Handling**: Comprehensive error logging and user feedback

## Deployment Guide

### 1. Prerequisites

- PHP 8.1+ with OpenSSL extension
- MySQL 8.0+
- IPFS node (local or remote)
- Polygon network access
- Deployed smart contract

### 2. Installation Steps

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

3. **Run Migrations**
   ```bash
   php artisan migrate
   ```

4. **Deploy Smart Contract**
   ```bash
   # Deploy CredentialRegistry.sol to Polygon
   # Update POLYGON_CONTRACT_ADDRESS in .env
   ```

5. **Start Services**
   ```bash
   php artisan serve
   # Ensure IPFS node is running
   ```

### 3. Smart Contract Deployment

```solidity
// Deploy to Polygon using Hardhat, Truffle, or Remix
// Example with Hardhat:
npx hardhat run scripts/deploy.js --network polygon
```

## Testing

### 1. Unit Tests

```bash
php artisan test --filter=IPFSServiceTest
php artisan test --filter=BlockchainServiceTest
```

### 2. Integration Tests

```bash
# Test full VC issuance flow
php artisan test --filter=VCIssuanceTest
```

### 3. API Tests

```bash
# Test verification endpoints
php artisan test --filter=VerificationAPITest
```

## Monitoring and Logging

### 1. Log Channels

- **IPFS Operations**: Storage, retrieval, and errors
- **Blockchain Operations**: Transactions, verifications, and gas usage
- **VC Operations**: Issuance, verification, and revocation

### 2. Metrics to Monitor

- IPFS storage success rate
- Blockchain transaction success rate
- Average verification time
- Gas usage statistics

## Troubleshooting

### Common Issues

1. **IPFS Node Unavailable**
   - Check IPFS daemon status
   - Verify API endpoint configuration
   - Test with public gateways

2. **Blockchain Connection Issues**
   - Verify RPC endpoint
   - Check private key format
   - Ensure sufficient gas balance

3. **Smart Contract Errors**
   - Verify contract deployment
   - Check function signatures
   - Review gas limit settings

### Debug Commands

```bash
# Check IPFS connectivity
curl http://localhost:5001/api/v0/version

# Test blockchain connection
php artisan tinker
>>> app(App\Services\BlockchainService::class)->isAvailable()

# View logs
tail -f storage/logs/laravel.log
```

## Future Enhancements

1. **IPFS Cluster Support** - Multiple IPFS nodes for redundancy
2. **Layer 2 Solutions** - Integrate with Polygon zkEVM
3. **Credential Templates** - Pre-defined VC structures
4. **Batch Operations** - Bulk credential issuance
5. **Analytics Dashboard** - Usage statistics and insights

## Support

For technical support or questions:
- Review the logs in `storage/logs/laravel.log`
- Check the API documentation
- Verify environment configuration
- Test with minimal examples

---

**Note**: This implementation provides a robust foundation for decentralized credential management. The system is designed to be scalable, secure, and maintainable while following W3C standards for verifiable credentials. 