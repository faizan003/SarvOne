# VC Revoke System Implementation

## Overview

This document describes the implementation of the Verifiable Credential (VC) revoke system in the SecureVerify platform. The system allows organizations to revoke previously issued credentials, with the revocation being recorded both in the local database and on the blockchain for transparency and immutability.

## Smart Contract Integration

### SarvOne Smart Contract

The system integrates with the SarvOne smart contract which provides the following key functions:

```solidity
function revokeVC(bytes32 userDID, bytes32 vcHash) external {
    string memory orgDID = addressToDID[msg.sender];
    require(bytes(orgDID).length > 0, "Sender not linked to any orgDID");
    require(organizations[orgDID].approved, "Org not approved");

    (bool found, uint256 idx) = _findVC(userDID, vcHash);
    require(found, "VC not found");
    require(keccak256(bytes(userVCs[userDID][idx].issuerDID)) == keccak256(bytes(orgDID)), "Only issuer can revoke");
    require(!userVCs[userDID][idx].revoked, "Already revoked");

    userVCs[userDID][idx].revoked = true;
    emit VCRevoked(userDID, vcHash, orgDID);
    emit VCAccessAttempt(userDID, vcHash, msg.sender, true, "VC Revoked", block.timestamp);
}
```

### Key Features

1. **Authorization**: Only the original issuer can revoke a VC
2. **Validation**: Checks if VC exists and is not already revoked
3. **Event Emission**: Records revocation events on blockchain
4. **Access Control**: Prevents unauthorized revocations

## Backend Implementation

### BlockchainService Updates

The `BlockchainService` class has been enhanced with new methods:

#### `revokeVC(string $userDID, string $vcHash): ?array`

```php
public function revokeVC(string $userDID, string $vcHash): ?array
{
    // Calls smart contract revokeVC function
    // Returns transaction details or error information
}
```

#### `issueVC(string $userDID, string $vcHash, string $vcType): ?array`

```php
public function issueVC(string $userDID, string $vcHash, string $vcType): ?array
{
    // Calls smart contract issueVC function
    // Used for issuing new VCs
}
```

### OrganizationController Updates

#### New Methods Added:

1. **`showIssuedVCs()`**: Displays all VCs issued by the organization
2. **`revokeVC(Request $request, $vcId)`**: Handles VC revocation requests
3. **`getVCStatus($vcId)`**: API endpoint for checking VC status

#### Revoke Process:

```php
public function revokeVC(Request $request, $vcId)
{
    // 1. Validate organization ownership
    // 2. Check if VC is already revoked
    // 3. Revoke on blockchain first
    // 4. Update local database
    // 5. Return success/error response
}
```

### VerifiableCredential Model Updates

The model includes revocation-related fields and methods:

```php
protected $fillable = [
    // ... existing fields ...
    'revoked_at',
    'revocation_reason',
];

public function isRevoked(): bool
{
    return $this->status === 'revoked';
}

public function revoke($reason = null): void
{
    $this->update([
        'status' => 'revoked',
        'revoked_at' => now(),
        'revocation_reason' => $reason,
    ]);
}
```

## Frontend Implementation

### Organization Dashboard

#### New Features:

1. **Issued VCs Page**: `/organization/issued-vcs`
   - Lists all VCs issued by the organization
   - Shows status (Active, Revoked, Expired)
   - Provides revoke functionality for active VCs
   - Displays statistics (Total, Active, Revoked, Expired)

2. **Revoke Modal**: 
   - Confirmation dialog before revocation
   - Optional reason field
   - Real-time status updates

3. **VC Details Modal**:
   - Comprehensive VC information
   - Blockchain transaction links
   - Revocation details (if applicable)

### User Dashboard Updates

#### Enhanced VC Display:

1. **Status Indicators**:
   - Active: Green badge with checkmark
   - Revoked: Red badge with ban icon
   - Expired: Yellow badge with clock icon

2. **Revocation Information**:
   - Revocation date and time
   - Revocation reason (if provided)
   - Visual indicators for revoked VCs

3. **CSS Styling**:
   ```css
   .status-revoked { background: #fee2e2; color: #991b1b; }
   ```

## API Endpoints

### New Endpoints:

1. **`GET /api/vc/status/{vcId}`**: Public endpoint for checking VC status
2. **`POST /organization/revoke-vc/{vcId}`**: Organization endpoint for revoking VCs

### Response Format:

```json
{
    "success": true,
    "vc_id": "urn:uuid:...",
    "status": "revoked",
    "revoked": true,
    "revoked_at": "2025-01-15T10:30:00Z",
    "revocation_reason": "Data updated",
    "expired": false,
    "expires_at": "2026-01-15T10:30:00Z",
    "issued_at": "2025-01-15T10:30:00Z",
    "issuer_did": "did:secureverify:org:..."
}
```

## Database Schema

### VerifiableCredentials Table

The table includes revocation-related fields:

```sql
ALTER TABLE verifiable_credentials ADD COLUMN revoked_at TIMESTAMP NULL;
ALTER TABLE verifiable_credentials ADD COLUMN revocation_reason TEXT NULL;
```

### Existing Fields Used:

- `status`: 'active', 'revoked', 'expired'
- `metadata`: JSON field for additional revocation data

## Security Considerations

### Authorization

1. **Organization Ownership**: Only the issuing organization can revoke VCs
2. **Smart Contract Validation**: Blockchain-level authorization checks
3. **Session Validation**: Web interface requires organization authentication

### Data Integrity

1. **Blockchain First**: Revocation recorded on blockchain before database update
2. **Transaction Rollback**: If blockchain fails, database is not updated
3. **Audit Trail**: All revocations are logged with timestamps and reasons

### Privacy

1. **Revocation Reasons**: Optional and can be generic
2. **User Notification**: Users can see revocation status but not internal reasons
3. **Data Retention**: Revoked VCs remain for audit purposes

## User Experience

### Organization Workflow

1. **Access Issued VCs**: Navigate to "Manage Credentials" from dashboard
2. **View VC List**: See all issued VCs with status indicators
3. **Revoke VC**: Click revoke button on active VCs
4. **Provide Reason**: Optional reason for revocation
5. **Confirm Action**: Modal confirmation before proceeding
6. **View Results**: Success/error feedback with transaction details

### User Workflow

1. **View Credentials**: Check credential status in dashboard
2. **Identify Revoked**: Red badges clearly indicate revoked VCs
3. **Access Details**: Click to view revocation information
4. **Verify Status**: Blockchain links for transparency

## Testing

### Test Script

Run `test_vc_revoke_system.php` to verify:

1. Blockchain service integration
2. VC model methods
3. API endpoints
4. Database operations

### Manual Testing

1. **Issue VCs**: Create test credentials
2. **Revoke VCs**: Use organization interface
3. **Verify Status**: Check user dashboard
4. **Check Blockchain**: Verify transaction on explorer

## Error Handling

### Common Scenarios

1. **VC Not Found**: 404 response with clear message
2. **Already Revoked**: 400 response preventing duplicate revocation
3. **Blockchain Failure**: Graceful fallback with error logging
4. **Authorization Failure**: 403 response for unauthorized access

### Logging

All revocation attempts are logged with:
- Organization ID
- VC ID
- Timestamp
- Success/failure status
- Error details (if applicable)

## Future Enhancements

### Planned Features

1. **Bulk Revocation**: Revoke multiple VCs at once
2. **Revocation Templates**: Predefined revocation reasons
3. **User Notifications**: Email/SMS notifications for revocations
4. **Revocation Analytics**: Dashboard metrics for revocation patterns
5. **Temporary Suspension**: Suspend VCs instead of permanent revocation

### Integration Opportunities

1. **Government APIs**: Integration with official revocation registries
2. **Third-party Services**: Webhook notifications to external systems
3. **Mobile Apps**: Push notifications for credential status changes
4. **Audit Systems**: Integration with compliance and audit platforms

## Conclusion

The VC revoke system provides a comprehensive solution for credential lifecycle management. It ensures data integrity through blockchain integration while maintaining a user-friendly interface for both organizations and users. The system is designed to be secure, transparent, and scalable for future enhancements. 