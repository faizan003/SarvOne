<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Government Admin - Organization Approval</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if(file_exists(public_path('build/manifest.json')))
        <link rel="stylesheet" href="{{ asset('build/assets/app-3BkLTkwI.css') }}">
    @endif
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global AJAX CSRF token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
    </script>
    <style>
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white !important;
        }
        .stat-card.pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
            color: white !important;
        }
        .stat-card.approved {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
            color: white !important;
        }
        .stat-card.rejected {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%) !important;
            color: white !important;
        }
        
        /* Ensure icons are visible */
        .stat-card i {
            color: white !important;
            font-size: 1.5rem !important;
        }
        
        /* Force card styles */
        .stat-card * {
            color: white !important;
        }
        
        /* Ensure responsive design works */
        @media (max-width: 640px) {
            .sticky { position: sticky !important; }
            .top-0 { top: 0 !important; }
            .z-40 { z-index: 40 !important; }
            .truncate { 
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                white-space: nowrap !important;
            }
            .break-words { 
                overflow-wrap: break-word !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
            }
            .flex-col { flex-direction: column !important; }
            .w-full { width: 100% !important; }
            .space-y-2 > * + * { margin-top: 0.5rem !important; }
            .space-y-3 > * + * { margin-top: 0.75rem !important; }
            .space-y-4 > * + * { margin-top: 1rem !important; }
        }
        
        /* Additional mobile optimizations */
        .min-w-0 { min-width: 0 !important; }
        .flex-shrink-0 { flex-shrink: 0 !important; }
        .overflow-y-auto { overflow-y: auto !important; }
        .max-h-screen { max-height: 100vh !important; }
        
        /* Ensure Tailwind styles are applied */
        .bg-gray-50 { background-color: #f9fafb !important; }
        .bg-white { background-color: #ffffff !important; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }
        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important; }
        .border-b { border-bottom-width: 1px !important; }
        .border-gray-200 { border-color: #e5e7eb !important; }
        .max-w-7xl { max-width: 80rem !important; }
        .mx-auto { margin-left: auto !important; margin-right: auto !important; }
        .px-4 { padding-left: 1rem !important; padding-right: 1rem !important; }
        .py-3 { padding-top: 0.75rem !important; padding-bottom: 0.75rem !important; }
        .py-4 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
        .py-8 { padding-top: 2rem !important; padding-bottom: 2rem !important; }
        .flex { display: flex !important; }
        .justify-between { justify-content: space-between !important; }
        .items-center { align-items: center !important; }
        .space-x-2 > * + * { margin-left: 0.5rem !important; }
        .space-x-4 > * + * { margin-left: 1rem !important; }
        .text-xl { font-size: 1.25rem !important; line-height: 1.75rem !important; }
        .text-lg { font-size: 1.125rem !important; line-height: 1.75rem !important; }
        .text-sm { font-size: 0.875rem !important; line-height: 1.25rem !important; }
        .text-xs { font-size: 0.75rem !important; line-height: 1rem !important; }
        .text-3xl { font-size: 1.875rem !important; line-height: 2.25rem !important; }
        .text-2xl { font-size: 1.5rem !important; line-height: 2rem !important; }
        .font-bold { font-weight: 700 !important; }
        .font-medium { font-weight: 500 !important; }
        .text-gray-900 { color: #111827 !important; }
        .text-gray-600 { color: #4b5563 !important; }
        .text-white { color: #ffffff !important; }
        .rounded-lg { border-radius: 0.5rem !important; }
        .rounded-xl { border-radius: 0.75rem !important; }
        .grid { display: grid !important; }
        .grid-cols-1 { grid-template-columns: repeat(1, minmax(0, 1fr)) !important; }
        .gap-4 { gap: 1rem !important; }
        .gap-6 { gap: 1.5rem !important; }
        .p-6 { padding: 1.5rem !important; }
        .mb-6 { margin-bottom: 1.5rem !important; }
        .mb-8 { margin-bottom: 2rem !important; }
        .hidden { display: none !important; }
        .h-12 { height: 3rem !important; }
        .w-12 { width: 3rem !important; }
        .bg-opacity-20 { background-color: rgba(255, 255, 255, 0.2) !important; }
        .opacity-90 { opacity: 0.9 !important; }
        
        @media (min-width: 640px) {
            .sm\:px-6 { padding-left: 1.5rem !important; padding-right: 1.5rem !important; }
            .sm\:py-4 { padding-top: 1rem !important; padding-bottom: 1rem !important; }
            .sm\:grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
            .sm\:gap-6 { gap: 1.5rem !important; }
            .sm\:mb-8 { margin-bottom: 2rem !important; }
            .sm\:block { display: block !important; }
            .sm\:hidden { display: none !important; }
            .sm\:flex { display: flex !important; }
            .sm\:space-x-8 > * + * { margin-left: 2rem !important; }
        }
        
        @media (min-width: 1024px) {
            .lg\:px-8 { padding-left: 2rem !important; padding-right: 2rem !important; }
            .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3 sm:py-4">
                <div class="flex items-center min-w-0 flex-1">
                    <div class="h-8 w-8 sm:h-10 sm:w-10 bg-indigo-600 rounded-lg flex items-center justify-center mr-2 sm:mr-3 flex-shrink-0">
                        <i class="fas fa-shield-alt text-white text-sm sm:text-lg"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h1 class="text-base sm:text-xl font-bold text-gray-900 truncate">SarvOne Government Admin</h1>
                        <p class="text-xs sm:text-sm text-gray-600 truncate">Organization Approval Dashboard</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
                    <!-- API Documentation Button -->
                    <a href="{{ route('government.api-documentation') }}" 
                       class="bg-indigo-600 text-white px-3 py-2 rounded-lg hover:bg-indigo-700 transition duration-200 text-sm font-medium hidden sm:flex items-center">
                        <i class="fas fa-code mr-2"></i>
                        API Docs
                    </a>
                    
                    <!-- Add Scheme Button -->
                    <a href="{{ route('government.create-scheme') }}" 
                       class="bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 transition duration-200 text-sm font-medium hidden sm:flex items-center">
                        <i class="fas fa-plus mr-2"></i>
                        Add Scheme
                    </a>
                    
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-medium text-gray-900">Admin User</p>
                        <p class="text-xs text-gray-600">Government Approver</p>
                    </div>
                    <div class="h-6 w-6 sm:h-8 sm:w-8 bg-gray-300 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-gray-600 text-xs sm:text-sm"></i>
                    </div>
                    <!-- Mobile menu button -->
                    <button class="sm:hidden p-2 rounded-md text-gray-400 hover:text-gray-600" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Admin Info (hidden by default) -->
    <div id="mobileAdminInfo" class="hidden sm:hidden bg-gray-50 border-b">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="text-center space-y-3">
                <div>
                    <p class="text-sm font-medium text-gray-900">Admin User</p>
                    <p class="text-xs text-gray-600">Government Approver</p>
                </div>
                <!-- Mobile API Documentation Button -->
                <a href="{{ route('government.api-documentation') }}" 
                   class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition duration-200 text-sm font-medium inline-flex items-center">
                    <i class="fas fa-code mr-2"></i>
                    API Documentation
                </a>
                
                <!-- Mobile Add Scheme Button -->
                <a href="{{ route('government.create-scheme') }}" 
                   class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200 text-sm font-medium inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>
                    Add Scheme
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
            <!-- Total Organizations -->
            <div class="stat-card text-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Total Organizations</p>
                        <p class="text-3xl font-bold">{{ $stats['total'] }}</p>
                    </div>
                    <div class="h-12 w-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Approval -->
            <div class="stat-card pending text-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Pending Approval</p>
                        <p class="text-3xl font-bold">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="h-12 w-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Approved -->
            <div class="stat-card approved text-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Approved</p>
                        <p class="text-3xl font-bold">{{ $stats['approved'] }}</p>
                    </div>
                    <div class="h-12 w-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Rejected -->
            <div class="stat-card rejected text-white rounded-xl p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Rejected</p>
                        <p class="text-3xl font-bold">{{ $stats['rejected'] }}</p>
                    </div>
                    <div class="h-12 w-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm mb-6 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('government.create-scheme') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition duration-200">
                    <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-plus text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">Add New Scheme</p>
                        <p class="text-sm opacity-90">Create government schemes</p>
                    </div>
                </a>
                
                <a href="{{ route('government.schemes') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-200">
                    <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-list text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">Manage Schemes</p>
                        <p class="text-sm opacity-90">View and edit schemes</p>
                    </div>
                </a>
                
                <a href="{{ route('government.opportunity-hub') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-200">
                    <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-lightbulb text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">View Opportunity Hub</p>
                        <p class="text-sm opacity-90">See user experience</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.simulate.documents') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 transition duration-200">
                    <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-file-alt text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">Issue Test Documents</p>
                        <p class="text-sm opacity-90">Simulate government docs</p>
                    </div>
                </a>
                
                <a href="{{ route('government.api-documentation') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition duration-200">
                    <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                        <i class="fas fa-code text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">API Documentation</p>
                        <p class="text-sm opacity-90">Integration guide & keys</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="bg-white rounded-lg shadow-sm mb-6">
            <div class="border-b border-gray-200">
                <!-- Desktop Tabs -->
                <nav class="hidden sm:flex space-x-8 px-6" aria-label="Tabs">
                    <button class="tab-button active border-b-2 border-indigo-500 py-4 px-1 text-sm font-medium text-indigo-600" data-tab="pending">
                        <i class="fas fa-clock mr-2"></i>Pending Approval
                        <span class="bg-red-100 text-red-800 ml-2 px-2 py-1 rounded-full text-xs">{{ $stats['pending'] }}</span>
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="approved">
                        <i class="fas fa-check-circle mr-2"></i>Approved
                        <span class="bg-green-100 text-green-800 ml-2 px-2 py-1 rounded-full text-xs">{{ $stats['approved'] }}</span>
                    </button>
                    <button class="tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="rejected">
                        <i class="fas fa-times-circle mr-2"></i>Rejected
                        <span class="bg-red-100 text-red-800 ml-2 px-2 py-1 rounded-full text-xs">{{ $stats['rejected'] }}</span>
                    </button>
                </nav>

                <!-- Mobile Tabs -->
                <div class="sm:hidden px-4 py-3">
                    <select id="mobileTabSelect" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="pending">🕒 Pending Approval ({{ $stats['pending'] }})</option>
                        <option value="approved">✅ Approved ({{ $stats['approved'] }})</option>
                        <option value="rejected">❌ Rejected ({{ $stats['rejected'] }})</option>
                    </select>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-4 sm:p-6">
                <!-- Pending Organizations Tab -->
                <div id="pending" class="tab-content active">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-3 sm:space-y-0">
                        <h3 class="text-lg font-medium text-gray-900">Organizations Pending Approval</h3>
                        <button onclick="refreshOrganizations('pending')" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition duration-200 w-full sm:w-auto text-center">
                            <i class="fas fa-refresh mr-2"></i>Refresh
                        </button>
                    </div>
                    <div id="pending-content">
                        <!-- Content will be loaded here -->
                        <div class="flex justify-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                        </div>
                    </div>
                </div>

                <!-- Approved Organizations Tab -->
                <div id="approved" class="tab-content">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-3 sm:space-y-0">
                        <h3 class="text-lg font-medium text-gray-900">Approved Organizations</h3>
                        <button onclick="refreshOrganizations('approved')" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-200 w-full sm:w-auto text-center">
                            <i class="fas fa-refresh mr-2"></i>Refresh
                        </button>
                    </div>
                    <div id="approved-content">
                        <!-- Content will be loaded here -->
                    </div>
                </div>

                <!-- Rejected Organizations Tab -->
                <div id="rejected" class="tab-content">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-3 sm:space-y-0">
                        <h3 class="text-lg font-medium text-gray-900">Rejected Organizations</h3>
                        <button onclick="refreshOrganizations('rejected')" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200 w-full sm:w-auto text-center">
                            <i class="fas fa-refresh mr-2"></i>Refresh
                        </button>
                    </div>
                    <div id="rejected-content">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Organization Details Modal -->
    <div id="organizationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 p-4">
        <div class="relative top-4 sm:top-20 mx-auto border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[calc(100vh-2rem)] sm:max-h-[calc(100vh-10rem)] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-4 sm:px-6 py-4 z-10">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Organization Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-2">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-4 sm:p-6">
                <div id="modalContent">
                    <!-- Organization details will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-md w-full mx-4 relative">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>Approve Organization
                    </h3>
                    <button onclick="closeApprovalModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div>
                    <label for="approvalRemarks" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-comment mr-2"></i>Approval Remarks (Optional)
                    </label>
                    <textarea 
                        id="approvalRemarks" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                        placeholder="Enter any remarks or notes for this approval..."></textarea>
                </div>

                <div>
                    <label for="didPrefix" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-fingerprint mr-2"></i>Organization Identifier (Optional)
                    </label>
                    <input 
                        type="text" 
                        id="didPrefix" 
                        maxlength="15"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        placeholder="e.g., statebankindia, reliancejio, bharatgovt">
                    <div class="mt-2 text-xs text-gray-500">
                        <p class="mb-1"><strong>W3C Compliant DID Format:</strong></p>
                        <p class="font-mono bg-gray-50 p-2 rounded border">did:sarvone:{org-type}:{identifier}:{timestamp}:{sequence}:{checksum}</p>
                        <ul class="mt-2 space-y-1">
                            <li>• <strong>Identifier:</strong> Custom name (up to 15 chars) or auto-generated from company name</li>
                            <li>• <strong>Org-type:</strong> Automatically determined from organization type</li>
                            <li>• <strong>Timestamp:</strong> Approval time for uniqueness</li>
                            <li>• <strong>Sequence:</strong> Sequential number for this org type</li>
                            <li>• <strong>Checksum:</strong> Security validation hash</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200 px-6 pb-6">
                <button onclick="closeApprovalModal()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition duration-200">
                    Cancel
                </button>
                <button onclick="confirmApproval()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200">
                    <i class="fas fa-check mr-2"></i>Confirm Approval
                </button>
            </div>
        </div>
    </div>

    <!-- Blockchain Processing Animation Modal -->
    <div id="blockchainModal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-60 flex items-center justify-center p-2 sm:p-4">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-2 sm:mx-4 relative overflow-hidden max-h-screen overflow-y-auto">
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 sm:p-6">
                <h3 class="text-lg sm:text-xl font-bold flex items-center">
                    <i class="fas fa-cog fa-spin mr-2 sm:mr-3"></i>Blockchain Processing
                </h3>
                <p class="text-blue-100 mt-1 text-sm sm:text-base">Please wait while we process your approval on the blockchain...</p>
            </div>

            <div class="p-4 sm:p-6">
                <!-- Progress Steps -->
                <div id="progressSteps" class="space-y-4 sm:space-y-6">
                    <!-- Step 1: DID Generation -->
                    <div id="step1" class="flex items-start sm:items-center space-x-3 sm:space-x-4 transition-all duration-500">
                        <div class="flex-shrink-0 mt-1 sm:mt-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gray-200 flex items-center justify-center step-icon">
                                <i class="fas fa-fingerprint text-gray-400 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Generating W3C Compliant DID</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Creating unique decentralized identifier for organization...</p>
                            <div id="didOutput" class="hidden mt-2 p-2 bg-gray-50 rounded text-xs font-mono break-all"></div>
                        </div>
                    </div>

                    <!-- Step 2: Smart Contract -->
                    <div id="step2" class="flex items-start sm:items-center space-x-3 sm:space-x-4 transition-all duration-500">
                        <div class="flex-shrink-0 mt-1 sm:mt-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gray-200 flex items-center justify-center step-icon">
                                <i class="fas fa-file-contract text-gray-400 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Smart Contract Interaction</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Calling approveOrganization function on SarvOne contract...</p>
                            <div id="contractOutput" class="hidden mt-2 p-2 bg-gray-50 rounded text-xs font-mono break-all whitespace-pre-wrap"></div>
                        </div>
                    </div>

                    <!-- Step 3: Transaction -->
                    <div id="step3" class="flex items-start sm:items-center space-x-3 sm:space-x-4 transition-all duration-500">
                        <div class="flex-shrink-0 mt-1 sm:mt-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gray-200 flex items-center justify-center step-icon">
                                <i class="fas fa-paper-plane text-gray-400 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Broadcasting Transaction</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Sending transaction to Polygon Amoy network...</p>
                            <div id="txOutput" class="hidden mt-2 p-2 bg-gray-50 rounded text-xs font-mono break-all"></div>
                        </div>
                    </div>

                    <!-- Step 4: Mining -->
                    <div id="step4" class="flex items-start sm:items-center space-x-3 sm:space-x-4 transition-all duration-500">
                        <div class="flex-shrink-0 mt-1 sm:mt-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gray-200 flex items-center justify-center step-icon">
                                <i class="fas fa-cube text-gray-400 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Block Confirmation</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Waiting for transaction to be mined and confirmed...</p>
                            <div id="blockOutput" class="hidden mt-2 p-2 bg-gray-50 rounded text-xs font-mono break-all whitespace-pre-wrap"></div>
                        </div>
                    </div>

                    <!-- Step 5: Database Update -->
                    <div id="step5" class="flex items-start sm:items-center space-x-3 sm:space-x-4 transition-all duration-500">
                        <div class="flex-shrink-0 mt-1 sm:mt-0">
                            <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gray-200 flex items-center justify-center step-icon">
                                <i class="fas fa-database text-gray-400 text-xs sm:text-sm"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 text-sm sm:text-base">Database Update</h4>
                            <p class="text-xs sm:text-sm text-gray-600">Updating organization status and blockchain references...</p>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Display -->
                <div id="resultDisplay" class="hidden mt-6 p-4 rounded-lg">
                    <div id="successResult" class="hidden">
                        <div class="flex items-center text-green-800">
                            <i class="fas fa-check-circle text-2xl mr-3"></i>
                            <div>
                                <h4 class="font-bold">Organization Approved Successfully!</h4>
                                <p class="text-sm">The organization has been approved and stored on the blockchain.</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-xs sm:text-sm">
                            <div id="finalDID" class="flex flex-col sm:flex-row sm:justify-between space-y-1 sm:space-y-0">
                                <span class="font-medium">W3C DID:</span>
                                <span class="font-mono text-xs break-all"></span>
                            </div>
                            <div id="finalTxHash" class="flex flex-col sm:flex-row sm:justify-between space-y-1 sm:space-y-0">
                                <span class="font-medium">Transaction:</span>
                                <span class="font-mono text-xs break-all"></span>
                            </div>
                            <div id="finalBlock" class="flex flex-col sm:flex-row sm:justify-between space-y-1 sm:space-y-0">
                                <span class="font-medium">Block Number:</span>
                                <span class="font-mono text-xs break-all"></span>
                            </div>
                            <div id="finalGasUsed" class="flex flex-col sm:flex-row sm:justify-between space-y-1 sm:space-y-0">
                                <span class="font-medium">Gas Used:</span>
                                <span class="font-mono text-xs break-all"></span>
                            </div>
                        </div>
                    </div>
                    <div id="errorResult" class="hidden">
                        <div class="flex items-center text-red-800">
                            <i class="fas fa-exclamation-triangle text-2xl mr-3"></i>
                            <div>
                                <h4 class="font-bold">Approval Failed</h4>
                                <p class="text-sm" id="errorMessage">Something went wrong during the approval process.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Close Button -->
                <div id="closeButton" class="hidden mt-4 sm:mt-6 text-center">
                    <button onclick="closeBlockchainModal()" class="px-4 sm:px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition duration-200 text-sm sm:text-base w-full sm:w-auto">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed top-4 right-4 z-50 transform transition-all duration-300 ease-in-out translate-x-full opacity-0">
        <div id="toast-content" class="bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center min-w-80">
            <div class="flex items-center">
                <i id="toast-icon" class="fas fa-check-circle mr-3 text-lg"></i>
                <span id="toast-message" class="font-medium"></span>
            </div>
            <button onclick="hideToast()" class="ml-4 text-white hover:text-gray-200 transition duration-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize page
            loadOrganizations('pending');

            // Tab switching (desktop)
            $('.tab-button').on('click', function() {
                const tabName = $(this).data('tab');
                switchTab(tabName);
            });

            // Tab switching (mobile)
            $('#mobileTabSelect').on('change', function() {
                const tabName = $(this).val();
                switchTab(tabName);
                // Update desktop tabs to match
                $('.tab-button').removeClass('active border-indigo-500 text-indigo-600')
                               .addClass('border-transparent text-gray-500');
                $(`.tab-button[data-tab="${tabName}"]`).removeClass('border-transparent text-gray-500')
                                                      .addClass('active border-indigo-500 text-indigo-600');
            });
        });

        function toggleMobileMenu() {
            $('#mobileAdminInfo').toggleClass('hidden');
        }

        function switchTab(tabName) {
            // Update tab buttons (desktop)
            $('.tab-button').removeClass('active border-indigo-500 text-indigo-600')
                           .addClass('border-transparent text-gray-500');
            $(`.tab-button[data-tab="${tabName}"]`).removeClass('border-transparent text-gray-500')
                                                  .addClass('active border-indigo-500 text-indigo-600');

            // Update mobile select
            $('#mobileTabSelect').val(tabName);

            // Update tab content
            $('.tab-content').removeClass('active');
            $(`#${tabName}`).addClass('active');

            // Load organizations for this tab
            loadOrganizations(tabName);
        }

        function loadOrganizations(status) {
            const contentId = `${status}-content`;
            $(`#${contentId}`).html('<div class="flex justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div></div>');

            $.get(`{{ url('gov/approval/organizations') }}/${status}`)
                .done(function(response) {
                    if (response.success) {
                        // Show data source notification for approved organizations
                        if (status === 'approved' && response.source) {
                            let sourceMessage = '';
                            if (response.source === 'blockchain') {
                                sourceMessage = '<div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg"><div class="flex items-center space-x-2"><i class="fas fa-shield-alt text-blue-600"></i><span class="text-blue-800 text-sm font-medium">Data verified from blockchain in real-time</span></div></div>';
                            } else if (response.source === 'database_fallback') {
                                sourceMessage = '<div class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg"><div class="flex items-center space-x-2"><i class="fas fa-exclamation-triangle text-orange-600"></i><span class="text-orange-800 text-sm font-medium">Showing database data (blockchain verification failed)</span></div></div>';
                            }
                            $(`#${contentId}`).html(sourceMessage);
                        }
                        renderOrganizations(response.data, contentId, status);
                    }
                })
                .fail(function() {
                    $(`#${contentId}`).html('<div class="text-center py-8 text-gray-500">Failed to load organizations</div>');
                });
        }

        function renderOrganizations(organizations, contentId, status) {
            if (organizations.length === 0) {
                const currentContent = $(`#${contentId}`).html();
                const noDataMessage = '<div class="text-center py-8 text-gray-500">No organizations found</div>';
                if (currentContent.includes('bg-blue-50') || currentContent.includes('bg-orange-50')) {
                    $(`#${contentId}`).append(noDataMessage);
                } else {
                    $(`#${contentId}`).html(noDataMessage);
                }
                return;
            }

            let html = '<div class="space-y-4">';
            organizations.forEach(function(org) {
                const statusBadge = getStatusBadge(org.verification_status, org.blockchain_verified);
                const actionButtons = getActionButtons(org, status);
                
                html += `
                    <div class="border border-gray-200 rounded-lg p-4 sm:p-6 hover:shadow-md transition duration-200">
                        <div class="space-y-4">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                                <h4 class="text-lg font-semibold text-gray-900 break-words">${org.legal_name}</h4>
                                <div class="flex items-center space-x-2">
                                    ${statusBadge}
                                    ${status === 'approved' && org.blockchain_verified ? `
                                        <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-medium flex items-center space-x-1" title="Verified on Blockchain">
                                            <i class="fas fa-shield-alt"></i>
                                            <span>Blockchain</span>
                                        </span>
                                    ` : ''}
                                    ${status === 'approved' && org.blockchain_tx_hash ? `
                                        <a href="https://amoy.polygonscan.com/tx/${org.blockchain_tx_hash}" 
                                           target="_blank" 
                                           class="bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1 rounded-full text-xs font-medium transition duration-200 flex items-center space-x-1"
                                           title="View transaction on Polygonscan">
                                            <i class="fas fa-external-link-alt"></i>
                                            <span>TX</span>
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-4 text-sm text-gray-600">
                                <div class="break-words"><strong>Type:</strong> ${org.organization_type}</div>
                                <div class="break-words"><strong>Email:</strong> ${org.official_email}</div>
                                <div class="break-words"><strong>Phone:</strong> ${org.official_phone}</div>
                                <div class="break-words"><strong>Registration:</strong> ${org.registration_number}</div>
                                <div><strong>Applied:</strong> ${new Date(org.created_at).toLocaleDateString()}</div>
                                ${org.verified_at ? `<div><strong>Processed:</strong> ${new Date(org.verified_at).toLocaleDateString()}</div>` : ''}
                                ${status === 'approved' && org.did ? `<div class="sm:col-span-2 break-words"><strong>DID:</strong> <code class="bg-gray-100 px-2 py-1 rounded text-xs">${org.did}</code></div>` : ''}
                                ${status === 'approved' && org.blockchain_address ? `<div class="sm:col-span-2 break-words"><strong>Blockchain Address:</strong> <code class="bg-gray-100 px-2 py-1 rounded text-xs">${org.blockchain_address}</code></div>` : ''}
                                ${status === 'approved' && org.blockchain_scopes ? `<div class="sm:col-span-2"><strong>Blockchain Scopes:</strong> <span class="text-green-600">${org.blockchain_scopes.join(', ')}</span></div>` : ''}
                                ${status === 'approved' && org.blockchain_tx_hash ? `<div class="sm:col-span-2"><strong>Blockchain TX:</strong> <a href="https://amoy.polygonscan.com/tx/${org.blockchain_tx_hash}" target="_blank" class="text-purple-600 hover:text-purple-800 font-mono text-xs">${org.blockchain_tx_hash.substring(0, 10)}...${org.blockchain_tx_hash.substring(org.blockchain_tx_hash.length - 6)}</a></div>` : ''}
                                ${status === 'approved' && org.blockchain_error ? `<div class="sm:col-span-2 text-red-600"><strong>Blockchain Status:</strong> ${org.blockchain_error}</div>` : ''}
                            </div>
                            ${org.verification_notes ? `<div class="mt-2 text-sm text-gray-600 break-words"><strong>Notes:</strong> ${org.verification_notes}</div>` : ''}
                            <div class="flex flex-col sm:flex-row sm:justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-2">
                                <button onclick="viewOrganization(${org.id})" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-200 w-full sm:w-auto text-center">
                                    <i class="fas fa-eye mr-2"></i>View Details
                                </button>
                                ${actionButtons}
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';

            // Check if there's already notification content and append to it
            const currentContent = $(`#${contentId}`).html();
            if (currentContent.includes('bg-blue-50') || currentContent.includes('bg-orange-50')) {
                $(`#${contentId}`).append(html);
            } else {
                $(`#${contentId}`).html(html);
            }
        }

        function getStatusBadge(status, blockchainVerified) {
            if (status === 'approved' && blockchainVerified === true) {
                return '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">✓ Blockchain Approved</span>';
            } else if (status === 'approved' && blockchainVerified === false) {
                return '<span class="bg-orange-100 text-orange-800 px-2 py-1 rounded-full text-xs font-medium">⚠ Approved (Blockchain Error)</span>';
            }
            
            const badges = {
                'pending': '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full text-xs font-medium">Pending</span>',
                'approved': '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs font-medium">Approved</span>',
                'rejected': '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">Rejected</span>'
            };
            return badges[status] || '';
        }

        function getActionButtons(org, status) {
            if (status === 'pending') {
                return `
                    <button onclick="approveOrganization(${org.id})" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-200 w-full sm:w-auto text-center">
                        <i class="fas fa-check mr-2"></i>Approve
                    </button>
                    <button onclick="rejectOrganization(${org.id})" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition duration-200 w-full sm:w-auto text-center">
                        <i class="fas fa-times mr-2"></i>Reject
                    </button>
                `;
            }
            return '';
        }

        function viewOrganization(orgId) {
            $.get(`{{ url('gov/approval/organization') }}/${orgId}`)
                .done(function(response) {
                    if (response.success) {
                        showOrganizationModal(response);
                    }
                })
                .fail(function() {
                    showToast('Failed to load organization details', 'error');
                });
        }

        function showOrganizationModal(data) {
            const org = data.organization;
            const writeScopes = data.write_scopes;
            const readScopes = data.read_scopes;
            
            let html = `
                <div class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900 text-base sm:text-lg">Organization Information</h4>
                            <div class="space-y-2 text-sm">
                                <div class="break-words"><strong>Legal Name:</strong> ${org.legal_name}</div>
                                <div><strong>Type:</strong> ${org.organization_type}</div>
                                <div class="break-words"><strong>Registration:</strong> ${org.registration_number}</div>
                                <div class="break-words"><strong>Email:</strong> ${org.official_email}</div>
                                <div class="break-words"><strong>Phone:</strong> ${org.official_phone}</div>
                                <div class="break-words"><strong>Website:</strong> ${org.website_url || 'N/A'}</div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h4 class="font-semibold text-gray-900 text-base sm:text-lg">Contact Details</h4>
                            <div class="space-y-2 text-sm">
                                <div class="break-words"><strong>Head Office:</strong> ${org.head_office_address}</div>
                                <div class="break-words"><strong>Signatory:</strong> ${org.signatory_name}</div>
                                <div><strong>Designation:</strong> ${org.signatory_designation}</div>
                                <div class="break-words"><strong>Wallet Address:</strong> <span class="font-mono text-xs">${org.wallet_address}</span></div>
                                ${org.did ? `<div class="break-words"><strong>DID:</strong> <span class="font-mono text-xs">${org.did}</span></div>` : ''}
                                ${org.blockchain_tx_hash ? `<div class="break-words"><strong>Blockchain TX:</strong> <a href="https://amoy.polygonscan.com/tx/${org.blockchain_tx_hash}" target="_blank" class="text-purple-600 hover:text-purple-800"><span class="font-mono text-xs">${org.blockchain_tx_hash.substring(0, 10)}...${org.blockchain_tx_hash.substring(org.blockchain_tx_hash.length - 6)}</span> <i class="fas fa-external-link-alt ml-1"></i></a></div>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900 text-base sm:text-lg">Credential Scopes</h4>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h5 class="font-medium text-green-700 mb-3 flex items-center">
                                    <i class="fas fa-edit mr-2"></i>Can Issue (Write)
                                </h5>
                                ${writeScopes.length > 0 ? 
                                    `<ul class="text-sm space-y-2">
                                        ${writeScopes.map(scope => `<li class="text-green-600 flex items-start"><i class="fas fa-check-circle mr-2 mt-0.5 flex-shrink-0"></i><span class="break-words">${scope}</span></li>`).join('')}
                                    </ul>` : 
                                    '<p class="text-sm text-gray-500">No write permissions requested</p>'
                                }
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h5 class="font-medium text-blue-700 mb-3 flex items-center">
                                    <i class="fas fa-search mr-2"></i>Can Verify (Read)
                                </h5>
                                ${readScopes.length > 0 ? 
                                    `<ul class="text-sm space-y-2">
                                        ${readScopes.map(scope => `<li class="text-blue-600 flex items-start"><i class="fas fa-eye mr-2 mt-0.5 flex-shrink-0"></i><span class="break-words">${scope}</span></li>`).join('')}
                                    </ul>` : 
                                    '<p class="text-sm text-gray-500">No read permissions requested</p>'
                                }
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="font-semibold text-gray-900 text-base sm:text-lg">Use Case Description</h4>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-700 break-words whitespace-pre-line">${org.use_case_description || 'No use case provided'}</p>
                        </div>
                    </div>
                </div>
            `;

            $('#modalContent').html(html);
            $('#organizationModal').removeClass('hidden');
        }

        function closeModal() {
            $('#organizationModal').addClass('hidden');
        }

                    let currentOrgIdForApproval = null;

            function approveOrganization(orgId) {
                console.log('Setting organization for approval:', orgId);
                currentOrgIdForApproval = orgId;
                document.getElementById('approvalModal').classList.remove('hidden');
                document.getElementById('approvalRemarks').value = '';
                document.getElementById('didPrefix').value = '';
                document.getElementById('approvalRemarks').focus();
            }

            function closeApprovalModal() {
                document.getElementById('approvalModal').classList.add('hidden');
                currentOrgIdForApproval = null;
            }

            function confirmApproval() {
                if (!currentOrgIdForApproval) {
                    console.error('No organization ID set for approval');
                    showToast('Error: No organization selected', 'error');
                    return;
                }

                const remarks = document.getElementById('approvalRemarks').value;
                const didPrefix = document.getElementById('didPrefix').value;
                
                // Store the org ID in a local variable before closing modal
                const orgIdToApprove = currentOrgIdForApproval;
                console.log('Approving organization:', orgIdToApprove);
                
                // Close approval modal and show blockchain processing modal
                closeApprovalModal();
                showBlockchainProcessing();
                
                // Start the approval process
                processBlockchainApproval(orgIdToApprove, remarks, didPrefix);
            }

            function showBlockchainProcessing() {
                document.getElementById('blockchainModal').classList.remove('hidden');
                resetProgressSteps();
            }

            function closeBlockchainModal() {
                document.getElementById('blockchainModal').classList.add('hidden');
                refreshCurrentTab();
            }

            function resetProgressSteps() {
                // Reset all steps
                for (let i = 1; i <= 5; i++) {
                    const step = document.getElementById(`step${i}`);
                    const icon = step.querySelector('.step-icon');
                    icon.className = 'w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-gray-200 flex items-center justify-center step-icon';
                    icon.innerHTML = step.querySelector('i').outerHTML;
                }
                
                // Hide all outputs and results
                document.getElementById('didOutput').classList.add('hidden');
                document.getElementById('contractOutput').classList.add('hidden');
                document.getElementById('txOutput').classList.add('hidden');
                document.getElementById('blockOutput').classList.add('hidden');
                document.getElementById('resultDisplay').classList.add('hidden');
                document.getElementById('closeButton').classList.add('hidden');
            }

            function updateStepStatus(stepNumber, status, output = '') {
                const step = document.getElementById(`step${stepNumber}`);
                const icon = step.querySelector('.step-icon');
                const outputDiv = step.querySelector('[id$="Output"]');
                
                if (status === 'processing') {
                    icon.className = 'w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-blue-500 flex items-center justify-center step-icon animate-pulse';
                    icon.innerHTML = '<i class="fas fa-spinner fa-spin text-white text-xs sm:text-sm"></i>';
                } else if (status === 'success') {
                    icon.className = 'w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-green-500 flex items-center justify-center step-icon';
                    icon.innerHTML = '<i class="fas fa-check text-white text-xs sm:text-sm"></i>';
                } else if (status === 'error') {
                    icon.className = 'w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-red-500 flex items-center justify-center step-icon';
                    icon.innerHTML = '<i class="fas fa-times text-white text-xs sm:text-sm"></i>';
                }
                
                if (output && outputDiv) {
                    outputDiv.textContent = output;
                    outputDiv.classList.remove('hidden');
                }
            }

            function processBlockchainApproval(orgId, remarks, didPrefix) {
                // Step 1: DID Generation
                updateStepStatus(1, 'processing');
                
                setTimeout(() => {
                    // Simulate DID generation
                    updateStepStatus(1, 'success', 'Generated: did:sarvone:col:gmit:' + Date.now().toString(36) + ':001:' + Math.random().toString(16).substr(2, 4));
                    
                    // Step 2: Smart Contract
                    updateStepStatus(2, 'processing');
                    
                    setTimeout(() => {
                        updateStepStatus(2, 'success', 'Contract: 0x959387840a40b3bc065033a5da73c75C42c46919\nFunction: approveOrganization(string,address,string[])');
                        
                        // Step 3: Transaction
                        updateStepStatus(3, 'processing');
                        
                        // Make actual API call with proper CSRF handling
                        $.ajax({
                            url: `{{ url('gov/approval/organization') }}/${orgId}/approve`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                remarks: remarks,
                                did_prefix: didPrefix
                            },
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            xhrFields: {
                                withCredentials: true
                            }
                        })
                        .done(function(response) {
                            if (response.success) {
                                updateStepStatus(3, 'success', 'TX Hash: ' + response.tx_hash);
                                
                                // Step 4: Mining
                                updateStepStatus(4, 'processing');
                                
                                setTimeout(() => {
                                    updateStepStatus(4, 'success', 'Block: ' + (response.block_number || 'Pending') + '\nGas Used: ' + (response.gas_used || 'N/A'));
                                    
                                    // Step 5: Database
                                    updateStepStatus(5, 'processing');
                                    
                                    setTimeout(() => {
                                        updateStepStatus(5, 'success');
                                        showSuccess(response);
                                    }, 1000);
                                }, 2000);
                            } else {
                                updateStepStatus(3, 'error');
                                showError('Transaction failed: ' + (response.message || 'Unknown error'));
                            }
                        })
                        .fail(function(xhr) {
                            updateStepStatus(3, 'error');
                            let errorMessage = 'Failed to approve organization';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            showError(errorMessage);
                        });
                        
                    }, 1500);
                }, 1000);
            }

            function showSuccess(response) {
                document.getElementById('resultDisplay').classList.remove('hidden');
                document.getElementById('resultDisplay').className = 'mt-6 p-4 rounded-lg bg-green-50';
                document.getElementById('successResult').classList.remove('hidden');
                
                // Populate success details
                document.querySelector('#finalDID span:last-child').textContent = response.did || 'N/A';
                document.querySelector('#finalTxHash span:last-child').textContent = response.tx_hash || 'N/A';
                document.querySelector('#finalBlock span:last-child').textContent = response.block_number || 'Pending';
                document.querySelector('#finalGasUsed span:last-child').textContent = response.gas_used || 'N/A';
                
                document.getElementById('closeButton').classList.remove('hidden');
                
                // Show success toast
                showToast('Organization approved successfully on blockchain!', 'success');
            }

            function showError(message) {
                document.getElementById('resultDisplay').classList.remove('hidden');
                document.getElementById('resultDisplay').className = 'mt-6 p-4 rounded-lg bg-red-50';
                document.getElementById('errorResult').classList.remove('hidden');
                document.getElementById('errorMessage').textContent = message;
                document.getElementById('closeButton').classList.remove('hidden');
                
                // Show error toast
                showToast('Blockchain approval failed: ' + message, 'error');
            }

        function rejectOrganization(orgId) {
            const remarks = prompt('Enter rejection reason (required):');
            if (!remarks) {
                showToast('Rejection reason is required', 'error');
                return;
            }
            
            $.ajax({
                url: `{{ url('gov/approval/organization') }}/${orgId}/reject`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    remarks: remarks
                },
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                xhrFields: {
                    withCredentials: true
                }
            })
            .done(function(response) {
                if (response.success) {
                    showToast('Organization rejected successfully', 'success');
                    refreshCurrentTab();
                }
            })
            .fail(function() {
                showToast('Failed to reject organization', 'error');
            });
        }

        function refreshOrganizations(status) {
            loadOrganizations(status);
        }

        function refreshCurrentTab() {
            const activeTab = $('.tab-button.active').data('tab');
            loadOrganizations(activeTab);
            
            // Refresh stats by reloading page
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        function showToast(message, type = 'success') {
            const toast = $('#toast');
            const toastContent = $('#toast-content');
            const toastIcon = $('#toast-icon');
            const toastMessage = $('#toast-message');
            
            // Set message
            toastMessage.text(message);
            
            // Set type-specific styling
            if (type === 'success') {
                toastContent.removeClass('bg-red-500 bg-blue-500').addClass('bg-green-500');
                toastIcon.removeClass('fa-exclamation-circle fa-info-circle').addClass('fa-check-circle');
            } else if (type === 'error') {
                toastContent.removeClass('bg-green-500 bg-blue-500').addClass('bg-red-500');
                toastIcon.removeClass('fa-check-circle fa-info-circle').addClass('fa-exclamation-circle');
            } else if (type === 'info') {
                toastContent.removeClass('bg-green-500 bg-red-500').addClass('bg-blue-500');
                toastIcon.removeClass('fa-check-circle fa-exclamation-circle').addClass('fa-info-circle');
            }
            
            // Show toast
            toast.removeClass('translate-x-full opacity-0').addClass('translate-x-0 opacity-100');
            
            // Auto-hide after 5 seconds
            setTimeout(hideToast, 5000);
        }

        function hideToast() {
            $('#toast').removeClass('translate-x-0 opacity-100').addClass('translate-x-full opacity-0');
        }

        // Close modal when clicking outside
        $(document).on('click', '#organizationModal', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html> 