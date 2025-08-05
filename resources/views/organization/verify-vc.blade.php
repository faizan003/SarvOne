@extends('layouts.organization')

@section('title', 'Verify Credentials - SarvOne')

@push('styles')
<style>
    .verify-card {
        transition: all 0.3s ease;
    }
    .verify-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .verification-result {
        animation: slideIn 0.5s ease-out;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .scope-option {
        transition: all 0.3s ease;
    }
    .scope-option:hover {
        transform: scale(1.02);
    }
    .scope-option.selected {
        border-color: #10B981;
        background: linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 100%);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-green-600 to-teal-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Verify Credentials</h1>
                    <p class="text-green-100 text-lg">Instantly verify digital credentials using blockchain technology</p>
                </div>
                <div class="hidden md:block">
                    <div class="h-20 w-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-check text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Organization Info -->
            <div class="mt-6 bg-white bg-opacity-10 rounded-xl p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-green-200">Organization:</span>
                        <p class="font-semibold">{{ auth('organization')->user()->legal_name }}</p>
                    </div>
                    <div>
                        <span class="text-green-200">Type:</span>
                        <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', auth('organization')->user()->organization_type)) }}</p>
                    </div>
                    <div>
                        <span class="text-green-200">Access Scopes:</span>
                        <p class="font-semibold">{{ count(auth('organization')->user()->read_scopes ?? []) }} types</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between max-w-4xl mx-auto">
            <div class="flex items-center">
                <div id="step1-indicator" class="step-indicator active w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-bold">1</div>
                <span class="ml-3 text-sm font-medium text-gray-900">Enter User DID</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-4">
                <div id="progress-bar" class="h-full bg-green-600 transition-all duration-500" style="width: 25%"></div>
            </div>
            <div class="flex items-center">
                <div id="step2-indicator" class="step-indicator w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">2</div>
                <span class="ml-3 text-sm font-medium text-gray-500">Select Credential Type</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
            <div class="flex items-center">
                <div id="step3-indicator" class="step-indicator w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">3</div>
                <span class="ml-3 text-sm font-medium text-gray-500">Verify & View Results</span>
            </div>
        </div>
    </div>

    <!-- Step 1: User DID Input -->
    <div id="step1" class="verify-card bg-white rounded-2xl shadow-xl p-8 mb-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Step 1: Enter User DID</h2>
            <p class="text-gray-600">Enter the user's DID or scan their QR code</p>
        </div>

        <!-- Input Methods -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Method 1: QR Code Scanner -->
            <div class="space-y-4">
                <div class="text-center">
                    <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-qrcode text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">QR Code Scanner</h3>
                    <p class="text-gray-600 text-sm">Scan QR code from user's credential</p>
                </div>
                
                <div id="qr-scanner" class="bg-gray-100 rounded-xl p-8 text-center min-h-48 flex items-center justify-center">
                    <div>
                        <i class="fas fa-camera text-gray-400 text-3xl mb-4"></i>
                        <p class="text-gray-500 mb-4">Click to start QR scanner</p>
                        <button onclick="startQRScanner()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-play mr-2"></i>
                            Start Scanner
                        </button>
                    </div>
                </div>
                
                <div class="text-center">
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Allow camera permissions for QR scanning
                    </p>
                </div>
            </div>

            <!-- Method 2: Manual Entry -->
            <div class="space-y-4">
                <div class="text-center">
                    <div class="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-keyboard text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Manual Entry</h3>
                    <p class="text-gray-600 text-sm">Enter user's DID manually</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">User DID</label>
                    <input type="text" id="user-did-input" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200"
                           placeholder="Enter user DID (e.g., did:sarvone:abc123...)">
                </div>
                
                <button onclick="lookupUser()" class="w-full px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-semibold">
                    <i class="fas fa-search mr-2"></i>
                    Lookup User
                </button>
            </div>
        </div>

        <!-- User Details (Hidden initially) -->
        <div id="user-details" class="hidden mt-6 bg-gray-50 rounded-xl p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-sm text-gray-500">Name:</span>
                    <p class="font-semibold" id="user-name">-</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Email:</span>
                    <p class="font-semibold" id="user-email">-</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Phone:</span>
                    <p class="font-semibold" id="user-phone">-</p>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-sm text-gray-500">DID:</span>
                <p class="font-mono text-sm" id="user-did-display">-</p>
            </div>
        </div>

        <!-- Error Message -->
        <div id="lookup-error" class="hidden mt-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                <span id="error-message" class="text-red-800"></span>
            </div>
        </div>

        <!-- Loading State -->
        <div id="lookup-loading" class="hidden mt-4 text-center">
            <div class="inline-flex items-center">
                <i class="fas fa-spinner fa-spin text-green-600 text-xl mr-3"></i>
                <span class="text-gray-600">Looking up user...</span>
            </div>
        </div>
    </div>

    <!-- Step 2: Credential Type Selection -->
    <div id="step2" class="verify-card bg-white rounded-2xl shadow-xl p-8 mb-8 hidden">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Step 2: Select Credential Type</h2>
            <p class="text-gray-600">Choose which credential type you want to verify</p>
        </div>

        <!-- Available Scopes -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Credential Types</h3>
            <div id="scope-options" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Scope options will be loaded here -->
            </div>
        </div>

        <!-- Selected Credential Info -->
        <div id="selected-credential-info" class="hidden bg-blue-50 rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-semibold text-blue-900">Selected Credential</h4>
                    <p id="selected-credential-name" class="text-blue-700">-</p>
                </div>
                <button onclick="proceedToVerification()" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-shield-check mr-2"></i>
                    Verify Credential
                </button>
            </div>
        </div>
    </div>

    <!-- Step 3: Verification Results -->
    <div id="step3" class="verify-card bg-white rounded-2xl shadow-xl p-8 mb-8 hidden">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Step 3: Verification Results</h2>
            <p class="text-gray-600">Credential verification results and details</p>
        </div>

        <!-- Loading State -->
        <div id="verification-loading" class="text-center py-12">
            <div class="inline-flex items-center">
                <i class="fas fa-spinner fa-spin text-green-600 text-2xl mr-3"></i>
                <span class="text-gray-600 text-lg">Verifying credential...</span>
            </div>
        </div>

        <!-- Verification Results -->
        <div id="verification-results" class="hidden">
            <!-- Results will be displayed here -->
        </div>
    </div>

    <!-- Access Logs Table -->
    <div class="mt-12">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Access Logs</h2>
                    <p class="text-gray-600 mt-1">Recent credential access attempts by your organization</p>
                </div>
                <button onclick="loadAccessLogs()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
            
            <!-- Loading State -->
            <div id="logs-loading" class="hidden text-center py-8">
                <div class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin text-green-600 text-xl mr-3"></i>
                    <span class="text-gray-600">Loading access logs...</span>
                </div>
            </div>
            
            <!-- Access Logs Table -->
            <div id="access-logs-table" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credential Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Access Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                        </tr>
                    </thead>
                    <tbody id="access-logs-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- Access logs will be loaded here -->
                    </tbody>
                </table>
                
                <!-- Empty State -->
                <div id="logs-empty" class="hidden text-center py-12">
                    <div class="text-gray-500">
                        <i class="fas fa-history text-4xl mb-4"></i>
                        <p class="text-lg font-medium">No access logs yet</p>
                        <p class="text-sm">Start verifying credentials to see access logs</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="success-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <div class="h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-check text-green-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Verification Complete</h3>
            <p class="text-gray-600 mb-6">The credential has been successfully verified and the user has been notified.</p>
            
            <div class="flex space-x-4">
                <button onclick="closeSuccessModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Close
                </button>
                <button onclick="verifyAnother()" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                    Verify Another
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
let currentStep = 1;
let selectedUser = null;
let selectedCredentialType = null;
let html5QrcodeScanner = null;
let orgReadScopes = @json(auth('organization')->user()->read_scopes ?? []);

// Step 1: User DID Input
async function lookupUser() {
    const didInput = document.getElementById('user-did-input');
    const did = didInput.value.trim();
    
    if (!did) {
        showError('Please enter a DID');
        return;
    }
    
    if (!did.startsWith('did:sarvone:')) {
        showError('Please enter a valid SarvOne DID starting with "did:sarvone:"');
        return;
    }
    
    showLoading();
    hideError();
    hideUserDetails();
    
    try {
        const response = await fetch('/organization/api/lookup-user-by-did', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ did: did })
        });
        
        const data = await response.json();
        
        if (data.success) {
            selectedUser = data.user;
            showUserDetails(data.user);
            loadAvailableScopes();
            goToStep(2);
        } else {
            showError(data.message || 'User not found');
        }
    } catch (error) {
        showError('Error looking up user. Please try again.');
        console.error('User lookup error:', error);
    } finally {
        hideLoading();
    }
}

// QR Code Scanner
function startQRScanner() {
    const qrContainer = document.getElementById('qr-scanner');
    
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }
    
    html5QrcodeScanner = new Html5QrcodeScanner(
        "qr-scanner",
        { fps: 10, qrbox: { width: 250, height: 250 } },
        false
    );
    
    html5QrcodeScanner.render(onQRCodeSuccess, onQRCodeError);
}

function onQRCodeSuccess(decodedText, decodedResult) {
    // Extract DID from QR code
    const did = extractDIDFromQR(decodedText);
    if (did) {
        document.getElementById('user-did-input').value = did;
        lookupUser();
    } else {
        showError('Invalid QR code format. Please scan a valid credential QR code.');
    }
    
    if (html5QrcodeScanner) {
        html5QrcodeScanner.clear();
    }
}

function onQRCodeError(error) {
    // Handle QR scan errors silently
    console.log('QR scan error:', error);
}

function extractDIDFromQR(qrText) {
    // Try to extract DID from various QR code formats
    const didMatch = qrText.match(/did:sarvone:[a-zA-Z0-9]+/);
    if (didMatch) {
        return didMatch[0];
    }
    
    // If QR contains a URL, try to extract DID from it
    if (qrText.includes('did:sarvone:')) {
        const urlMatch = qrText.match(/did:sarvone:[a-zA-Z0-9]+/);
        return urlMatch ? urlMatch[0] : null;
    }
    
    return null;
}

// Step 2: Load Available Scopes
function loadAvailableScopes() {
    const scopeOptions = document.getElementById('scope-options');
    
    if (orgReadScopes.length === 0) {
        scopeOptions.innerHTML = `
            <div class="col-span-full text-center py-8">
                <div class="text-gray-500">
                    <i class="fas fa-lock text-3xl mb-4"></i>
                    <p class="text-lg font-medium">No Access Permissions</p>
                    <p class="text-sm">Your organization doesn't have permission to verify any credentials.</p>
                </div>
            </div>
        `;
        return;
    }
    
    scopeOptions.innerHTML = orgReadScopes.map(scope => `
        <div class="scope-option border-2 border-gray-200 rounded-xl p-4 cursor-pointer hover:border-green-300" 
             onclick="selectCredentialType('${scope}')">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-semibold text-gray-900">${scope.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h4>
                    <p class="text-sm text-gray-600">Verify ${scope.replace(/_/g, ' ')} credentials</p>
                </div>
                <div class="h-6 w-6 border-2 border-gray-300 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-white text-xs hidden"></i>
                </div>
            </div>
        </div>
    `).join('');
}

function selectCredentialType(credentialType) {
    selectedCredentialType = credentialType;
    
    // Update UI
    document.querySelectorAll('.scope-option').forEach(option => {
        option.classList.remove('selected');
    });
    
    event.currentTarget.classList.add('selected');
    
    // Show selected credential info
    document.getElementById('selected-credential-name').textContent = 
        credentialType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById('selected-credential-info').classList.remove('hidden');
}

function proceedToVerification() {
    if (!selectedCredentialType) {
        alert('Please select a credential type to verify');
        return;
    }
    
    goToStep(3);
    performVerification();
}

// Step 3: Perform Verification
async function performVerification() {
    showVerificationLoading();
    
    try {
        // Debug CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        console.log('CSRF Token:', csrfToken);
        
        const requestData = {
            user_did: selectedUser.did,
            credential_type: selectedCredentialType,
            purpose: 'Verification Request'
        };
        
        console.log('Request data:', requestData);
        
        const response = await fetch('/organization/api/verify-credential', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        const data = await response.json();
        console.log('Response data:', data);
        
        if (data.success) {
            displayVerificationResults(data);
            showSuccessModal();
            loadAccessLogs(); // Refresh access logs
        } else {
            displayVerificationError(data.message);
            loadAccessLogs(); // Refresh access logs even for failed attempts
        }
    } catch (error) {
        console.error('Verification error:', error);
        displayVerificationError('Network error: ' + error.message);
    } finally {
        hideVerificationLoading();
    }
}

function displayVerificationResults(data) {
    const resultsDiv = document.getElementById('verification-results');
    
    resultsDiv.innerHTML = `
        <div class="verification-result">
            <div class="bg-green-50 border border-green-200 rounded-xl p-6 mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-600 text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-green-900">Credential Verified Successfully</h3>
                        <p class="text-green-700">The credential has been verified on the blockchain</p>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Credential Details</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Type:</span>
                            <span class="font-medium">${data.credential.vc_type}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Issuer:</span>
                            <span class="font-medium">${data.credential.issuer_name}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Issued Date:</span>
                            <span class="font-medium">${new Date(data.credential.issued_at).toLocaleDateString()}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-medium text-green-600">Active</span>
                        </div>
                        ${data.credential.essential_data && data.credential.essential_data.aadhaar_number ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">Aadhaar Number:</span>
                            <span class="font-medium font-mono">${data.credential.essential_data.aadhaar_number}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-xl p-6">
                    <h4 class="font-semibold text-gray-900 mb-4">Blockchain Verification</h4>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Transaction Hash:</span>
                            <a href="https://amoy.polygonscan.com/tx/${data.credential.blockchain_tx_hash || '#'}" 
                               target="_blank" class="text-blue-600 hover:text-blue-800 font-mono text-xs">
                                ${data.credential.blockchain_tx_hash ? data.credential.blockchain_tx_hash.substring(0, 10) + '...' : 'Not available'}
                            </a>
                        </div>
                        ${data.credential.vc_type !== 'aadhaar_card' ? `
                        <div class="flex justify-between">
                            <span class="text-gray-600">IPFS CID:</span>
                            <a href="https://ipfs.io/ipfs/${data.credential.ipfs_hash || '#'}" 
                               target="_blank" class="text-blue-600 hover:text-blue-800 font-mono text-xs">
                                ${data.credential.ipfs_hash ? data.credential.ipfs_hash.substring(0, 10) + '...' : 'Not available'}
                            </a>
                        </div>
                        ` : ''}
                        <div class="flex justify-between">
                            <span class="text-gray-600">Verification Time:</span>
                            <span class="font-medium">${new Date().toLocaleTimeString()}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-center">
                    <i class="fas fa-bell text-blue-600 mr-3"></i>
                    <div>
                        <p class="text-blue-900 font-medium">User Notification Sent</p>
                        <p class="text-blue-700 text-sm">The user has been notified about this verification access</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    resultsDiv.classList.remove('hidden');
}

function displayVerificationError(message) {
    const resultsDiv = document.getElementById('verification-results');
    
    resultsDiv.innerHTML = `
        <div class="verification-result">
            <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-2xl mr-3"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-red-900">Verification Failed</h3>
                        <p class="text-red-700">${message}</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    resultsDiv.classList.remove('hidden');
}

// Access Logs
async function loadAccessLogs() {
    showLogsLoading();
    hideLogsError();
    hideLogsEmpty();
    
    try {
        const response = await fetch('/organization/api/access-logs', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayAccessLogs(data.logs);
        } else {
            showLogsError(data.message || 'Failed to load access logs');
        }
    } catch (error) {
        console.error('Error loading access logs:', error);
        showLogsError('Network error: ' + error.message);
    } finally {
        hideLogsLoading();
    }
}

function displayAccessLogs(logs) {
    const tbody = document.getElementById('access-logs-tbody');
    const emptyDiv = document.getElementById('logs-empty');
    const tableDiv = document.getElementById('access-logs-table');
    
    if (!logs || logs.length === 0) {
        tableDiv.classList.add('hidden');
        emptyDiv.classList.remove('hidden');
        return;
    }
    
    tableDiv.classList.remove('hidden');
    emptyDiv.classList.add('hidden');
    
    tbody.innerHTML = logs.map(log => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${log.user_name || 'Unknown'}</div>
                        <div class="text-sm text-gray-500 font-mono text-xs">${log.user_did}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${log.credential_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${new Date(log.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                    log.status === 'success' ? 'bg-green-100 text-green-800' :
                    log.status === 'failed' ? 'bg-red-100 text-red-800' :
                    'bg-yellow-100 text-yellow-800'
                }">
                    ${log.status.charAt(0).toUpperCase() + log.status.slice(1)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${log.purpose || 'Verification'}
            </td>
        </tr>
    `).join('');
}

// Utility Functions
function showLoading() {
    document.getElementById('lookup-loading').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('lookup-loading').classList.add('hidden');
}

function showError(message) {
    document.getElementById('error-message').textContent = message;
    document.getElementById('lookup-error').classList.remove('hidden');
}

function hideError() {
    document.getElementById('lookup-error').classList.add('hidden');
}

function showUserDetails(user) {
    document.getElementById('user-name').textContent = user.name || '-';
    document.getElementById('user-email').textContent = user.email || '-';
    document.getElementById('user-phone').textContent = user.phone || '-';
    document.getElementById('user-did-display').textContent = user.did || '-';
    document.getElementById('user-details').classList.remove('hidden');
}

function hideUserDetails() {
    document.getElementById('user-details').classList.add('hidden');
}

function showVerificationLoading() {
    document.getElementById('verification-loading').classList.remove('hidden');
    document.getElementById('verification-results').classList.add('hidden');
}

function hideVerificationLoading() {
    document.getElementById('verification-loading').classList.add('hidden');
}

function showLogsLoading() {
    document.getElementById('logs-loading').classList.remove('hidden');
}

function hideLogsLoading() {
    document.getElementById('logs-loading').classList.add('hidden');
}

function showLogsError(message) {
    // Add error display for logs if needed
}

function hideLogsError() {
    // Hide error display for logs if needed
}

function showLogsEmpty() {
    document.getElementById('logs-empty').classList.remove('hidden');
}

function hideLogsEmpty() {
    document.getElementById('logs-empty').classList.add('hidden');
}

function showSuccessModal() {
    document.getElementById('success-modal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('success-modal').classList.add('hidden');
}

function verifyAnother() {
    closeSuccessModal();
    resetForm();
    goToStep(1);
}

function resetForm() {
    selectedUser = null;
    selectedCredentialType = null;
    document.getElementById('user-did-input').value = '';
    hideUserDetails();
    hideError();
    document.getElementById('selected-credential-info').classList.add('hidden');
    document.getElementById('verification-results').classList.add('hidden');
}

function goToStep(step) {
    currentStep = step;
    
    // Hide all steps
    document.getElementById('step1').classList.add('hidden');
    document.getElementById('step2').classList.add('hidden');
    document.getElementById('step3').classList.add('hidden');
    
    // Show current step
    document.getElementById(`step${step}`).classList.remove('hidden');
    
    // Update step indicators
    updateStepIndicators(step);
    updateProgressBar(step);
}

function updateStepIndicators(step) {
    // Reset all indicators
    for (let i = 1; i <= 3; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        indicator.classList.remove('active', 'completed');
        indicator.classList.add('bg-gray-300', 'text-gray-600');
    }
    
    // Set current and completed indicators
    for (let i = 1; i <= step; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        if (i === step) {
            indicator.classList.add('active', 'bg-green-600', 'text-white');
        } else {
            indicator.classList.add('completed', 'bg-green-600', 'text-white');
        }
    }
}

function updateProgressBar(step) {
    const progress = (step / 3) * 100;
    document.getElementById('progress-bar').style.width = progress + '%';
}

// Load access logs when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadAccessLogs();
});
</script>
@endpush 