<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Government Document Simulation - SarvOne Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .document-card {
            transition: all 0.3s ease;
        }
        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
                        <h1 class="text-base sm:text-xl font-bold text-gray-900 truncate">Government Document Simulation</h1>
                        <p class="text-xs sm:text-sm text-gray-600 truncate">Issue pre-defined government documents for testing</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
                    <a href="{{ route('admin.approval.dashboard') }}" class="text-gray-600 hover:text-gray-900 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.approval.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                            <i class="fas fa-home mr-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-sm font-medium text-gray-500">Document Simulation</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- User Lookup Section -->
        <div class="bg-white rounded-lg shadow-sm mb-8 p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-search mr-2"></i>Step 1: Lookup User
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="user_did" class="block text-sm font-medium text-gray-700 mb-2">User DID</label>
                    <input type="text" id="user_did" name="user_did" 
                           placeholder="did:sarvone:user:..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="flex items-end">
                    <button onclick="lookupUser()" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition duration-200">
                        <i class="fas fa-search mr-2"></i>Lookup User
                    </button>
                </div>
            </div>
            
            <!-- User Info Display -->
            <div id="user-info" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-2">User Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Name:</strong> <span id="user-name"></span></div>
                    <div><strong>Email:</strong> <span id="user-email"></span></div>
                    <div><strong>DID:</strong> <span id="user-did-display"></span></div>
                    <div><strong>Phone:</strong> <span id="user-phone"></span></div>
                </div>
            </div>
        </div>

        <!-- Document Selection Section -->
        <div id="document-section" class="hidden">
            <div class="bg-white rounded-lg shadow-sm mb-8 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-file-alt mr-2"></i>Step 2: Select Documents to Issue
                </h2>
                <p class="text-gray-600 mb-4">Select which government documents you want to issue to the user. Each document will be stored separately in IPFS and then on the blockchain.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- PAN Card -->
                    <div class="document-card bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-indigo-300" onclick="toggleDocument('pan_card')">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="pan_card" class="mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <i class="fas fa-id-card text-indigo-600 mr-2"></i>
                                <span class="font-medium">PAN Card</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Permanent Account Number</p>
                    </div>

                    <!-- Aadhaar Card -->
                    <div class="document-card bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-indigo-300" onclick="toggleDocument('aadhaar_card')">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="aadhaar_card" class="mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <i class="fas fa-fingerprint text-indigo-600 mr-2"></i>
                                <span class="font-medium">Aadhaar Card</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Unique Identification</p>
                    </div>

                    <!-- Income Certificate -->
                    <div class="document-card bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-indigo-300" onclick="toggleDocument('income_certificate')">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="income_certificate" class="mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <i class="fas fa-money-bill-wave text-indigo-600 mr-2"></i>
                                <span class="font-medium">Income Certificate</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Annual Income Proof</p>
                    </div>

                    <!-- Voter ID -->
                    <div class="document-card bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-indigo-300" onclick="toggleDocument('voter_id')">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="voter_id" class="mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <i class="fas fa-vote-yea text-indigo-600 mr-2"></i>
                                <span class="font-medium">Voter ID</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Electoral Photo Identity</p>
                    </div>

                    <!-- Driving License -->
                    <div class="document-card bg-white border border-gray-200 rounded-lg p-4 cursor-pointer hover:border-indigo-300" onclick="toggleDocument('driving_license')">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <input type="checkbox" id="driving_license" class="mr-3 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <i class="fas fa-car text-indigo-600 mr-2"></i>
                                <span class="font-medium">Driving License</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600">Motor Vehicle License</p>
                    </div>
                </div>

                <div class="mt-6">
                    <button onclick="issueDocuments()" id="issue-btn" disabled
                            class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                        <i class="fas fa-upload mr-2"></i>Issue Selected Documents
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <div id="progress-section" class="hidden">
            <div class="bg-white rounded-lg shadow-sm mb-8 p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-cogs mr-2"></i>Step 3: Issuance Progress
                </h2>
                <div id="progress-container">
                    <!-- Progress items will be added here dynamically -->
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div id="results-section" class="hidden">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-check-circle mr-2"></i>Issuance Results
                </h2>
                <div id="results-container">
                    <!-- Results will be displayed here -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentUser = null;
        let selectedDocuments = [];

        // Setup CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function lookupUser() {
            const userDid = $('#user_did').val().trim();
            
            if (!userDid) {
                alert('Please enter a user DID');
                return;
            }

            // Show loading state
            $('#user-info').hide();
            $('#document-section').hide();

            $.ajax({
                url: '{{ route("admin.simulate.lookup-user") }}',
                method: 'POST',
                data: { user_did: userDid },
                success: function(response) {
                    if (response.success) {
                        currentUser = response.user;
                        displayUserInfo(response.user);
                        $('#document-section').show();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    alert('Error: ' + (response?.message || 'Failed to lookup user'));
                }
            });
        }

        function displayUserInfo(user) {
            $('#user-name').text(user.name);
            $('#user-email').text(user.email);
            $('#user-did-display').text(user.did);
            $('#user-phone').text(user.phone || 'N/A');
            $('#user-info').show();
        }

        function toggleDocument(documentType) {
            const checkbox = $(`#${documentType}`);
            checkbox.prop('checked', !checkbox.prop('checked'));
            
            updateSelectedDocuments();
        }

        function updateSelectedDocuments() {
            selectedDocuments = [];
            $('input[type="checkbox"]:checked').each(function() {
                selectedDocuments.push($(this).attr('id'));
            });
            
            $('#issue-btn').prop('disabled', selectedDocuments.length === 0);
        }

        function issueDocuments() {
            if (!currentUser || selectedDocuments.length === 0) {
                alert('Please select a user and at least one document');
                return;
            }

            // Show progress section
            $('#progress-section').show();
            $('#results-section').hide();
            
            // Initialize progress
            initializeProgress();
            
            // Start issuance
            $.ajax({
                url: '{{ route("admin.simulate.issue") }}',
                method: 'POST',
                data: {
                    user_did: currentUser.did,
                    user_name: currentUser.name,
                    documents: selectedDocuments
                },
                success: function(response) {
                    if (response.success) {
                        displayResults(response.credentials);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    alert('Error: ' + (response?.message || 'Failed to issue documents'));
                }
            });
        }

        function initializeProgress() {
            const container = $('#progress-container');
            container.empty();
            
            selectedDocuments.forEach(docType => {
                const docName = getDocumentDisplayName(docType);
                container.append(`
                    <div class="flex items-center justify-between p-3 border-b border-gray-200 last:border-b-0">
                        <div class="flex items-center">
                            <div class="loading-spinner mr-3"></div>
                            <span class="font-medium">${docName}</span>
                        </div>
                        <span class="text-sm text-gray-500">Processing...</span>
                    </div>
                `);
            });
        }

        function displayResults(credentials) {
            $('#progress-section').hide();
            $('#results-section').show();
            
            const container = $('#results-container');
            container.empty();
            
            let successCount = 0;
            let failureCount = 0;
            
            credentials.forEach(credential => {
                const docName = getDocumentDisplayName(credential.type);
                const isSuccess = credential.success;
                
                if (isSuccess) successCount++;
                else failureCount++;
                
                container.append(`
                    <div class="border rounded-lg p-4 mb-4 ${isSuccess ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'}">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <i class="fas ${isSuccess ? 'fa-check-circle text-green-600' : 'fa-times-circle text-red-600'} mr-2"></i>
                                <span class="font-medium">${docName}</span>
                            </div>
                            <span class="text-sm ${isSuccess ? 'text-green-600' : 'text-red-600'}">
                                ${isSuccess ? 'Success' : 'Failed'}
                            </span>
                        </div>
                        
                                                 ${isSuccess ? `
                             <div class="text-sm text-gray-600 space-y-1">
                                 <div><strong>Credential ID:</strong> <code class="bg-gray-100 px-1 rounded">${credential.credential_id || 'N/A'}</code></div>
                                 <div><strong>Transaction Hash:</strong> <code class="bg-gray-100 px-1 rounded">${credential.tx_hash}</code></div>
                                 <div><strong>IPFS CID:</strong> <code class="bg-gray-100 px-1 rounded">${credential.ipfs_cid}</code></div>
                                 <div><strong>IPFS CID Hash:</strong> <code class="bg-gray-100 px-1 rounded">${credential.ipfs_cid_hash || 'N/A'}</code></div>
                             </div>
                         ` : `
                            <div class="text-sm text-red-600">
                                <strong>Error:</strong> ${credential.error}
                            </div>
                        `}
                    </div>
                `);
            });
            
            // Add summary
            container.prepend(`
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h3 class="font-medium text-blue-900 mb-2">Issuance Summary</h3>
                    <div class="text-sm text-blue-800">
                        <div>Total Documents: ${credentials.length}</div>
                        <div>Successful: ${successCount}</div>
                        <div>Failed: ${failureCount}</div>
                    </div>
                </div>
            `);
        }

        function getDocumentDisplayName(docType) {
            const names = {
                'pan_card': 'PAN Card',
                'aadhaar_card': 'Aadhaar Card',
                'income_certificate': 'Income Certificate',
                'voter_id': 'Voter ID',
                'driving_license': 'Driving License'
            };
            return names[docType] || docType;
        }

        // Initialize
        $(document).ready(function() {
            // Handle checkbox changes
            $('input[type="checkbox"]').change(function() {
                updateSelectedDocuments();
            });
        });
    </script>
</body>
</html> 