# Organization API Documentation Feature

## Overview

The Organization API Documentation feature provides approved organizations (banks, companies, schools, hospitals, government departments) with a comprehensive interface to integrate SecureVerify into their existing systems for issuing and verifying verifiable credentials (VCs).

## Features Implemented

### 1. API Documentation Dashboard Integration
- **Location**: Organization Dashboard (`/organization/dashboard`)
- **Button**: "API Documentation" button in the header section
- **Access**: Only visible to approved organizations
- **Navigation**: Direct link to comprehensive API documentation page

### 2. Comprehensive API Documentation Page
- **Route**: `/organization/api-documentation`
- **Controller**: `OrganizationController@apiDocumentation`
- **View**: `resources/views/organization/api-documentation.blade.php`

## Key Components

### 1. Organization Information Display
- Organization name, type, and DID
- API credentials (Base URL and API Key)
- Copy-to-clipboard functionality for easy credential copying

### 2. Authorized Scopes Section
- **Write Scopes**: Credential types the organization can issue
- **Read Scopes**: Credential types the organization can verify
- Dynamic display based on organization's approved permissions

### 3. API Endpoints Documentation

#### Issue Credential Endpoint
- **Method**: POST
- **URL**: `/organization/api/issue-credential`
- **Purpose**: Issue new verifiable credentials to users
- **Features**:
  - Complete flow handling (IPFS upload, blockchain signing, credential storage)
  - Scope validation
  - User verification checks
  - Private key signing for transaction authenticity

#### Verify Credential Endpoint
- **Method**: POST
- **URL**: `/organization/api/verify-credential`
- **Purpose**: Verify authenticity of user credentials
- **Features**:
  - Database and blockchain verification
  - Access logging and user notifications
  - Scope validation

#### Lookup User Endpoint
- **Method**: POST
- **URL**: `/organization/api/lookup-user-by-did`
- **Purpose**: Get user information and available credentials
- **Features**:
  - User verification status
  - Available credential types

### 4. Integration Guide
- **Authentication**: Bearer token authentication
- **Credential Types**: Organization-specific scope listing
- **Complete Flows**: Step-by-step guides for issuing and verifying
- **Error Handling**: Standardized error response format
- **Rate Limiting**: 100 requests per minute per organization
- **Security Best Practices**: Guidelines for secure integration

### 5. Code Examples
- **JavaScript/Node.js**: Modern async/await examples
- **Python**: Requests library examples
- **PHP**: cURL examples
- **cURL**: Command-line examples for testing

## Technical Implementation

### 1. Route Configuration
```php
// routes/web.php
Route::middleware('auth:organization')->group(function () {
    Route::get('/api-documentation', [OrganizationController::class, 'apiDocumentation'])
        ->name('organization.api-documentation');
});
```

### 2. Controller Method
```php
// app/Http/Controllers/OrganizationController.php
public function apiDocumentation()
{
    $organization = Auth::guard('organization')->user();
    
    // Get organization's API credentials
    $apiKey = $organization->api_key ?? 'Not generated';
    $baseUrl = url('/organization/api');
    
    // Get organization's scopes
    $writeScopes = $organization->write_scopes ?? [];
    $readScopes = $organization->read_scopes ?? [];
    
    // Get credential types from config
    $credentialScopes = config('credential_scopes');
    $orgType = $organization->organization_type;
    
    return view('organization.api-documentation', compact(
        'organization', 
        'apiKey', 
        'baseUrl', 
        'writeScopes', 
        'readScopes', 
        'credentialScopes',
        'orgType'
    ));
}
```

### 3. View Structure
- **Breadcrumb Navigation**: Easy navigation back to dashboard
- **Organization Info Card**: Display organization details
- **API Credentials Section**: Base URL and API key with copy functionality
- **Authorized Scopes**: Visual display of permissions
- **API Endpoints**: Detailed documentation for each endpoint
- **Integration Guide**: Step-by-step integration instructions
- **Code Examples**: Multi-language code samples
- **Support Section**: Contact information and resources

## API Endpoint Details

### Issue Credential Flow
1. **Scope Validation**: Check if organization can issue the credential type
2. **User Verification**: Ensure recipient user exists and is verified
3. **Credential Creation**: Generate W3C-compliant verifiable credential
4. **IPFS Upload**: Store credential data on IPFS
5. **Blockchain Signing**: Sign transaction with organization's private key
6. **Database Storage**: Store credential metadata in database
7. **Response**: Return credential ID, IPFS hash, and blockchain transaction

### Verify Credential Flow
1. **Scope Validation**: Check if organization can verify the credential type
2. **User Lookup**: Find user by DID
3. **Credential Retrieval**: Get latest active credential
4. **Blockchain Verification**: Verify credential on blockchain
5. **Access Logging**: Log the verification attempt
6. **User Notification**: Notify user of credential access
7. **Response**: Return credential data and verification status

## Security Features

### 1. Authentication
- Bearer token authentication using organization API keys
- Session-based authentication for web interface

### 2. Authorization
- Scope-based access control
- Organization-specific credential type permissions
- User verification status checks

### 3. Data Protection
- Private key handling for transaction signing
- Secure credential data storage
- Access logging and audit trails

### 4. Rate Limiting
- 100 requests per minute per organization
- Prevents API abuse and ensures fair usage

## Integration Examples

### Banking System Integration
```javascript
// Example: Bank issuing loan approval credential
async function issueLoanApproval(userDid, loanData, privateKey) {
    const response = await fetch('/organization/api/issue-credential', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer YOUR_API_KEY'
        },
        body: JSON.stringify({
            recipient_did: userDid,
            credential_type: 'loan_approval',
            credential_data: {
                loan_amount: loanData.amount,
                interest_rate: loanData.rate,
                tenure_months: loanData.tenure,
                approval_date: new Date().toISOString().split('T')[0],
                loan_id: loanData.id
            },
            org_private_key: privateKey
        })
    });
    
    return await response.json();
}
```

### Company System Integration
```python
# Example: Company verifying employee credentials
def verify_employee_credentials(user_did):
    url = "http://localhost:8000/organization/api/verify-credential"
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {API_KEY}'
    }
    data = {
        'user_did': user_did,
        'credential_type': 'employment_certificate',
        'purpose': 'background_check'
    }
    
    response = requests.post(url, headers=headers, json=data)
    return response.json()
```

## Testing

### Test Script
- **File**: `test_organization_api_documentation.php`
- **Purpose**: Verify API documentation feature functionality
- **Tests**:
  - Organization data retrieval
  - API credentials validation
  - Scope configuration verification
  - Route accessibility testing
  - Sample request/response structure validation

### Test Results
```
✅ Organization API Documentation Feature Test Completed!
📝 Summary:
   • Organization: Government of India - Test Department
   • API Key: Not generated
   • Write Scopes: 21
   • Read Scopes: 21
   • Routes: Configured
   • Documentation: Ready
```

## Benefits

### 1. For Organizations
- **Easy Integration**: Comprehensive documentation and code examples
- **Secure Operations**: Built-in security features and best practices
- **Scalable**: Rate limiting and efficient API design
- **Compliant**: W3C verifiable credential standards compliance

### 2. For Users
- **Transparent**: Access logging and notifications
- **Secure**: Blockchain-verified credentials
- **Portable**: Credentials can be used across different organizations
- **Privacy-Preserving**: Selective disclosure of credential data

### 3. For the Platform
- **Standardized**: Consistent API design across all endpoints
- **Monitored**: Comprehensive logging and audit trails
- **Extensible**: Easy to add new credential types and organizations
- **Reliable**: Robust error handling and validation

## Future Enhancements

### 1. API Key Management
- Generate API keys for organizations that don't have them
- API key rotation and expiration
- Usage analytics and monitoring

### 2. Enhanced Documentation
- Interactive API playground
- SDK downloads for popular languages
- Video tutorials and webinars

### 3. Advanced Features
- Batch credential operations
- Webhook notifications
- Advanced filtering and search
- Credential revocation endpoints

## Support and Maintenance

### 1. Technical Support
- Email: api-support@sarvone.com
- Phone: +91-XXXXXXXXXX
- Documentation: Comprehensive guides and examples

### 2. Monitoring
- API usage analytics
- Error tracking and alerting
- Performance monitoring
- Security monitoring

### 3. Updates
- Regular security updates
- New credential type additions
- API versioning and backward compatibility
- Feature enhancements based on feedback

## Conclusion

The Organization API Documentation feature provides a complete solution for organizations to integrate SecureVerify into their existing systems. With comprehensive documentation, code examples, and security features, organizations can easily issue and verify verifiable credentials while maintaining the highest standards of security and compliance.

The implementation follows best practices for API design, security, and user experience, making it easy for organizations to adopt and integrate the SecureVerify platform into their workflows. 