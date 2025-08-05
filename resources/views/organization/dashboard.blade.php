@extends('layouts.organization')

@section('title', 'Dashboard - SarvOne Organization Portal')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Header Section -->
    <div class="mb-6">
                    <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600">{{ now()->format('l, F j, Y') }}</p>
                </div>
                @if(auth('organization')->user()->verification_status === 'approved')
                <div class="flex items-center space-x-3">
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Organization DID</p>
                        <p class="text-xs font-mono text-gray-900 bg-gray-100 px-2 py-1 rounded">{{ auth('organization')->user()->did ?? 'Not generated' }}</p>
                    </div>
                    <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
                @endif
            </div>
    </div>

    <!-- Statistics Cards for Approved Organizations -->
    @if(auth('organization')->user()->verification_status === 'approved')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- VCs Issued -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">VCs Issued</p>
                    <p class="text-3xl font-bold text-blue-600">{{ auth('organization')->user()->vcs_issued ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total credentials issued</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-certificate text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- VCs Verified -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">VCs Verified</p>
                    <p class="text-3xl font-bold text-green-600">{{ auth('organization')->user()->vcs_verified ?? 0 }}</p>
                    <p class="text-xs text-gray-500 mt-1">Total verifications done</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-check text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Issue VC Card -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Issue New Credential</h3>
                    <p class="text-blue-100 text-sm mb-4">Create and issue verifiable credentials to users</p>
                    <a href="{{ route('organization.issue-vc') }}" class="inline-flex items-center px-4 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition-colors font-medium">
                        <i class="fas fa-plus mr-2"></i>Issue VC
                    </a>
                </div>
                <div class="h-16 w-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-certificate text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Verify VC Card -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Verify Credential</h3>
                    <p class="text-green-100 text-sm mb-4">Verify authenticity of submitted credentials</p>
                    <a href="{{ route('organization.verify-vc') }}" class="inline-flex items-center px-4 py-2 bg-white text-green-600 rounded-lg hover:bg-green-50 transition-colors font-medium">
                        <i class="fas fa-search mr-2"></i>Verify VC
                    </a>
                </div>
                <div class="h-16 w-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shield-check text-white text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Manage VCs Card -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-sm p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold mb-2">Manage Credentials</h3>
                    <p class="text-purple-100 text-sm mb-4">View and revoke issued credentials</p>
                    <a href="{{ route('organization.issued-vcs') }}" class="inline-flex items-center px-4 py-2 bg-white text-purple-600 rounded-lg hover:bg-purple-50 transition-colors font-medium">
                        <i class="fas fa-list mr-2"></i>View Issued VCs
                    </a>
                </div>
                <div class="h-16 w-16 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-certificate text-white text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Organization Details & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Organization Information -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Organization Information</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Legal Name</p>
                        <p class="text-sm text-gray-900">{{ auth('organization')->user()->legal_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Organization Type</p>
                        <p class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', auth('organization')->user()->organization_type)) }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Registration Number</p>
                        <p class="text-sm text-gray-900">{{ auth('organization')->user()->registration_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Verification Status</p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Approved
                        </span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Wallet Address</p>
                        <p class="text-xs font-mono text-gray-900 bg-gray-50 px-2 py-1 rounded">{{ auth('organization')->user()->wallet_address }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Approved Date</p>
                        <p class="text-sm text-gray-900">{{ auth('organization')->user()->verified_at ? auth('organization')->user()->verified_at->format('M j, Y') : 'Recently' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Authorized Scopes -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Authorized Scopes</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-2">Can Issue ({{ count(auth('organization')->user()->write_scopes ?? []) }})</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach((auth('organization')->user()->write_scopes ?? []) as $scope)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ str_replace('_', ' ', $scope) }}
                        </span>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-2">Can Verify ({{ count(auth('organization')->user()->read_scopes ?? []) }})</p>
                    <div class="flex flex-wrap gap-1 max-h-32 overflow-y-auto">
                        @foreach((auth('organization')->user()->read_scopes ?? []) as $scope)
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                            {{ str_replace('_', ' ', $scope) }}
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Application Status -->
    @if(auth('organization')->user()->verification_status === 'pending')
        <div class="mb-8">
            <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-l-4 border-yellow-400 rounded-xl p-6 shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-xl font-semibold text-yellow-800 mb-2">Government Approval Pending</h3>
                        <p class="text-yellow-700 mb-4">
                            Your organization application is currently under review by the government authorities. 
                            Once approved, you'll receive your unique SarvOne DID and can start issuing/verifying credentials.
                        </p>
                        <div class="bg-yellow-100 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-semibold text-yellow-800">Application Date:</span>
                                    <p class="text-yellow-700">{{ auth('organization')->user()->created_at->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="font-semibold text-yellow-800">Status:</span>
                                    <p class="text-yellow-700">Pending Review</p>
                                </div>
                                <div>
                                    <span class="font-semibold text-yellow-800">DID Status:</span>
                                    <p class="text-yellow-700">Will be assigned after approval</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(auth('organization')->user()->verification_status === 'approved')
        <div class="mb-8">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 rounded-xl p-6 shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-xl font-semibold text-green-800 mb-2">Organization Approved!</h3>
                        <p class="text-green-700 mb-4">
                            Congratulations! Your organization has been approved and is now part of the SarvOne ecosystem.
                        </p>
                        <div class="bg-green-100 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="font-semibold text-green-800">Approved Date:</span>
                                    <p class="text-green-700">{{ auth('organization')->user()->verified_at?->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="font-semibold text-green-800">SarvOne DID:</span>
                                    <p class="text-green-700 font-mono text-xs">{{ auth('organization')->user()->did }}</p>
                                </div>
                            </div>
                            @if(auth('organization')->user()->blockchain_tx_hash)
                                <div class="mt-4">
                                    <p class="text-sm font-semibold text-green-800 mb-2">Blockchain Transaction:</p>
                                    <a href="https://amoy.polygonscan.com/tx/{{ auth('organization')->user()->blockchain_tx_hash }}" 
                                       target="_blank" 
                                       class="inline-flex items-center space-x-2 bg-purple-100 hover:bg-purple-200 text-purple-700 px-4 py-2 rounded-lg text-sm font-medium transition duration-200">
                                         <i class="fas fa-external-link-alt"></i>
                                         <span>View on Polygonscan</span>
                                         <span class="font-mono text-xs">{{ substr(auth('organization')->user()->blockchain_tx_hash, 0, 10) }}...{{ substr(auth('organization')->user()->blockchain_tx_hash, -6) }}</span>
                                     </a>
                                 </div>
                             @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif(auth('organization')->user()->verification_status === 'rejected')
        <div class="mb-8">
            <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-400 rounded-xl p-6 shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-times-circle text-red-600 text-xl"></i>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-xl font-semibold text-red-800 mb-2">Application Rejected</h3>
                        <p class="text-red-700 mb-4">
                            Unfortunately, your organization application has been rejected. Please review the feedback below.
                        </p>
                        @if(auth('organization')->user()->verification_notes)
                            <div class="bg-red-100 rounded-lg p-4">
                                <span class="font-semibold text-red-800">Admin Feedback:</span>
                                <p class="text-red-700 mt-1">{{ auth('organization')->user()->verification_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Organization Information -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Basic Information -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center mb-6">
                <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-info-circle text-blue-600"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Organization Details</h2>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Legal Name</label>
                        <p class="text-gray-900 font-medium">{{ auth('organization')->user()->legal_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Organization Type</label>
                        <p class="text-gray-900 font-medium">{{ ucfirst(str_replace('_', ' ', auth('organization')->user()->organization_type)) }}</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Registration Number</label>
                        <p class="text-gray-900 font-medium">{{ auth('organization')->user()->registration_number }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Official Email</label>
                        <p class="text-gray-900 font-medium">{{ auth('organization')->user()->official_email }}</p>
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-semibold text-gray-600">Head Office Address</label>
                    <p class="text-gray-900">{{ auth('organization')->user()->head_office_address }}</p>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center mb-6">
                <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-address-book text-green-600"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Contact Information</h2>
            </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Official Phone</label>
                        <p class="text-gray-900 font-medium">{{ auth('organization')->user()->official_phone }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Website</label>
                        <p class="text-gray-900 font-medium">{{ auth('organization')->user()->website_url ?? 'Not provided' }}</p>
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-semibold text-gray-600">Authorized Signatory</label>
                    <p class="text-gray-900 font-medium">{{ auth('organization')->user()->signatory_name }}</p>
                    <p class="text-sm text-gray-600">{{ auth('organization')->user()->signatory_designation }}</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Signatory Email</label>
                        <p class="text-gray-900">{{ auth('organization')->user()->signatory_email }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600">Signatory Phone</label>
                        <p class="text-gray-900">{{ auth('organization')->user()->signatory_phone }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blockchain & Technical Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Blockchain Information -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center mb-6">
                <div class="h-10 w-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-link text-purple-600"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Blockchain Details</h2>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-gray-600">Polygon Wallet Address</label>
                    <p class="text-gray-900 font-mono text-sm break-all bg-gray-50 p-2 rounded">{{ auth('organization')->user()->wallet_address }}</p>
                </div>
                
                @if(auth('organization')->user()->technical_contact_name)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Technical Contact</label>
                            <p class="text-gray-900 font-medium">{{ auth('organization')->user()->technical_contact_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-600">Technical Email</label>
                            <p class="text-gray-900">{{ auth('organization')->user()->technical_contact_email }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Use Case & Volume -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center mb-6">
                <div class="h-10 w-10 bg-orange-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-chart-bar text-orange-600"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Usage Information</h2>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-gray-600">Expected Volume</label>
                    <p class="text-gray-900 font-medium">{{ auth('organization')->user()->expected_volume }} credentials per month</p>
                </div>
                
                <div>
                    <label class="text-sm font-semibold text-gray-600">Use Case Description</label>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p class="text-gray-900 text-sm leading-relaxed">{{ auth('organization')->user()->use_case_description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Next Steps -->
    @if(auth('organization')->user()->verification_status === 'pending')
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <div class="flex items-center mb-6">
                <div class="h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-road text-indigo-600"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">What's Next?</h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center p-4 bg-gray-50 rounded-xl">
                    <div class="h-12 w-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-search text-blue-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Under Review</h3>
                    <p class="text-sm text-gray-600">Government officials are reviewing your application</p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-xl opacity-50">
                    <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-gray-400"></i>
                    </div>
                    <h3 class="font-semibold text-gray-500 mb-2">Get Approved</h3>
                    <p class="text-sm text-gray-400">Receive your SarvOne DID and credentials</p>
                </div>
                
                <div class="text-center p-4 bg-gray-50 rounded-xl opacity-50">
                    <div class="h-12 w-12 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-rocket text-gray-400"></i>
                    </div>
                    <h3 class="font-semibold text-gray-500 mb-2">Start Issuing</h3>
                    <p class="text-sm text-gray-400">Begin issuing verifiable credentials</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection 