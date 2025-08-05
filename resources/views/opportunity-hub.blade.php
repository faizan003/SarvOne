@extends('layouts.app')

@section('title', 'Opportunity Hub - SarvOne')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Opportunity Hub</h1>
                    <p class="text-green-100 text-lg">Discover government schemes you're eligible for</p>
                </div>
                <div class="hidden md:block">
                    <div class="h-20 w-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-lightbulb text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
        <div class="flex flex-wrap items-center gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                <select id="categoryFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All Categories</option>
                    <option value="education">Education</option>
                    <option value="agriculture">Agriculture</option>
                    <option value="employment">Employment</option>
                    <option value="health">Health</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Eligibility</label>
                <select id="eligibilityFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All Schemes</option>
                    <option value="eligible" selected>Eligible Only</option>
                    <option value="not-eligible">Not Eligible</option>
                </select>
            </div>
            
            <div class="ml-auto">
                <button onclick="refreshSchemes()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12">
        <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-green-600">
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading schemes...
        </div>
    </div>

    <!-- Schemes Grid -->
    <div id="schemesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" style="display: none;">
        <!-- Schemes will be loaded here -->
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-12" style="display: none;">
        <div class="max-w-md mx-auto">
            <div class="h-24 w-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-search text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No schemes found</h3>
            <p class="text-gray-500">Try adjusting your filters or check back later for new opportunities.</p>
        </div>
    </div>
</div>

<!-- Scheme Details Modal -->
<div id="schemeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 id="modalSchemeName" class="text-2xl font-bold text-gray-900"></h2>
                    <button onclick="closeSchemeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div id="modalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let allSchemes = [];
let userProfile = @json(auth()->user());

// Load schemes on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSchemes();
    
    // Add filter event listeners
    document.getElementById('categoryFilter').addEventListener('change', filterSchemes);
    document.getElementById('eligibilityFilter').addEventListener('change', filterSchemes);
});

async function loadSchemes() {
    try {
        showLoading();
        
        // Fetch schemes from API
        const response = await fetch('/api/government-schemes');
        const data = await response.json();
        
        if (data.success) {
            allSchemes = data.schemes;
            // Store user VCs globally for eligibility checking
            window.userVCs = data.user_vcs || [];
            // Apply default filter (eligible only)
            filterSchemes();
        } else {
            showError('Failed to load schemes');
        }
    } catch (error) {
        console.error('Error loading schemes:', error);
        showError('Failed to load schemes');
    }
}

function displaySchemes(schemes) {
    const grid = document.getElementById('schemesGrid');
    const emptyState = document.getElementById('emptyState');
    
    if (schemes.length === 0) {
        grid.style.display = 'none';
        emptyState.style.display = 'block';
        hideLoading();
        return;
    }
    
    grid.style.display = 'grid';
    emptyState.style.display = 'none';
    
    grid.innerHTML = schemes.map(scheme => createSchemeCard(scheme)).join('');
    hideLoading();
}

function createSchemeCard(scheme) {
    const eligibilityDetails = checkEligibility(scheme);
    const isEligible = eligibilityDetails.eligible;
    
    return `
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-shadow duration-300">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">${scheme.name}</h3>
                        <p class="text-sm text-gray-600 mb-3">${scheme.description.substring(0, 100)}${scheme.description.length > 100 ? '...' : ''}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        ${scheme.category === 'education' ? 'bg-blue-100 text-blue-800' :
                          scheme.category === 'agriculture' ? 'bg-green-100 text-green-800' :
                          scheme.category === 'employment' ? 'bg-yellow-100 text-yellow-800' :
                          scheme.category === 'health' ? 'bg-red-100 text-red-800' :
                          'bg-gray-100 text-gray-800'}">
                        ${scheme.category.charAt(0).toUpperCase() + scheme.category.slice(1)}
                    </span>
                </div>
                
                <div class="space-y-3 mb-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Benefit:</span>
                        <span class="text-sm font-semibold text-gray-900">
                            ${scheme.benefit_type.charAt(0).toUpperCase() + scheme.benefit_type.slice(1)}
                            ${scheme.benefit_amount ? ` - ₹${new Intl.NumberFormat('en-IN').format(scheme.benefit_amount)}` : ''}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Deadline:</span>
                        <span class="text-sm font-semibold ${scheme.application_deadline && new Date(scheme.application_deadline) < new Date() ? 'text-red-600' : 'text-gray-900'}">
                            ${scheme.application_deadline ? new Date(scheme.application_deadline).toLocaleDateString('en-IN') : 'No deadline'}
                        </span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="flex items-center mb-2">
                        <span class="text-sm font-medium text-gray-700">Eligibility Status:</span>
                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            ${isEligible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                            ${isEligible ? 'Eligible' : 'Not Eligible'}
                        </span>
                    </div>
                    
                    ${!isEligible && eligibilityDetails.missing_criteria.length > 0 ? `
                        <div class="text-xs text-red-600">
                            <strong>Missing:</strong> ${eligibilityDetails.missing_criteria.join(', ')}
                        </div>
                    ` : ''}
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="viewSchemeDetails(${scheme.id})" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm font-medium">
                        View Details
                    </button>
                    ${isEligible ? `
                        <button onclick="applyForScheme(${scheme.id})" 
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 text-sm font-medium">
                            Apply Now
                        </button>
                    ` : ''}
                </div>
            </div>
        </div>
    `;
}

function checkEligibility(scheme) {
    // Use eligibility details from API response if available
    if (scheme.eligibility_details) {
        return scheme.eligibility_details;
    }
    
    // Fallback to local calculation if API doesn't provide details
    const details = {
        eligible: true,
        checks: {},
        missing_criteria: []
    };
    
    // Check income criteria
    if (scheme.max_income) {
        const userIncome = userProfile.family_income;
        
        if (userIncome === null || userIncome === undefined) {
            // User has no income data - they are ineligible
            details.checks.income = {
                required: `Family income ≤ ₹${new Intl.NumberFormat('en-IN').format(scheme.max_income)}`,
                user_value: 'Income data not available (Income certificate required)',
                eligible: false
            };
            details.eligible = false;
            details.missing_criteria.push('Income data not available - Income certificate required');
        } else {
            const isEligible = userIncome <= scheme.max_income;
            details.checks.income = {
                required: `Family income ≤ ₹${new Intl.NumberFormat('en-IN').format(scheme.max_income)}`,
                user_value: `₹${new Intl.NumberFormat('en-IN').format(userIncome)}`,
                eligible: isEligible
            };
            if (!isEligible) {
                details.eligible = false;
                details.missing_criteria.push('Income exceeds limit');
            }
        }
    }
    
    // Check age criteria
    if (scheme.min_age || scheme.max_age) {
        const userAge = userProfile.age || 0;
        let isEligible = true;
        if (scheme.min_age && userAge < scheme.min_age) isEligible = false;
        if (scheme.max_age && userAge > scheme.max_age) isEligible = false;
        
        details.checks.age = {
            required: scheme.min_age && scheme.max_age ? `Age ${scheme.min_age}-${scheme.max_age} years` :
                       scheme.min_age ? `Age ≥ ${scheme.min_age} years` :
                       `Age ≤ ${scheme.max_age} years`,
            user_value: `${userAge} years`,
            eligible: isEligible
        };
        if (!isEligible) {
            details.eligible = false;
            details.missing_criteria.push('Age criteria not met');
        }
    }
    
    // Check caste criteria
    if (scheme.caste_criteria && scheme.caste_criteria.length > 0) {
        // Get caste from the API response eligibility details
        const casteCheck = scheme.eligibility_details?.checks?.caste;
        if (casteCheck) {
            details.checks.caste = casteCheck;
            if (!casteCheck.eligible) {
                details.eligible = false;
                details.missing_criteria.push('Caste not eligible');
            }
        } else {
            // Fallback: try to get from user profile
            const userCaste = userProfile.caste || '';
            const isEligible = scheme.caste_criteria.some(caste => 
                caste.toLowerCase() === userCaste.toLowerCase()
            );
            details.checks.caste = {
                required: `Caste: ${scheme.caste_criteria.join(', ')}`,
                user_value: userCaste || 'Not specified',
                eligible: isEligible
            };
            if (!isEligible) {
                details.eligible = false;
                details.missing_criteria.push('Caste not eligible');
            }
        }
    }
    
    // Check required credentials using actual user VCs from API
    if (scheme.required_credentials && scheme.required_credentials.length > 0) {
        // Use actual user VCs from the API response
        const userCredentials = window.userVCs || [];
        const isEligible = scheme.required_credentials.some(cred => 
            userCredentials.includes(cred)
        );
        details.checks.credentials = {
            required: `Required VCs: ${scheme.required_credentials.join(', ')}`,
            user_value: userCredentials.join(', ') || 'No VCs',
            eligible: isEligible
        };
        if (!isEligible) {
            details.eligible = false;
            details.missing_criteria.push('Required credentials missing');
        }
    }
    
    return details;
}

function filterSchemes() {
    const categoryFilter = document.getElementById('categoryFilter').value;
    const eligibilityFilter = document.getElementById('eligibilityFilter').value;
    
    let filteredSchemes = allSchemes;
    
    // Filter by category
    if (categoryFilter) {
        filteredSchemes = filteredSchemes.filter(scheme => scheme.category === categoryFilter);
    }
    
    // Filter by eligibility
    if (eligibilityFilter) {
        filteredSchemes = filteredSchemes.filter(scheme => {
            const eligibility = checkEligibility(scheme);
            return eligibilityFilter === 'eligible' ? eligibility.eligible : !eligibility.eligible;
        });
    }
    
    displaySchemes(filteredSchemes);
}

function viewSchemeDetails(schemeId) {
    const scheme = allSchemes.find(s => s.id === schemeId);
    if (!scheme) return;
    
    const eligibilityDetails = checkEligibility(scheme);
    
    document.getElementById('modalSchemeName').textContent = scheme.name;
    
    const modalContent = document.getElementById('modalContent');
    modalContent.innerHTML = `
        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                <p class="text-gray-600">${scheme.description}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-900">Benefit Type</h4>
                    <p class="text-gray-600">${scheme.benefit_type.charAt(0).toUpperCase() + scheme.benefit_type.slice(1)}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Benefit Amount</h4>
                    <p class="text-gray-600">${scheme.benefit_amount ? `₹${new Intl.NumberFormat('en-IN').format(scheme.benefit_amount)}` : 'Not specified'}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Application Deadline</h4>
                    <p class="text-gray-600">${scheme.application_deadline ? new Date(scheme.application_deadline).toLocaleDateString('en-IN') : 'No deadline'}</p>
                </div>
                <div>
                    <h4 class="font-medium text-gray-900">Status</h4>
                    <p class="text-gray-600">${scheme.status.charAt(0).toUpperCase() + scheme.status.slice(1)}</p>
                </div>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Eligibility Criteria</h3>
                <div class="space-y-3">
                    ${Object.entries(eligibilityDetails.checks).map(([key, check]) => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div>
                                <div class="font-medium text-gray-900">${key.charAt(0).toUpperCase() + key.slice(1)}</div>
                                <div class="text-sm text-gray-600">${check.required}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">${check.user_value}</div>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    ${check.eligible ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${check.eligible ? '✓ Eligible' : '✗ Not Eligible'}
                                </span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
            
            <div class="flex space-x-3 pt-4">
                ${eligibilityDetails.eligible ? `
                    <button onclick="applyForScheme(${scheme.id})" 
                            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium">
                        Apply for Scheme
                    </button>
                ` : `
                    <div class="flex-1 px-4 py-2 bg-gray-100 text-gray-500 rounded-lg text-center font-medium">
                        Not Eligible
                    </div>
                `}
                <button onclick="closeSchemeModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Close
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('schemeModal').classList.remove('hidden');
}

function applyForScheme(schemeId) {
    // This would integrate with the actual application system
    alert('Application feature will be implemented in the next phase. This would submit your VCs and profile data to the government portal.');
}

function closeSchemeModal() {
    document.getElementById('schemeModal').classList.add('hidden');
}

function refreshSchemes() {
    loadSchemes();
}

function showLoading() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('schemesGrid').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
}

function hideLoading() {
    document.getElementById('loadingState').style.display = 'none';
}

function showError(message) {
    hideLoading();
    alert(message);
}
</script>
@endpush