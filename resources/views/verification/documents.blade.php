<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Upload Documents - SarvOne</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="h-8 w-8 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center mr-3">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-gray-900">SarvOne</span>
                    </div>
                </div>
                
                <div class="flex items-center">
                    <span class="text-sm text-gray-600">Step 2 of 3</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center mb-6">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Upload Your Aadhaar</h1>
            <p class="text-lg text-gray-600">
                We'll extract and verify your information using AI
            </p>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center">
                <div class="flex items-center text-green-600">
                    <div class="w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium">✓</div>
                    <span class="ml-2 text-sm font-medium text-green-600">Live Selfie</span>
                </div>
                <div class="flex-1 mx-4 h-1 bg-green-200 rounded"></div>
                <div class="flex items-center text-blue-600">
                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center text-sm font-medium">2</div>
                    <span class="ml-2 text-sm font-medium text-blue-600">Upload Aadhaar</span>
                </div>
                <div class="flex-1 mx-4 h-1 bg-gray-200 rounded"></div>
                <div class="flex items-center text-gray-400">
                    <div class="w-8 h-8 bg-gray-200 text-gray-400 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                    <span class="ml-2 text-sm font-medium text-gray-400">Live Video</span>
                </div>
            </div>
        </div>

        <!-- Upload Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6">
                <!-- Upload Area -->
                <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-colors duration-200 hover:border-blue-400 hover:bg-blue-50">
                    <input type="file" id="file-input" accept="image/*" class="hidden">
                    
                    <div id="upload-prompt">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Upload your Aadhaar card</h3>
                        <p class="text-sm text-gray-600 mb-4">Drag and drop your image here, or click to browse</p>
                        <button class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            Choose File
                        </button>
                    </div>

                    <!-- Preview Area -->
                    <div id="preview-area" class="hidden">
                        <img id="preview-image" class="mx-auto max-h-64 rounded-lg shadow-md mb-4">
                        <div id="file-info" class="text-sm text-gray-600 mb-4"></div>
                        <div class="flex gap-4 justify-center">
                            <button id="change-file" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                Change File
                            </button>
                            <button id="upload-file" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors duration-200">
                                Process Document
                            </button>
                        </div>
                    </div>

                    <!-- Processing State -->
                    <div id="processing-area" class="hidden">
                        <div class="flex items-center justify-center">
                            <svg class="animate-spin h-8 w-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span class="text-lg font-medium text-gray-900">Processing your document...</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">AI is extracting and verifying your information</p>
                    </div>

                    <!-- Results Area -->
                    <div id="results-area" class="hidden">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <h4 class="font-medium text-green-900 mb-2">Document processed successfully!</h4>
                            <div id="extracted-data" class="text-sm text-green-800"></div>
                        </div>
                        <button id="continue-btn" class="w-full bg-blue-600 text-white font-medium py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            Continue to Live Video
                        </button>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mt-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-amber-900 mb-2">Document Requirements:</h4>
                        <ul class="text-xs text-amber-800 space-y-1">
                            <li>• Clear, high-quality image of your Aadhaar card</li>
                            <li>• All text should be readable and not blurred</li>
                            <li>• Good lighting without shadows or glare</li>
                            <li>• Both front and back sides accepted</li>
                            <li>• Maximum file size: 5MB</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('file-input');
        const uploadPrompt = document.getElementById('upload-prompt');
        const previewArea = document.getElementById('preview-area');
        const processingArea = document.getElementById('processing-area');
        const resultsArea = document.getElementById('results-area');
        const previewImage = document.getElementById('preview-image');
        const fileInfo = document.getElementById('file-info');
        const extractedData = document.getElementById('extracted-data');

        let selectedFile = null;

        // Click to upload
        uploadArea.addEventListener('click', () => {
            if (!selectedFile) {
                fileInput.click();
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('border-blue-400', 'bg-blue-50');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        });

        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        // Handle file selection
        function handleFile(file) {
            if (!file.type.startsWith('image/')) {
                alert('Please select an image file');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                return;
            }

            selectedFile = file;

            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImage.src = e.target.result;
                fileInfo.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                
                uploadPrompt.classList.add('hidden');
                previewArea.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        // Change file
        document.getElementById('change-file').addEventListener('click', (e) => {
            e.stopPropagation();
            selectedFile = null;
            previewArea.classList.add('hidden');
            uploadPrompt.classList.remove('hidden');
            fileInput.value = '';
        });

        // Upload file
        document.getElementById('upload-file').addEventListener('click', (e) => {
            e.stopPropagation();
            processDocument();
        });

        // Process document
        async function processDocument() {
            if (!selectedFile) return;

            // Show processing state
            previewArea.classList.add('hidden');
            processingArea.classList.remove('hidden');

            const formData = new FormData();
            formData.append('aadhaar', selectedFile);

            try {
                const response = await fetch('{{ route("verification.documents.process") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Show results
                    processingArea.classList.add('hidden');
                    resultsArea.classList.remove('hidden');

                    // Display extracted data
                    if (result.extracted_data) {
                        const data = result.extracted_data;
                        extractedData.innerHTML = `
                            <div class="grid grid-cols-2 gap-2 text-left">
                                <div><strong>Name:</strong> ${data.name || 'N/A'}</div>
                                <div><strong>DOB:</strong> ${data.dob || 'N/A'}</div>
                                <div><strong>Gender:</strong> ${data.gender || 'N/A'}</div>
                                <div><strong>Aadhaar:</strong> ${data.aadhaar_number || 'N/A'}</div>
                            </div>
                        `;
                    }

                    // Set up continue button
                    document.getElementById('continue-btn').onclick = () => {
                        window.location.href = result.next_step;
                    };

                } else {
                    // Show error
                    alert(result.message);
                    processingArea.classList.add('hidden');
                    previewArea.classList.remove('hidden');
                }

            } catch (error) {
                console.error('Upload error:', error);
                alert('Network error. Please check your connection and try again.');
                processingArea.classList.add('hidden');
                previewArea.classList.remove('hidden');
            }
        }
    </script>
</body>
</html> 