@extends('layouts.organization')

@section('title', 'Issued VCs - Organization Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Issued Verifiable Credentials</h1>
                <p class="text-gray-600">Manage and revoke credentials you have issued</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('organization.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <a href="{{ route('organization.issue-vc') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <i class="fas fa-plus mr-2"></i>Issue New VC
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Issued</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $issuedVCs->total() }}</p>
                </div>
                <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-certificate text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Active</p>
                    <p class="text-3xl font-bold text-green-600">{{ $issuedVCs->where('status', 'active')->count() }}</p>
                </div>
                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Revoked</p>
                    <p class="text-3xl font-bold text-red-600">{{ $issuedVCs->where('status', 'revoked')->count() }}</p>
                </div>
                <div class="h-12 w-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-ban text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Expired</p>
                    <p class="text-3xl font-bold text-yellow-600">{{ $issuedVCs->where('expires_at', '<', now())->count() }}</p>
                </div>
                <div class="h-12 w-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- VCs Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Issued Credentials</h3>
        </div>
        
        @if($issuedVCs->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">VC ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issued</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($issuedVCs as $vc)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ Str::limit($vc->vc_id, 20) }}</div>
                            <div class="text-xs text-gray-500">{{ Str::limit($vc->credential_hash, 16) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $vc->subject_name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">{{ Str::limit($vc->subject_did, 20) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucwords(str_replace('_', ' ', $vc->vc_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($vc->isRevoked())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-ban mr-1"></i>Revoked
                                </span>
                            @elseif($vc->isExpired())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>Expired
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Active
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $vc->issued_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @if($vc->expires_at)
                                {{ $vc->expires_at->format('M j, Y') }}
                            @else
                                <span class="text-gray-400">No expiry</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <!-- View Details -->
                                <button onclick="viewVCDetails('{{ $vc->vc_id }}')" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </button>
                                
                                <!-- Revoke Button (only for active VCs) -->
                                @if($vc->status === 'active' && !$vc->isExpired())
                                <button onclick="showRevokeModal('{{ $vc->vc_id }}', '{{ $vc->subject_name }}', '{{ $vc->vc_type }}')" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @endif
                                
                                <!-- Blockchain Explorer -->
                                @if($vc->blockchain_tx_hash)
                                <a href="{{ $vc->getBlockchainExplorerUrl() }}" target="_blank" class="text-gray-600 hover:text-gray-900">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $issuedVCs->links() }}
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <div class="text-gray-400 mb-4">
                <i class="fas fa-certificate text-6xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No VCs Issued Yet</h3>
            <p class="text-gray-600 mb-6">You haven't issued any verifiable credentials yet.</p>
            <a href="{{ route('organization.issue-vc') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>Issue Your First VC
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Revoke VC Modal -->
<div id="revokeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Revoke Verifiable Credential</h3>
                <button onclick="hideRevokeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Are you sure you want to revoke this credential?</p>
                <div class="bg-gray-50 p-3 rounded-md">
                    <p class="text-sm font-medium text-gray-900" id="revokeVCDetails"></p>
                </div>
            </div>
            
            <form id="revokeForm">
                <div class="mb-4">
                    <label for="revocation_reason" class="block text-sm font-medium text-gray-700 mb-2">Reason for Revocation (Optional)</label>
                    <textarea id="revocation_reason" name="revocation_reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter reason for revocation..."></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="private_key" class="block text-sm font-medium text-gray-700 mb-2">Private Key *</label>
                    <input type="password" id="private_key" name="private_key" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter your wallet private key" required>
                    <p class="text-xs text-gray-500 mt-1">Your private key is required to sign the blockchain transaction. It will not be stored.</p>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="hideRevokeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" id="revokeButton" class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <span class="flex items-center">
                            <i class="fas fa-ban mr-2"></i>
                            Revoke VC
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- VC Details Modal -->
<div id="vcDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">VC Details</h3>
                <button onclick="hideVCDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div id="vcDetailsContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let currentVCId = null;

function showRevokeModal(vcId, subjectName, vcType) {
    currentVCId = vcId;
    document.getElementById('revokeVCDetails').textContent = `${subjectName} - ${vcType.replace(/_/g, ' ')}`;
    document.getElementById('revokeModal').classList.remove('hidden');
}

function hideRevokeModal() {
    document.getElementById('revokeModal').classList.add('hidden');
    document.getElementById('revokeForm').reset();
    currentVCId = null;
}

function hideVCDetailsModal() {
    document.getElementById('vcDetailsModal').classList.add('hidden');
}

function viewVCDetails(vcId) {
    // Show loading state
    document.getElementById('vcDetailsContent').innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-2xl text-gray-400"></i><p class="mt-2 text-gray-600">Loading...</p></div>';
    document.getElementById('vcDetailsModal').classList.remove('hidden');
    
    // Fetch VC details
    fetch(`/api/vc/status/${vcId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayVCDetails(data);
            } else {
                document.getElementById('vcDetailsContent').innerHTML = '<div class="text-center py-8 text-red-600">Failed to load VC details</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('vcDetailsContent').innerHTML = '<div class="text-center py-8 text-red-600">Error loading VC details</div>';
        });
}

function displayVCDetails(data) {
    const content = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">VC ID</h4>
                    <p class="text-sm text-gray-900 font-mono">${data.vc_id}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Status</h4>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        data.revoked ? 'bg-red-100 text-red-800' : 
                        data.expired ? 'bg-yellow-100 text-yellow-800' : 
                        'bg-green-100 text-green-800'
                    }">
                        ${data.revoked ? 'Revoked' : data.expired ? 'Expired' : 'Active'}
                    </span>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Issued At</h4>
                    <p class="text-sm text-gray-900">${new Date(data.issued_at).toLocaleString()}</p>
                </div>
                ${data.expires_at ? `
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Expires At</h4>
                    <p class="text-sm text-gray-900">${new Date(data.expires_at).toLocaleString()}</p>
                </div>
                ` : ''}
            </div>
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Issuer DID</h4>
                    <p class="text-sm text-gray-900 font-mono">${data.issuer_did}</p>
                </div>
                ${data.revoked ? `
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Revoked At</h4>
                    <p class="text-sm text-gray-900">${new Date(data.revoked_at).toLocaleString()}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Revocation Reason</h4>
                    <p class="text-sm text-gray-900">${data.revocation_reason || 'No reason provided'}</p>
                </div>
                ` : ''}
            </div>
        </div>
    `;
    
    document.getElementById('vcDetailsContent').innerHTML = content;
}

document.getElementById('revokeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const button = document.getElementById('revokeButton');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Revoking...';
    button.disabled = true;
    
    const formData = new FormData();
    formData.append('revocation_reason', document.getElementById('revocation_reason').value);
    formData.append('private_key', document.getElementById('private_key').value);
    
    fetch(`/organization/revoke-vc/${currentVCId}`, {
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
            // Show success message
            showNotification('VC revoked successfully!', 'success');
            hideRevokeModal();
            // Reload the page to update the table
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showNotification(data.message || 'Failed to revoke VC', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while revoking the VC', 'error');
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

// Close modals when clicking outside
window.addEventListener('click', function(e) {
    const revokeModal = document.getElementById('revokeModal');
    const vcDetailsModal = document.getElementById('vcDetailsModal');
    
    if (e.target === revokeModal) {
        hideRevokeModal();
    }
    if (e.target === vcDetailsModal) {
        hideVCDetailsModal();
    }
});
</script>
@endpush 