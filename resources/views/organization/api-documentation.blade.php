@extends('layouts.organization')

@section('title', 'API Documentation - SarvOne Organization Portal')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('organization.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
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
                <h1 class="text-3xl font-bold text-gray-900">Organization API Documentation</h1>
                <p class="text-gray-600 mt-2">Integrate SarvOne into your existing systems for issuing and verifying credentials</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('organization.dashboard') }}"
                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition duration-200 text-sm font-medium flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Organization Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Organization Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <p class="text-sm font-medium text-gray-600">Organization Name</p>
                <p class="text-lg font-semibold text-gray-900">{{ $organization->legal_name }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">Organization Type</p>
                <p class="text-lg font-semibold text-gray-900">{{ ucfirst(str_replace('_', ' ', $organization->organization_type)) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-600">DID</p>
                <p class="text-sm font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">{{ $organization->did }}</p>
            </div>
        </div>
    </div>

    <!-- API Credentials -->
    <div id="api-credentials" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">API Credentials</h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Base URL</label>
                <div class="flex items-center space-x-2">
                    <input type="text" value="{{ $baseUrl }}" readonly 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-mono text-sm">
                    <button onclick="copyToClipboard('{{ $baseUrl }}')" 
                            class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">API Key</label>
                <div class="flex items-center space-x-2">
                    <input type="text" value="{{ $apiKey }}" readonly 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-900 font-mono text-sm">
                    <button onclick="copyToClipboard('{{ $apiKey }}')" 
                            class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                        <i class="fas fa-copy"></i>
                    </button>
                    @if($organization->verification_status === 'approved')
                    <button onclick="regenerateApiKey()" 
                            class="px-3 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition duration-200" 
                            title="Regenerate API Key">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    @endif
                </div>
                <p class="text-xs text-gray-500 mt-1">Include this in the Authorization header: Bearer YOUR_API_KEY</p>
            </div>
        </div>
    </div>

    <!-- Authorized Scopes -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Authorized Scopes</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Can Issue Credentials ({{ count($writeScopes) }})</h3>
                <div class="space-y-2">
                    @foreach($writeScopes as $scope)
                        <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                            <span class="text-sm font-medium text-blue-900">{{ str_replace('_', ' ', $scope) }}</span>
                            <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded">ISSUE</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Can Verify Credentials ({{ count($readScopes) }})</h3>
                <div class="space-y-2">
                    @foreach($readScopes as $scope)
                        <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                            <span class="text-sm font-medium text-green-900">{{ str_replace('_', ' ', $scope) }}</span>
                            <span class="text-xs text-green-600 bg-green-100 px-2 py-1 rounded">VERIFY</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- API Endpoints -->
    <div class="space-y-8">
        <!-- Issue Credential Endpoint -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Issue Credential</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    POST
                </span>
            </div>
            <p class="text-gray-600 mb-4">Issue a new verifiable credential to a user. This endpoint handles the complete flow including IPFS upload, blockchain signing, and credential storage.</p>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Endpoint</p>
                <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $baseUrl }}/issue-credential</code>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Headers</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">Content-Type: application/json
Authorization: Bearer {{ $apiKey }}</pre>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Request Body</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">{
  "recipient_did": "did:sarvone:z6Mk...",
  "credential_type": "loan_approval",
  "credential_data": {
    "loan_amount": 500000,
    "interest_rate": 8.5,
    "tenure_months": 60,
    "approval_date": "2024-01-15",
    "loan_id": "LOAN123456"
  },
  "org_private_key": "0x1234567890abcdef..."
}</pre>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Response</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">{
  "success": true,
  "message": "Credential issued successfully",
  "data": {
    "vc_id": "vc_123456",
    "ipfs_hash": "QmX...",
    "blockchain_tx": "0xabc...",
    "credential_url": "https://ipfs.io/ipfs/QmX..."
  }
}</pre>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">cURL Example</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">curl -X POST "{{ $baseUrl }}/issue-credential" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {{ $apiKey }}" \
  -d '{
    "recipient_did": "did:sarvone:z6Mk...",
    "credential_type": "loan_approval",
    "credential_data": {
      "loan_amount": 500000,
      "interest_rate": 8.5,
      "tenure_months": 60,
      "approval_date": "2024-01-15",
      "loan_id": "LOAN123456"
    },
    "org_private_key": "0x1234567890abcdef..."
  }'</pre>
                </div>
            </div>
        </div>

        <!-- Verify Credential Endpoint -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Verify Credential</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    POST
                </span>
            </div>
            <p class="text-gray-600 mb-4">Verify the authenticity of a user's credential. This endpoint checks both the database and blockchain for verification.</p>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Endpoint</p>
                <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $baseUrl }}/verify-credential</code>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Headers</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">Content-Type: application/json
Authorization: Bearer {{ $apiKey }}</pre>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Request Body</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">{
  "user_did": "did:sarvone:z6Mk...",
  "credential_type": "income_proof",
  "purpose": "loan_application"
}</pre>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Response</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">{
  "success": true,
  "message": "Credential verified successfully",
  "credential": {
    "id": 123,
    "vc_type": "income_proof",
    "subject_did": "did:sarvone:z6Mk...",
    "credential_data": {
      "annual_income": 750000,
      "employer": "Tech Corp",
      "position": "Software Engineer"
    },
    "status": "active",
    "created_at": "2024-01-15T10:30:00Z"
  },
  "blockchain_verified": true
}</pre>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">cURL Example</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">curl -X POST "{{ $baseUrl }}/verify-credential" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {{ $apiKey }}" \
  -d '{
    "user_did": "did:sarvone:z6Mk...",
    "credential_type": "income_proof",
    "purpose": "loan_application"
  }'</pre>
                </div>
            </div>
        </div>

        <!-- Lookup User Endpoint -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Lookup User by DID</h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    POST
                </span>
            </div>
            <p class="text-gray-600 mb-4">Look up a user by their DID to get basic information and available credentials.</p>
            
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Endpoint</p>
                <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $baseUrl }}/lookup-user-by-did</code>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Request Body</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">{
  "user_did": "did:sarvone:z6Mk..."
}</pre>
                </div>
            </div>

            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Response</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">{
  "success": true,
  "user": {
    "name": "John Doe",
    "did": "did:sarvone:z6Mk...",
    "is_verified": true,
    "available_credentials": [
      "aadhaar_card",
      "income_proof",
      "employment_certificate"
    ]
  }
}</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Integration Guide -->
    <div id="integration-guide" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Integration Guide</h2>
        
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">1. Authentication</h3>
                <p class="text-gray-600 mb-2">All API requests require authentication using your API key in the Authorization header:</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">Authorization: Bearer YOUR_API_KEY</pre>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">2. Credential Types</h3>
                <p class="text-gray-600 mb-2">Your organization can issue and verify the following credential types:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Can Issue:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            @foreach($writeScopes as $scope)
                                <li>• {{ str_replace('_', ' ', $scope) }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Can Verify:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            @foreach($readScopes as $scope)
                                <li>• {{ str_replace('_', ' ', $scope) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">3. Complete Flow for Issuing Credentials</h3>
                <div class="space-y-3">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-blue-600">1</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Prepare Credential Data</p>
                            <p class="text-sm text-gray-600">Structure your credential data according to the credential type requirements.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-blue-600">2</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Get User's DID</p>
                            <p class="text-sm text-gray-600">Obtain the user's DID (Decentralized Identifier) from your user management system.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-blue-600">3</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Call Issue API</p>
                            <p class="text-sm text-gray-600">Send POST request to /issue-credential with recipient DID, credential type, data, and your private key.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-blue-600">4</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Automatic Processing</p>
                            <p class="text-sm text-gray-600">The system automatically handles IPFS upload, blockchain signing, and credential storage.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-blue-600">5</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Receive Response</p>
                            <p class="text-sm text-gray-600">Get the credential ID, IPFS hash, and blockchain transaction details.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">4. Complete Flow for Verifying Credentials</h3>
                <div class="space-y-3">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-green-600">1</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Get User's DID</p>
                            <p class="text-sm text-gray-600">Obtain the user's DID from your user management system.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-green-600">2</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Call Verify API</p>
                            <p class="text-sm text-gray-600">Send POST request to /verify-credential with user DID and credential type.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-green-600">3</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Automatic Verification</p>
                            <p class="text-sm text-gray-600">The system checks both database and blockchain for credential authenticity.</p>
                        </div>
                    </div>
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                            <span class="text-xs font-medium text-green-600">4</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Receive Credential Data</p>
                            <p class="text-sm text-gray-600">Get the verified credential data and blockchain verification status.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">5. Error Handling</h3>
                <p class="text-gray-600 mb-2">All API responses include a success flag and detailed error messages:</p>
                <div class="bg-gray-50 p-3 rounded-lg">
                    <pre class="text-sm text-gray-800">{
  "success": false,
  "message": "Error description",
  "errors": {
    "field": ["validation error"]
  }
}</pre>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">6. Rate Limiting</h3>
                <p class="text-gray-600">API requests are limited to 100 requests per minute per organization. Exceeding this limit will result in a 429 status code.</p>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">7. Security Best Practices</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Store your API key securely and never expose it in client-side code</li>
                    <li>• Use HTTPS for all API communications</li>
                    <li>• Validate all input data before sending to the API</li>
                    <li>• Implement proper error handling in your integration</li>
                    <li>• Monitor API usage and implement appropriate logging</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Code Examples -->
    <div id="code-examples" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Code Examples</h2>
        
        <div class="space-y-6">
            <!-- JavaScript Example -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">JavaScript/Node.js</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <pre class="text-sm text-gray-800"><code>// Issue a credential
async function issueCredential(recipientDid, credentialType, credentialData, privateKey) {
  const response = await fetch('{{ $baseUrl }}/issue-credential', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer {{ $apiKey }}'
    },
    body: JSON.stringify({
      recipient_did: recipientDid,
      credential_type: credentialType,
      credential_data: credentialData,
      org_private_key: privateKey
    })
  });
  
  return await response.json();
}

// Verify a credential
async function verifyCredential(userDid, credentialType) {
  const response = await fetch('{{ $baseUrl }}/verify-credential', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer {{ $apiKey }}'
    },
    body: JSON.stringify({
      user_did: userDid,
      credential_type: credentialType,
      purpose: 'verification'
    })
  });
  
  return await response.json();
}</code></pre>
                </div>
            </div>

            <!-- Python Example -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">Python</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <pre class="text-sm text-gray-800"><code>import requests
import json

# Issue a credential
def issue_credential(recipient_did, credential_type, credential_data, private_key):
    url = "{{ $baseUrl }}/issue-credential"
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {{ $apiKey }}'
    }
    data = {
        'recipient_did': recipient_did,
        'credential_type': credential_type,
        'credential_data': credential_data,
        'org_private_key': private_key
    }
    
    response = requests.post(url, headers=headers, json=data)
    return response.json()

# Verify a credential
def verify_credential(user_did, credential_type):
    url = "{{ $baseUrl }}/verify-credential"
    headers = {
        'Content-Type': 'application/json',
        'Authorization': f'Bearer {{ $apiKey }}'
    }
    data = {
        'user_did': user_did,
        'credential_type': credential_type,
        'purpose': 'verification'
    }
    
    response = requests.post(url, headers=headers, json=data)
    return response.json()</code></pre>
                </div>
            </div>

            <!-- PHP Example -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-3">PHP</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <pre class="text-sm text-gray-800"><code>// Issue a credential
function issueCredential($recipientDid, $credentialType, $credentialData, $privateKey) {
    $url = "{{ $baseUrl }}/issue-credential";
    $data = [
        'recipient_did' => $recipientDid,
        'credential_type' => $credentialType,
        'credential_data' => $credentialData,
        'org_private_key' => $privateKey
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer {{ $apiKey }}'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Verify a credential
function verifyCredential($userDid, $credentialType) {
    $url = "{{ $baseUrl }}/verify-credential";
    $data = [
        'user_did' => $userDid,
        'credential_type' => $credentialType,
        'purpose' => 'verification'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer {{ $apiKey }}'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Support -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Support & Contact</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Technical Support</h3>
                <p class="text-sm text-gray-600 mb-2">For technical issues and API integration support:</p>
                <p class="text-sm text-gray-900">Email: api-support@sarvone.com</p>
                <p class="text-sm text-gray-900">Phone: +91-XXXXXXXXXX</p>
            </div>
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Documentation</h3>
                <p class="text-sm text-gray-600 mb-2">Additional resources:</p>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• <a href="#" class="text-blue-600 hover:text-blue-800">API Reference Guide</a></li>
                    <li>• <a href="#" class="text-blue-600 hover:text-blue-800">SDK Downloads</a></li>
                    <li>• <a href="#" class="text-blue-600 hover:text-blue-800">Integration Examples</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show success message
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        button.classList.add('bg-green-600');
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}

function regenerateApiKey() {
    if (!confirm('Are you sure you want to regenerate your API key? This will invalidate the current key and you will need to update your integrations.')) {
        return;
    }
    
    fetch('{{ route("organization.regenerate-api-key") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update the API key input field
            const apiKeyInput = document.querySelector('input[value="{{ $apiKey }}"]');
            if (apiKeyInput) {
                apiKeyInput.value = data.api_key;
            }
            
            // Show success message
            alert('API key regenerated successfully! Please update your integrations with the new key.');
            
            // Reload the page to show the new key
            location.reload();
        } else {
            alert('Failed to regenerate API key: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to regenerate API key. Please try again.');
    });
}
</script>
@endsection 