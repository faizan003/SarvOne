# ONDC Integration - Privacy-Preserving Verifiable Credential Lookup

## Overview

SecureVerify provides ONDC-compliant API endpoints for verifiable credential lookup and verification. The design prioritizes privacy by using VC IDs instead of exposing DIDs directly.

## API Endpoints

### Base URL
```
https://your-domain.com/api/ondc
```

### 1. VC Lookup by VC ID
**GET** `/api/ondc/vc/{vcId}`

Lookup a specific verifiable credential using its unique VC ID.

#### Request
```http
GET /api/ondc/vc/550e8400-e29b-41d4-a716-446655440000
```

#### Response
```json
{
  "success": true,
  "data": {
    "vc_id": "550e8400-e29b-41d4-a716-446655440000",
    "vc_type": "employment_verification",
    "subject_name": "John Doe",
    "issuer": {
      "name": "TechCorp Inc",
      "type": "employer"
    },
    "issued_at": "2024-01-15T10:30:00Z",
    "expires_at": "2025-01-15T10:30:00Z",
    "credential_data": {
      "position": "Software Engineer",
      "department": "Engineering",
      "start_date": "2023-06-01",
      "salary_range": "80000-120000"
    },
    "ipfs_hash": "QmX...",
    "blockchain_tx_hash": "0x123...",
    "status": "active",
    "verification_urls": {
      "ipfs": "https://ipfs.io/ipfs/QmX...",
      "blockchain": "https://polygonscan.com/tx/0x123..."
    }
  }
}
```

#### Error Responses
```json
{
  "success": false,
  "error": "VC not found",
  "message": "No verifiable credential found with the provided VC ID"
}
```

### 2. VC Verification
**POST** `/api/ondc/verify`

Verify a verifiable credential using VC ID and cryptographic signature.

#### Request
```json
{
  "vc_id": "550e8400-e29b-41d4-a716-446655440000",
  "signature": "base64_encoded_signature",
  "public_key": "base64_encoded_public_key"
}
```

#### Response
```json
{
  "success": true,
  "data": {
    "vc_id": "550e8400-e29b-41d4-a716-446655440000",
    "verified": true,
    "verification_timestamp": "2024-01-15T12:00:00Z",
    "status": "active"
  }
}
```

### 3. VC Metadata
**GET** `/api/ondc/vc/{vcId}/metadata`

Get public metadata for a verifiable credential without exposing sensitive data.

#### Request
```http
GET /api/ondc/vc/550e8400-e29b-41d4-a716-446655440000/metadata
```

#### Response
```json
{
  "success": true,
  "data": {
    "vc_id": "550e8400-e29b-41d4-a716-446655440000",
    "vc_type": "employment_verification",
    "issuer_name": "TechCorp Inc",
    "issued_at": "2024-01-15T10:30:00Z",
    "expires_at": "2025-01-15T10:30:00Z",
    "status": "active",
    "verification_urls": {
      "ipfs": "https://ipfs.io/ipfs/QmX...",
      "blockchain": "https://polygonscan.com/tx/0x123..."
    }
  }
}
```

### 4. Health Check
**GET** `/api/ondc/health`

Check the status of the ONDC service.

#### Response
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "timestamp": "2024-01-15T12:00:00Z",
    "total_vcs": 1250,
    "version": "1.0.0",
    "endpoints": {
      "vc_lookup": "https://your-domain.com/api/ondc/vc/example-vc-id",
      "vc_verify": "https://your-domain.com/api/ondc/verify",
      "vc_metadata": "https://your-domain.com/api/ondc/vc/example-vc-id/metadata"
    }
  }
}
```

## Privacy Features

### 1. VC ID-Based Lookup
- **No DID Exposure**: DIDs are never exposed in public endpoints
- **Selective Access**: Only the specific VC requested is returned
- **No User Tracking**: Lookups don't require user authentication

### 2. Audit Trail
All ONDC API calls are logged for transparency:
- IP address
- User agent
- VC ID accessed
- Timestamp
- Action type

### 3. Data Minimization
- Only necessary credential data is returned
- Sensitive personal information is protected
- Public metadata endpoint for basic verification

## Integration Examples

### 1. Bank Loan Application
```javascript
// Bank verifies employment credential
const response = await fetch('/api/ondc/vc/550e8400-e29b-41d4-a716-446655440000');
const vc = await response.json();

if (vc.success && vc.data.status === 'active') {
  // Process loan application
  console.log('Employment verified:', vc.data.credential_data.position);
}
```

### 2. University Admission
```javascript
// University verifies education credential
const response = await fetch('/api/ondc/vc/660e8400-e29b-41d4-a716-446655440001');
const vc = await response.json();

if (vc.success && vc.data.credential_data.degree === 'Bachelor of Science') {
  // Process admission
  console.log('Education verified:', vc.data.credential_data.institution);
}
```

### 3. Healthcare Provider
```javascript
// Hospital verifies medical credential
const response = await fetch('/api/ondc/vc/770e8400-e29b-41d4-a716-446655440002');
const vc = await response.json();

if (vc.success && vc.data.vc_type === 'medical_license') {
  // Grant medical privileges
  console.log('Medical license verified:', vc.data.issuer.name);
}
```

## Security Considerations

### 1. Rate Limiting
Implement rate limiting to prevent abuse:
```php
// In your routes file
Route::middleware(['throttle:60,1'])->group(function () {
    Route::prefix('api/ondc')->group(function () {
        // ONDC routes
    });
});
```

### 2. CORS Configuration
Allow ONDC partners to access the API:
```php
// In config/cors.php
'allowed_origins' => [
    'https://ondc.org',
    'https://*.ondc.org',
    // Add your ONDC partner domains
],
```

### 3. API Authentication (Optional)
For enhanced security, implement API key authentication:
```php
// In ONDCController
public function __construct()
{
    $this->middleware('api.key')->only(['lookupVC', 'verifyVC']);
}
```

## Error Handling

### Common Error Codes
- `400`: Invalid VC ID format or missing parameters
- `404`: VC not found
- `400`: VC not active (expired/revoked)
- `500`: Internal server error

### Error Response Format
```json
{
  "success": false,
  "error": "Error type",
  "message": "Human-readable error message"
}
```

## Testing

### 1. Test with Sample VC ID
```bash
curl -X GET "https://your-domain.com/api/ondc/vc/550e8400-e29b-41d4-a716-446655440000"
```

### 2. Test Health Check
```bash
curl -X GET "https://your-domain.com/api/ondc/health"
```

### 3. Test Verification
```bash
curl -X POST "https://your-domain.com/api/ondc/verify" \
  -H "Content-Type: application/json" \
  -d '{
    "vc_id": "550e8400-e29b-41d4-a716-446655440000",
    "signature": "base64_signature",
    "public_key": "base64_public_key"
  }'
```

## ONDC Compliance

### 1. Standards Adherence
- W3C Verifiable Credentials Data Model
- DID (Decentralized Identifier) standards
- ONDC Network Policy

### 2. Data Formats
- JSON-LD for credential data
- JWT for credential signatures
- IPFS for decentralized storage
- Blockchain for immutability

### 3. Privacy Compliance
- GDPR compliance through data minimization
- User consent and control
- Transparent audit trails
- No unnecessary data collection

## Implementation Notes

### 1. VC ID Generation
VC IDs should be:
- Globally unique
- Cryptographically secure
- Human-readable (optional)
- Compatible with ONDC standards

### 2. Signature Verification
Implement proper cryptographic verification:
```php
private function verifySignature(VerifiableCredential $vc, string $signature, string $publicKey): bool
{
    // Use proper cryptographic libraries
    $data = $vc->vc_id . $vc->issued_at->toISOString();
    return openssl_verify($data, base64_decode($signature), $publicKey, OPENSSL_ALGO_SHA256) === 1;
}
```

### 3. Performance Optimization
- Cache frequently accessed VCs
- Use database indexing on VC ID
- Implement response compression
- Consider CDN for static verification URLs

## Support

For ONDC integration support:
- Email: ondc-support@secureverify.com
- Documentation: https://docs.secureverify.com/ondc
- API Status: https://status.secureverify.com 