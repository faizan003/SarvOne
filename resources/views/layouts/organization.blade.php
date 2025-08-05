<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SarvOne Organization Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Fallback FontAwesome -->
    <script>
        // Check if FontAwesome loaded, if not load from backup CDN
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.FontAwesome) {
                var link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://use.fontawesome.com/releases/v6.5.1/css/all.css';
                document.head.appendChild(link);
            }
        });
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .status-pending {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .status-approved {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .status-rejected {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }
        
        /* Ensure icons display properly */
        i.fas, i.far, i.fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands", sans-serif;
            font-weight: 900;
            -webkit-font-smoothing: antialiased;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            text-rendering: auto;
            line-height: 1;
        }
        
        /* Icon fallbacks if FontAwesome fails to load */
        .fa-shield-halved:before { content: "🛡️"; }
        .fa-tachometer-alt:before { content: "📊"; }
        .fa-certificate:before { content: "📜"; }
        .fa-shield-check:before { content: "✅"; }
        .fa-code:before { content: "💻"; }
        .fa-chevron-down:before { content: "▼"; }
        .fa-user:before { content: "👤"; }
        .fa-check-circle:before { content: "✅"; }
        .fa-clock:before { content: "⏰"; }
        .fa-times-circle:before { content: "❌"; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <div class="h-8 w-8 gradient-bg rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-halved text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-gray-900">SarvOne</h1>
                        <p class="text-xs text-gray-500">Organization Portal</p>
                    </div>
                </div>

                <!-- Right - Organization Info & Profile Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Organization Name & Status -->
                    <div class="hidden md:flex items-center space-x-3">
                        <div class="text-right">
                            <div class="flex items-center space-x-2">
                                <p class="text-sm font-semibold text-gray-900">{{ auth('organization')->user()->legal_name }}</p>
                                @if(auth('organization')->user()->verification_status === 'approved')
                                <div class="flex items-center justify-center h-5 w-5 bg-green-100 rounded-full">
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                </div>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">{{ ucfirst(str_replace('_', ' ', auth('organization')->user()->organization_type)) }}</p>
                        </div>
                    </div>

                    <!-- Profile Menu -->
                    <div class="relative" id="profile-dropdown">
                        <button id="profile-button" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900 transition duration-200 focus:outline-none p-2 rounded-lg hover:bg-gray-50">
                            <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-gray-600 text-sm"></i>
                            </div>
                            <i id="chevron-icon" class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                        </button>
                    
                    <!-- Dropdown menu -->
                    <div id="dropdown-menu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg py-1 z-50 border border-gray-200">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ auth('organization')->user()->signatory_name }}</p>
                            <p class="text-xs text-gray-500">{{ auth('organization')->user()->signatory_email }}</p>
                        </div>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user-circle mr-3 text-gray-400"></i>Profile Settings
                        </a>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-key mr-3 text-gray-400"></i>API Keys
                        </a>
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-cog mr-3 text-gray-400"></i>Settings
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('organization.logout') }}" class="block">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-3 text-red-500"></i>Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Navigation Menu for Approved Organizations -->
        @if(auth('organization')->user()->verification_status === 'approved')
        <div class="border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex space-x-8">
                <a href="{{ route('organization.dashboard') }}" 
                   class="flex items-center px-3 py-4 text-sm font-medium border-b-2 transition-colors duration-200 {{ request()->routeIs('organization.dashboard') ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-transparent hover:text-gray-900 hover:border-gray-300' }}">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('organization.issue-vc') }}" 
                   class="flex items-center px-3 py-4 text-sm font-medium border-b-2 transition-colors duration-200 {{ request()->routeIs('organization.issue-vc') ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-transparent hover:text-gray-900 hover:border-gray-300' }}">
                    <i class="fas fa-certificate mr-2"></i>
                    Issue VC
                </a>
                
                <a href="{{ route('organization.verify-vc') }}" 
                   class="flex items-center px-3 py-4 text-sm font-medium border-b-2 transition-colors duration-200 {{ request()->routeIs('organization.verify-vc') ? 'text-blue-600 border-blue-600' : 'text-gray-600 border-transparent hover:text-gray-900 hover:border-gray-300' }}">
                    <i class="fas fa-shield-check mr-2"></i>
                    Verify VC
                </a>
                
                <!-- API Documentation Dropdown -->
                <div class="relative group">
                    <button class="flex items-center px-3 py-4 text-sm font-medium text-gray-600 border-b-2 border-transparent hover:text-gray-900 hover:border-gray-300 transition-colors duration-200">
                        <i class="fas fa-code mr-2"></i>
                        API & Docs
                        <i class="fas fa-chevron-down ml-1 text-xs"></i>
                    </button>
                    
                    <div class="absolute left-0 mt-1 w-64 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <div class="py-2">
                            <a href="{{ route('organization.api-documentation') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                                <i class="fas fa-file-code mr-2"></i>
                                API Documentation
                            </a>
                            <a href="{{ route('organization.api-documentation') }}#api-credentials" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                                <i class="fas fa-key mr-2"></i>
                                API Keys
                            </a>
                            <a href="{{ route('organization.api-documentation') }}#integration-guide" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                                <i class="fas fa-cogs mr-2"></i>
                                Integration Guide
                            </a>
                            <div class="border-t border-gray-100 my-1"></div>
                            <a href="{{ route('organization.api-documentation') }}#code-examples" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors duration-200">
                                <i class="fas fa-download mr-2"></i>
                                SDK Downloads
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center space-x-2">
                    <div class="h-6 w-6 gradient-bg rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-halved text-white text-xs"></i>
                    </div>
                    <span class="text-sm text-gray-600">SarvOne © 2025 - Secure Digital Identity Platform</span>
                </div>
                <div class="mt-4 md:mt-0">
                    <p class="text-xs text-gray-500">Powered by Blockchain Technology</p>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
    
    <!-- Dropdown functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileButton = document.getElementById('profile-button');
            const dropdownMenu = document.getElementById('dropdown-menu');
            const chevronIcon = document.getElementById('chevron-icon');

            if (profileButton && dropdownMenu && chevronIcon) {
                // Toggle dropdown on button click
                profileButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isHidden = dropdownMenu.classList.contains('hidden');
                    
                    if (isHidden) {
                        dropdownMenu.classList.remove('hidden');
                        chevronIcon.classList.add('rotate-180');
                    } else {
                        dropdownMenu.classList.add('hidden');
                        chevronIcon.classList.remove('rotate-180');
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!document.getElementById('profile-dropdown').contains(e.target)) {
                        dropdownMenu.classList.add('hidden');
                        chevronIcon.classList.remove('rotate-180');
                    }
                });

                // Close dropdown on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        dropdownMenu.classList.add('hidden');
                        chevronIcon.classList.remove('rotate-180');
                    }
                });
            }
        });
    </script>
</body>
</html> 