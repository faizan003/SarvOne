@extends('layouts.app')

@section('title', 'Flagged Access Review - Government Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Flagged Access Review</h1>
                <p class="text-gray-600">Review and manage access flags reported by users</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('government.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Flags</p>
                    <p class="text-3xl font-bold text-blue-600" id="totalFlags">-</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-flag text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pending Review</p>
                    <p class="text-3xl font-bold text-yellow-600" id="pendingFlags">-</p>
                </div>
                <div class="h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Resolved</p>
                    <p class="text-3xl font-bold text-green-600" id="resolvedFlags">-</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Dismissed</p>
                    <p class="text-3xl font-bold text-gray-600" id="dismissedFlags">-</p>
                </div>
                <div class="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-gray-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="statusFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>
            
            <div>
                <label for="typeFilter" class="block text-sm font-medium text-gray-700 mb-2">Flag Type</label>
                <select id="typeFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Types</option>
                    <option value="unauthorized_access">Unauthorized Access</option>
                    <option value="suspicious_activity">Suspicious Activity</option>
                    <option value="data_misuse">Data Misuse</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label for="organizationFilter" class="block text-sm font-medium text-gray-700 mb-2">Organization</label>
                <select id="organizationFilter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Organizations</option>
                </select>
            </div>
            
            <div class="flex items-end">
                <button onclick="loadFlags()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Flags Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Flagged Access Reports</h3>
        </div>
        
        <div id="flagsContainer" class="overflow-x-auto">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Review Flag Modal -->
<div id="reviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Review Access Flag</h3>
                <button onclick="hideReviewModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="reviewContent" class="space-y-6">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentFlagId = null;

function loadFlags() {
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const organizationFilter = document.getElementById('organizationFilter').value;
    
    const params = new URLSearchParams();
    if (statusFilter) params.append('status', statusFilter);
    if (typeFilter) params.append('flag_type', typeFilter);
    if (organizationFilter) params.append('organization_id', organizationFilter);
    
    fetch(`/government/flags?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFlags(data.flags);
                updateStats(data.flags);
            } else {
                showNotification('Failed to load flags', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading flags', 'error');
        });
}

function displayFlags(flags) {
    const container = document.getElementById('flagsContainer');
    
    if (flags.data.length === 0) {
        container.innerHTML = `
            <div class="px-6 py-12 text-center">
                <div class="text-gray-400 mb-4">
                    <i class="fas fa-flag text-6xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Flags Found</h3>
                <p class="text-gray-600">No access flags match the current filters.</p>
            </div>
        `;
        return;
    }
    
    const tableHTML = `
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flag Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                ${flags.data.map(flag => `
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-user text-blue-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        ${flag.user ? flag.user.name : 'Unknown User'}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        ${flag.user ? flag.user.email : 'No email'}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                ${flag.organization ? flag.organization.legal_name : 'Unknown Organization'}
                            </div>
                            <div class="text-sm text-gray-500">
                                ${flag.organization ? flag.organization.type : 'Unknown Type'}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                ${flag.flag_type_display}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${flag.status_badge_class}">
                                ${flag.status.charAt(0).toUpperCase() + flag.status.slice(1)}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            ${new Date(flag.created_at).toLocaleDateString()}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="showReviewModal(${flag.id})" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye mr-1"></i>Review
                            </button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            ${generatePagination(flags)}
        </div>
    `;
    
    container.innerHTML = tableHTML;
}

function updateStats(flags) {
    const total = flags.data.length;
    const pending = flags.data.filter(f => f.status === 'pending').length;
    const resolved = flags.data.filter(f => f.status === 'resolved').length;
    const dismissed = flags.data.filter(f => f.status === 'dismissed').length;
    
    document.getElementById('totalFlags').textContent = total;
    document.getElementById('pendingFlags').textContent = pending;
    document.getElementById('resolvedFlags').textContent = resolved;
    document.getElementById('dismissedFlags').textContent = dismissed;
}

function generatePagination(flags) {
    if (!flags.prev_page_url && !flags.next_page_url) {
        return '';
    }
    
    return `
        <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
                ${flags.prev_page_url ? `<a href="#" onclick="loadPage(${flags.current_page - 1})" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Previous</a>` : ''}
                ${flags.next_page_url ? `<a href="#" onclick="loadPage(${flags.current_page + 1})" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">Next</a>` : ''}
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">${(flags.current_page - 1) * flags.per_page + 1}</span> to <span class="font-medium">${Math.min(flags.current_page * flags.per_page, flags.total)}</span> of <span class="font-medium">${flags.total}</span> results
                    </p>
                </div>
                <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                        ${flags.prev_page_url ? `<a href="#" onclick="loadPage(${flags.current_page - 1})" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Previous</a>` : ''}
                        ${flags.next_page_url ? `<a href="#" onclick="loadPage(${flags.current_page + 1})" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">Next</a>` : ''}
                    </nav>
                </div>
            </div>
        </div>
    `;
}

function loadPage(page) {
    const params = new URLSearchParams();
    params.append('page', page);
    
    const statusFilter = document.getElementById('statusFilter').value;
    const typeFilter = document.getElementById('typeFilter').value;
    const organizationFilter = document.getElementById('organizationFilter').value;
    
    if (statusFilter) params.append('status', statusFilter);
    if (typeFilter) params.append('flag_type', typeFilter);
    if (organizationFilter) params.append('organization_id', organizationFilter);
    
    fetch(`/government/flags?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayFlags(data.flags);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading page', 'error');
        });
}

function showReviewModal(flagId) {
    currentFlagId = flagId;
    
    // Load flag details
    fetch(`/government/flags?flag_id=${flagId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.flags.data.length > 0) {
                const flag = data.flags.data[0];
                displayReviewContent(flag);
                document.getElementById('reviewModal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error loading flag details', 'error');
        });
}

function displayReviewContent(flag) {
    const content = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">User Information</h4>
                    <p class="text-sm text-gray-900">${flag.user ? flag.user.name : 'Unknown'}</p>
                    <p class="text-sm text-gray-500">${flag.user ? flag.user.email : 'No email'}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Organization</h4>
                    <p class="text-sm text-gray-900">${flag.organization ? flag.organization.legal_name : 'Unknown'}</p>
                    <p class="text-sm text-gray-500">${flag.organization ? flag.organization.type : 'Unknown type'}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Access Details</h4>
                    <p class="text-sm text-gray-900">${flag.access_log ? flag.access_log.formatted_access_type : 'Unknown'}</p>
                    <p class="text-sm text-gray-500">${flag.access_log ? flag.access_log.purpose : 'No reason provided'}</p>
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Flag Information</h4>
                    <p class="text-sm text-gray-900"><strong>Type:</strong> ${flag.flag_type_display}</p>
                    <p class="text-sm text-gray-900"><strong>Status:</strong> 
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${flag.status_badge_class}">
                            ${flag.status.charAt(0).toUpperCase() + flag.status.slice(1)}
                        </span>
                    </p>
                    <p class="text-sm text-gray-900"><strong>Date:</strong> ${new Date(flag.created_at).toLocaleString()}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">User's Reason</h4>
                    <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-md">${flag.flag_reason}</p>
                </div>
                ${flag.government_notes ? `
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Government Notes</h4>
                    <p class="text-sm text-gray-900 bg-blue-50 p-3 rounded-md">${flag.government_notes}</p>
                </div>
                ` : ''}
            </div>
        </div>
        
        ${flag.status === 'pending' ? `
        <div class="mt-6 border-t pt-6">
            <h4 class="text-sm font-medium text-gray-500 mb-4">Review Decision</h4>
            <form id="reviewForm">
                <div class="mb-4">
                    <label for="review_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="review_status" name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="reviewed">Reviewed</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="government_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                    <textarea id="government_notes" name="government_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Add your review notes..."></textarea>
                </div>
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="hideReviewModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" id="reviewButton" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="flex items-center">
                            <i class="fas fa-check mr-2"></i>
                            Update Status
                        </span>
                    </button>
                </div>
            </form>
        </div>
        ` : ''}
    `;
    
    document.getElementById('reviewContent').innerHTML = content;
    
    // Add form event listener if form exists
    const form = document.getElementById('reviewForm');
    if (form) {
        form.addEventListener('submit', handleReviewSubmit);
    }
}

function handleReviewSubmit(e) {
    e.preventDefault();
    
    const button = document.getElementById('reviewButton');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Updating...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('status', document.getElementById('review_status').value);
    formData.append('government_notes', document.getElementById('government_notes').value);
    
    fetch(`/government/review-flag/${currentFlagId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Flag status updated successfully', 'success');
            hideReviewModal();
            loadFlags(); // Reload the flags
        } else {
            showNotification(data.message || 'Failed to update flag status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while updating the flag', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function hideReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    currentFlagId = null;
}

function showNotification(message, type) {
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
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Load flags when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadFlags();
});

// Close modal when clicking outside
window.addEventListener('click', function(e) {
    const reviewModal = document.getElementById('reviewModal');
    if (e.target === reviewModal) {
        hideReviewModal();
    }
});
</script>
@endpush 