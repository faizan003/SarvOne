<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Data Access Control - SarvOne</title>
    
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
        
        .org-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .org-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .vc-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 500;
            margin: 2px;
        }
        
        .vc-badge.mandatory {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .vc-badge.optional {
            background: #f0f9ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        
        .vc-badge.disabled {
            background: #f3f4f6;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #3b82f6;
        }
        
        input:checked + .slider:before {
            transform: translateX(24px);
        }
    </style>
</head>
<body>
    <div class="mobile-container">
        <!-- Header -->
        <header class="bg-white shadow-sm px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="text-gray-600">
                        <i class="fas fa-arrow-left text-lg"></i>
                    </a>
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">Data Access Control</h1>
                        <p class="text-xs text-gray-500">Manage who can access your data</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            @if(session('success'))
                <div class="mx-4 mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <span class="text-green-800">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Info Card -->
            <div class="mx-4 mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900 mb-1">How it works</h3>
                        <p class="text-xs text-blue-800">
                            Control which organization types can access your verifiable credentials. 
                            Toggle the switch to enable/disable access for each organization type.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Organization Types -->
            <form method="POST" action="{{ route('data-access-control.update') }}" class="px-4 py-4">
                @csrf
                <div class="space-y-4">
                    @foreach($organizationTypes as $orgType => $orgConfig)
                        @php
                            $preference = $userPreferences[$orgType] ?? null;
                            $isActive = $preference ? $preference->is_active : true;
                            $allowedTypes = $preference ? $preference->allowed_data_types : $orgConfig['mandatory'];
                            $mandatoryTypes = $orgConfig['mandatory'];
                            $optionalTypes = $orgConfig['optional'];
                            $verifiableCredentials = $orgConfig['verifiable_credentials'];
                        @endphp
                        
                        <div class="org-card">
                            <!-- Header -->
                            <div class="p-4 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-lg flex items-center justify-center mr-3"
                                             style="background: {{ $orgConfig['color'] === 'blue' ? '#dbeafe' : ($orgConfig['color'] === 'yellow' ? '#fef3c7' : ($orgConfig['color'] === 'green' ? '#dcfce7' : ($orgConfig['color'] === 'purple' ? '#f3e8ff' : '#e0e7ff'))) }};
                                             color: {{ $orgConfig['color'] === 'blue' ? '#2563eb' : ($orgConfig['color'] === 'yellow' ? '#d97706' : ($orgConfig['color'] === 'green' ? '#16a34a' : ($orgConfig['color'] === 'purple' ? '#9333ea' : '#4f46e5'))) }};">
                                            <i class="{{ $orgConfig['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-base font-semibold text-gray-900">{{ $orgConfig['name'] }}</h3>
                                            <p class="text-xs text-gray-500">{{ $orgConfig['description'] }}</p>
                                        </div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="preferences[{{ $orgType }}][is_active]" value="1" 
                                               {{ $isActive ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-4">
                                <input type="hidden" name="preferences[{{ $orgType }}][organization_type]" value="{{ $orgType }}">
                                
                                <!-- Verifiable Credentials Section -->
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                                        <i class="fas fa-shield-alt text-green-600 mr-2"></i>
                                        Can Verify These Credentials
                                    </h4>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($verifiableCredentials as $vcType)
                                            @php
                                                $isMandatory = in_array($vcType, $mandatoryTypes);
                                                $isAllowed = in_array($vcType, $allowedTypes);
                                                $isDisabled = !$isActive;
                                            @endphp
                                            <span class="vc-badge {{ $isMandatory ? 'mandatory' : ($isDisabled ? 'disabled' : 'optional') }}">
                                                <i class="fas {{ $isMandatory ? 'fa-lock' : 'fa-check' }} mr-1"></i>
                                                {{ $availableDataTypes[$vcType] ?? ucfirst(str_replace('_', ' ', $vcType)) }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Mandatory vs Optional -->
                                @if(!empty($mandatoryTypes) || !empty($optionalTypes))
                                    <div class="mb-4">
                                        <div class="grid grid-cols-2 gap-4">
                                            @if(!empty($mandatoryTypes))
                                                <div>
                                                    <h5 class="text-xs font-medium text-red-700 mb-2 flex items-center">
                                                        <i class="fas fa-lock mr-1"></i>
                                                        Always Required
                                                    </h5>
                                                    <div class="space-y-1">
                                                        @foreach($mandatoryTypes as $dataType)
                                                            <div class="text-xs text-red-600 flex items-center">
                                                                <i class="fas fa-check mr-1"></i>
                                                                {{ $availableDataTypes[$dataType] ?? ucfirst(str_replace('_', ' ', $dataType)) }}
                                                            </div>
                                                            <input type="hidden" name="preferences[{{ $orgType }}][allowed_data_types][]" value="{{ $dataType }}">
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(!empty($optionalTypes))
                                                <div>
                                                    <h5 class="text-xs font-medium text-blue-700 mb-2 flex items-center">
                                                        <i class="fas fa-plus-circle mr-1"></i>
                                                        Optional Access
                                                    </h5>
                                                    <div class="space-y-1">
                                                        @foreach($optionalTypes as $dataType)
                                                            <label class="flex items-center text-xs text-blue-600">
                                                                <input type="checkbox" name="preferences[{{ $orgType }}][allowed_data_types][]" 
                                                                       value="{{ $dataType }}" 
                                                                       class="h-3 w-3 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mr-2"
                                                                       {{ in_array($dataType, $allowedTypes) ? 'checked' : '' }}
                                                                       {{ !$isActive ? 'disabled' : '' }}>
                                                                {{ $availableDataTypes[$dataType] ?? ucfirst(str_replace('_', ' ', $dataType)) }}
                                                            </label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Status Summary -->
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h5 class="text-xs font-medium text-gray-900">Current Status</h5>
                                            <p class="text-xs text-gray-600">
                                                {{ $isActive ? 'Access Enabled' : 'Access Disabled' }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-medium {{ $isActive ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $isActive ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" 
                            class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150 ease-in-out font-medium">
                        <i class="fas fa-save mr-2"></i>
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <div class="flex items-center justify-between">
                <button class="nav-item flex-1" onclick="window.location.href='{{ route('dashboard') }}'">
                    <i class="fas fa-home"></i>
                    <span>Home</span>
                </button>
                <button class="nav-item flex-1" onclick="window.location.href='{{ route('dashboard') }}#vcs'">
                    <i class="fas fa-certificate"></i>
                    <span>My VCs</span>
                </button>
                <button class="nav-item flex-1" onclick="window.location.href='{{ route('dashboard') }}#profile'">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </button>
                <button class="nav-item flex-1" onclick="window.location.href='{{ route('access-history') }}'">
                    <i class="fas fa-shield-alt"></i>
                    <span>Access</span>
                </button>
                <button class="nav-item active flex-1">
                    <i class="fas fa-cog"></i>
                    <span>Control</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Handle toggle switches
        document.addEventListener('DOMContentLoaded', function() {
            const toggleSwitches = document.querySelectorAll('.toggle-switch input');
            toggleSwitches.forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const card = this.closest('.org-card');
                    // Only target optional checkboxes, not the main toggle switch
                    const optionalCheckboxes = card.querySelectorAll('input[name*="[allowed_data_types][]"]');
                    const statusText = card.querySelector('.text-xs.text-gray-600');
                    const statusBadge = card.querySelector('.text-xs.font-medium');
                    
                    if (this.checked) {
                        optionalCheckboxes.forEach(checkbox => checkbox.disabled = false);
                        statusText.textContent = 'Access Enabled';
                        statusBadge.textContent = 'Active';
                        statusBadge.className = 'text-xs font-medium text-green-600';
                    } else {
                        optionalCheckboxes.forEach(checkbox => {
                            checkbox.disabled = true;
                            checkbox.checked = false;
                        });
                        statusText.textContent = 'Access Disabled';
                        statusBadge.textContent = 'Inactive';
                        statusBadge.className = 'text-xs font-medium text-red-600';
                    }
                });
            });
        });
    </script>
</body>
</html> 