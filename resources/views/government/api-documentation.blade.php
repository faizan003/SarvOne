@extends('layouts.app')

@section('title', 'API Documentation - Government Schemes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.approval.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Approval Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">API Documentation</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Government Scheme API Documentation</h1>
                <p class="text-gray-600 mt-2">Integrate government schemes into your existing software systems</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.approval.dashboard') }}" 
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200 text-sm font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Approval Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- API Keys Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-key text-blue-600 mr-2"></i>
            API Keys
        </h2>
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm text-gray-600 mb-3">Use one of these API keys in the <code class="bg-gray-200 px-2 py-1 rounded">X-API-Key</code> header for authentication:</p>
            <div class="space-y-2">
                @forelse($apiKeys as $key)
                    <div class="flex items-center justify-between bg-white p-3 rounded border">
                        <code class="text-sm font-mono text-gray-800">{{ $key }}</code>
                        <button onclick="copyToClipboard('{{ $key }}')" class="text-blue-600 hover:text-blue-700 text-sm">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                @empty
                    <div class="text-red-600 bg-red-50 p-3 rounded border">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        No API keys configured. Please contact the administrator.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Base URL Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-link text-green-600 mr-2"></i>
            Base URL
        </h2>
        <div class="bg-gray-50 rounded-lg p-4">
            <code class="text-lg font-mono text-gray-800">{{ $baseUrl }}</code>
            <button onclick="copyToClipboard('{{ $baseUrl }}')" class="ml-3 text-blue-600 hover:text-blue-700 text-sm">
                <i class="fas fa-copy mr-1"></i>Copy
            </button>
        </div>
    </div>

    <!-- Endpoints Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-code text-purple-600 mr-2"></i>
            API Endpoints
        </h2>

        <!-- Submit Scheme Endpoint -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium mr-3">POST</span>
                <h3 class="text-lg font-medium text-gray-900">Submit New Scheme</h3>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <code class="text-sm font-mono text-gray-800">{{ $baseUrl }}/submit</code>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Request Example -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Request Example</h4>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                        <pre><code>curl -X POST "{{ $baseUrl }}/submit" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY" \
  -d '{
    "scheme_name": "Digital India Scholarship",
    "description": "Scholarship for students pursuing digital technology courses",
    "category": "education",
    "max_income": 800000,
    "min_age": 18,
    "max_age": 25,
    "required_credentials": ["aadhaar_card", "income_certificate"],
    "benefit_amount": 50000,
    "benefit_type": "scholarship",
    "application_deadline": "2024-12-31",
    "organization_name": "Ministry of Electronics and IT",
    "organization_did": "did:gov:india:meity:123",
    "contact_email": "scholarship@meity.gov.in",
    "priority_level": "high"
  }'</code></pre>
                    </div>
                </div>

                <!-- Response Example -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Response Example</h4>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                        <pre><code>{
  "success": true,
  "message": "Scheme submitted successfully",
  "data": {
    "scheme_id": 123,
    "scheme_name": "Digital India Scholarship",
    "status": "active",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "application_deadline": "2024-12-31T00:00:00.000000Z"
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Scheme Endpoint -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium mr-3">PUT</span>
                <h3 class="text-lg font-medium text-gray-900">Update Existing Scheme</h3>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <code class="text-sm font-mono text-gray-800">{{ $baseUrl }}/{scheme_id}</code>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Request Example -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Request Example</h4>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                        <pre><code>curl -X PUT "{{ $baseUrl }}/123" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: YOUR_API_KEY" \
  -d '{
    "benefit_amount": 75000,
    "application_deadline": "2024-12-31",
    "status": "active"
  }'</code></pre>
                    </div>
                </div>

                <!-- Response Example -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Response Example</h4>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                        <pre><code>{
  "success": true,
  "message": "Scheme updated successfully",
  "data": {
    "scheme_id": 123,
    "scheme_name": "Digital India Scholarship",
    "status": "active",
    "updated_at": "2024-01-15T11:30:00.000000Z"
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Get Scheme Status Endpoint -->
        <div class="mb-8">
            <div class="flex items-center mb-4">
                <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium mr-3">GET</span>
                <h3 class="text-lg font-medium text-gray-900">Get Scheme Status</h3>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <code class="text-sm font-mono text-gray-800">{{ $baseUrl }}/{scheme_id}/status</code>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Request Example -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Request Example</h4>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                        <pre><code>curl -X GET "{{ $baseUrl }}/123/status" \
  -H "X-API-Key: YOUR_API_KEY"</code></pre>
                    </div>
                </div>

                <!-- Response Example -->
                <div>
                    <h4 class="font-medium text-gray-900 mb-3">Response Example</h4>
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-x-auto">
                        <pre><code>{
  "success": true,
  "data": {
    "scheme_id": 123,
    "scheme_name": "Digital India Scholarship",
    "status": "active",
    "created_at": "2024-01-15T10:30:00.000000Z",
    "updated_at": "2024-01-15T11:30:00.000000Z",
    "application_deadline": "2024-12-31T00:00:00.000000Z",
    "total_applications": 0,
    "eligibility_checks": 0
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Parameters Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-list text-orange-600 mr-2"></i>
            Request Parameters
        </h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Parameter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Required</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Example</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">scheme_name</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yes</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Name of the government scheme</td>
                        <td class="px-6 py-4 text-sm text-gray-500">"Digital India Scholarship"</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">description</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yes</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Detailed description of the scheme</td>
                        <td class="px-6 py-4 text-sm text-gray-500">"Scholarship for students..."</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">category</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yes</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Scheme category</td>
                        <td class="px-6 py-4 text-sm text-gray-500">"education", "health", "employment"</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">benefit_amount</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">numeric</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yes</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Amount of benefit in INR</td>
                        <td class="px-6 py-4 text-sm text-gray-500">50000</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">benefit_type</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yes</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Type of benefit</td>
                        <td class="px-6 py-4 text-sm text-gray-500">"scholarship", "loan", "subsidy"</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">organization_name</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yes</td>
                        <td class="px-6 py-4 text-sm text-gray-500">Name of the government organization</td>
                        <td class="px-6 py-4 text-sm text-gray-500">"Ministry of Electronics and IT"</td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">organization_did</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">string</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yes</td>
                        <td class="px-6 py-4 text-sm text-gray-500">DID of the organization</td>
                        <td class="px-6 py-4 text-sm text-gray-500">"did:gov:india:meity:123"</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Integration Guide Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-book text-indigo-600 mr-2"></i>
            Integration Guide
        </h2>

        <div class="space-y-6">
            <!-- Step 1 -->
            <div class="border-l-4 border-blue-500 pl-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Step 1: Get Your API Key</h3>
                <p class="text-gray-600 mb-3">Contact the government administrator to get your API key. The key should be included in all API requests in the <code class="bg-gray-200 px-2 py-1 rounded">X-API-Key</code> header.</p>
            </div>

            <!-- Step 2 -->
            <div class="border-l-4 border-green-500 pl-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Step 2: Submit Your First Scheme</h3>
                <p class="text-gray-600 mb-3">Use the POST endpoint to submit a new government scheme. Make sure to include all required fields and validate the response.</p>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="text-sm text-gray-700 mb-2"><strong>Required Headers:</strong></p>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li><code>Content-Type: application/json</code></li>
                        <li><code>X-API-Key: YOUR_API_KEY</code></li>
                    </ul>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="border-l-4 border-purple-500 pl-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Step 3: Monitor Scheme Status</h3>
                <p class="text-gray-600 mb-3">Use the GET endpoint to check the status of your submitted schemes and track their performance.</p>
            </div>

            <!-- Step 4 -->
            <div class="border-l-4 border-orange-500 pl-4">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Step 4: Update Schemes as Needed</h3>
                <p class="text-gray-600 mb-3">Use the PUT endpoint to update scheme details, extend deadlines, or modify benefit amounts.</p>
            </div>
        </div>
    </div>

    <!-- Error Handling Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">
            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
            Error Handling
        </h2>

        <div class="space-y-4">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="font-medium text-red-800 mb-2">401 - Unauthorized</h4>
                <p class="text-red-700 text-sm">Invalid or missing API key</p>
                <div class="bg-red-100 p-3 rounded mt-2">
                    <pre class="text-sm text-red-800"><code>{
  "success": false,
  "message": "Invalid or missing API key",
  "error_code": "INVALID_API_KEY"
}</code></pre>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h4 class="font-medium text-yellow-800 mb-2">422 - Validation Error</h4>
                <p class="text-yellow-700 text-sm">Invalid request data</p>
                <div class="bg-yellow-100 p-3 rounded mt-2">
                    <pre class="text-sm text-yellow-800"><code>{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "scheme_name": ["The scheme name field is required."]
  },
  "error_code": "VALIDATION_ERROR"
}</code></pre>
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h4 class="font-medium text-gray-800 mb-2">404 - Not Found</h4>
                <p class="text-gray-700 text-sm">Scheme not found</p>
                <div class="bg-gray-100 p-3 rounded mt-2">
                    <pre class="text-sm text-gray-800"><code>{
  "success": false,
  "message": "Scheme not found",
  "error_code": "SCHEME_NOT_FOUND"
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Rate Limiting Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-tachometer-alt text-blue-600 mr-2"></i>
            Rate Limiting
        </h2>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 mb-2"><strong>Current Limits:</strong></p>
            <ul class="text-blue-700 text-sm space-y-1">
                <li>• 100 requests per minute per API key</li>
                <li>• 1000 requests per hour per API key</li>
                <li>• 10000 requests per day per API key</li>
            </ul>
            <p class="text-blue-600 text-sm mt-3">Contact the administrator if you need higher limits for your use case.</p>
        </div>
    </div>

    <!-- Support Section -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">
            <i class="fas fa-headset text-green-600 mr-2"></i>
            Support & Contact
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h4 class="font-medium text-green-800 mb-2">Technical Support</h4>
                <p class="text-green-700 text-sm mb-2">For API integration issues and technical questions:</p>
                                        <p class="text-green-600 text-sm">Email: api-support@sarvone.gov.in</p>
                <p class="text-green-600 text-sm">Phone: +91-11-23456789</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="font-medium text-blue-800 mb-2">API Key Requests</h4>
                <p class="text-blue-700 text-sm mb-2">To request a new API key or modify existing permissions:</p>
                                        <p class="text-blue-600 text-sm">Email: api-keys@sarvone.gov.in</p>
                <p class="text-blue-600 text-sm">Include your organization details and use case</p>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
        button.classList.add('text-green-600');
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('text-green-600');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>
@endsection 