<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Dashboard - SarvOne</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    
    <style>
        /* Mobile-first responsive design */
        .mobile-container {
            max-width: 428px;
            margin: 0 auto;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        .content-area {
            padding-bottom: 100px; /* Space for bottom navigation */
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            width: calc(100% - 32px);
            max-width: 360px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            z-index: 50;
            padding: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .nav-item {
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 16px;
            padding: 12px 8px;
            min-height: 48px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 10px;
            font-weight: 500;
        }
        
        .nav-item.active {
            color: #3b82f6;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }
        
        .nav-item:not(.active):hover {
            color: #3b82f6;
            transform: translateY(-1px);
        }
        
        .nav-item i {
            font-size: 16px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }
        
        .nav-item.active i {
            transform: scale(1.1);
        }
        
        .nav-item span {
            font-size: 10px;
            font-weight: 500;
            line-height: 1;
        }
        
        .tab-content {
            display: none;
            opacity: 0;
            transform: translateY(10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .tab-content.active {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        .vc-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .vc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .category-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-bank { background: #dbeafe; color: #1e40af; }
        .badge-education { background: #dcfce7; color: #166534; }
        .badge-employment { background: #f3e8ff; color: #7c3aed; }
        .badge-healthcare { background: #fee2e2; color: #dc2626; }
        .badge-government { background: #fef3c7; color: #d97706; }
        .badge-other { background: #f1f5f9; color: #64748b; }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active { background: #dcfce7; color: #166534; }
        .status-expired { background: #fee2e2; color: #dc2626; }
        .status-revoked { background: #fef3c7; color: #d97706; }
        
        @media (min-width: 768px) {
            .mobile-container {
                max-width: 768px;
            }
            
            .bottom-nav {
                max-width: 480px;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="mobile-container">
        <!-- Header -->
        <header class="bg-white shadow-sm px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-sm"></i>
                        </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">SarvOne</h1>
                        <p class="text-xs text-gray-500">Digital Identity</p>
                    </div>
                </div>
                    <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-sign-out-alt text-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            @if(auth()->user()->verification_status === 'verified')
                <!-- Home Tab -->
                <div id="home-tab" class="tab-content active px-4 py-6">
                    <!-- Professional ID Card -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 mb-6 overflow-hidden">
                        <!-- Indian Government Header -->
                        <div class="bg-gradient-to-r from-orange-500 via-white to-green-600 px-4 py-2">
                            <div class="flex items-center justify-center">
                                <div class="w-4 h-4 bg-orange-500 rounded-full mr-2"></div>
                                <h3 class="text-black text-sm font-bold">GOVERNMENT OF INDIA</h3>
                                <div class="w-4 h-4 bg-green-600 rounded-full ml-2"></div>
                            </div>
                        </div>
                        
                        <!-- Card Header -->
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-4 py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="w-6 h-6 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                        <i class="fas fa-shield-alt text-white text-xs"></i>
                                    </div>
                                    <div>
                                        <h2 class="text-base font-bold text-white">SarvOne</h2>
                                        <p class="text-blue-100 text-xs">Digital Identity Card</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="flex items-center space-x-1">
                                        <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                        <span class="text-white text-xs font-medium">VERIFIED</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Card Body -->
                        <div class="p-4">
                            <div class="flex items-start space-x-3">
                                <!-- Left Side - QR Code -->
                                <div class="flex-shrink-0">
                                    <div class="w-20 h-20 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                        <div id="qrCodeSmall" class="w-full h-full flex items-center justify-center">
                                            <i class="fas fa-qrcode text-gray-400 text-xl"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Center - User Photo -->
                                <div class="flex-shrink-0">
                                    <div class="w-16 h-20 bg-gray-100 rounded-lg overflow-hidden border border-gray-200 flex items-center justify-center relative">
                                        @if(auth()->user()->selfie_path)
                                            <img src="{{ asset('storage/' . auth()->user()->selfie_path) }}" 
                                                 alt="Profile Photo" 
                                                 class="w-full h-full object-cover"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                                 onload="this.nextElementSibling.style.display='none';">
                                            <div class="hidden w-full h-full items-center justify-center bg-gray-50">
                                                <i class="fas fa-user text-gray-400 text-lg"></i>
                                            </div>
                                        @else
                                            <i class="fas fa-user text-gray-400 text-lg"></i>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Right Side - User Details -->
                                <div class="flex-1">
                                    <div class="space-y-2">
                                        <div>
                                            <p class="text-xs text-gray-500 font-medium">NAME</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500 font-medium">PHONE</p>
                                            <p class="text-sm font-semibold text-gray-900">{{ auth()->user()->phone }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- DID Section -->
                            <div class="mt-3">
                                <p class="text-xs text-gray-500 font-medium mb-1">DIGITAL IDENTIFIER</p>
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-50 rounded-lg p-2 border border-gray-200">
                                        <p class="font-mono text-xs text-gray-900 break-all leading-tight">
                                            {{ auth()->user()->did ?? 'Not generated' }}
                                        </p>
                                    </div>
                                    <button onclick="copyDID()" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-2 px-3 rounded transition-colors">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Card Footer -->
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center space-x-1">
                                        <i class="fas fa-calendar text-gray-400"></i>
                                        <span class="text-gray-600">Issued: {{ auth()->user()->verified_at ? auth()->user()->verified_at->format('M j, Y') : 'Recently' }}</span>
                                    </div>
                                    <button onclick="showQrModal()" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                        <i class="fas fa-expand-alt mr-1"></i>View Full QR
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats & Actions -->
                    <div class="space-y-4 mb-6">
                        <div class="bg-white rounded-xl p-4 shadow-sm">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">{{ isset($vcs) ? $vcs->count() : 0 }}</p>
                                    <p class="text-sm text-gray-500">Total VCs</p>
                                </div>
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-certificate text-blue-600"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl p-4 shadow-sm text-white cursor-pointer hover:from-purple-600 hover:to-indigo-700 transition-all duration-200" onclick="location.href='{{ route('opportunity-hub') }}'">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-lg font-bold">Opportunity Hub</p>
                                    <p class="text-sm text-purple-100">Explore opportunities</p>
                                </div>
                                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-rocket text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-download text-blue-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Download ID</span>
                            </button>
                            <button class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-share text-green-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Share Profile</span>
                            </button>
                            <button class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-history text-purple-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">View History</span>
                            </button>
                            <button class="flex items-center space-x-3 p-3 rounded-lg bg-gray-50 hover:bg-gray-100 transition-colors">
                                <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-cog text-orange-600 text-sm"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Settings</span>
                            </button>
                        </div>
                    </div>

                    <!-- Verification Status -->
                    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Verification Status</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 rounded-lg bg-green-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Identity Verified</span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Complete</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-green-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Phone Verified</span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Complete</span>
                            </div>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-green-50">
                                <div class="flex items-center space-x-3">
                                    <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-check text-green-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Aadhaar Verified</span>
                                </div>
                                <span class="text-xs text-green-600 font-medium">Complete</span>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Info -->
                    <div class="bg-white rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Full Name</span>
                                <span class="font-medium">{{ auth()->user()->name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Phone</span>
                                <span class="font-medium">{{ auth()->user()->phone }}</span>
                        </div>
                            @if(auth()->user()->aadhaar_number)
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Aadhaar</span>
                                <span class="font-medium font-mono">**** **** {{ substr(auth()->user()->aadhaar_number, -4) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Age</span>
                                <span class="font-medium">{{ auth()->user()->getAgeFormatted() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Verified</span>
                                <span class="text-green-600 font-medium">{{ auth()->user()->verified_at ? auth()->user()->verified_at->format('M j, Y') : 'Recently' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- VCs Tab -->
                <div id="vcs-tab" class="tab-content px-4 py-6">
                    <div class="mb-6">
                                        <div class="flex items-center justify-between mb-2">
                    <h2 class="text-xl font-bold text-gray-900">My Credentials</h2>

                </div>
                        <p class="text-gray-600 text-sm">
                            <span id="vc-count">{{ isset($vcs) ? $vcs->count() : 0 }}</span> verifiable credentials
                        </p>
                    </div>

                    <!-- Loading indicator -->
                    <div id="blockchain-loading" class="hidden text-center py-8">
                        <i class="fas fa-spinner fa-spin text-blue-600 text-2xl mb-2"></i>
                        <p class="text-gray-600">Loading credentials from blockchain...</p>
                    </div>


                    


                    <!-- VCs will be loaded dynamically via JavaScript -->
                    <div id="vcs-container" class="space-y-4">
                        <!-- Loading indicator -->
                        <div id="vcs-loading" class="text-center py-8">
                            <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-white bg-blue-500 hover:bg-blue-400 transition ease-in-out duration-150 cursor-not-allowed">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Loading credentials...
                            </div>
                        </div>
                    </div>
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-certificate text-gray-400 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Credentials Yet</h3>
                            <p class="text-gray-600 text-sm mb-4">You haven't received any verifiable credentials yet.</p>
                            <p class="text-gray-500 text-xs">Organizations can issue credentials to your DID</p>
                        </div>
                </div>

                <!-- Profile Tab -->
                <div id="profile-tab" class="tab-content px-4 py-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Profile</h2>
                        <p class="text-gray-600 text-sm">Manage your account settings</p>
            </div>

                    <div class="space-y-4">
                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Full Name</label>
                                    <p class="mt-1 text-base text-gray-900">{{ auth()->user()->name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Phone Number</label>
                                    <p class="mt-1 text-base text-gray-900">{{ auth()->user()->phone }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">DID</label>
                                    <p class="mt-1 text-xs font-mono text-gray-600 break-all">{{ auth()->user()->did }}</p>
                                </div>
                </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Security</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Verification Status</p>
                                        <p class="text-sm text-gray-600">Your identity is verified</p>
                                    </div>
                                    <div class="flex items-center text-green-600">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span class="text-sm font-medium">Verified</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900">Trust Score</p>
                                        <p class="text-sm text-gray-600">Based on verification quality</p>
                                    </div>
                                    <span class="text-lg font-bold text-green-600">{{ auth()->user()->trust_score ?? 95 }}%</span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-6 shadow-sm">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                            <div class="space-y-3">
                                <button class="w-full flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-download text-gray-600 mr-3"></i>
                                        <span class="font-medium text-gray-900">Export Data</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>
                                <button class="w-full flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-shield-alt text-gray-600 mr-3"></i>
                                        <span class="font-medium text-gray-900">Privacy Settings</span>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-400"></i>
                                </button>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center justify-between p-3 bg-red-50 rounded-lg text-red-600">
                                        <div class="flex items-center">
                                            <i class="fas fa-sign-out-alt mr-3"></i>
                                            <span class="font-medium">Sign Out</span>
                                        </div>
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Access History Tab -->
                <div id="access-tab" class="tab-content px-4 py-6">
                    <div class="mb-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-2">Credential Access History</h2>
                        <p class="text-gray-600 text-sm">Track when organizations access your credentials</p>
                    </div>

                    <!-- Loading State -->
                    <div id="access-loading" class="hidden text-center py-8">
                        <div class="inline-flex items-center">
                            <i class="fas fa-spinner fa-spin text-blue-600 text-xl mr-3"></i>
                            <span class="text-gray-600">Loading access history...</span>
                        </div>
                    </div>

                    <!-- Access Logs Summary -->
                    <div id="access-logs-summary" class="space-y-4">
                        <!-- Access logs will be loaded here -->
                    </div>

                    <!-- Empty State -->
                    <div id="access-empty" class="hidden text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shield-alt text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Access History</h3>
                        <p class="text-gray-600 text-sm mb-4">No organizations have accessed your credentials yet.</p>
                        <p class="text-gray-500 text-xs">Your information is secure and private</p>
                    </div>

                    <!-- View All Button -->
                    <div class="mt-6">
                        <a href="{{ route('access-history') }}" class="w-full bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg inline-flex items-center justify-center">
                            <i class="fas fa-history mr-2"></i>
                            View Full Access History
                        </a>
                    </div>
                </div>
                    @else
                <!-- Unverified User Content -->
                <div class="px-4 py-6">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-gray-900 mb-2">Complete Your Verification</h1>
                        <p class="text-gray-600">Get your Digital Identity (DID) in 3 simple steps</p>
                    </div>

                    <!-- Progress Steps -->
                    <div class="bg-white rounded-xl p-6 shadow-sm mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Verification Progress</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-bold mr-4">1</div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">Live Selfie</p>
                                    <p class="text-sm text-gray-600">Capture your live photo</p>
                                </div>
                                <div class="w-4 h-4 bg-blue-600 rounded-full"></div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 {{ session('selfie_results') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400' }} rounded-full flex items-center justify-center text-sm font-bold mr-4">2</div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">Aadhaar Number</p>
                                    <p class="text-sm text-gray-600">Enter your Aadhaar details</p>
                                </div>
                                <div class="w-4 h-4 {{ session('selfie_results') ? 'bg-blue-600' : 'bg-gray-200' }} rounded-full"></div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-8 h-8 {{ session('otp_verification') ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-400' }} rounded-full flex items-center justify-center text-sm font-bold mr-4">3</div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">OTP Verification</p>
                                    <p class="text-sm text-gray-600">Verify your mobile number</p>
                                </div>
                                <div class="w-4 h-4 {{ session('otp_verification') ? 'bg-blue-600' : 'bg-gray-200' }} rounded-full"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="text-center">
                        @if(session('otp_verification'))
                            <a href="{{ route('verification.otp') }}" class="w-full bg-blue-600 text-white font-semibold py-4 px-6 rounded-xl inline-flex items-center justify-center">
                                <i class="fas fa-mobile-alt mr-2"></i>
                                Enter OTP
                            </a>
                        @elseif(session('selfie_results'))
                            <a href="{{ route('verification.aadhaar') }}" class="w-full bg-blue-600 text-white font-semibold py-4 px-6 rounded-xl inline-flex items-center justify-center">
                                <i class="fas fa-id-card mr-2"></i>
                                Enter Aadhaar Number
                            </a>
                        @else
                            <a href="{{ route('verification.selfie') }}" class="w-full bg-blue-600 text-white font-semibold py-4 px-6 rounded-xl inline-flex items-center justify-center">
                                <i class="fas fa-camera mr-2"></i>
                                Take Live Selfie
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        @if(auth()->user()->verification_status === 'verified')
        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <div class="flex items-center justify-between">
                <button class="nav-item active flex-1" onclick="switchTab('home')">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </button>
                <button class="nav-item flex-1" onclick="switchTab('vcs')">
                    <i class="fas fa-certificate"></i>
                    <span>My VCs</span>
                </button>
                <button class="nav-item flex-1" onclick="switchTab('profile')">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </button>
                <button class="nav-item flex-1" onclick="switchTab('access')">
                    <i class="fas fa-shield-alt"></i>
                    <span>Access</span>
                </button>
                <button class="nav-item flex-1" onclick="window.location.href='{{ route('data-access-control') }}'">
                    <i class="fas fa-cog"></i>
                    <span>Control</span>
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Toast Message -->
    <div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 opacity-0 z-50">
        <div class="flex items-center">
            <i class="fas fa-check mr-2"></i>
            <span id="toast-message">DID copied to clipboard!</span>
        </div>
    </div>

    <!-- QR Code Modal -->
    <div id="qrModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-6 relative w-80 flex flex-col items-center">
            <button onclick="closeQrModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Your DID QR Code</h3>
            <div id="qrCodeContainer" class="w-44 h-44 mb-4 border rounded-lg flex items-center justify-center bg-gray-50">
                <div id="qrLoading" class="text-center">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Generating QR Code...</p>
                </div>
                <canvas id="qrCodeCanvas" class="w-full h-full object-contain hidden"></canvas>
                <div id="qrError" class="text-center hidden">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl mb-2"></i>
                    <p class="text-sm text-gray-600">Failed to load QR Code</p>
                </div>
            </div>
            <div class="text-center mb-4">
                <p class="text-xs text-gray-500 mb-2">Your DID: {{ auth()->user()->did ?? 'Not available' }}</p>
                <button onclick="copyDID()" class="text-blue-600 text-sm font-medium hover:text-blue-700">
                    <i class="fas fa-copy mr-1"></i>Copy DID
                </button>
            </div>
            <button onclick="downloadQrCode()" class="mt-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center disabled:opacity-50 disabled:cursor-not-allowed" id="downloadQrBtn" disabled>
                <i class="fas fa-download mr-2"></i>Download QR
            </button>
        </div>
    </div>

    <!-- Certificate Modal -->
    <div id="certificateModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-lg shadow-lg relative w-full max-w-md max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Digital Certificate</h3>
                <button onclick="closeCertificateModal()" class="text-gray-400 hover:text-gray-700 p-1">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <!-- Certificate Content -->
            <div id="certificateContent" class="flex-1 overflow-y-auto p-4">
                <!-- Content will be loaded here -->
            </div>
            
            <!-- Footer -->
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                <div class="flex items-center justify-center text-xs text-gray-500">
                    <i class="fas fa-shield-alt mr-2 text-green-600"></i>
                    <span>Secured by SarvOne Blockchain</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab switching functionality with enhanced animations
        function switchTab(tabName) {
            // Hide all tab contents with fade out
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.opacity = '0';
                tab.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    tab.classList.remove('active');
                }, 150);
            });
            
            // Remove active class from all nav items with smooth transition
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected tab with fade in
            setTimeout(() => {
                const targetTab = document.getElementById(tabName + '-tab');
                targetTab.classList.add('active');
                setTimeout(() => {
                    targetTab.style.opacity = '1';
                    targetTab.style.transform = 'translateY(0)';
                }, 50);
            }, 150);
            
            // Add active class to the nav item for this tab
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.getAttribute('onclick') && item.getAttribute('onclick').includes("'" + tabName + "'")) {
                    item.classList.add('active');
                }
            });
        }

        // Copy DID functionality
        function copyDID() {
            const did = '{{ auth()->user()->did ?? '' }}';
            if (did) {
                navigator.clipboard.writeText(did).then(() => {
                    showToast('DID copied to clipboard!');
                }).catch(() => {
                    showToast('Failed to copy DID', 'error');
                });
            }
        }

        // Toggle VC details
        function toggleVCDetails(vcId) {
            const details = document.getElementById('vc-details-' + vcId);
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                details.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Show toast message
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');
            
            toastMessage.textContent = message;
            
            if (type === 'error') {
                toast.classList.remove('bg-green-500');
                toast.classList.add('bg-red-500');
            } else {
                toast.classList.remove('bg-red-500');
                toast.classList.add('bg-green-500');
            }
            
            toast.classList.remove('translate-x-full', 'opacity-0');
            toast.classList.add('translate-x-0', 'opacity-100');
            
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                toast.classList.remove('translate-x-0', 'opacity-100');
            }, 3000);
        }

        // QR Code Modal Functions (ensure global scope)
        function showQrModal() {
            const modal = document.getElementById('qrModal');
            const qrLoading = document.getElementById('qrLoading');
            const qrCanvas = document.getElementById('qrCodeCanvas');
            const qrError = document.getElementById('qrError');
            const downloadBtn = document.getElementById('downloadQrBtn');
            
            modal.classList.remove('hidden');
            
            // Reset states
            qrLoading.classList.remove('hidden');
            qrCanvas.classList.add('hidden');
            qrError.classList.add('hidden');
            downloadBtn.disabled = true;
            
            // Get DID from the page
            const did = '{{ auth()->user()->did ?? "" }}';
            
            if (!did) {
                qrLoading.classList.add('hidden');
                qrError.classList.remove('hidden');
                downloadBtn.disabled = true;
                return;
            }
            
            // Load QR code from server
            const img = new Image();
            img.onload = function() {
                qrLoading.classList.add('hidden');
                qrCanvas.classList.remove('hidden');
                
                // Convert image to canvas for download functionality
                const canvas = document.getElementById('qrCodeCanvas');
                const ctx = canvas.getContext('2d');
                canvas.width = this.width;
                canvas.height = this.height;
                ctx.drawImage(this, 0, 0);
                
                downloadBtn.disabled = false;
            };
            img.onerror = function() {
                console.error('QR Code image failed to load');
                qrLoading.classList.add('hidden');
                qrError.classList.remove('hidden');
                downloadBtn.disabled = true;
            };
            img.src = '{{ route('dashboard.did-qr') }}';
        }
        
        function closeQrModal() {
            document.getElementById('qrModal').classList.add('hidden');
        }
        
        function downloadQrCode() {
            const canvas = document.getElementById('qrCodeCanvas');
            if (canvas) {
                // Convert canvas to blob and download
                canvas.toBlob(function(blob) {
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'did-qr.png';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    showToast('QR Code downloaded!');
                }, 'image/png');
            } else {
                showToast('QR Code not available', 'error');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial tab
            switchTab('home');
            
            // Check selfie image
            setTimeout(checkSelfieImage, 1000);
            
            // Load VCs from database only
            loadVCs();
            
            // Load small QR code
            loadSmallQrCode();
        });
        
        // Load small QR code for the ID card
        function loadSmallQrCode() {
            const qrContainer = document.getElementById('qrCodeSmall');
            if (qrContainer) {
                const did = '{{ auth()->user()->did ?? "" }}';
                if (did) {
                    // Load small QR code from server
                    const img = new Image();
                    img.className = 'w-full h-full object-contain';
                    img.onload = function() {
                        qrContainer.innerHTML = '';
                        qrContainer.appendChild(img);
                    };
                    img.onerror = function() {
                        console.error('Small QR Code failed to load');
                        qrContainer.innerHTML = '<i class="fas fa-qrcode text-gray-400 text-xl"></i>';
                    };
                    img.src = '{{ route('dashboard.did-qr') }}?size=small';
                } else {
                    qrContainer.innerHTML = '<i class="fas fa-qrcode text-gray-400 text-xl"></i>';
                }
            }
        }

        // Check if selfie image loads properly
        function checkSelfieImage() {
            const selfieImg = document.querySelector('#home-tab img[alt="Profile Photo"]');
            if (selfieImg) {
                selfieImg.onerror = function() {
                    console.log('Selfie image failed to load:', this.src);
                    this.style.display = 'none';
                    const fallback = this.nextElementSibling;
                    if (fallback) {
                        fallback.style.display = 'flex';
                    }
                };
                selfieImg.onload = function() {
                    console.log('Selfie image loaded successfully:', this.src);
                    const fallback = this.nextElementSibling;
                    if (fallback) {
                        fallback.style.display = 'none';
                    }
                };
            }
        }



        // Load VCs from blockchain (disabled - using database only)
        function loadVCsFromBlockchain(autoLoad = false) {
            // This function is disabled - we only load from database
            console.log('Blockchain loading disabled - using database only');
            return;
        }







        // Format VC type for display
        function formatVCType(type) {
            return type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // Show error message
        function showError(message) {
            console.error('Error:', message);
            alert('Error: ' + message);
        }

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show a temporary success message
                const button = event.target.closest('button');
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check text-green-600 text-xs"></i>';
                button.title = 'Copied!';
                
                setTimeout(() => {
                    button.innerHTML = originalIcon;
                    button.title = 'Copy transaction hash';
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy to clipboard');
            });
        }
        
        // Load VCs from database
        async function loadVCs() {
            try {
                const response = await fetch('/api/my-vcs');
                const data = await response.json();
                
                if (data.success) {
                    displayVCs(data.data.vcs);
                    updateVCCount(data.data.vcs.length);
                } else {
                    showError('Failed to load credentials: ' + data.message);
                }
            } catch (error) {
                showError('Error loading credentials: ' + error.message);
            }
        }
        
        // Display VCs in the container
        function displayVCs(vcs) {
            const container = document.getElementById('vcs-container');
            const loading = document.getElementById('vcs-loading');
            
            if (vcs.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-gray-500">
                            <i class="fas fa-certificate text-4xl mb-4"></i>
                            <p class="text-lg font-medium">No credentials found</p>
                            <p class="text-sm">You haven't been issued any verifiable credentials yet.</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            // Hide loading if it exists
            if (loading) {
                loading.style.display = 'none';
            }
            
            const vcsHTML = vcs.map(vc => `
                <div class="vc-card bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-certificate text-purple-600 text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">${vc.recipient_name}</h4>
                                    <p class="text-sm text-gray-600">${vc.vc_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <span class="status-badge status-${vc.status} px-2 py-1 rounded-full text-xs font-medium">
                                    ${vc.status.toUpperCase()}
                                </span>
                                <span class="text-xs text-gray-500">
                                    Issued ${new Date(vc.issued_at).toLocaleDateString()}
                                </span>
                                ${vc.blockchain_verified ? 
                                    '<span class="text-xs text-green-600"><i class="fas fa-check-circle"></i> Verified</span>' : 
                                    '<span class="text-xs text-yellow-600"><i class="fas fa-clock"></i> Not verified</span>'
                                }
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-gray-400 hover:text-gray-600" onclick="toggleVCDetails('${vc.vc_id}', this)">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="border-t pt-3">
                        <div class="flex items-center justify-between text-sm">
                            <div>
                                <span class="text-gray-500">Issued by</span>
                                <p class="font-medium">${vc.issuer_name}</p>
                            </div>
                            <div class="flex space-x-2">
                                ${vc.ipfs_url ? 
                                    `<a href="${vc.ipfs_url}" target="_blank" class="text-blue-600 hover:text-blue-700">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>` : ''
                                }
                                ${vc.blockchain_url ? 
                                    `<a href="${vc.blockchain_url}" target="_blank" class="text-green-600 hover:text-green-700">
                                        <i class="fas fa-link text-xs"></i>
                                    </a>` : ''
                                }
                            </div>
                        </div>
                    </div>
                    
                    <!-- Expandable Details -->
                    <div id="vc-details-${vc.vc_id}" class="hidden mt-4 pt-4 border-t">
                        <div class="space-y-3">
                            <div>
                                <span class="text-sm font-medium text-gray-700">VC ID</span>
                                <p class="text-xs font-mono text-gray-600 break-all">${vc.vc_id}</p>
                            </div>
                            
                            ${vc.expires_at ? `
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Expires</span>
                                    <p class="text-sm text-gray-600">${new Date(vc.expires_at).toLocaleDateString()}</p>
                                </div>
                            ` : ''}
                            
                            ${vc.last_blockchain_sync ? `
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Last Verified</span>
                                    <p class="text-sm text-gray-600">${new Date(vc.last_blockchain_sync).toLocaleString()}</p>
                                </div>
                            ` : ''}
                            
                            ${Object.keys(vc.credential_data || {}).length > 0 ? `
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">Credential Data</span>
                                        <button onclick="showCertificateModal('${vc.vc_id}', '${vc.vc_type}')" 
                                                class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded-lg hover:bg-blue-100 transition-colors"
                                                data-credential='${JSON.stringify(vc.credential_data)}'>
                                            <i class="fas fa-certificate mr-1"></i>View Certificate
                                        </button>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        ${formatCredentialData(vc.credential_data, vc.vc_type)}
                                    </div>
                                </div>
                            ` : ''}
                            
                            <div class="flex flex-col sm:flex-row gap-2">
                                <button onclick="verifyVCOnBlockchain('${vc.vc_id}', this)" class="hidden flex-1 bg-yellow-50 text-yellow-600 px-2 py-1.5 rounded-lg text-center text-xs font-medium hover:bg-yellow-100 transition-colors">
                                    <i class="fas fa-shield-alt mr-1"></i>Verify on<br>Blockchain
                                </button>
                                
                                ${vc.ipfs_url ? `
                                    <a href="${vc.ipfs_url}" target="_blank" class="flex-1 bg-blue-50 text-blue-600 px-2 py-1.5 rounded-lg text-center text-xs font-medium hover:bg-blue-100 transition-colors">
                                        <i class="fas fa-external-link-alt mr-1"></i>View on<br>IPFS
                                    </a>
                                ` : ''}
                                
                                ${vc.blockchain_url ? `
                                    <a href="${vc.blockchain_url}" target="_blank" class="flex-1 bg-green-50 text-green-600 px-2 py-1.5 rounded-lg text-center text-xs font-medium hover:bg-green-100 transition-colors">
                                        <i class="fas fa-link mr-1"></i>View on<br>Blockchain
                                    </a>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = vcsHTML;
        }
        
        // Verify VC on blockchain
        async function verifyVCOnBlockchain(vcId, buttonElement) {
            try {
                const button = buttonElement || event?.target;
                if (!button) {
                    console.error('Button element not found');
                    return;
                }
                
                const originalText = button.innerHTML;
                
                // Show loading state
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Verifying...';
                button.disabled = true;
                
                const response = await fetch('/api/verify-vc-blockchain', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ vc_id: vcId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.data.verified) {
                        alert('Success: Credential verified successfully!');
                        // Reload VCs to update verification status
                        loadVCs();
                    } else {
                        alert('Error: Verification failed: ' + data.data.verification_details);
                    }
                } else {
                    alert('Error: Verification failed: ' + data.message);
                }
            } catch (error) {
                alert('Error: Error during verification: ' + error.message);
            } finally {
                // Reset button
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }
        
        // Update VC count
        function updateVCCount(count) {
            const countElement = document.getElementById('vc-count');
            if (countElement) {
                countElement.textContent = count;
            }
        }
        
        // Toggle VC details
        function toggleVCDetails(vcId, buttonElement) {
            const details = document.getElementById(`vc-details-${vcId}`);
            const button = buttonElement || event?.target?.closest('button');
            if (!button) {
                console.error('Button element not found');
                return;
            }
            const icon = button.querySelector('i');
            
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                details.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
        
        // Load VCs when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadVCs();
            loadAccessHistory();
        });

        // Load access history for dashboard tab
        async function loadAccessHistory() {
            const loadingDiv = document.getElementById('access-loading');
            const summaryDiv = document.getElementById('access-logs-summary');
            const emptyDiv = document.getElementById('access-empty');
            
            // Show loading
            loadingDiv.classList.remove('hidden');
            summaryDiv.classList.add('hidden');
            emptyDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/api/user/access-logs', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success && data.logs.length > 0) {
                    displayAccessSummary(data.logs.slice(0, 3)); // Show only 3 most recent
                } else {
                    emptyDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Error loading access history:', error);
                emptyDiv.classList.remove('hidden');
            } finally {
                loadingDiv.classList.add('hidden');
            }
        }

        // Display access summary in dashboard
        function displayAccessSummary(logs) {
            const summaryDiv = document.getElementById('access-logs-summary');
            
            summaryDiv.innerHTML = logs.map(log => `
                <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-200">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <i class="fas fa-building text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">
                                    ${log.organization_name}
                                </div>
                                <div class="text-xs text-gray-500">
                                    ${new Date(log.created_at).toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })}
                                </div>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                            ${log.status === 'success' ? 'bg-green-100 text-green-800' :
                              log.status === 'failed' ? 'bg-red-100 text-red-800' :
                              'bg-yellow-100 text-yellow-800'}">
                            ${log.status.charAt(0).toUpperCase() + log.status.slice(1)}
                        </span>
                    </div>
                    
                    <div class="text-xs text-gray-600 mb-2">
                        <span class="font-medium">Credential:</span> ${log.credential_type.replace(/_/g, ' ')}
                    </div>
                    
                    <div class="flex items-center text-xs text-gray-500">
                        <i class="fas fa-bell text-green-600 mr-1"></i>
                        <span>SMS notification sent</span>
                    </div>
                </div>
            `).join('');
            
            summaryDiv.classList.remove('hidden');
        }

        // Certificate Modal Functions
        function showCertificateModal(vcId, vcType) {
            const modal = document.getElementById('certificateModal');
            const contentDiv = document.getElementById('certificateContent');
            const modalTitle = document.querySelector('#certificateModal h3');
            
            // Get credential data from the button's data attribute
            const button = event.target.closest('button');
            const credentialData = JSON.parse(button.getAttribute('data-credential'));

            // Update modal title
            modalTitle.textContent = `${vcType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())} Certificate`;
            
            // Create certificate design
            contentDiv.innerHTML = createCertificateDesign(vcId, vcType, credentialData);
            modal.classList.remove('hidden');
        }

        function createCertificateDesign(vcId, vcType, credentialData) {
            const credentialType = vcType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            const icon = getCredentialIcon(vcType);
            const color = getCredentialColor(vcType);
            
            return `
                <div class="bg-gradient-to-br from-${color}-50 to-white border-2 border-${color}-200 rounded-xl p-6 shadow-sm">
                    <!-- Certificate Header -->
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-${color}-100 rounded-full mb-4">
                            <i class="${icon} text-${color}-600 text-2xl"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 mb-2">${credentialType}</h2>
                        <p class="text-sm text-gray-600">Digital Verifiable Credential</p>
                    </div>

                    <!-- Certificate Content -->
                    <div class="space-y-4">
                        <!-- VC ID -->
                        <div class="bg-white rounded-lg p-3 border border-gray-200">
                            <div class="text-xs font-medium text-gray-500 mb-1">Credential ID</div>
                            <div class="text-xs font-mono text-gray-700 break-all">${vcId}</div>
                        </div>

                        <!-- Credential Data -->
                        <div class="bg-white rounded-lg p-3 border border-gray-200">
                            <div class="text-xs font-medium text-gray-500 mb-3">Credential Details</div>
                            <div class="space-y-2">
                                ${formatCertificateData(credentialData, vcType)}
                            </div>
                        </div>

                        <!-- Certificate Footer -->
                        <div class="text-center pt-4">
                            <div class="inline-flex items-center text-xs text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                <span>Issued on ${new Date().toLocaleDateString('en-US', { 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                })}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function getCredentialIcon(vcType) {
            const icons = {
                'aadhaar_card': 'fas fa-id-card',
                'pan_card': 'fas fa-credit-card',
                'voter_id': 'fas fa-vote-yea',
                'driving_license': 'fas fa-car',
                'passport': 'fas fa-passport',
                'degree': 'fas fa-graduation-cap',
                'bank_account': 'fas fa-university',
                'loan_approval': 'fas fa-hand-holding-usd',
                'credit_score': 'fas fa-chart-line',
                'default': 'fas fa-certificate'
            };
            return icons[vcType] || icons.default;
        }

        function getCredentialColor(vcType) {
            const colors = {
                'aadhaar_card': 'blue',
                'pan_card': 'green',
                'voter_id': 'purple',
                'driving_license': 'orange',
                'passport': 'indigo',
                'degree': 'teal',
                'bank_account': 'emerald',
                'loan_approval': 'amber',
                'credit_score': 'rose',
                'default': 'gray'
            };
            return colors[vcType] || colors.default;
        }

        function closeCertificateModal() {
            document.getElementById('certificateModal').classList.add('hidden');
        }

        // Format credential data for display
        function formatCredentialData(data, vcType) {
            if (!data || Object.keys(data).length === 0) {
                return '<div class="text-xs text-gray-500 italic">No data available</div>';
            }

            // Filter out unwanted fields and parse nested objects
            const filteredData = filterCredentialData(data, vcType);
            
            if (Object.keys(filteredData).length === 0) {
                return '<div class="text-xs text-gray-500 italic">No relevant data available</div>';
            }

            // Special formatting for Aadhaar card
            if (vcType === 'aadhaar_card') {
                return formatAadhaarCardData(filteredData);
            }

            let formattedData = '';

            for (const [key, value] of Object.entries(filteredData)) {
                const displayKey = formatFieldName(key);
                const displayValue = formatFieldValue(value, key);
                const displayClass = getValueDisplayClass(value);

                formattedData += `
                    <div class="flex justify-between items-start py-1 border-b border-gray-100 last:border-b-0">
                        <span class="text-xs font-medium text-gray-600 flex-shrink-0 mr-2 min-w-[80px]">${displayKey}</span>
                        <span class="text-xs ${displayClass} text-right break-words max-w-[70%] leading-tight">${displayValue}</span>
                    </div>
                `;
            }
            return formattedData;
        }

        // Special formatting for Aadhaar card data
        function formatAadhaarCardData(data) {
            const importantFields = ['aadhaar_number', 'name', 'date_of_birth', 'gender', 'address'];
            let formattedData = '';

            // Show important fields first
            for (const field of importantFields) {
                if (data[field]) {
                    const displayKey = formatFieldName(field);
                    const displayValue = formatFieldValue(data[field], field);
                    const displayClass = getValueDisplayClass(data[field]);

                    formattedData += `
                        <div class="flex justify-between items-start py-1 border-b border-gray-100 last:border-b-0">
                            <span class="text-xs font-medium text-gray-600 flex-shrink-0 mr-2 min-w-[80px]">${displayKey}</span>
                            <span class="text-xs ${displayClass} text-right break-words max-w-[70%] leading-tight">${displayValue}</span>
                        </div>
                    `;
                }
            }

            // Show other fields
            for (const [key, value] of Object.entries(data)) {
                if (!importantFields.includes(key)) {
                    const displayKey = formatFieldName(key);
                    const displayValue = formatFieldValue(value, key);
                    const displayClass = getValueDisplayClass(value);

                    formattedData += `
                        <div class="flex justify-between items-start py-1 border-b border-gray-100 last:border-b-0">
                            <span class="text-xs font-medium text-gray-600 flex-shrink-0 mr-2 min-w-[80px]">${displayKey}</span>
                            <span class="text-xs ${displayClass} text-right break-words max-w-[70%] leading-tight">${displayValue}</span>
                        </div>
                    `;
                }
            }

            return formattedData;
        }

        // Filter out unwanted fields and parse nested objects
        function filterCredentialData(data, vcType) {
            const unwantedFields = ['id', 'simulation', 'issuer_did', 'subject_did', 'vc_id', 'vc_type', 'photo_url'];
            const filtered = {};

            // First, check if there's a nested credential object
            let hasNestedCredential = false;
            
            for (const [key, value] of Object.entries(data)) {
                if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                    if (key === vcType || key.includes('_card') || key.includes('_id') || key.includes('_license') || key.includes('_passport')) {
                        hasNestedCredential = true;
                        // Extract nested fields from the credential object
                        for (const [nestedKey, nestedValue] of Object.entries(value)) {
                            if (!unwantedFields.includes(nestedKey)) {
                                filtered[nestedKey] = nestedValue;
                            }
                        }
                        break; // Found the nested credential, no need to process other fields
                    }
                }
            }

            // If no nested credential found, process top-level fields
            if (!hasNestedCredential) {
                for (const [key, value] of Object.entries(data)) {
                    // Skip unwanted fields
                    if (unwantedFields.includes(key)) {
                        continue;
                    }

                    // Handle nested objects (like address)
                    if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                        if (key === 'address') {
                            // Handle address objects specially
                            filtered[key] = value;
                        } else if (key === 'aadhaar_card') {
                            // Handle Aadhaar card object specially
                            filtered[key] = value;
                        }
                    } else {
                        // Only include non-null, non-empty values
                        if (value !== null && value !== undefined && value !== '') {
                            filtered[key] = value;
                        }
                    }
                }
            }

            return filtered;
        }

        // Format field names for display
        function formatFieldName(key) {
            const fieldMappings = {
                'aadhaar_number': 'Aadhaar Number',
                'name': 'Full Name',
                'date_of_birth': 'Date of Birth',
                'gender': 'Gender',
                'address': 'Address',
                'photo_url': 'Photo',
                'issued_date': 'Issued Date',
                'valid_until': 'Valid Until',
                'issuing_authority': 'Issuing Authority',
                'pan_number': 'PAN Number',
                'voter_id_number': 'Voter ID Number',
                'driving_license_number': 'License Number',
                'passport_number': 'Passport Number',
                'degree_name': 'Degree Name',
                'specialization': 'Specialization',
                'cgpa_percentage': 'CGPA/Percentage',
                'year_of_graduation': 'Year of Graduation',
                'student_id': 'Student ID',
                'account_number': 'Account Number',
                'account_type': 'Account Type',
                'account_status': 'Account Status',
                'opening_date': 'Opening Date',
                'loan_amount': 'Loan Amount',
                'interest_rate': 'Interest Rate',
                'tenure_months': 'Tenure (Months)',
                'approval_date': 'Approval Date',
                'loan_id': 'Loan ID',
                'credit_score': 'Credit Score',
                'credit_rating': 'Credit Rating',
                'house_number': 'House Number',
                'street': 'Street',
                'area': 'Area',
                'city': 'City',
                'district': 'District',
                'state': 'State',
                'pincode': 'Pincode'
            };

            return fieldMappings[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // Format field values for display
        function formatFieldValue(value, key) {
            if (value === null || value === undefined) {
                return 'Not provided';
            }

            if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                // Handle address objects
                if (key === 'address') {
                    const addressParts = [];
                    if (value.house_number) addressParts.push(value.house_number);
                    if (value.street) addressParts.push(value.street);
                    if (value.area) addressParts.push(value.area);
                    if (value.city) addressParts.push(value.city);
                    if (value.district) addressParts.push(value.district);
                    if (value.state) addressParts.push(value.state);
                    if (value.pincode) addressParts.push(value.pincode);
                    return addressParts.join(', ');
                }
                
                // Handle Aadhaar card object - extract and format the data
                if (key === 'aadhaar_card') {
                    const aadhaarData = value;
                    const importantFields = ['aadhaar_number', 'name', 'date_of_birth', 'gender'];
                    const formattedFields = [];
                    
                                            for (const field of importantFields) {
                            if (aadhaarData[field]) {
                                let displayValue = aadhaarData[field];
                                if (field === 'date_of_birth') {
                                    displayValue = new Date(displayValue).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'short',
                                        day: 'numeric'
                                    });
                                }
                                formattedFields.push(`${formatFieldName(field)}: ${displayValue}`);
                            }
                        }
                    
                    // Add address if available
                    if (aadhaarData.address && typeof aadhaarData.address === 'object') {
                        const addressParts = [];
                        if (aadhaarData.address.house_number) addressParts.push(aadhaarData.address.house_number);
                        if (aadhaarData.address.street) addressParts.push(aadhaarData.address.street);
                        if (aadhaarData.address.area) addressParts.push(aadhaarData.address.area);
                        if (aadhaarData.address.city) addressParts.push(aadhaarData.address.city);
                        if (aadhaarData.address.state) addressParts.push(aadhaarData.address.state);
                        if (aadhaarData.address.pincode) addressParts.push(aadhaarData.address.pincode);
                        formattedFields.push(`Address: ${addressParts.join(', ')}`);
                    }
                    
                    return formattedFields.join(' | ');
                }
                
                // For other objects, show a summary instead of raw JSON
                const keys = Object.keys(value);
                if (keys.length <= 3) {
                    return keys.map(k => `${k}: ${value[k]}`).join(', ');
                } else {
                    return `${keys.length} fields available`;
                }
            }

            // Format specific field types
            if (key === 'date_of_birth' || key === 'issued_date' || key === 'valid_until' || key === 'opening_date' || key === 'approval_date') {
                return new Date(value).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            }

            if (key === 'gender') {
                return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
            }

            if (key === 'aadhaar_number') {
                // Display full Aadhaar number
                return value.toString();
            }

            if (key === 'photo_url') {
                return 'Photo Available';
            }

            // Truncate long values
            if (typeof value === 'string' && value.length > 30) {
                return value.substring(0, 30) + '...';
            }

            return value;
        }

        // Get CSS class for value display
        function getValueDisplayClass(value) {
            if (value === null || value === undefined) {
                return 'text-gray-400 italic';
            }
            if (typeof value === 'object' && value !== null && !Array.isArray(value)) {
                return 'text-gray-600';
            }
            return 'text-gray-800';
        }

        // Format certificate data for modal display
        function formatCertificateData(data, vcType) {
            const filteredData = filterCredentialData(data, vcType);
            
            if (Object.keys(filteredData).length === 0) {
                return '<div class="text-xs text-gray-500 italic text-center py-2">No relevant data available</div>';
            }

            // Special handling for Aadhaar card in certificate modal
            if (vcType === 'aadhaar_card') {
                return formatAadhaarCardCertificateData(filteredData);
            }

            let formattedData = '';

            for (const [key, value] of Object.entries(filteredData)) {
                const displayKey = formatFieldName(key);
                const displayValue = formatFieldValue(value, key);
                const displayClass = getValueDisplayClass(value);

                formattedData += `
                    <div class="flex justify-between items-start py-1 border-b border-gray-100 last:border-b-0">
                        <span class="text-xs font-medium text-gray-600 flex-shrink-0">${displayKey}</span>
                        <span class="text-xs ${displayClass} text-right ml-2 break-words">${displayValue}</span>
                    </div>
                `;
            }
            return formattedData;
        }

        // Special formatting for Aadhaar card certificate data
        function formatAadhaarCardCertificateData(data) {
            let formattedData = '';
            
            // Check if we have Aadhaar card object
            if (data.aadhaar_card && typeof data.aadhaar_card === 'object') {
                const aadhaarData = data.aadhaar_card;
                const importantFields = ['aadhaar_number', 'name', 'date_of_birth', 'gender', 'address', 'issued_date', 'valid_until', 'issuing_authority'];
                
                for (const field of importantFields) {
                    if (aadhaarData[field]) {
                        const displayKey = formatFieldName(field);
                        const displayValue = formatFieldValue(aadhaarData[field], field);
                        const displayClass = getValueDisplayClass(aadhaarData[field]);

                        formattedData += `
                            <div class="flex justify-between items-start py-1 border-b border-gray-100 last:border-b-0">
                                <span class="text-xs font-medium text-gray-600 flex-shrink-0">${displayKey}</span>
                                <span class="text-xs ${displayClass} text-right ml-2 break-words">${displayValue}</span>
                            </div>
                        `;
                    }
                }
            } else {
                // Fallback to regular formatting
                for (const [key, value] of Object.entries(data)) {
                    const displayKey = formatFieldName(key);
                    const displayValue = formatFieldValue(value, key);
                    const displayClass = getValueDisplayClass(value);

                    formattedData += `
                        <div class="flex justify-between items-start py-1 border-b border-gray-100 last:border-b-0">
                            <span class="text-xs font-medium text-gray-600 flex-shrink-0">${displayKey}</span>
                            <span class="text-xs ${displayClass} text-right ml-2 break-words">${displayValue}</span>
                        </div>
                    `;
                }
            }
            
            return formattedData;
        }
    </script>
</body>
</html> 