# Government Scheme API Documentation

## Overview

The Government Scheme API Documentation page provides a comprehensive interface for government organizations to access API keys, understand how to integrate with the SecureVerify platform, and manage their government schemes programmatically.

## Features Implemented

### 1. API Documentation Page (`/government/api-documentation`)

**Location**: `resources/views/government/api-documentation.blade.php`

**Features**:
- **API Keys Display**: Shows all configured API keys with copy-to-clipboard functionality
- **Base URL Information**: Displays the API base URL for integration
- **Endpoint Documentation**: Complete documentation for all API endpoints
- **Request/Response Examples**: Real-world examples for each endpoint
- **Parameter Reference**: Detailed table of all request parameters
- **Integration Guide**: Step-by-step integration instructions
- **Error Handling**: Common error codes and responses
- **Rate Limiting**: Information about API usage limits
- **Support Information**: Contact details for technical support

### 2. API Endpoints Documented

#### POST `/api/government-schemes/submit`
- **Purpose**: Submit a new government scheme
- **Authentication**: Requires `X-API-Key` header
- **Request Body**: JSON with scheme details
- **Response**: Success confirmation with scheme ID

#### PUT `/api/government-schemes/{scheme_id}`
- **Purpose**: Update an existing scheme
- **Authentication**: Requires `X-API-Key` header
- **Request Body**: JSON with fields to update
- **Response**: Success confirmation with updated details

#### GET `/api/government-schemes/{scheme_id}/status`
- **Purpose**: Get scheme status and statistics
- **Authentication**: Requires `X-API-Key` header
- **Response**: Scheme status, metrics, and metadata

### 3. Controller Implementation

**File**: `app/Http/Controllers/GovernmentController.php`

**New Method**: `apiDocumentation()`
- Retrieves API keys from environment variables
- Generates base URL for API endpoints
- Passes data to the documentation view

### 4. Route Configuration

**File**: `routes/web.php`

**New Route**: 
```php
Route::get('/api-documentation', [GovernmentController::class, 'apiDocumentation'])
    ->name('government.api-documentation');
```

### 5. Navigation Integration

**Dashboard Integration**: Added API Documentation link to government dashboard
**Schemes Page Integration**: Added API Docs button to schemes management page

## API Key Management

### Configuration
API keys are configured in the `.env` file:
```env
GOVERNMENT_API_KEYS=gov_api_key_123456,uidai_api_key_789012,ministry_api_key_345678
```

### Validation
The API controller validates keys using the `validateApiKey()` method:
```php
private function validateApiKey(?string $apiKey): bool
{
    if (!$apiKey) {
        return false;
    }

    $validApiKeys = explode(',', env('GOVERNMENT_API_KEYS', ''));
    $validApiKeys = array_map('trim', $validApiKeys);
    $validApiKeys = array_filter($validApiKeys);

    return in_array($apiKey, $validApiKeys);
}
```

## Request Parameters

### Required Parameters
- `scheme_name` (string): Name of the government scheme
- `description` (string): Detailed description
- `category` (string): Scheme category (education, health, employment, etc.)
- `benefit_amount` (numeric): Amount of benefit in INR
- `benefit_type` (string): Type of benefit (scholarship, loan, subsidy, etc.)
- `application_deadline` (date): Application deadline
- `organization_name` (string): Government organization name
- `organization_did` (string): DID of the organization

### Optional Parameters
- `max_income` (numeric): Maximum income criteria
- `min_age` / `max_age` (integer): Age criteria
- `required_credentials` (array): Required verifiable credentials
- `caste_criteria` (array): Caste-based criteria
- `education_criteria` (array): Education-based criteria
- `employment_criteria` (array): Employment-based criteria
- `contact_email` (email): Contact email
- `contact_phone` (string): Contact phone
- `website_url` (url): Organization website
- `application_url` (url): Application portal URL
- `documents_required` (array): Required documents
- `additional_info` (string): Additional information
- `priority_level` (string): Priority level (low, medium, high, urgent)
- `target_audience` (string): Target audience description
- `implementation_phase` (string): Implementation phase

## Error Handling

### Common Error Codes
- **401 Unauthorized**: Invalid or missing API key
- **422 Validation Error**: Invalid request data
- **404 Not Found**: Scheme not found
- **500 Internal Server Error**: Server-side error

### Error Response Format
```json
{
  "success": false,
  "message": "Error description",
  "error_code": "ERROR_CODE",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

## Rate Limiting

### Current Limits
- 100 requests per minute per API key
- 1000 requests per hour per API key
- 10000 requests per day per API key

## Integration Examples

### JavaScript/Node.js Example
```javascript
const axios = require('axios');

const apiKey = 'your_api_key_here';
const baseUrl = 'http://localhost:8000/api/government-schemes';

// Submit a new scheme
const submitScheme = async (schemeData) => {
  try {
    const response = await axios.post(`${baseUrl}/submit`, schemeData, {
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': apiKey
      }
    });
    return response.data;
  } catch (error) {
    console.error('Error submitting scheme:', error.response.data);
  }
};
```

### Python Example
```python
import requests

api_key = 'your_api_key_here'
base_url = 'http://localhost:8000/api/government-schemes'

# Submit a new scheme
def submit_scheme(scheme_data):
    headers = {
        'Content-Type': 'application/json',
        'X-API-Key': api_key
    }
    
    response = requests.post(f'{base_url}/submit', 
                           json=scheme_data, 
                           headers=headers)
    
    if response.status_code == 201:
        return response.json()
    else:
        print(f'Error: {response.status_code} - {response.text}')
```

### PHP Example
```php
$apiKey = 'your_api_key_here';
$baseUrl = 'http://localhost:8000/api/government-schemes';

// Submit a new scheme
function submitScheme($schemeData) {
    global $apiKey, $baseUrl;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . '/submit');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($schemeData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-API-Key: ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201) {
        return json_decode($response, true);
    } else {
        echo "Error: $httpCode - $response";
    }
}
```

## Testing

### Test Script
A test script is provided at `test_api_documentation.php` to verify:
- API key configuration
- Base URL generation
- Route accessibility
- API key validation
- Sample API requests

Run the test:
```bash
php test_api_documentation.php
```

### Manual Testing
1. Start the Laravel server: `php artisan serve`
2. Visit: `http://localhost:8000/government/api-documentation`
3. Verify all sections are displayed correctly
4. Test copy-to-clipboard functionality
5. Verify API keys are displayed (if configured)

## Security Considerations

### API Key Security
- API keys are stored in environment variables
- Keys are validated on every request
- Invalid keys return 401 Unauthorized
- Keys should be rotated regularly

### Request Validation
- All requests are validated using Laravel's validation system
- Required fields are enforced
- Data types are validated
- Input sanitization is applied

### Rate Limiting
- Prevents abuse of the API
- Configurable limits per API key
- Helps maintain system performance

## Support and Contact

### Technical Support
- Email: api-support@secureverify.gov.in
- Phone: +91-11-23456789

### API Key Requests
- Email: api-keys@secureverify.gov.in
- Include organization details and use case

## Future Enhancements

### Planned Features
1. **API Key Management Interface**: Web interface to manage API keys
2. **Usage Analytics**: Track API usage and performance
3. **Webhook Support**: Real-time notifications for scheme updates
4. **Bulk Operations**: Submit multiple schemes at once
5. **Advanced Filtering**: Filter schemes by various criteria
6. **Export Functionality**: Export scheme data in various formats

### API Versioning
- Current version: v1
- Future versions will maintain backward compatibility
- Version deprecation notices will be provided in advance

## Conclusion

The Government Scheme API Documentation provides a comprehensive solution for government organizations to integrate with the SecureVerify platform. The documentation is user-friendly, includes practical examples, and covers all aspects of API integration from authentication to error handling.

The implementation follows Laravel best practices, includes proper security measures, and provides a solid foundation for future enhancements. 