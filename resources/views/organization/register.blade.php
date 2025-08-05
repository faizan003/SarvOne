<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Registration - SarvOne</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
        }
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background: linear-gradient(135deg, #3B82F6 0%, #1D4ED8 100%);
            color: white;
        }
        .step-indicator.completed {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
        }
        .form-section {
            display: none;
        }
        .form-section.active {
            display: block;
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .file-upload-area {
            border: 2px dashed #CBD5E0;
            transition: all 0.3s ease;
        }
        .file-upload-area:hover {
            border-color: #3B82F6;
            background-color: #F8FAFC;
        }
        .file-upload-area.dragover {
            border-color: #3B82F6;
            background-color: #EBF4FF;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mx-auto h-20 w-20 gradient-bg rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-building text-white text-3xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-2">Organization Registration</h1>
                <p class="text-lg text-gray-600">
                    Join SarvOne as a verified organization to issue blockchain-backed digital credentials
                </p>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="step-indicator active flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold border-2" data-step="1">1</div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
                    <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold border-2 bg-white text-gray-400" data-step="2">2</div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
                    <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold border-2 bg-white text-gray-400" data-step="3">3</div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
                    <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold border-2 bg-white text-gray-400" data-step="4">4</div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
                    <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold border-2 bg-white text-gray-400" data-step="5">5</div>
                    <div class="flex-1 h-1 bg-gray-200 mx-2"></div>
                    <div class="step-indicator flex items-center justify-center w-10 h-10 rounded-full text-sm font-semibold border-2 bg-white text-gray-400" data-step="6">6</div>
                </div>
                <div class="flex items-center justify-between mt-3 text-xs text-gray-600">
                    <span>Legal Details</span>
                    <span>Contact Info</span>
                    <span>Signatory</span>
                    <span>Blockchain</span>
                    <span>VC Scopes</span>
                    <span>Compliance</span>
                </div>
            </div>

            <!-- Registration Form -->
            <form id="registrationForm" action="{{ route('organization.register.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Step 1: Legal & Organization Details -->
                <div class="form-section active bg-white rounded-xl shadow-lg p-8" data-step="1">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Legal & Organization Details</h3>
                        <p class="text-gray-600">Provide your organization's legal information and official details</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Full Legal Name -->
                        <div class="md:col-span-2">
                            <label for="legal_name" class="block text-sm font-semibold text-gray-700 mb-2">Full Legal Name of Organization *</label>
                            <input id="legal_name" name="legal_name" type="text" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="e.g., Indian Institute of Technology Delhi"
                                   value="{{ old('legal_name') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="legal_name"></p>
                        </div>

                        <!-- Organization Type -->
                        <div>
                            <label for="organization_type" class="block text-sm font-semibold text-gray-700 mb-2">Registered Organization Type *</label>
                            <select id="organization_type" name="organization_type" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                <option value="">Select organization type</option>
            <option value="uidai" {{ old('organization_type') == 'uidai' ? 'selected' : '' }}>UIDAI (Aadhaar Authority)</option>
                                <option value="government" {{ old('organization_type') == 'government' ? 'selected' : '' }}>Government Agency/Department</option>
                                <option value="land_property" {{ old('organization_type') == 'land_property' ? 'selected' : '' }}>Land Property Organization</option>
                                <option value="bank" {{ old('organization_type') == 'bank' ? 'selected' : '' }}>Bank/Financial Institution</option>
                                <option value="school_university" {{ old('organization_type') == 'school_university' ? 'selected' : '' }}>School/University</option>
                            </select>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="organization_type"></p>
                        </div>

                        <!-- Registration Number -->
                        <div>
                            <label for="registration_number" class="block text-sm font-semibold text-gray-700 mb-2">Official Registration Number/License *</label>
                            <input id="registration_number" name="registration_number" type="text" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="e.g., CIN, RBI code, UDISE, etc."
                                   value="{{ old('registration_number') }}">
                            <p class="mt-1 text-sm text-gray-600">For banks: RBI code; for schools: UDISE; for companies: CIN number</p>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="registration_number"></p>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Contact & Identity Information -->
                <div class="form-section bg-white rounded-xl shadow-lg p-8" data-step="2">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Contact & Identity Information</h3>
                        <p class="text-gray-600">Provide official contact details and address information</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Official Email -->
                        <div>
                            <label for="official_email" class="block text-sm font-semibold text-gray-700 mb-2">Official Email Address *</label>
                            <input id="official_email" name="official_email" type="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="admin@organization.edu.in"
                                   value="{{ old('official_email') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="official_email"></p>
                        </div>

                        <!-- Official Phone -->
                        <div>
                            <label for="official_phone" class="block text-sm font-semibold text-gray-700 mb-2">Official Phone Number *</label>
                            <input id="official_phone" name="official_phone" type="tel" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="+91 11 2659 1234"
                                   value="{{ old('official_phone') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="official_phone"></p>
                        </div>

                        <!-- Website URL -->
                        <div class="md:col-span-2">
                            <label for="website_url" class="block text-sm font-semibold text-gray-700 mb-2">Official Website URL</label>
                            <input id="website_url" name="website_url" type="url" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="https://www.organization.edu.in"
                                   value="{{ old('website_url') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="website_url"></p>
                        </div>

                        <!-- Head Office Address -->
                        <div class="md:col-span-2">
                            <label for="head_office_address" class="block text-sm font-semibold text-gray-700 mb-2">Registered Head Office Address *</label>
                            <textarea id="head_office_address" name="head_office_address" rows="3" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                      placeholder="Complete address with PIN code">{{ old('head_office_address') }}</textarea>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="head_office_address"></p>
                        </div>

                        <!-- Branch Address (Optional) -->
                        <div class="md:col-span-2">
                            <label for="branch_address" class="block text-sm font-semibold text-gray-700 mb-2">Branch Address (if different from head office)</label>
                            <textarea id="branch_address" name="branch_address" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                      placeholder="Branch office address (optional)">{{ old('branch_address') }}</textarea>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="branch_address"></p>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Representative/Authorized Signatory -->
                <div class="form-section bg-white rounded-xl shadow-lg p-8" data-step="3">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Authorized Signatory Details</h3>
                        <p class="text-gray-600">Details of the person authorized to sign and verify credentials</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Signatory Name -->
                        <div>
                            <label for="signatory_name" class="block text-sm font-semibold text-gray-700 mb-2">Name of Authorized Person *</label>
                            <input id="signatory_name" name="signatory_name" type="text" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Dr. John Doe"
                                   value="{{ old('signatory_name') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="signatory_name"></p>
                        </div>

                        <!-- Designation -->
                        <div>
                            <label for="signatory_designation" class="block text-sm font-semibold text-gray-700 mb-2">Designation/Role in Organization *</label>
                            <input id="signatory_designation" name="signatory_designation" type="text" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="Director, Registrar, CEO, etc."
                                   value="{{ old('signatory_designation') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="signatory_designation"></p>
                        </div>

                        <!-- Signatory Email -->
                        <div>
                            <label for="signatory_email" class="block text-sm font-semibold text-gray-700 mb-2">Contact Email of Signatory *</label>
                            <input id="signatory_email" name="signatory_email" type="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="director@organization.edu.in"
                                   value="{{ old('signatory_email') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="signatory_email"></p>
                        </div>

                        <!-- Signatory Phone -->
                        <div>
                            <label for="signatory_phone" class="block text-sm font-semibold text-gray-700 mb-2">Contact Phone of Signatory *</label>
                            <input id="signatory_phone" name="signatory_phone" type="tel" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                   placeholder="+91 98765 43210"
                                   value="{{ old('signatory_phone') }}">
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="signatory_phone"></p>
                        </div>

                        <!-- Identity Document -->
                        <div class="md:col-span-2">
                            <label for="signatory_id_document" class="block text-sm font-semibold text-gray-700 mb-2">Supporting Identity Document *</label>
                            <div class="file-upload-area rounded-lg p-6 text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-600 mb-2">Upload Government ID or Employee ID</p>
                                <input type="file" id="signatory_id_document" name="signatory_id_document" accept=".pdf,.jpg,.jpeg,.png" required class="hidden">
                                <button type="button" onclick="document.getElementById('signatory_id_document').click()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                                    Choose File
                                </button>
                                <p class="text-xs text-gray-500 mt-2">PDF, JPG, PNG (Max 5MB)</p>
                            </div>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="signatory_id_document"></p>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Technical & Blockchain Details -->
                <div class="form-section bg-white rounded-xl shadow-lg p-8" data-step="4">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Blockchain & Technical Details</h3>
                        <p class="text-gray-600">Provide your Polygon wallet address for blockchain interactions</p>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Wallet Address -->
                        <div>
                            <label for="wallet_address" class="block text-sm font-semibold text-gray-700 mb-2">Polygon Testnet Wallet Address *</label>
                            <input id="wallet_address" name="wallet_address" type="text" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 font-mono"
                                   placeholder="0x742d35Cc6634C0532925a3b8D400fA97A8CE4AC6"
                                   value="{{ old('wallet_address') }}">
                            <p class="mt-1 text-sm text-gray-600">This wallet will be linked to your organization's DID for credential issuance on the Polygon Amoy testnet</p>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="wallet_address"></p>
                        </div>

                        <!-- Technical Contact (Optional) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="technical_contact_name" class="block text-sm font-semibold text-gray-700 mb-2">Technical Contact Person (Optional)</label>
                                <input id="technical_contact_name" name="technical_contact_name" type="text" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="IT Administrator name"
                                       value="{{ old('technical_contact_name') }}">
                                <p class="mt-1 text-sm text-red-600 error-message" data-field="technical_contact_name"></p>
                            </div>

                            <div>
                                <label for="technical_contact_email" class="block text-sm font-semibold text-gray-700 mb-2">Technical Contact Email (Optional)</label>
                                <input id="technical_contact_email" name="technical_contact_email" type="email" 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="it@organization.edu.in"
                                       value="{{ old('technical_contact_email') }}">
                                <p class="mt-1 text-sm text-red-600 error-message" data-field="technical_contact_email"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: VC/Scope Details -->
                <div class="form-section bg-white rounded-xl shadow-lg p-8" data-step="5">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Credential Scopes & Use Case</h3>
                        <p class="text-gray-600">Select the types of credentials you plan to issue</p>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Credential Scopes -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-4">Requested Credential Scopes *</label>
                            <p class="text-sm text-gray-600 mb-4">Select the credentials your organization can <strong>ISSUE</strong> (write) and <strong>VERIFY</strong> (read). Scopes shown are based on your organization type.</p>
                            
                            <!-- Credential Categories will be populated by JavaScript -->
                            <div id="credential-scopes-container">
                                <p class="text-gray-500 text-center py-8">Please select an organization type in Step 1 to see available credential scopes.</p>
                            </div>
                            
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="credential_scopes"></p>
                        </div>

                        <!-- Expected Volume -->
                        <div>
                            <label for="expected_volume" class="block text-sm font-semibold text-gray-700 mb-2">Expected Volume per Month *</label>
                            <select id="expected_volume" name="expected_volume" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200">
                                <option value="">Select expected volume</option>
                                <option value="1-50" {{ old('expected_volume') == '1-50' ? 'selected' : '' }}>1-50 credentials</option>
                                <option value="51-200" {{ old('expected_volume') == '51-200' ? 'selected' : '' }}>51-200 credentials</option>
                                <option value="201-1000" {{ old('expected_volume') == '201-1000' ? 'selected' : '' }}>201-1000 credentials</option>
                                <option value="1000+" {{ old('expected_volume') == '1000+' ? 'selected' : '' }}>1000+ credentials</option>
                            </select>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="expected_volume"></p>
                        </div>

                        <!-- Use Case Description -->
                        <div>
                            <label for="use_case_description" class="block text-sm font-semibold text-gray-700 mb-2">Use Case Description *</label>
                            <textarea id="use_case_description" name="use_case_description" rows="4" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                      placeholder="Describe your planned use of SarvOne platform. E.g., issuing digital mark sheets for students, KYC verification for banking customers, etc.">{{ old('use_case_description') }}</textarea>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="use_case_description"></p>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Compliance & Documentation -->
                <div class="form-section bg-white rounded-xl shadow-lg p-8" data-step="6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Compliance & Documentation</h3>
                        <p class="text-gray-600">Upload required documents and agree to terms</p>
                    </div>
                    
                    <div class="space-y-6">
                        <!-- Registration Certificate -->
                        <div>
                            <label for="registration_certificate" class="block text-sm font-semibold text-gray-700 mb-2">Government Registration Certificate *</label>
                            <div class="file-upload-area rounded-lg p-6 text-center">
                                <i class="fas fa-file-pdf text-4xl text-red-400 mb-4"></i>
                                <p class="text-gray-600 mb-2">Upload your organization's registration certificate</p>
                                <input type="file" id="registration_certificate" name="registration_certificate" accept=".pdf,.jpg,.jpeg,.png" required class="hidden">
                                <button type="button" onclick="document.getElementById('registration_certificate').click()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                                    Choose File
                                </button>
                                <p class="text-xs text-gray-500 mt-2">PDF, JPG, PNG (Max 10MB)</p>
                            </div>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="registration_certificate"></p>
                        </div>

                        <!-- Authorization Proof -->
                        <div>
                            <label for="authorization_proof" class="block text-sm font-semibold text-gray-700 mb-2">Proof of Authorization for Signatory *</label>
                            <div class="file-upload-area rounded-lg p-6 text-center">
                                <i class="fas fa-file-contract text-4xl text-green-400 mb-4"></i>
                                <p class="text-gray-600 mb-2">Upload document proving signatory's authority</p>
                                <input type="file" id="authorization_proof" name="authorization_proof" accept=".pdf,.jpg,.jpeg,.png" required class="hidden">
                                <button type="button" onclick="document.getElementById('authorization_proof').click()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition duration-200">
                                    Choose File
                                </button>
                                <p class="text-xs text-gray-500 mt-2">Board resolution, appointment letter, etc. (Max 10MB)</p>
                            </div>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="authorization_proof"></p>
                        </div>

                        <!-- Account Credentials -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t pt-6">
                            <div>
                                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Account Password *</label>
                                <input id="password" name="password" type="password" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="Create a strong password">
                                <p class="mt-1 text-sm text-red-600 error-message" data-field="password"></p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">Confirm Password *</label>
                                <input id="password_confirmation" name="password_confirmation" type="password" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
                                       placeholder="Confirm your password">
                                <p class="mt-1 text-sm text-red-600 error-message" data-field="password_confirmation"></p>
                            </div>
                        </div>

                        <!-- Terms Agreement -->
                        <div class="border-t pt-6">
                            <label class="flex items-start">
                                <input type="checkbox" name="terms_agreement" required class="mt-1 mr-3">
                                <div class="text-sm">
                                    <span class="font-semibold text-gray-700">Data Policy & Platform Terms Agreement *</span>
                                    <p class="text-gray-600 mt-1">
                                        I confirm that all information provided is accurate and consent to verification/checks as per SarvOne platform requirements and applicable legal requirements. I understand that false information may result in rejection or termination of services.
                                    </p>
                                </div>
                            </label>
                            <p class="mt-1 text-sm text-red-600 error-message" data-field="terms_agreement"></p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between mt-8">
                    <button type="button" id="prevBtn" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition duration-200 hidden">
                        <i class="fas fa-arrow-left mr-2"></i>Previous
                    </button>
                    
                    <div class="flex space-x-4">
                        <button type="button" id="nextBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                            Next<i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        
                        <button type="submit" id="submitBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 hidden">
                            <span class="normal-text">
                                <i class="fas fa-paper-plane mr-2"></i>Submit Application
                            </span>
                            <span class="loading-text hidden">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Submitting...
                            </span>
                        </button>
                    </div>
                </div>

                <!-- Login Link -->
                <div class="text-center mt-8">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <a href="{{ route('organization.login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

                <!-- Toast Messages -->
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
            let currentStep = 1;
            const totalSteps = 6;

            // Simplified credential scopes configuration
            const credentialScopes = {
                'uidai': {
                    'write': [
                        { value: 'aadhaar_card', name: 'Aadhaar Card', desc: 'Issue and manage Aadhaar cards' }
                    ],
                    'read': [
                        { value: 'aadhaar_card', name: 'Aadhaar Card', desc: 'Verify Aadhaar card authenticity' }
                    ]
                },
                'government': {
                    'write': [
                        { value: 'aadhaar_card', name: 'Aadhaar Card', desc: 'Issue and manage Aadhaar cards' },
                        { value: 'pan_card', name: 'PAN Card', desc: 'Issue and manage PAN cards' },
                        { value: 'voter_id', name: 'Voter ID', desc: 'Issue and manage Voter ID cards' },
                        { value: 'caste_certificate', name: 'Caste Certificate', desc: 'Issue caste certificates' },
                        { value: 'ration_card', name: 'Ration Card', desc: 'Issue ration cards' },
                        { value: 'income_certificate', name: 'Income Certificate', desc: 'Issue income certificates' },
                        { value: 'domicile_certificate', name: 'Domicile Certificate', desc: 'Issue domicile certificates' },
                        { value: 'birth_certificate', name: 'Birth Certificate', desc: 'Issue birth certificates' },
                        { value: 'death_certificate', name: 'Death Certificate', desc: 'Issue death certificates' },
                        { value: 'marriage_certificate', name: 'Marriage Certificate', desc: 'Issue marriage certificates' }
                    ],
                    'read': [
                        { value: 'aadhaar_card', name: 'Aadhaar Card', desc: 'Verify Aadhaar cards' },
                        { value: 'pan_card', name: 'PAN Card', desc: 'Verify PAN cards' },
                        { value: 'voter_id', name: 'Voter ID', desc: 'Verify Voter ID cards' },
                        { value: 'caste_certificate', name: 'Caste Certificate', desc: 'Verify caste certificates' },
                        { value: 'ration_card', name: 'Ration Card', desc: 'Verify ration cards' },
                        { value: 'income_certificate', name: 'Income Certificate', desc: 'Verify income certificates' },
                        { value: 'domicile_certificate', name: 'Domicile Certificate', desc: 'Verify domicile certificates' },
                        { value: 'birth_certificate', name: 'Birth Certificate', desc: 'Verify birth certificates' },
                        { value: 'death_certificate', name: 'Death Certificate', desc: 'Verify death certificates' },
                        { value: 'marriage_certificate', name: 'Marriage Certificate', desc: 'Verify marriage certificates' }
                    ]
                },
                'land_property': {
                    'write': [
                        { value: 'land_property', name: 'Land Property', desc: 'Issue land property certificates' },
                        { value: 'property_tax_receipt', name: 'Property Tax Receipt', desc: 'Issue property tax receipts' },
                        { value: 'encumbrance_certificate', name: 'Encumbrance Certificate', desc: 'Issue encumbrance certificates' }
                    ],
                    'read': [
                        { value: 'aadhaar_card', name: 'Aadhaar Card', desc: 'Verify Aadhaar cards' },
                        { value: 'pan_card', name: 'PAN Card', desc: 'Verify PAN cards' },
                        { value: 'land_property', name: 'Land Property', desc: 'Verify land property documents' },
                        { value: 'income_certificate', name: 'Income Certificate', desc: 'Verify income certificates' }
                    ]
                },
                'bank': {
                    'write': [
                        { value: 'bank_account', name: 'Bank Account', desc: 'Issue bank account certificates' },
                        { value: 'loan', name: 'Loan', desc: 'Issue loan certificates' },
                        { value: 'land_loan', name: 'Land Loan', desc: 'Issue land loan certificates' },
                        { value: 'credit_score', name: 'Credit Score', desc: 'Issue credit score reports' },
                        { value: 'income_certificate', name: 'Income Certificate', desc: 'Issue income certificates' },
                        { value: 'employment_certificate', name: 'Employment Certificate', desc: 'Issue employment certificates' }
                    ],
                    'read': [
                        { value: 'aadhaar_card', name: 'Aadhaar Card', desc: 'Verify Aadhaar cards' },
                        { value: 'pan_card', name: 'PAN Card', desc: 'Verify PAN cards' },
                        { value: 'income_certificate', name: 'Income Certificate', desc: 'Verify income certificates' },
                        { value: 'land_property', name: 'Land Property', desc: 'Verify land property documents' },
                        { value: 'employment_certificate', name: 'Employment Certificate', desc: 'Verify employment certificates' }
                    ]
                },
                'school_university': {
                    'write': [
                        { value: 'student_status', name: 'Student Status', desc: 'Issue current student status' },
                        { value: 'marksheet', name: 'Marksheet', desc: 'Issue academic marksheets' },
                        { value: 'study_certificate', name: 'Study Certificate', desc: 'Issue study certificates' },
                        { value: 'degree_certificate', name: 'Degree Certificate', desc: 'Issue degree certificates' },
                        { value: 'transfer_certificate', name: 'Transfer Certificate', desc: 'Issue transfer certificates' }
                    ],
                    'read': [
                        { value: 'aadhaar_card', name: 'Aadhaar Card', desc: 'Verify Aadhaar cards' },
                        { value: 'income_certificate', name: 'Income Certificate', desc: 'Verify income certificates' },
                        { value: 'marksheet', name: 'Marksheet', desc: 'Verify academic marksheets' },
                        { value: 'caste_certificate', name: 'Caste Certificate', desc: 'Verify caste certificates' }
                    ]
                }
            };

            // Initialize form
            showStep(currentStep);

            // Organization type change handler
            $('#organization_type').on('change', function() {
                updateCredentialScopes($(this).val());
            });

            // Update credential scopes based on organization type
            function updateCredentialScopes(orgType) {
                const container = $('#credential-scopes-container');
                
                if (!orgType || !credentialScopes[orgType]) {
                    container.html('<p class="text-gray-500 text-center py-8">Please select a valid organization type to see available credential scopes.</p>');
                    return;
                }

                const scopes = credentialScopes[orgType];
                let html = '';

                // Write Permissions Section
                if (scopes.write && scopes.write.length > 0) {
                    html += `
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold text-green-700 mb-3 flex items-center">
                                <i class="fas fa-edit mr-2"></i>
                                Credentials You Can ISSUE (Write Permissions)
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">These are credentials your organization can create and issue to users:</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    `;
                    
                    scopes.write.forEach(scope => {
                        html += `
                            <label class="flex items-start p-4 border-2 border-green-200 rounded-lg hover:bg-green-50 cursor-pointer transition duration-200">
                                <input type="checkbox" name="write_scopes[]" value="${scope.value}" class="mt-1 mr-3 text-green-600">
                                <div class="flex-1">
                                    <span class="font-medium text-green-800">${scope.name}</span>
                                    <p class="text-sm text-gray-600 mt-1">${scope.desc}</p>
                                </div>
                            </label>
                        `;
                    });
                    
                    html += `
                            </div>
                        </div>
                    `;
                }

                // Read Permissions Section - Grouped by document type
                if (scopes.read && scopes.read.length > 0) {
                    html += `
                        <div class="mb-4">
                            <h4 class="text-lg font-semibold text-blue-700 mb-3 flex items-center">
                                <i class="fas fa-search mr-2"></i>
                                Documents You Can VERIFY (Read Permissions)
                            </h4>
                            <p class="text-sm text-gray-600 mb-4">Select individual documents your organization can access and verify from users:</p>
                    `;
                    
                    // Group documents by their group property
                    const groupedScopes = {};
                    scopes.read.forEach(scope => {
                        const group = scope.group || 'Other Documents';
                        if (!groupedScopes[group]) {
                            groupedScopes[group] = [];
                        }
                        groupedScopes[group].push(scope);
                    });
                    
                    // Display each group
                    Object.keys(groupedScopes).forEach(groupName => {
                        html += `
                            <div class="mb-6 bg-blue-50 rounded-lg p-4">
                                <h5 class="text-md font-semibold text-blue-800 mb-3 flex items-center">
                                    <i class="fas fa-folder mr-2"></i>
                                    ${groupName}
                                </h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        `;
                        
                        groupedScopes[groupName].forEach(scope => {
                            html += `
                                <label class="flex items-start p-3 border-2 border-blue-200 rounded-lg hover:bg-blue-100 cursor-pointer transition duration-200 bg-white">
                                <input type="checkbox" name="read_scopes[]" value="${scope.value}" class="mt-1 mr-3 text-blue-600">
                                <div class="flex-1">
                                    <span class="font-medium text-blue-800">${scope.name}</span>
                                        <p class="text-xs text-gray-600 mt-1">${scope.desc}</p>
                                </div>
                            </label>
                        `;
                    });
                    
                    html += `
                            </div>
                        </div>
                    `;
                    });
                    
                    html += `</div>`;
                }

                container.html(html);
            }

            // File upload handlers
            function handleFileUpload(inputId, buttonText) {
                $(`#${inputId}`).on('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const button = $(this).siblings('button');
                        button.text(`✓ ${file.name.substring(0, 20)}...`).addClass('bg-green-500').removeClass('bg-blue-500');
                    }
                });
            }

            handleFileUpload('signatory_id_document', 'ID Document Selected');
            handleFileUpload('registration_certificate', 'Certificate Selected');
            handleFileUpload('authorization_proof', 'Authorization Selected');

            // Step navigation
            $('#nextBtn').on('click', function() {
                if (validateStep(currentStep)) {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showStep(currentStep);
                    }
                }
            });

            $('#prevBtn').on('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            });

            // Show specific step
            function showStep(step) {
                // Hide all sections
                $('.form-section').removeClass('active');
                
                // Show current section
                $(`.form-section[data-step="${step}"]`).addClass('active');
                
                // Update progress indicators
                updateProgressIndicators(step);
                
                // Update navigation buttons
                updateNavigationButtons(step);
            }

            // Update progress indicators
            function updateProgressIndicators(step) {
                $('.step-indicator').each(function() {
                    const indicatorStep = parseInt($(this).data('step'));
                    
                    if (indicatorStep < step) {
                        $(this).removeClass('active').addClass('completed');
                    } else if (indicatorStep === step) {
                        $(this).removeClass('completed').addClass('active');
                    } else {
                        $(this).removeClass('active completed');
                    }
                });
            }

            // Update navigation buttons
            function updateNavigationButtons(step) {
                // Previous button
                if (step === 1) {
                    $('#prevBtn').addClass('hidden');
                } else {
                    $('#prevBtn').removeClass('hidden');
                }
                
                // Next/Submit buttons
                if (step === totalSteps) {
                    $('#nextBtn').addClass('hidden');
                    $('#submitBtn').removeClass('hidden');
                } else {
                    $('#nextBtn').removeClass('hidden');
                    $('#submitBtn').addClass('hidden');
                }
            }

            // Validate current step
            function validateStep(step) {
                let isValid = true;
                const currentSection = $(`.form-section[data-step="${step}"]`);
                
                // Clear previous errors
                currentSection.find('.error-message').text('');
                
                // Check required fields in current step
                currentSection.find('input[required], select[required], textarea[required]').each(function() {
                    if (!$(this).val() || ($(this).attr('type') === 'checkbox' && !$(this).is(':checked'))) {
                        isValid = false;
                        const fieldName = $(this).attr('name');
                        const errorElement = currentSection.find(`.error-message[data-field="${fieldName}"]`);
                        errorElement.text('This field is required.');
                        $(this).addClass('border-red-500');
                    } else {
                        $(this).removeClass('border-red-500');
                    }
                });

                // Special validation for credential scopes (checkboxes)
                if (step === 5) {
                    const checkedWriteScopes = currentSection.find('input[name="write_scopes[]"]:checked');
                    const checkedReadScopes = currentSection.find('input[name="read_scopes[]"]:checked');
                    if (checkedWriteScopes.length === 0 && checkedReadScopes.length === 0) {
                        isValid = false;
                        currentSection.find('.error-message[data-field="credential_scopes"]').text('Please select at least one credential scope (either write or read permissions).');
                    }
                }

                // Wallet address validation
                if (step === 4) {
                    const walletAddress = $('#wallet_address').val();
                    if (walletAddress && !walletAddress.match(/^0x[a-fA-F0-9]{40}$/)) {
                        isValid = false;
                        $('.error-message[data-field="wallet_address"]').text('Please enter a valid Ethereum wallet address.');
                        $('#wallet_address').addClass('border-red-500');
                    }
                }

                return isValid;
            }

            // Form submission
            $('#registrationForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validate final step
                if (!validateStep(totalSteps)) {
                    return;
                }
                
                // Clear previous error messages
                $('.error-message').text('');
                
                // Show loading state
                const submitBtn = $('#submitBtn');
                submitBtn.prop('disabled', true);
                submitBtn.find('.normal-text').addClass('hidden');
                submitBtn.find('.loading-text').removeClass('hidden');
                
                // Create FormData for file uploads
                const formData = new FormData(this);
                
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Show success toast
                            showToast(response.message, 'success');
                            
                            // Redirect after a short delay
                            setTimeout(function() {
                                window.location.href = response.redirect || '/organization/login';
                            }, 2000);
                        }
                    },
                    error: function(xhr) {
                        // Reset button state
                        submitBtn.prop('disabled', false);
                        submitBtn.find('.normal-text').removeClass('hidden');
                        submitBtn.find('.loading-text').addClass('hidden');
                        
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(field) {
                                $(`.error-message[data-field="${field}"]`).text(errors[field][0]);
                                $(`[name="${field}"]`).addClass('border-red-500');
                            });
                            
                            // Go to first step with errors
                            for (let step = 1; step <= totalSteps; step++) {
                                const stepSection = $(`.form-section[data-step="${step}"]`);
                                if (stepSection.find('.error-message').filter(function() { return $(this).text() !== ''; }).length > 0) {
                                    currentStep = step;
                                    showStep(currentStep);
                                    break;
                                }
                            }
                        } else {
                            // Show error toast
                            showToast(xhr.responseJSON?.message || 'An error occurred. Please try again.', 'error');
                        }
                    }
                });
            });
            
            // Toast helper functions
            function showToast(message, type = 'success') {
                const toast = $('#toast');
                const toastContent = $('#toast-content');
                const toastIcon = $('#toast-icon');
                const toastMessage = $('#toast-message');
                
                // Set message
                toastMessage.text(message);
                
                // Set type-specific styling
                if (type === 'success') {
                    toastContent.removeClass('bg-red-500').addClass('bg-green-500');
                    toastIcon.removeClass('fa-exclamation-circle').addClass('fa-check-circle');
                } else if (type === 'error') {
                    toastContent.removeClass('bg-green-500').addClass('bg-red-500');
                    toastIcon.removeClass('fa-check-circle').addClass('fa-exclamation-circle');
                }
                
                // Show toast
                toast.removeClass('translate-x-full opacity-0').addClass('translate-x-0 opacity-100');
                
                // Auto-hide after 5 seconds
                setTimeout(function() {
                    hideToast();
                }, 5000);
            }
            
            window.hideToast = function() {
                $('#toast').removeClass('translate-x-0 opacity-100').addClass('translate-x-full opacity-0');
            };
            
            // Hide toast when clicked
            $('#toast').on('click', function() {
                hideToast();
            });
        });
    </script>
</body>
</html> 