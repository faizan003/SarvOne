<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Access History - SarvOne</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Mobile Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-lg sm:text-xl font-bold text-gray-900">SarvOne</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <span class="text-xs sm:text-sm text-gray-700 hidden sm:block">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 text-xs sm:text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-1"></i><span class="hidden sm:inline">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation Tabs -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 px-1 pt-1 pb-4 text-sm font-medium">Dashboard</a>
                <a href="{{ route('access-history') }}" class="text-blue-600 border-b-2 border-blue-600 px-1 pt-1 pb-4 text-sm font-medium">Access History</a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Access History</h2>
            <p class="mt-1 text-sm text-gray-600">
                Track who has accessed your data and what information they viewed
            </p>
        </div>

        <!-- Stats Cards - Mobile First Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8">
            <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-eye text-blue-600 text-xs sm:text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Total Accesses</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ $accessLogs->total() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-building text-green-600 text-xs sm:text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Organizations</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ $accessLogs->getCollection()->unique('organization_id')->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar text-purple-600 text-xs sm:text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">This Month</p>
                        <p class="text-lg sm:text-2xl font-bold text-gray-900">{{ $accessLogs->getCollection()->where('created_at', '>=', now()->startOfMonth())->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-3 sm:p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield-alt text-orange-600 text-xs sm:text-sm"></i>
                        </div>
                    </div>
                    <div class="ml-2 sm:ml-4">
                        <p class="text-xs sm:text-sm font-medium text-gray-600">Last Access</p>
                        <p class="text-xs sm:text-sm font-bold text-gray-900">
                            {{ $accessLogs->first() ? \Carbon\Carbon::parse($accessLogs->first()->created_at)->diffForHumans() : 'Never' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Logs - Mobile Cards Layout -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-medium text-gray-900">Credential Access Logs</h3>
                        <p class="mt-1 text-xs sm:text-sm text-gray-600">
                            Track when organizations accessed your credentials and verification results
                        </p>
                    </div>
                    <button onclick="loadAccessLogs()" class="px-3 py-1 bg-blue-600 text-white text-xs rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-1"></i>Refresh
                    </button>
                </div>
            </div>

            <!-- Loading State -->
            <div id="logs-loading" class="hidden text-center py-8">
                <div class="inline-flex items-center">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-xl mr-3"></i>
                    <span class="text-gray-600">Loading access logs...</span>
                </div>
            </div>

            <!-- Access Logs Content -->
            <div id="access-logs-content">
                @if($accessLogs->count() > 0)
                <!-- Mobile Cards Layout -->
                <div class="block sm:hidden" id="mobile-access-logs">
                    @foreach($accessLogs as $log)
                        <div class="border-b border-gray-200 p-4 relative @if($log->hasFlags()) bg-red-50 border-l-4 border-l-red-400 @endif">

                            
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-8 w-8">
                                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-building text-blue-600 text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $log->organization_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}
                                        </div>
                                    </div>
                                </div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                    @if($log->status === 'success') bg-green-100 text-green-800
                                    @elseif($log->status === 'failed') bg-red-100 text-red-800
                                    @else bg-yellow-100 text-yellow-800
                                    @endif">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </div>
                            
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Credential Type:</span>
                                    <span class="text-gray-900">{{ str_replace('_', ' ', $log->credential_type) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Purpose:</span>
                                    <span class="text-gray-900">{{ $log->purpose ?: 'Verification' }}</span>
                                </div>
                                @if($log->status === 'failed')
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Reason:</span>
                                        <span class="text-red-600 font-medium">Credential not found or inactive</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-3 pt-3 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-xs text-gray-600">
                                        <i class="fas fa-bell text-green-600 mr-2"></i>
                                        <span>SMS notification sent to your phone</span>
                                    </div>
                                    <!-- Mobile Flag Action - More prominent -->
                                    @if(!$log->hasFlags())
                                        <button onclick="showFlagModal({{ $log->id }}, '{{ $log->organization_name }}')" 
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 transition-colors duration-200 border border-red-200 flag-button">
                                            <i class="fas fa-flag mr-1"></i>Report Issue
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>



                <!-- Desktop Table Layout -->
                <div class="hidden sm:block overflow-x-auto" id="desktop-access-logs">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credential Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($accessLogs as $log)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8">
                                                <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-building text-blue-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $log->organization_name }}
                                                </div>
                                                                                            <div class="text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}
                                            </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ str_replace('_', ' ', $log->credential_type) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($log->status === 'success') bg-green-100 text-green-800
                                            @elseif($log->status === 'failed') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800
                                            @endif">
                                            {{ ucfirst($log->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $log->purpose ?: 'Verification' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div>
                                            <div>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y') }}</div>
                                            <div class="text-gray-500">{{ \Carbon\Carbon::parse($log->created_at)->format('h:i A') }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-xs">
                                            @if($log->status === 'success')
                                                <div class="flex items-center text-green-600 text-xs mb-2">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    <span>Credential verified successfully</span>
                                                </div>
                                            @elseif($log->status === 'failed')
                                                <div class="flex items-center text-red-600 text-xs mb-2">
                                                    <i class="fas fa-times-circle mr-1"></i>
                                                    <span>Credential not found or inactive</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center text-gray-600 text-xs">
                                                <i class="fas fa-bell text-green-600 mr-1"></i>
                                                <span>SMS notification sent</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if($log->hasFlags())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-flag mr-1"></i>Flagged
                                            </span>
                                        @else
                                            <button onclick="showFlagModal({{ $log->id }}, '{{ $log->organization_name }}')" 
                                                    class="text-red-600 hover:text-red-900 text-sm">
                                                <i class="fas fa-flag mr-1"></i>Flag Access
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
                    {{ $accessLogs->links() }}
                </div>
            @else
                <div class="px-4 sm:px-6 py-8 text-center">
                    <div class="flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mx-auto">
                        <i class="fas fa-shield-alt text-gray-400 text-xl"></i>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">No Access History</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        No organizations have accessed your data yet. Your information is secure.
                    </p>
                </div>
            @endif
        </div>

        <!-- Privacy Information -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Privacy & Transparency</h3>
                    <p class="mt-1 text-xs sm:text-sm text-blue-700">
                        This page shows you exactly who has accessed your data and when. 
                        Organizations can only access information relevant to their type (e.g., employers see employment data, banks see financial data).
                        All access is logged for your transparency and security.
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-4 sm:px-6 py-3 rounded-md shadow-lg z-50 max-w-xs sm:max-w-md">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        </div>
        <script>
            setTimeout(function() {
                const message = document.getElementById('success-message');
                if (message) {
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }
            }, 3000);
        </script>
    @endif

    <!-- JavaScript for dynamic loading -->
    <script>
        // Load access logs dynamically
        async function loadAccessLogs() {
            const loadingDiv = document.getElementById('logs-loading');
            const contentDiv = document.getElementById('access-logs-content');
            
            // Show loading
            loadingDiv.classList.remove('hidden');
            contentDiv.classList.add('hidden');
            
            try {
                const response = await fetch('/api/user/access-logs', {
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
                    console.error('Failed to load access logs:', data.message);
                }
            } catch (error) {
                console.error('Error loading access logs:', error);
            } finally {
                // Hide loading
                loadingDiv.classList.add('hidden');
                contentDiv.classList.remove('hidden');
            }
        }

        // Display access logs
        function displayAccessLogs(logs) {
            const mobileContainer = document.getElementById('mobile-access-logs');
            const desktopContainer = document.getElementById('desktop-access-logs');
            
            if (!logs || logs.length === 0) {
                mobileContainer.innerHTML = `
                    <div class="px-4 sm:px-6 py-8 text-center">
                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mx-auto">
                            <i class="fas fa-shield-alt text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No Access History</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            No organizations have accessed your credentials yet. Your information is secure.
                        </p>
                    </div>
                `;
                desktopContainer.innerHTML = `
                    <div class="px-4 sm:px-6 py-8 text-center">
                        <div class="flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mx-auto">
                            <i class="fas fa-shield-alt text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No Access History</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            No organizations have accessed your credentials yet. Your information is secure.
                        </p>
                    </div>
                `;
                return;
            }
            
            // Generate mobile cards
            mobileContainer.innerHTML = logs.map(log => `
                <div class="border-b border-gray-200 p-4">
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
                                        year: 'numeric',
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
                    
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Credential Type:</span>
                            <span class="text-gray-900">${log.credential_type.replace(/_/g, ' ')}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Purpose:</span>
                            <span class="text-gray-900">${log.purpose || 'Verification'}</span>
                        </div>
                        ${log.status === 'failed' ? `
                            <div class="flex justify-between">
                                <span class="text-gray-500">Reason:</span>
                                <span class="text-red-600 font-medium">Credential not found or inactive</span>
                            </div>
                        ` : ''}
                    </div>
                    
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="flex items-center text-xs text-gray-600">
                            <i class="fas fa-bell text-green-600 mr-2"></i>
                            <span>SMS notification sent to your phone</span>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Generate desktop table
            desktopContainer.innerHTML = `
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credential Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${logs.map(log => `
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <i class="fas fa-building text-blue-600 text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                ${log.organization_name}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ${new Date(log.created_at).toLocaleDateString('en-US', {
                                                    month: 'short',
                                                    day: 'numeric',
                                                    year: 'numeric',
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${log.credential_type.replace(/_/g, ' ')}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        ${log.status === 'success' ? 'bg-green-100 text-green-800' :
                                          log.status === 'failed' ? 'bg-red-100 text-red-800' :
                                          'bg-yellow-100 text-yellow-800'}">
                                        ${log.status.charAt(0).toUpperCase() + log.status.slice(1)}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ${log.purpose || 'Verification'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>
                                        <div>${new Date(log.created_at).toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                            year: 'numeric'
                                        })}</div>
                                        <div class="text-gray-500">${new Date(log.created_at).toLocaleTimeString('en-US', {
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="max-w-xs">
                                        ${log.status === 'success' ? `
                                            <div class="flex items-center text-green-600 text-xs mb-2">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <span>Credential verified successfully</span>
                                            </div>
                                        ` : log.status === 'failed' ? `
                                            <div class="flex items-center text-red-600 text-xs mb-2">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                <span>Credential not found or inactive</span>
                                            </div>
                                        ` : ''}
                                        <div class="flex items-center text-gray-600 text-xs">
                                            <i class="fas fa-bell text-green-600 mr-1"></i>
                                            <span>SMS notification sent</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        }

        // Load access logs when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initial load is handled by server-side rendering
        });
    </script>

    <!-- Flag Access Modal -->
    <div id="flagModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Flag Unauthorized Access</h3>
                    <button onclick="hideFlagModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-2">Are you sure you want to flag this access as unauthorized?</p>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <p class="text-sm font-medium text-gray-900" id="flagAccessDetails"></p>
                    </div>
                </div>
                
                <form id="flagForm">
                    @csrf
                    <div class="mb-4">
                        <label for="flag_type" class="block text-sm font-medium text-gray-700 mb-2">Flag Type *</label>
                        <select id="flag_type" name="flag_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select flag type...</option>
                            <option value="unauthorized_access">Unauthorized Access</option>
                            <option value="suspicious_activity">Suspicious Activity</option>
                            <option value="data_misuse">Data Misuse</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="flag_reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Flagging *</label>
                        <textarea id="flag_reason" name="flag_reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Please provide details about why you believe this access was unauthorized..." required></textarea>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3">
                        <button type="button" onclick="hideFlagModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit" id="flagButton" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <span class="flex items-center">
                                <i class="fas fa-flag mr-2"></i>
                                Flag Access
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>



    <script>
        let currentAccessLogId = null;



        function showFlagModal(accessLogId, organizationName) {
            currentAccessLogId = accessLogId;
            document.getElementById('flagAccessDetails').textContent = `Organization: ${organizationName}`;
            document.getElementById('flagModal').classList.remove('hidden');
        }

        function hideFlagModal() {
            document.getElementById('flagModal').classList.add('hidden');
            document.getElementById('flagForm').reset();
            currentAccessLogId = null;
        }

        // Ensure the form exists before adding event listener
        const flagForm = document.getElementById('flagForm');
        if (flagForm) {
        
        flagForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = document.getElementById('flagButton');
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Flagging...';
            button.disabled = true;
            
            const formData = new FormData();
            formData.append('flag_type', document.getElementById('flag_type').value);
            formData.append('flag_reason', document.getElementById('flag_reason').value);
            
            // Get CSRF token from the form
            const csrfInput = document.querySelector('input[name="_token"]');
            const csrfToken = csrfInput ? csrfInput.value : '';
            
            if (!csrfToken) {
                showNotification('CSRF token not found. Please refresh the page and try again.', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
                return;
            }
            
            fetch(`/flag-access/${currentAccessLogId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Access has been flagged for government review', 'success');
                    hideFlagModal();
                    // Reload the page to update the table
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.message || 'Failed to flag access', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (error.message.includes('HTTP error! status: 419')) {
                    showNotification('Session expired. Please refresh the page and try again.', 'error');
                } else if (error.message.includes('HTTP error! status: 422')) {
                    showNotification('Please check your input and try again.', 'error');
                } else {
                    showNotification('An error occurred while flagging the access. Please try again.', 'error');
                }
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} mr-2"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Remove notification after 5 seconds
            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            const flagModal = document.getElementById('flagModal');
            if (e.target === flagModal) {
                hideFlagModal();
            }
        });
    </script>
</body>
</html> 