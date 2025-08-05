@extends('layouts.organization')

@section('title', 'Issue Verifiable Credentials - SarvOne')

@push('styles')
<style>
    .credential-card {
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .credential-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .credential-card.selected {
        border-color: #3B82F6;
        background: linear-gradient(135deg, #EBF4FF 0%, #F0F9FF 100%);
    }
    .step-indicator {
        transition: all 0.3s ease;
    }
    .step-indicator.active {
        background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
        transform: scale(1.1);
    }
    .step-indicator.completed {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
    }
    .form-section {
        transition: all 0.5s ease;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
    }
    .form-section.active {
        max-height: 2000px;
        opacity: 1;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header Section -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Issue Verifiable Credentials</h1>
                    <p class="text-blue-100 text-lg">Create secure, blockchain-verified digital credentials</p>
                </div>
                <div class="hidden md:block">
                    <div class="h-20 w-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-certificate text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <!-- Organization Info -->
            <div class="mt-6 bg-white bg-opacity-10 rounded-xl p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-blue-200">Organization:</span>
                        <p class="font-semibold">{{ auth('organization')->user()->legal_name }}</p>
                    </div>
                    <div>
                        <span class="text-blue-200">Type:</span>
                        <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', auth('organization')->user()->organization_type)) }}</p>
                    </div>
                    <div>
                        <span class="text-blue-200">SarvOne DID:</span>
                        <p class="font-semibold font-mono text-xs break-all">{{ auth('organization')->user()->did ?? 'Not Available' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step Indicator -->
    <div class="mb-8">
        <div class="flex items-center justify-between max-w-3xl mx-auto">
            <div class="flex items-center">
                <div id="step1-indicator" class="step-indicator active w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">1</div>
                <span class="ml-3 text-sm font-medium text-gray-900">Find User</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-4">
                <div id="progress-bar" class="h-full bg-blue-600 transition-all duration-500" style="width: 25%"></div>
            </div>
            <div class="flex items-center">
                <div id="step2-indicator" class="step-indicator w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">2</div>
                <span class="ml-3 text-sm font-medium text-gray-500">Choose Credential</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
            <div class="flex items-center">
                <div id="step3-indicator" class="step-indicator w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">3</div>
                <span class="ml-3 text-sm font-medium text-gray-500">Enter Details</span>
            </div>
            <div class="flex-1 h-1 bg-gray-200 mx-4"></div>
            <div class="flex items-center">
                <div id="step4-indicator" class="step-indicator w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold">4</div>
                <span class="ml-3 text-sm font-medium text-gray-500">Issue & Sign</span>
            </div>
        </div>
    </div>

    <!-- Step 1: User DID Lookup -->
    <div id="step1-content" class="form-section active">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Find User by DID</h2>
                <p class="text-gray-600">Enter the user's SarvOne DID to auto-fill their details</p>
            </div>
            
            <div class="max-w-lg mx-auto space-y-6">
                <div>
                    <label for="user-did" class="block text-sm font-medium text-gray-700 mb-2">
                        User DID <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="user-did" name="user_did" 
                               class="w-full px-4 py-3 pr-20 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors font-mono text-sm"
                               placeholder="did:sarvone:user:example..." required>
                        <button type="button" id="lookup-user-btn" onclick="lookupUser()" 
                                class="absolute right-3 top-3 bg-blue-600 text-white px-4 py-1.5 rounded-lg text-sm hover:bg-blue-700 transition-colors">
                            <i class="fas fa-search mr-1"></i>Lookup
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Enter the complete DID starting with "did:sarvone:"</p>
                </div>

                <!-- Loading State -->
                <div id="lookup-loading" class="hidden text-center py-4">
                    <div class="inline-flex items-center">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mr-3"></div>
                        <span class="text-gray-600">Looking up user...</span>
                    </div>
                </div>

                <!-- Error State -->
                <div id="lookup-error" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        <span class="text-red-700" id="error-message">User not found</span>
                    </div>
                </div>

                <!-- User Found - Auto-filled Details -->
                <div id="user-details" class="hidden space-y-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            <span class="text-green-700 font-medium">User Found!</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Full Name:</span>
                                <p class="font-semibold text-gray-900" id="user-name">-</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Email:</span>
                                <p class="font-semibold text-gray-900" id="user-email">-</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Phone:</span>
                                <p class="font-semibold text-gray-900" id="user-phone">-</p>
                            </div>
                            <div>
                                <span class="text-gray-600">Verification Status:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800" id="user-status">
                                    <i class="fas fa-shield-check mr-1"></i>Verified
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end mt-8">
                <button type="button" id="next-step1" onclick="goToStep(2)" disabled
                        class="bg-gray-400 text-white px-8 py-3 rounded-xl cursor-not-allowed transition-colors font-medium">
                    Select Credential Type
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 2: Credential Type Selection -->
    <div id="step2-content" class="form-section">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Select Credential Type</h2>
                <p class="text-gray-600">Choose what type of credential to issue based on your organization's authorized scopes</p>
            </div>
            
            <!-- Available Credential Types -->
            <div id="available-credentials" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Credentials will be dynamically loaded based on organization write scopes -->
            </div>
            
            <div class="mt-8 flex justify-between">
                <button type="button" onclick="goToStep(1)" 
                        class="bg-gray-500 text-white px-6 py-3 rounded-xl hover:bg-gray-600 transition-colors font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </button>
                <button type="button" id="next-step2" onclick="goToStep(3)" disabled
                        class="bg-gray-400 text-white px-8 py-3 rounded-xl cursor-not-allowed transition-colors font-medium">
                    Enter Details
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 3: Credential Details Form -->
    <div id="step3-content" class="form-section">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Enter Credential Details</h2>
                <p class="text-gray-600">Fill in the required information for the credential</p>
            </div>
            
            <form id="credential-form" class="space-y-6">
                <!-- Recipient Information (Auto-filled from DID lookup) -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-blue-600"></i>
                        Recipient Information
                        <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Auto-filled</span>
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">User DID</label>
                            <input type="text" id="form-user-did" readonly 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-100 font-mono text-sm text-gray-700"
                                   placeholder="Will be filled from Step 1">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" id="form-recipient-name" readonly 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-100 text-gray-700"
                                   placeholder="Will be filled from Step 1">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" id="form-recipient-email" readonly 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-100 text-gray-700"
                                   placeholder="Will be filled from Step 1">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="form-recipient-phone" readonly 
                                   class="w-full px-4 py-3 border border-gray-200 rounded-lg bg-gray-100 text-gray-700"
                                   placeholder="Will be filled from Step 1">
                        </div>
                    </div>
                </div>
                
                <!-- Dynamic Credential Fields -->
                <div id="credential-fields" class="bg-blue-50 rounded-xl p-6">
                    <!-- Fields will be dynamically populated based on selected credential type -->
                </div>
                
                <!-- Validity & Expiration -->
                <div class="bg-green-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-green-600"></i>
                        Validity & Expiration
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Issue Date *</label>
                            <input type="date" id="issue-date" required value="{{ date('Y-m-d') }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Expiration Date</label>
                            <select id="expiration-type" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                                <option value="never">Never Expires</option>
                                <option value="1year">1 Year</option>
                                <option value="2years">2 Years</option>
                                <option value="5years">5 Years</option>
                                <option value="custom">Custom Date</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="custom-expiry" class="hidden mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Custom Expiration Date</label>
                        <input type="date" id="custom-expiry-date" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors duration-200">
                    </div>
                </div>
            </form>
            
            <div class="mt-8 flex justify-between">
                <button type="button" onclick="goToStep(2)" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </button>
                <button type="button" id="next-step3" onclick="goToStep(4)" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200">
                    Review & Issue
                    <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Step 4: Review & Issue -->
    <div id="step4-content" class="form-section">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Review & Issue Credential</h2>
                <p class="text-gray-600">Please review the details before issuing the credential</p>
            </div>
            
            <!-- Preview Card -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl p-8 text-white shadow-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="h-12 w-12 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                <i class="fas fa-certificate text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">Verifiable Credential</h3>
                                <p class="text-blue-100 text-sm">SarvOne Digital Identity Platform</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-blue-100 text-sm">Issued by</p>
                            <p class="font-semibold text-sm">{{ auth('organization')->user()->legal_name }}</p>
                        </div>
                    </div>
                    
                    <div id="credential-preview" class="space-y-4">
                        <!-- Preview content will be populated dynamically -->
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-white border-opacity-20">
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <p class="text-blue-100">Credential ID</p>
                                <p class="font-mono" id="preview-credential-id">SARV-{{ strtoupper(substr(md5(time()), 0, 8)) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-blue-100">Issue Date</p>
                                <p id="preview-issue-date">{{ date('M d, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Private Key Input -->
            <div class="mt-8 max-w-2xl mx-auto">
                <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-red-800 mb-4 flex items-center">
                        <i class="fas fa-key mr-2"></i>
                        Organization Private Key (Required)
                    </h4>
                    <p class="text-sm text-red-700 mb-4">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Security Note:</strong> Your Ethereum private key is required to sign the blockchain transaction. This key is used only temporarily and is not stored.
                    </p>
                    <div class="space-y-2">
                        <label for="org-private-key" class="block text-sm font-medium text-gray-700">
                            Ethereum Private Key <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="password" 
                            id="org-private-key" 
                            placeholder="Enter your organization's Ethereum private key (64 hex characters)"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            maxlength="66"
                            required
                        >
                        <p class="text-xs text-gray-500">
                            This should be the private key corresponding to your organization's wallet address: <strong>{{ auth('organization')->user()->wallet_address }}</strong>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Issue Options -->
            <div class="mt-8 max-w-2xl mx-auto">
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                    <h4 class="text-lg font-semibold text-yellow-800 mb-4 flex items-center">
                        <i class="fas fa-cog mr-2"></i>
                        Issue Options
                    </h4>
                    
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="send-email" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm text-gray-700">Send credential to recipient via email</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" id="blockchain-anchor" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm text-gray-700">Anchor credential hash on blockchain for immutable verification</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" id="generate-qr" checked class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm text-gray-700">Generate QR code for easy verification</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 flex justify-between max-w-2xl mx-auto">
                <button onclick="goToStep(2)" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-all duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Edit
                </button>
                <button onclick="issueCredential()" class="px-8 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-all duration-200">
                    <i class="fas fa-check mr-2"></i>
                    Issue Credential
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full mx-4 p-8 text-center">
            <div class="h-20 w-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-check text-green-600 text-3xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">Credential Issued Successfully!</h3>
            <p class="text-gray-600 mb-6">The verifiable credential has been created and sent to the recipient.</p>
            
            <div class="space-y-3 text-left mb-6">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Credential ID:</span>
                    <span class="text-sm font-mono" id="success-credential-id">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Transaction Hash:</span>
                    <span class="text-sm font-mono" id="success-tx-hash">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Verification URL:</span>
                    <span class="text-sm text-blue-600" id="success-verify-url">-</span>
                </div>
            </div>
            
            <div class="flex space-x-4">
                <button onclick="closeSuccessModal()" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Close
                </button>
                <button onclick="issueAnother()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    Issue Another
                </button>
            </div>
        </div>
    </div>

    <!-- Issued Credentials Table -->
    <div class="mt-12">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Recently Issued Credentials</h2>
                    <p class="text-gray-600 mt-1">View all credentials issued by your organization</p>
                </div>
                <button onclick="loadIssuedCredentials()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
            
            <!-- Loading State -->
            <div id="credentials-loading" class="hidden text-center py-8">
                <div class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-xl mr-3"></i>
                    <span class="text-gray-600">Loading issued credentials...</span>
                </div>
            </div>
            
            <!-- Error State -->
            <div id="credentials-error" class="hidden text-center py-8">
                <div class="text-red-600">
                    <i class="fas fa-exclamation-triangle text-xl mb-2"></i>
                    <p id="credentials-error-message">Failed to load credentials</p>
                </div>
            </div>
            
            <!-- Credentials Table -->
            <div id="credentials-table" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credential Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blockchain TX</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="credentials-tbody" class="bg-white divide-y divide-gray-200">
                        <!-- Credentials will be loaded here -->
                    </tbody>
                </table>
                
                <!-- Empty State -->
                <div id="credentials-empty" class="hidden text-center py-12">
                    <div class="text-gray-500">
                        <i class="fas fa-certificate text-4xl mb-4"></i>
                        <p class="text-lg font-medium">No credentials issued yet</p>
                        <p class="text-sm">Start by issuing your first credential above</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentStep = 1;
let selectedCredentialType = null;
let selectedUser = null;
let orgType = '{{ auth("organization")->user()->organization_type }}';
let orgWriteScopes = @json(auth('organization')->user()->write_scopes ?? []);

// User lookup and auto-fill functions
async function lookupUser() {
    const didInput = document.getElementById('user-did');
    const did = didInput.value.trim();
    
    if (!did) {
        showError('Please enter a DID');
        return;
    }
    
    if (!did.startsWith('did:sarvone:')) {
        showError('Please enter a valid SarvOne DID starting with "did:sarvone:"');
        return;
    }
    
    // Show loading state
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
            enableNextStep1();
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
    document.getElementById('user-details').classList.remove('hidden');
    
    // Also update the form fields for Step 3
    document.getElementById('form-user-did').value = user.did || '';
    document.getElementById('form-recipient-name').value = user.name || '';
    document.getElementById('form-recipient-email').value = user.email || '';
    document.getElementById('form-recipient-phone').value = user.phone || '';
}

function hideUserDetails() {
    document.getElementById('user-details').classList.add('hidden');
    disableNextStep1();
}

function enableNextStep1() {
    const btn = document.getElementById('next-step1');
    btn.disabled = false;
    btn.className = 'bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition-colors font-medium';
}

function disableNextStep1() {
    const btn = document.getElementById('next-step1');
    btn.disabled = true;
    btn.className = 'bg-gray-400 text-white px-8 py-3 rounded-xl cursor-not-allowed transition-colors font-medium';
}

// Global credential mapping
const credentialMap = {
    // Banking & Financial - Write Permissions (what org can ISSUE)
    'loan_approval': { id: 'loan_approval', title: 'Loan Approval', description: 'Official loan approval document', icon: 'fas fa-handshake', category: 'financial' },
    'account_opening': { id: 'account_opening', title: 'Bank Account Opening', description: 'Bank account verification and opening certificate', icon: 'fas fa-university', category: 'banking' },
    'credit_score_report': { id: 'credit_score_report', title: 'Credit Score Report', description: 'Credit score and financial assessment', icon: 'fas fa-chart-line', category: 'financial' },
    'loan_closure': { id: 'loan_closure', title: 'Loan Closure Certificate', description: 'Official loan closure and clearance', icon: 'fas fa-check-circle', category: 'financial' },
    'defaulter_status': { id: 'defaulter_status', title: 'Defaulter Status', description: 'Credit defaulter status verification', icon: 'fas fa-exclamation-triangle', category: 'financial' },
    'financial_statement': { id: 'financial_statement', title: 'Financial Statement', description: 'Official financial statement verification', icon: 'fas fa-money-check-alt', category: 'financial' },
    
    // Legacy mappings for backward compatibility
    'loan_sanction_letter': { id: 'loan_approval', title: 'Loan Sanction Letter', description: 'Official loan approval document', icon: 'fas fa-handshake', category: 'financial' },
    'bank_account_certificate': { id: 'account_opening', title: 'Bank Account Opening', description: 'Bank account verification and opening certificate', icon: 'fas fa-university', category: 'banking' },
    'credit_assessment': { id: 'credit_score_report', title: 'Credit Assessment', description: 'Credit score and financial assessment', icon: 'fas fa-chart-line', category: 'financial' },
    'loan_closure_certificate': { id: 'loan_closure', title: 'Loan Closure Certificate', description: 'Official loan closure and clearance', icon: 'fas fa-check-circle', category: 'financial' },
    'npa_declaration': { id: 'defaulter_status', title: 'NPA Declaration', description: 'Non-Performing Asset declaration', icon: 'fas fa-exclamation-triangle', category: 'financial' },
    'financial_verification': { id: 'financial_statement', title: 'Financial Verification', description: 'General financial status verification', icon: 'fas fa-money-check-alt', category: 'financial' },
    
    // Education - Database scope names (what's actually stored)
    'degree_certificate': { id: 'degree_certificate', title: 'Degree Certificate', description: 'Official academic degree certificate', icon: 'fas fa-graduation-cap', category: 'education' },
    'diploma_certificate': { id: 'diploma_certificate', title: 'Diploma Certificate', description: 'Official diploma certificate', icon: 'fas fa-certificate', category: 'education' },
    'marksheet_university': { id: 'marksheet_university', title: 'University Marksheet', description: 'University academic transcript and grades', icon: 'fas fa-scroll', category: 'education' },
    'course_completion': { id: 'course_completion', title: 'Course Completion', description: 'Course completion certificate', icon: 'fas fa-chalkboard-teacher', category: 'education' },
    
    // New Education Scopes from config
    'student_status': { id: 'student_status', title: 'Student Status', description: 'Current student enrollment status', icon: 'fas fa-user-graduate', category: 'education' },
    'marksheet': { id: 'marksheet', title: 'Marksheet', description: 'Academic marksheet and grades', icon: 'fas fa-scroll', category: 'education' },
    'study_certificate': { id: 'study_certificate', title: 'Study Certificate', description: 'Official study certificate', icon: 'fas fa-book-open', category: 'education' },
    'transfer_certificate': { id: 'transfer_certificate', title: 'Transfer Certificate', description: 'Official transfer certificate', icon: 'fas fa-exchange-alt', category: 'education' },
    'admission_certificate': { id: 'admission_certificate', title: 'Admission Certificate', description: 'Official admission certificate', icon: 'fas fa-user-plus', category: 'education' },
    'attendance_certificate': { id: 'attendance_certificate', title: 'Attendance Certificate', description: 'Official attendance certificate', icon: 'fas fa-calendar-check', category: 'education' },
    'character_certificate': { id: 'character_certificate', title: 'Character Certificate', description: 'Character and conduct certificate', icon: 'fas fa-award', category: 'education' },
    'migration_certificate': { id: 'migration_certificate', title: 'Migration Certificate', description: 'Official migration certificate', icon: 'fas fa-plane', category: 'education' },
    'bonafide_certificate': { id: 'bonafide_certificate', title: 'Bonafide Certificate', description: 'Official bonafide certificate', icon: 'fas fa-stamp', category: 'education' },
    'scholarship_certificate': { id: 'scholarship_certificate', title: 'Scholarship Certificate', description: 'Scholarship eligibility certificate', icon: 'fas fa-gift', category: 'education' },
    'sports_certificate': { id: 'sports_certificate', title: 'Sports Certificate', description: 'Sports achievement certificate', icon: 'fas fa-trophy', category: 'education' },
    'academic_transcript': { id: 'academic_transcript', title: 'Academic Transcript', description: 'Detailed academic transcript', icon: 'fas fa-file-alt', category: 'education' },
    'post_graduation_certificate': { id: 'post_graduation_certificate', title: 'Post Graduation Certificate', description: 'Post graduation certificate', icon: 'fas fa-user-graduate', category: 'education' },
    
    // Education - Legacy mappings (for backward compatibility)
    'graduation_certificate': { id: 'degree_certificate', title: 'Degree Certificate', description: 'Official academic degree certificate', icon: 'fas fa-graduation-cap', category: 'education' },
    'diploma_award': { id: 'diploma_certificate', title: 'Diploma Certificate', description: 'Official diploma certificate', icon: 'fas fa-certificate', category: 'education' },
    'university_marksheet': { id: 'marksheet_university', title: 'University Marksheet', description: 'University academic transcript and grades', icon: 'fas fa-scroll', category: 'education' },
    'training_course_completion': { id: 'course_completion', title: 'Course Completion', description: 'Course completion certificate', icon: 'fas fa-chalkboard-teacher', category: 'education' },
    'marksheet_school': { id: 'school_marksheet', title: 'School Marksheet', description: 'School academic records and grades', icon: 'fas fa-book', category: 'education' },
    'verify_10th_marksheet_verification': { id: 'verify_10th_marksheet_verification', title: '10th Marksheet Verification', description: '10th standard marksheet verification', icon: 'fas fa-book', category: 'education' },
    'verify_12th_marksheet_verification': { id: 'verify_12th_marksheet_verification', title: '12th Marksheet Verification', description: '12th standard marksheet verification', icon: 'fas fa-book', category: 'education' },
    'verify_school_leaving_certificate_verification': { id: 'verify_school_leaving_certificate_verification', title: 'School Leaving Certificate Verification', description: 'School leaving certificate verification', icon: 'fas fa-certificate', category: 'education' },
    'character_certificate': { id: 'character_cert', title: 'Character Certificate', description: 'Certificate of good conduct and character', icon: 'fas fa-award', category: 'education' },
    'transfer_certificate': { id: 'transfer_cert', title: 'Transfer Certificate', description: 'Official school/college transfer certificate', icon: 'fas fa-exchange-alt', category: 'education' },
    'attendance_certificate': { id: 'attendance_cert', title: 'Attendance Certificate', description: 'Official attendance record certificate', icon: 'fas fa-calendar-check', category: 'education' },
    
    // Government & Identity
    'aadhaar_card': { id: 'aadhaar_card', title: 'Aadhaar Card', description: 'Official Aadhaar identity card', icon: 'fas fa-id-card', category: 'government' },
    'pan_card': { id: 'pan_card', title: 'PAN Card', description: 'Official PAN identity card', icon: 'fas fa-id-card', category: 'government' },
    'voter_id': { id: 'voter_id', title: 'Voter ID', description: 'Official voter identity card', icon: 'fas fa-vote-yea', category: 'government' },
    'driving_license': { id: 'driving_license', title: 'Driving License', description: 'Official driving license', icon: 'fas fa-car', category: 'government' },
    'passport': { id: 'passport', title: 'Passport', description: 'Official passport document', icon: 'fas fa-passport', category: 'government' },
    'identity_verification': { id: 'identity_verification', title: 'Identity Verification', description: 'Official government identity verification', icon: 'fas fa-id-card', category: 'government' },
    'address_proof': { id: 'address_proof', title: 'Address Proof', description: 'Verified residential address certificate', icon: 'fas fa-home', category: 'government' },
    'income_certificate': { id: 'income_certificate', title: 'Income Certificate', description: 'Official income verification certificate', icon: 'fas fa-rupee-sign', category: 'government' },
    'caste_certificate': { id: 'caste_certificate', title: 'Caste Certificate', description: 'Official caste verification certificate', icon: 'fas fa-users', category: 'government' },
    'domicile_certificate': { id: 'domicile_certificate', title: 'Domicile Certificate', description: 'Official domicile/residence certificate', icon: 'fas fa-map-marker-alt', category: 'government' },
    
    // Employment & HR
    'employment_verification': { id: 'employment_verification', title: 'Employment Verification', description: 'Official employment status verification', icon: 'fas fa-briefcase', category: 'employment' },
    'salary_certificate': { id: 'salary_certificate', title: 'Salary Certificate', description: 'Official salary verification certificate', icon: 'fas fa-money-bill', category: 'employment' },
    'experience_certificate': { id: 'experience_certificate', title: 'Experience Certificate', description: 'Work experience verification', icon: 'fas fa-clock', category: 'employment' },
    
    // Healthcare
    'medical_certificate': { id: 'medical_certificate', title: 'Medical Certificate', description: 'Official medical health certificate', icon: 'fas fa-heart', category: 'healthcare' },
    'vaccination_certificate': { id: 'vaccination_certificate', title: 'Vaccination Certificate', description: 'COVID-19 and other vaccination records', icon: 'fas fa-syringe', category: 'healthcare' },
    
    // Skills & Training
    'skill_certificate': { id: 'skill_certificate', title: 'Skill Certificate', description: 'Professional skill certification', icon: 'fas fa-tools', category: 'skills' },
    'training_certificate': { id: 'training_certificate', title: 'Training Certificate', description: 'Training program completion certificate', icon: 'fas fa-chalkboard-teacher', category: 'skills' },
    
    // Scheme & Welfare
    'scheme_eligibility': { id: 'scheme_eligibility', title: 'Scheme Eligibility', description: 'Government scheme eligibility certificate', icon: 'fas fa-shield-alt', category: 'welfare' },
    'pension_certificate': { id: 'pension_certificate', title: 'Pension Certificate', description: 'Pension eligibility and payment certificate', icon: 'fas fa-user-clock', category: 'welfare' },
    'disability_certificate': { id: 'disability_certificate', title: 'Disability Certificate', description: 'Official disability verification certificate', icon: 'fas fa-wheelchair', category: 'welfare' },
    
    // General
    'general_certificate': { id: 'general_certificate', title: 'General Certificate', description: 'General purpose verification certificate', icon: 'fas fa-certificate', category: 'general' }
};

// Load available credentials based on organization write scopes
function loadAvailableCredentials() {
    const container = document.getElementById('available-credentials');
    
    const availableCredentials = orgWriteScopes.map(scope => credentialMap[scope]).filter(Boolean);
    
    if (availableCredentials.length === 0) {
        console.log('Debug: No credentials found');
        console.log('Org write scopes:', orgWriteScopes);
        console.log('Available scope keys:', Object.keys(credentialMap));
        
        container.innerHTML = `
            <div class="col-span-full text-center py-8">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-3xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No Credentials Available</h3>
                <p class="text-gray-600 mb-4">Your organization doesn't have authorization to issue any credentials yet.</p>
                <div class="text-left bg-gray-100 p-4 rounded-lg max-w-2xl mx-auto">
                    <p class="text-sm font-semibold text-gray-700 mb-2">Debug Information:</p>
                    <p class="text-xs text-gray-600 mb-1">Organization Type: <span class="font-mono">${orgType}</span></p>
                    <p class="text-xs text-gray-600 mb-1">Write Scopes: <span class="font-mono">${JSON.stringify(orgWriteScopes)}</span></p>
                    <p class="text-xs text-gray-600">Available Mappings: ${Object.keys(credentialMap).length} credential types</p>
                </div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = availableCredentials.map(cred => `
        <div class="credential-option bg-white rounded-xl p-6 shadow-sm border-2 border-transparent hover:border-blue-500 hover:shadow-md cursor-pointer transition-all duration-300" 
             onclick="selectCredential('${cred.id}')">
            <div class="text-center">
                <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="${cred.icon} text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">${cred.title}</h3>
                <p class="text-sm text-gray-600">${cred.description}</p>
                <span class="inline-block mt-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${cred.category}</span>
            </div>
        </div>
    `).join('');
}

function selectCredential(credentialId) {
    // Remove previous selection
    document.querySelectorAll('.credential-option').forEach(card => {
        card.classList.remove('border-blue-500', 'bg-blue-50');
    });
    
    // Mark current selection
    event.currentTarget.classList.add('border-blue-500', 'bg-blue-50');
    selectedCredentialType = credentialId;
    
    // Load dynamic form for this credential type
    loadCredentialForm(credentialId);
    
    // Enable next step
    const btn = document.getElementById('next-step2');
    btn.disabled = false;
    btn.className = 'bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition-colors font-medium';
}

// Load JSON format example based on credential type
function loadCredentialForm(credentialId) {
    const container = document.getElementById('credential-fields');
    
    // Get JSON example for the selected credential type
    const jsonExample = getCredentialJSONExample(credentialId);
    
    container.innerHTML = `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-code mr-2 text-blue-600"></i>
            JSON Format Example
        </h3>
        <div class="bg-gray-900 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-white font-medium">Credential Data (JSON Format)</span>
                <button type="button" onclick="copyToClipboard('json-example')" class="text-blue-400 hover:text-blue-300 text-sm">
                    <i class="fas fa-copy mr-1"></i>Copy
                </button>
            </div>
            <pre id="json-example" class="text-green-400 text-sm overflow-x-auto whitespace-pre-wrap">${jsonExample}</pre>
        </div>
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Credential Data (JSON) *</label>
            <textarea id="credential-data" required 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono text-sm" 
                      rows="10" 
                      placeholder="Paste or enter your credential data in JSON format"></textarea>
            <p class="mt-2 text-sm text-gray-600">Enter the credential data in valid JSON format. You can copy the example above and modify it as needed.</p>
        </div>
    `;
}

// Get JSON example for different credential types
function getCredentialJSONExample(credentialId) {
    const examples = {
        'aadhaar_card': `{
  "aadhaar_number": "123456789012",
  "name": "John Doe",
  "date_of_birth": "1990-01-01",
  "gender": "Male",
  "address": {
    "house_number": "123",
    "street": "Main Street",
    "city": "Mumbai",
    "state": "Maharashtra",
    "pincode": "400001"
  },
  "photo_url": "https://example.com/photo.jpg",
  "issued_date": "2020-01-01",
  "valid_until": "2030-01-01"
}`,
        'pan_card': `{
  "pan_number": "ABCDE1234F",
  "name": "John Doe",
  "date_of_birth": "1990-01-01",
  "father_name": "Father Name",
  "issued_date": "2020-01-01",
  "valid_until": "2030-01-01"
}`,
        'voter_id': `{
  "voter_id": "ABC123456789",
  "name": "John Doe",
  "date_of_birth": "1990-01-01",
  "gender": "Male",
  "address": "123 Main Street, Mumbai, Maharashtra",
  "constituency": "Mumbai North",
  "issued_date": "2020-01-01"
}`,
        'income_certificate': `{
  "annual_income": "500000",
  "income_type": "salary",
  "employer": "ABC Company",
  "valid_from": "2024-01-01",
  "valid_until": "2029-01-01",
  "issuing_authority": "Tehsildar Office"
}`,
        'land_property': `{
  "property_id": "PROP_001",
  "property_type": "residential",
  "area": "2000",
  "area_unit": "sqft",
  "location": "Mumbai",
  "registration_number": "REG123456",
  "current_owner": "John Doe"
}`,
        'bank_account': `{
  "account_number": "1234567890",
  "account_type": "savings",
  "bank_name": "State Bank of India",
  "branch": "Mumbai Main",
  "ifsc_code": "SBIN0001234",
  "opened_date": "2020-01-01",
  "status": "active"
}`,
        'loan': `{
  "loan_id": "LOAN_001",
  "loan_type": "personal",
  "amount": "500000",
  "interest_rate": "12.5",
  "tenure": "60",
  "emi_amount": "11250",
  "disbursed_date": "2024-01-01",
  "due_date": "2029-01-01"
}`,
        'student_status': `{
  "student_id": "STU_001",
  "institution": "Mumbai University",
  "course": "Bachelor of Engineering",
  "year": "3rd",
  "semester": "6th",
  "enrollment_date": "2022-07-01",
  "status": "active"
}`,
        'marksheet': `{
  "student_id": "STU_001",
  "institution": "Mumbai University",
  "course": "Bachelor of Engineering",
  "semester": "6th",
  "total_marks": "800",
  "obtained_marks": "720",
  "percentage": "90",
  "grade": "A+",
  "exam_date": "2024-05-01"
}`,
        'caste_certificate': `{
  "caste": "OBC",
  "category": "Other Backward Class",
  "certificate_number": "CST123456",
  "issued_by": "Tehsildar Office",
  "issued_date": "2020-01-01",
  "valid_until": "2030-01-01"
}`,
        'employment_certificate': `{
  "employee_id": "EMP_001",
  "company": "ABC Company",
  "designation": "Software Engineer",
  "joining_date": "2020-01-01",
  "salary": "50000",
  "status": "active",
  "issued_date": "2024-01-01"
}`
    };
    
    return examples[credentialId] || `{
  "example_field": "example_value",
  "another_field": "another_value",
  "date_field": "2024-01-01"
}`;
}

// Copy to clipboard function
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        const button = element.parentElement.querySelector('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
        button.className = 'text-green-400 hover:text-green-300 text-sm';
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.className = 'text-blue-400 hover:text-blue-300 text-sm';
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}

function getPANCardForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-id-card mr-2 text-blue-600"></i>
            PAN Card Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PAN Number *</label>
                <input type="text" id="pan-number" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="ABCDE1234F" maxlength="10">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" id="pan-full-name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter full name as per PAN">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                <input type="date" id="pan-date-of-birth" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Issued Date *</label>
                <input type="date" id="pan-issued-date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    `;
}

function getVoterIDForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-vote-yea mr-2 text-blue-600"></i>
            Voter ID Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Voter ID Number *</label>
                <input type="text" id="voter-id-number" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter voter ID number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" id="voter-full-name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter full name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                <input type="date" id="voter-date-of-birth" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Constituency *</label>
                <input type="text" id="constituency" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter constituency">
            </div>
        </div>
    `;
}

function getDrivingLicenseForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-car mr-2 text-blue-600"></i>
            Driving License Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">License Number *</label>
                <input type="text" id="license-number" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter license number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" id="license-full-name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter full name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                <input type="date" id="license-date-of-birth" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">License Type *</label>
                <select id="license-type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select License Type</option>
                    <option value="MCWG">Motorcycle Without Gear</option>
                    <option value="MCWOG">Motorcycle With Gear</option>
                    <option value="LMV">Light Motor Vehicle</option>
                    <option value="HMV">Heavy Motor Vehicle</option>
                </select>
            </div>
        </div>
    `;
}

function getPassportForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-passport mr-2 text-blue-600"></i>
            Passport Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Passport Number *</label>
                <input type="text" id="passport-number" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter passport number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                <input type="text" id="passport-full-name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter full name as per passport">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                <input type="date" id="passport-date-of-birth" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nationality *</label>
                <input type="text" id="nationality" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Indian">
            </div>
        </div>
    `;
}

function getBankAccountForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-university mr-2 text-blue-600"></i>
            Bank Account Opening Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Type *</label>
                <select id="account-type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Account Type</option>
                    <option value="savings">Savings Account</option>
                    <option value="current">Current Account</option>
                    <option value="salary">Salary Account</option>
                    <option value="fixed_deposit">Fixed Deposit</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Number *</label>
                <input type="text" id="account-number" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter account number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">IFSC Code *</label>
                <input type="text" id="ifsc-code" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="SBIN0000123">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Branch Name *</label>
                <input type="text" id="branch-name" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter branch name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Opening Date *</label>
                <input type="date" id="opening-date" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Initial Deposit Amount</label>
                <input type="number" id="initial-deposit" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="10000">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">KYC Documents Verified</label>
                <div class="grid grid-cols-2 gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="kyc-aadhaar" class="mr-2">
                        <span class="text-sm">Aadhaar Card</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="kyc-pan" class="mr-2">
                        <span class="text-sm">PAN Card</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="kyc-passport" class="mr-2">
                        <span class="text-sm">Passport</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" id="kyc-address" class="mr-2">
                        <span class="text-sm">Address Proof</span>
                    </label>
                </div>
            </div>
        </div>
    `;
}

function getLoanSanctionForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-handshake mr-2 text-blue-600"></i>
            Loan Sanction Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Loan Type *</label>
                <select id="loan-type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Loan Type</option>
                    <option value="home_loan">Home Loan</option>
                    <option value="personal_loan">Personal Loan</option>
                    <option value="car_loan">Car Loan</option>
                    <option value="education_loan">Education Loan</option>
                    <option value="business_loan">Business Loan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sanctioned Amount *</label>
                <input type="number" id="loan-amount" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="500000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%) *</label>
                <input type="number" step="0.01" id="interest-rate" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="8.50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tenure (Months) *</label>
                <input type="number" id="loan-tenure" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="240">
            </div>
        </div>
    `;
}

function getCreditScoreForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-chart-line mr-2 text-blue-600"></i>
            Credit Assessment Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Credit Score *</label>
                <input type="number" min="300" max="900" id="credit-score" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="750">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Credit Rating *</label>
                <select id="credit-rating" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Rating</option>
                    <option value="excellent">Excellent (750-900)</option>
                    <option value="good">Good (700-749)</option>
                    <option value="fair">Fair (650-699)</option>
                    <option value="poor">Poor (300-649)</option>
                </select>
            </div>
        </div>
    `;
}

function getDegreeForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-graduation-cap mr-2 text-blue-600"></i>
            Degree Certificate Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Degree Type *</label>
                <select id="degree-type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select Degree</option>
                    <option value="bachelor">Bachelor's Degree</option>
                    <option value="master">Master's Degree</option>
                    <option value="phd">PhD</option>
                    <option value="diploma">Diploma</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Field of Study *</label>
                <input type="text" id="field-study" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Computer Science">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Graduation Year *</label>
                <input type="number" min="1900" max="2030" id="graduation-year" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="2023">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Grade/CGPA *</label>
                <input type="text" id="grade" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="8.5/10">
            </div>
        </div>
    `;
}

function getLoanClosureForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-check-circle mr-2 text-green-600"></i>
            Loan Closure Certificate Details
        </h3>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Loan Account Number</label>
                <input type="text" id="loan-account-number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter loan account number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Loan Type</label>
                <select id="loan-type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select loan type</option>
                    <option value="personal">Personal Loan</option>
                    <option value="home">Home Loan</option>
                    <option value="car">Car Loan</option>
                    <option value="business">Business Loan</option>
                    <option value="education">Education Loan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Original Loan Amount</label>
                <input type="number" id="original-amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter original loan amount">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Closure Date</label>
                <input type="date" id="closure-date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Final Settlement Amount</label>
                <input type="number" id="settlement-amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter final settlement amount">
            </div>
        </div>
    `;
}

function getDefaulterStatusForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>
            Defaulter Status Certificate Details
        </h3>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                <input type="text" id="account-number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter account number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Default Status</label>
                <select id="default-status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select status</option>
                    <option value="clear">Clear - No Defaults</option>
                    <option value="minor">Minor Default</option>
                    <option value="major">Major Default</option>
                    <option value="willful">Willful Defaulter</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Outstanding Amount</label>
                <input type="number" id="outstanding-amount" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter outstanding amount">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Payment Date</label>
                <input type="date" id="last-payment-date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <textarea id="default-remarks" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter additional remarks about default status"></textarea>
            </div>
        </div>
    `;
}

function getFinancialStatementForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-money-check-alt mr-2 text-blue-600"></i>
            Financial Statement Certificate Details
        </h3>
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                <input type="text" id="account-number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter account number">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statement Period</label>
                <select id="statement-period" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select period</option>
                    <option value="3months">Last 3 Months</option>
                    <option value="6months">Last 6 Months</option>
                    <option value="1year">Last 1 Year</option>
                    <option value="custom">Custom Period</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Average Balance</label>
                <input type="number" id="average-balance" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter average balance">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Balance</label>
                <input type="number" id="current-balance" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter current balance">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Credits</label>
                <input type="number" id="total-credits" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter total credits">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Total Debits</label>
                <input type="number" id="total-debits" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter total debits">
            </div>
        </div>
    `;
}

function getGenericForm() {
    return `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-certificate mr-2 text-blue-600"></i>
            Credential Details
        </h3>
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Information</label>
                <textarea id="additional-info" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter any additional details for this credential..."></textarea>
            </div>
        </div>
    `;
}

// Credential types for different organizations
const credentialTypes = {
    bank: [
        { id: 'account_verification', title: 'Account Verification', description: 'Verify bank account ownership and status', icon: 'fas fa-university' },
        { id: 'loan_eligibility', title: 'Loan Eligibility', description: 'Certify customer loan eligibility and credit worthiness', icon: 'fas fa-handshake' },
        { id: 'credit_score', title: 'Credit Score Certificate', description: 'Official credit score and rating certificate', icon: 'fas fa-chart-line' },
        { id: 'financial_statement', title: 'Financial Statement', description: 'Verified financial statement and balance confirmation', icon: 'fas fa-file-invoice-dollar' }
    ],
    college: [
        { id: 'degree_certificate', title: 'Degree Certificate', description: 'Official degree completion certificate', icon: 'fas fa-graduation-cap' },
        { id: 'marksheet_university', title: 'University Marksheet', description: 'Official academic transcript and grades', icon: 'fas fa-scroll' },
        { id: 'admission_offer', title: 'Admission Offer Letter', description: 'Official admission confirmation letter', icon: 'fas fa-envelope-open-text' },
        { id: 'course_completion', title: 'Course Completion', description: 'Certificate for completed courses and programs', icon: 'fas fa-certificate' }
    ],
    school: [
        { id: 'school_marksheet', title: 'School Marksheet', description: 'Official school academic records and grades', icon: 'fas fa-book' },
        { id: 'character_certificate', title: 'Character Certificate', description: 'Certificate of good conduct and character', icon: 'fas fa-award' },
        { id: 'transfer_certificate', title: 'Transfer Certificate', description: 'Official school transfer and leaving certificate', icon: 'fas fa-exchange-alt' },
        { id: 'attendance_certificate', title: 'Attendance Certificate', description: 'Official attendance record certificate', icon: 'fas fa-calendar-check' }
    ],
    government: [
        { id: 'identity_verification', title: 'Identity Verification', description: 'Official government identity verification', icon: 'fas fa-id-card' },
        { id: 'address_proof', title: 'Address Proof', description: 'Verified residential address certificate', icon: 'fas fa-home' },
        { id: 'income_certificate', title: 'Income Certificate', description: 'Official income verification certificate', icon: 'fas fa-rupee-sign' },
        { id: 'caste_certificate', title: 'Caste Certificate', description: 'Official caste verification certificate', icon: 'fas fa-users' }
    ]
};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with Step 1 (User DID lookup)
    currentStep = 1;
    goToStep(1);
    setupEventListeners();
    
    // Load available credentials for Step 2
    loadAvailableCredentials();
});

function setupEventListeners() {
    // Enter key support for DID lookup
    document.getElementById('user-did').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            lookupUser();
        }
    });
    
    // Expiration type change handler
    const expirySelect = document.getElementById('expiration-type');
    if (expirySelect) {
        expirySelect.addEventListener('change', function() {
            const customExpiry = document.getElementById('custom-expiry');
            if (this.value === 'custom') {
                customExpiry.classList.remove('hidden');
            } else {
                customExpiry.classList.add('hidden');
            }
        });
    }
}

function selectCredentialType(typeId) {
    selectedCredentialType = typeId;
    
    // Update UI
    document.querySelectorAll('.credential-card').forEach(card => {
        card.classList.remove('selected');
    });
    event.currentTarget.classList.add('selected');
    
    // Enable next button
    const nextBtn = document.getElementById('next-step1');
    nextBtn.disabled = false;
    nextBtn.className = 'px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-all duration-200';
    
    // Load specific fields for this credential type
    loadCredentialFields(typeId);
}

function loadCredentialFields(typeId) {
    const container = document.getElementById('credential-fields');
    
    // Define fields for each credential type
    const fieldConfigs = {
        account_verification: [
            { label: 'Account Number', type: 'text', required: true, placeholder: 'Enter account number' },
            { label: 'Account Type', type: 'select', required: true, options: ['Savings', 'Current', 'Fixed Deposit', 'Loan'] },
            { label: 'Account Status', type: 'select', required: true, options: ['Active', 'Dormant', 'Closed'] },
            { label: 'Opening Date', type: 'date', required: true }
        ],
        degree_certificate: [
            { label: 'Degree Name', type: 'text', required: true, placeholder: 'e.g., Bachelor of Technology' },
            { label: 'Specialization', type: 'text', required: true, placeholder: 'e.g., Computer Science' },
            { label: 'CGPA/Percentage', type: 'text', required: true, placeholder: 'e.g., 8.5 CGPA or 85%' },
            { label: 'Year of Graduation', type: 'number', required: true, placeholder: '2024' },
            { label: 'Student ID', type: 'text', required: true, placeholder: 'University student ID' }
        ],
        identity_verification: [
            { label: 'Aadhaar Number', type: 'text', required: true, placeholder: 'XXXX-XXXX-XXXX' },
            { label: 'PAN Number', type: 'text', required: false, placeholder: 'ABCDE1234F' },
            { label: 'Nationality', type: 'text', required: true, placeholder: 'Indian' },
            { label: 'Verification Level', type: 'select', required: true, options: ['Basic', 'Standard', 'Enhanced'] }
        ]
        // Add more field configurations as needed
    };
    
    const fields = fieldConfigs[typeId] || [
        { label: 'Certificate Details', type: 'textarea', required: true, placeholder: 'Enter certificate details' }
    ];
    
    container.innerHTML = `
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-file-alt mr-2 text-blue-600"></i>
            ${credentialTypes[orgType].find(c => c.id === typeId)?.title || 'Credential'} Details
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            ${fields.map((field, index) => `
                <div class="${field.type === 'textarea' ? 'md:col-span-2' : ''}">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        ${field.label} ${field.required ? '*' : ''}
                    </label>
                    ${field.type === 'select' ? `
                        <select id="field-${index}" ${field.required ? 'required' : ''} 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200">
                            <option value="">Select ${field.label}</option>
                            ${field.options.map(opt => `<option value="${opt}">${opt}</option>`).join('')}
                        </select>
                    ` : field.type === 'textarea' ? `
                        <textarea id="field-${index}" ${field.required ? 'required' : ''} rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                                  placeholder="${field.placeholder || ''}"></textarea>
                    ` : `
                        <input type="${field.type}" id="field-${index}" ${field.required ? 'required' : ''}
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                               placeholder="${field.placeholder || ''}">
                    `}
                </div>
            `).join('')}
        </div>
    `;
}

function setupEventListeners() {
    // Expiration type change
    document.getElementById('expiration-type').addEventListener('change', function() {
        const customExpiry = document.getElementById('custom-expiry');
        if (this.value === 'custom') {
            customExpiry.classList.remove('hidden');
        } else {
            customExpiry.classList.add('hidden');
        }
    });
}

function goToStep(step) {
    // Hide all sections
    document.querySelectorAll('.form-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Update step indicators for 4 steps
    for (let i = 1; i <= 4; i++) {
        const indicator = document.getElementById(`step${i}-indicator`);
        indicator.classList.remove('active', 'completed');
        
        if (i < step) {
            indicator.classList.add('completed');
            indicator.innerHTML = '<i class="fas fa-check text-white"></i>';
        } else if (i === step) {
            indicator.classList.add('active');
            indicator.innerHTML = i;
        } else {
            indicator.innerHTML = i;
            indicator.className = 'step-indicator w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold';
        }
    }
    
    // Update progress bar for 4 steps
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = `${(step - 1) * 25 + 25}%`;
    
    // Show current step
    document.getElementById(`step${step}-content`).classList.add('active');
    currentStep = step;
    
    // Load specific content based on step
    if (step === 2) {
        // Load available credentials when going to step 2
        loadAvailableCredentials();
    } else if (step === 4) {
        // Update preview when going to step 4
        updatePreview();
    }
}

function updatePreview() {
    const preview = document.getElementById('credential-preview');
    if (!preview) return;
    
    // Get user and credential info
    const userDetails = selectedUser;
    const credentialType = selectedCredentialType;
    
    if (!userDetails || !credentialType) {
        preview.innerHTML = `
            <div class="text-center text-blue-100">
                <i class="fas fa-info-circle text-2xl mb-2"></i>
                <p>Complete previous steps to see preview</p>
            </div>
        `;
        return;
    }
    
    // Get credential info from mapping
    const credInfo = credentialMap[credentialType];
    
    // Get form data
    const credentialFields = document.getElementById('credential-fields');
    const formData = {};
    
    if (credentialFields) {
        const inputs = credentialFields.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (input && input.value && input.value.trim()) {
                const label = input.previousElementSibling ? 
                    input.previousElementSibling.textContent.replace('*', '').trim() : 
                    input.id.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                formData[label] = input.value.trim();
            }
        });
    }
    
    preview.innerHTML = `
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-blue-100">Credential Type:</span>
                <span class="font-semibold">${credInfo?.title || credentialType}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-blue-100">Recipient:</span>
                <span class="font-semibold">${userDetails.name || 'Unknown'}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-blue-100">Recipient DID:</span>
                <span class="font-mono text-xs">${userDetails.did || 'Unknown'}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-blue-100">Issue Date:</span>
                <span class="font-semibold">${new Date().toLocaleDateString()}</span>
            </div>
            ${Object.keys(formData).length > 0 ? `
                <div class="mt-4 pt-3 border-t border-white border-opacity-20">
                    <p class="text-blue-100 text-sm mb-2">Credential Details:</p>
                    <div class="space-y-1">
                        ${Object.entries(formData).map(([label, value]) => `
                            <div class="flex justify-between text-xs">
                                <span class="text-blue-200">${label}:</span>
                                <span class="text-right max-w-32 truncate">${value}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            ` : ''}
        </div>
    `;
}

function issueCredential() {
    // Validate all required data
    if (!selectedUser || !selectedCredentialType) {
        alert('Please complete all steps before issuing the credential');
        return;
    }

    // Collect form data
    const credentialData = collectFormData();
    if (!credentialData) {
        alert('Please fill in all required fields');
        return;
    }

    // Validate private key
    const privateKey = document.getElementById('org-private-key').value.trim();
    if (!privateKey) {
        alert('Please enter your organization\'s Ethereum private key');
        document.getElementById('org-private-key').focus();
        return;
    }

    // Basic private key validation
    const cleanKey = privateKey.replace(/^0x/, '');
    if (cleanKey.length !== 64 || !/^[0-9a-fA-F]+$/.test(cleanKey)) {
        alert('Invalid private key format. Please enter a valid 64-character hexadecimal Ethereum private key');
        document.getElementById('org-private-key').focus();
        return;
    }

    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Issuing...';
    button.disabled = true;

    // Prepare request data
    const requestData = {
        subject_did: selectedUser.did,
        credential_type: selectedCredentialType,
        credential_data: credentialData,
        org_private_key: privateKey
    };

    console.log('Issuing credential with data:', requestData);

    // Call the credential issuance API
    fetch('/organization/api/issue-credential', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Credential issuance response:', data);
        
        if (data.success) {
            // Show success information
            showIssuanceSuccess(data.data);
        } else {
            // Show error
            showIssuanceError(data.message || 'Failed to issue credential');
        }
    })
    .catch(error => {
        console.error('Credential issuance error:', error);
        showIssuanceError('Network error: ' + error.message);
    })
    .finally(() => {
        // Reset button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function collectFormData() {
    const credentialDataTextarea = document.getElementById('credential-data');
    if (!credentialDataTextarea) return null;

    // Get JSON data from textarea
    const jsonData = credentialDataTextarea.value.trim();
    
    if (!jsonData) {
        credentialDataTextarea.focus();
        credentialDataTextarea.classList.add('border-red-500');
        alert('Please enter credential data in JSON format');
        return null;
    }

    try {
        // Parse JSON to validate it
        const formData = JSON.parse(jsonData);
        
        // Add additional metadata
        formData['issue_date'] = document.getElementById('issue-date')?.value || new Date().toISOString().split('T')[0];
        formData['expiration_type'] = document.getElementById('expiration-type')?.value || 'never';
        
        if (formData['expiration_type'] === 'custom') {
            formData['custom_expiry_date'] = document.getElementById('custom-expiry-date')?.value || '';
        }
        
        return formData;
    } catch (error) {
        credentialDataTextarea.focus();
        credentialDataTextarea.classList.add('border-red-500');
        alert('Invalid JSON format. Please check your JSON syntax.');
        console.error('JSON parsing error:', error);
        return null;
    }
}

function showIssuanceSuccess(result) {
    // Hide loading and show success
    document.getElementById('success-credential-id').textContent = result.credential_id || 'N/A';
    document.getElementById('success-tx-hash').textContent = result.transaction_hash || 'N/A';
    document.getElementById('success-verify-url').textContent = result.ipfs_cid ? 
        `https://ipfs.io/ipfs/${result.ipfs_cid}` : 'N/A';
    
    document.getElementById('success-modal').classList.remove('hidden');
    
    console.log('Credential issued successfully:', result);
    
    // Show SMS notification info
    console.log('SMS notification should have been sent to the recipient');
    
    // Auto-redirect after 3 seconds to show issued VCs
    setTimeout(() => {
        window.location.href = '/organization/dashboard';
    }, 3000);
}

function showIssuanceError(message) {
    alert('Credential Issuance Failed: ' + message);
    console.error('Issuance error:', message);
}

function closeSuccessModal() {
    document.getElementById('success-modal').classList.add('hidden');
}

function issueAnother() {
    closeSuccessModal();
    // Reset form
    selectedCredentialType = null;
    document.getElementById('credential-form').reset();
    goToStep(1);
    
    // Reset credential type selection
    document.querySelectorAll('.credential-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Disable next button
    const nextBtn = document.getElementById('next-step1');
    nextBtn.disabled = true;
    nextBtn.className = 'px-8 py-3 bg-gray-300 text-gray-500 rounded-lg font-semibold cursor-not-allowed transition-all duration-200';
    
    // Reload credentials table
    loadIssuedCredentials();
}

// Load issued credentials
async function loadIssuedCredentials() {
    showCredentialsLoading();
    hideCredentialsError();
    hideCredentialsEmpty();
    
    try {
        const response = await fetch('/organization/api/issued-credentials', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayCredentials(data.credentials);
        } else {
            showCredentialsError(data.message || 'Failed to load credentials');
        }
    } catch (error) {
        console.error('Error loading credentials:', error);
        showCredentialsError('Network error: ' + error.message);
    } finally {
        hideCredentialsLoading();
    }
}

function displayCredentials(credentials) {
    const tbody = document.getElementById('credentials-tbody');
    const emptyDiv = document.getElementById('credentials-empty');
    const tableDiv = document.getElementById('credentials-table');
    
    if (!credentials || credentials.length === 0) {
        tableDiv.classList.add('hidden');
        emptyDiv.classList.remove('hidden');
        return;
    }
    
    tableDiv.classList.remove('hidden');
    emptyDiv.classList.add('hidden');
    
    tbody.innerHTML = credentials.map(credential => `
        <tr class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-user text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${credential.subject_name || 'Unknown'}</div>
                        <div class="text-sm text-gray-500 font-mono text-xs">${credential.subject_did}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${credential.vc_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${new Date(credential.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                })}
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                    credential.status === 'active' ? 'bg-green-100 text-green-800' :
                    credential.status === 'revoked' ? 'bg-red-100 text-red-800' :
                    'bg-yellow-100 text-yellow-800'
                }">
                    ${credential.status.charAt(0).toUpperCase() + credential.status.slice(1)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ${credential.blockchain_tx_hash ? 
                    `<a href="https://amoy.polygonscan.com/tx/${credential.blockchain_tx_hash}" target="_blank" class="text-blue-600 hover:text-blue-800 font-mono text-xs">
                        ${credential.blockchain_tx_hash.substring(0, 10)}...
                    </a>` : 
                    '<span class="text-gray-400">Not available</span>'
                }
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                    <button onclick="viewCredential('${credential.id}')" class="text-blue-600 hover:text-blue-900">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="verifyCredential('${credential.id}')" class="text-green-600 hover:text-green-900">
                        <i class="fas fa-shield-alt"></i>
                    </button>
                    ${credential.status === 'active' ? 
                        `<button onclick="revokeCredential('${credential.id}')" class="text-red-600 hover:text-red-900">
                            <i class="fas fa-ban"></i>
                        </button>` : ''
                    }
                </div>
            </td>
        </tr>
    `).join('');
}

function showCredentialsLoading() {
    document.getElementById('credentials-loading').classList.remove('hidden');
}

function hideCredentialsLoading() {
    document.getElementById('credentials-loading').classList.add('hidden');
}

function showCredentialsError(message) {
    document.getElementById('credentials-error-message').textContent = message;
    document.getElementById('credentials-error').classList.remove('hidden');
}

function hideCredentialsError() {
    document.getElementById('credentials-error').classList.add('hidden');
}

function showCredentialsEmpty() {
    document.getElementById('credentials-empty').classList.remove('hidden');
}

function hideCredentialsEmpty() {
    document.getElementById('credentials-empty').classList.add('hidden');
}

function viewCredential(credentialId) {
    // TODO: Implement credential viewing
    alert('View credential functionality coming soon!');
}

function verifyCredential(credentialId) {
    // TODO: Implement credential verification
    alert('Verify credential functionality coming soon!');
}

function revokeCredential(credentialId) {
    if (confirm('Are you sure you want to revoke this credential? This action cannot be undone.')) {
        // TODO: Implement credential revocation
        alert('Revoke credential functionality coming soon!');
    }
}

// Load credentials when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadIssuedCredentials();
});
</script>
@endpush 