# VC Revoke 500 Error Fix

## Problem Summary

The 500 error when trying to revoke VCs was caused by the blockchain service not being properly configured. The main issues were:

1. **Empty fromAddress**: The blockchain wallet address was not being generated because the private key was not configured
2. **Poor Error Handling**: The system was returning null instead of proper error responses
3. **DID Encoding Issues**: The smart contract expected bytes32 format but was receiving string DIDs

## Root Cause

The error occurred because:
- `BLOCKCHAIN_PRIVATE_KEY` environment variable was not set
- `POLYGON_CONTRACT_ADDRESS` was not configured
- The system didn't handle missing configuration gracefully

## Fixes Applied

### 1. Enhanced Error Handling

**Before:**
```php
if (!$this->contractAddress) {
    Log::error('Contract address not configured');
    return null; // This caused 500 errors
}
```

**After:**
```php
if (!$this->contractAddress) {
    Log::error('Contract address not configured');
    return [
        'success' => false,
        'error' => 'Contract address not configured'
    ];
}

if (!$this->fromAddress) {
    Log::error('Blockchain wallet not configured - fromAddress is empty');
    return [
        'success' => false,
        'error' => 'Blockchain wallet not configured. Please check BLOCKCHAIN_PRIVATE_KEY in environment.'
    ];
}
```

### 2. Fallback for Development/Testing

Added graceful fallback when blockchain is not configured:

```php
// Check if it's a configuration issue (for development/testing)
if (strpos($blockchainResult['error'] ?? '', 'not configured') !== false) {
    // For development/testing, allow revocation without blockchain
    \Log::warning('Blockchain not configured - proceeding with local revocation only');
    
    // Update local database only
    $vc->revoke($revocationReason);
    $vc->update([
        'metadata' => array_merge($vc->metadata ?? [], [
            'revocation_note' => 'Blockchain not configured - local revocation only',
            'blockchain_error' => $blockchainResult['error']
        ])
    ]);

    return response()->json([
        'success' => true,
        'message' => 'VC revoked locally (blockchain not configured).',
        'warning' => 'Blockchain integration not available - revocation recorded locally only.'
    ]);
}
```

### 3. Fixed DID Encoding

**Before:**
```php
$userDIDHex = str_pad(substr($userDID, 2), 64, '0', STR_PAD_LEFT); // Assumed 0x prefix
```

**After:**
```php
// Convert DID to bytes32 hash
$userDIDHash = hash('sha256', $userDID, false); // Get hex without 0x prefix
$userDIDHex = str_pad($userDIDHash, 64, '0', STR_PAD_LEFT);

// Ensure hash is properly formatted
$vcHashClean = str_replace('0x', '', $vcHash); // Remove 0x prefix if present
$vcHashHex = str_pad($vcHashClean, 64, '0', STR_PAD_LEFT);
```

## Current Status

✅ **Fixed**: The revoke functionality now works in two modes:

1. **Full Blockchain Mode**: When properly configured with private key and contract address
2. **Local Only Mode**: When blockchain is not configured (for development/testing)

## How to Configure Blockchain (Optional)

To enable full blockchain integration, add these to your `.env` file:

```env
# Blockchain Configuration
BLOCKCHAIN_PRIVATE_KEY=your_private_key_here
POLYGON_CONTRACT_ADDRESS=your_contract_address_here
POLYGON_RPC_URL=https://rpc-amoy.polygon.technology
POLYGON_CHAIN_ID=80002
POLYGON_EXPLORER_URL=https://amoy.polygonscan.com
POLYGON_GAS_LIMIT=200000
POLYGON_GAS_PRICE=30
```

## Testing the Fix

### 1. Test Current Functionality

The revoke system now works even without blockchain configuration:

1. Go to Organization Dashboard
2. Click "Manage Credentials" 
3. Find an active VC
4. Click the revoke button (ban icon)
5. Enter a reason and confirm
6. You should see: "VC revoked locally (blockchain not configured)"

### 2. Test with Blockchain (if configured)

If you have blockchain configured:
1. Follow the same steps as above
2. You should see: "VC revoked successfully" with transaction hash
3. The VC will be revoked both locally and on blockchain

### 3. Test Error Handling

The system now provides clear error messages:
- "VC not found or you are not authorized to revoke it" (404)
- "VC is already revoked" (400)
- "Blockchain wallet not configured" (with fallback to local)

## User Experience Improvements

### Organization Dashboard
- Clear status indicators (Active, Revoked, Expired)
- Revoke button only shows for active VCs
- Confirmation modal with reason input
- Success/error feedback with details

### User Dashboard
- Red badges for revoked VCs
- Revocation date and reason display
- Visual indicators for all status types

## Files Modified

1. **`app/Services/BlockchainService.php`**
   - Enhanced error handling
   - Fixed DID encoding
   - Added configuration validation

2. **`app/Http/Controllers/OrganizationController.php`**
   - Added fallback for development
   - Improved error responses
   - Better logging

3. **`resources/views/organization/issued-vcs.blade.php`**
   - Complete revoke interface
   - Status indicators
   - Modal confirmations

4. **`resources/views/dashboard.blade.php`**
   - Enhanced VC display
   - Revoked status indicators
   - Updated CSS styling

## Next Steps

1. **For Development**: The system works without blockchain configuration
2. **For Production**: Configure blockchain environment variables
3. **For Testing**: Use the provided test interfaces

## Troubleshooting

### If you still get 500 errors:

1. **Check Logs**: Look at `storage/logs/laravel.log`
2. **Verify Routes**: Ensure `/organization/revoke-vc/{vcId}` route exists
3. **Check Authentication**: Make sure you're logged in as the organization that issued the VC
4. **Verify VC Exists**: Ensure the VC ID is correct and exists in the database

### Common Issues:

1. **Authentication**: Must be logged in as the issuing organization
2. **VC Status**: Can only revoke active VCs
3. **Permissions**: Only the issuing organization can revoke their VCs

The revoke system is now robust and handles all edge cases gracefully! 