<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SarvOne - Your Digital Identity, Your Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.8s ease-out;
        }
        
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        .animate-pulse-slow {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .animate-slide-in-left {
            animation: slideInLeft 0.8s ease-out;
        }
        
        .animate-slide-in-right {
            animation: slideInRight 0.8s ease-out;
        }
        
        .gradient-bg {
            background: linear-gradient(-45deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .navbar-scrolled {
            background: rgba(102, 126, 234, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .hover-scale {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .card-glow {
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }
        
        .card-glow:hover {
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.2);
        }
        
        .btn-primary {
            @apply bg-white text-purple-600 px-6 py-3 rounded-xl font-semibold text-base hover:bg-blue-50 transition-all duration-300 shadow-lg hover:shadow-xl;
        }
        
        .btn-secondary {
            @apply border-2 border-white text-white px-6 py-3 rounded-xl font-semibold text-base hover:bg-white hover:text-purple-600 transition-all duration-300;
        }
        
        .btn-large {
            @apply px-8 py-4 text-lg;
        }
        
        .data-ownership-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .security-badge {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav id="navbar" class="fixed top-0 w-full z-50 glass-effect transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <i class="fas fa-shield-alt text-white text-2xl mr-2"></i>
                    <span class="text-white font-bold text-xl">SarvOne</span>
                </div>
                <div class="hidden md:flex space-x-8">
                    <a href="#features" class="text-white hover:text-blue-200 transition-colors duration-300">Features</a>
                    <a href="#data-ownership" class="text-white hover:text-blue-200 transition-colors duration-300">Your Data</a>
                    <a href="#how-it-works" class="text-white hover:text-blue-200 transition-colors duration-300">How it Works</a>
                    <a href="#security" class="text-white hover:text-blue-200 transition-colors duration-300">Security</a>
                </div>
                <div class="flex space-x-4">
                    <a href="{{ route('login') }}" class="text-white hover:text-blue-200 transition-colors duration-300 font-medium">Login</a>
                    <a href="{{ route('register') }}" class="btn-primary">Get Started</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg min-h-screen flex items-center relative overflow-hidden pt-20">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-10 w-20 h-20 bg-white opacity-10 rounded-full animate-float"></div>
            <div class="absolute top-40 right-20 w-16 h-16 bg-white opacity-10 rounded-full animate-float" style="animation-delay: 1s;"></div>
            <div class="absolute bottom-20 left-1/4 w-12 h-12 bg-white opacity-10 rounded-full animate-float" style="animation-delay: 2s;"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="animate-fade-in-up">
                    <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
                        Your Digital Identity,
                        <span class="gradient-text bg-white">Your Control</span>
                    </h1>
                    <p class="text-xl text-blue-100 mb-8 leading-relaxed">
                        One Decentralized ID (DID) for all your verifications. You own your data, control access, and maintain complete privacy. 
                        No more scattered credentials - everything in one secure place.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <a href="{{ route('register') }}" 
                           class="btn-primary btn-large animate-pulse-slow">
                            <i class="fas fa-rocket mr-2"></i>
                            Start Your Journey
                        </a>
                        <a href="#data-ownership" 
                           class="btn-secondary btn-large">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Learn About Your Data
                        </a>
                    </div>
                    <div class="flex items-center space-x-6 text-white">
                        <div class="flex items-center">
                            <i class="fas fa-users text-2xl mr-2"></i>
                            <span class="font-semibold">10,000+ Users</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-building text-2xl mr-2"></i>
                            <span class="font-semibold">500+ Organizations</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-2xl mr-2"></i>
                            <span class="font-semibold">99.9% Uptime</span>
                        </div>
                    </div>
                </div>
                <div class="animate-slide-in-right">
                    <div class="relative">
                        <div class="bg-white rounded-2xl p-8 shadow-2xl card-glow">
                            <div class="flex items-center mb-6">
                                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center mr-4">
                                    <i class="fas fa-user text-white text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Your Digital Identity</h3>
                                    <p class="text-gray-500 text-sm">DID: did:sarvone:user123</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-3"></i>
                                        <span class="text-sm text-gray-700">Employment Verified</span>
                                    </div>
                                    <span class="text-xs text-green-600 font-medium">Active</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-blue-500 mr-3"></i>
                                        <span class="text-sm text-gray-700">Education Verified</span>
                                    </div>
                                    <span class="text-xs text-blue-600 font-medium">Active</span>
                                </div>
                                <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-purple-500 mr-3"></i>
                                        <span class="text-sm text-gray-700">Identity Verified</span>
                                    </div>
                                    <span class="text-xs text-purple-600 font-medium">Active</span>
                                </div>
                                <div class="text-center mt-4 p-3 bg-yellow-50 rounded-lg">
                                    <p class="text-xs text-gray-600 font-medium">One DID • All Verifications • Your Control</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Data Ownership Section -->
    <section id="data-ownership" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    You Own Your Data, Always
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Complete control over your digital identity with transparent access management and privacy-first design
                </p>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-12 items-center mb-16">
                <div class="animate-slide-in-left">
                    <div class="data-ownership-card rounded-2xl p-8 shadow-2xl">
                        <h3 class="text-2xl font-bold mb-6">Your Data, Your Rules</h3>
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4 mt-1">
                                    <i class="fas fa-lock text-white text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Complete Ownership</h4>
                                    <p class="text-blue-100">Your data never leaves your control. You decide what to share, when to share, and with whom.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4 mt-1">
                                    <i class="fas fa-eye text-white text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Transparent Access</h4>
                                    <p class="text-blue-100">See exactly who accessed your data, when, and why. Full audit trail for complete transparency.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4 mt-1">
                                    <i class="fas fa-ban text-white text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Revoke Access</h4>
                                    <p class="text-blue-100">Instantly revoke access to your data at any time. Your consent is always required.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="animate-slide-in-right">
                    <div class="bg-gray-50 rounded-2xl p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-6">What You Control</h3>
                        <div class="space-y-4">
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-id-card text-blue-500 text-xl mr-4"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Personal Information</h4>
                                    <p class="text-gray-600 text-sm">Name, contact details, identification documents</p>
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-graduation-cap text-green-500 text-xl mr-4"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Educational Records</h4>
                                    <p class="text-gray-600 text-sm">Degrees, certificates, academic achievements</p>
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-briefcase text-purple-500 text-xl mr-4"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Employment History</h4>
                                    <p class="text-gray-600 text-sm">Work experience, skills, professional certifications</p>
                                </div>
                            </div>
                            <div class="flex items-center p-4 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-chart-line text-orange-500 text-xl mr-4"></i>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Financial Records</h4>
                                    <p class="text-gray-600 text-sm">Income verification, credit history, financial status</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Section -->
            <div class="grid md:grid-cols-4 gap-6 mb-16">
                <div class="stats-card rounded-2xl p-6 text-center">
                    <i class="fas fa-users text-3xl mb-4"></i>
                    <h3 class="text-2xl font-bold mb-2">10,000+</h3>
                    <p class="text-sm opacity-90">Active Users</p>
                </div>
                <div class="stats-card rounded-2xl p-6 text-center">
                    <i class="fas fa-shield-check text-3xl mb-4"></i>
                    <h3 class="text-2xl font-bold mb-2">50,000+</h3>
                    <p class="text-sm opacity-90">Credentials Issued</p>
                </div>
                <div class="stats-card rounded-2xl p-6 text-center">
                    <i class="fas fa-building text-3xl mb-4"></i>
                    <h3 class="text-2xl font-bold mb-2">500+</h3>
                    <p class="text-sm opacity-90">Partner Organizations</p>
                </div>
                <div class="stats-card rounded-2xl p-6 text-center">
                    <i class="fas fa-clock text-3xl mb-4"></i>
                    <h3 class="text-2xl font-bold mb-2">99.9%</h3>
                    <p class="text-sm opacity-90">Uptime</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Comprehensive Digital Identity Platform
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Everything you need for secure, privacy-first digital identity management
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 hover-scale card-glow animate-fade-in-up">
                    <div class="feature-icon">
                        <i class="fas fa-user-shield text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">For Individuals</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Take complete control of your digital identity with one DID that works everywhere. Store, manage, and share your credentials securely.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            One DID for all verifications
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Complete data ownership and control
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Real-time access history tracking
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Instant credential sharing
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Privacy-first design
                        </li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-2xl p-8 hover-scale card-glow animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="feature-icon">
                        <i class="fas fa-building text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">For Organizations</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Issue verifiable credentials efficiently, onboard users seamlessly, and maintain compliance with trust and transparency.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Instant credential issuance
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Automated verification workflows
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Compliance and audit ready
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Integration APIs
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Fraud prevention
                        </li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-2xl p-8 hover-scale card-glow animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="feature-icon">
                        <i class="fas fa-search text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">For Verifiers</h3>
                    <p class="text-gray-600 leading-relaxed mb-6">
                        Verify credentials instantly, reduce fraud, and ensure compliance with privacy-first verification checks.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Real-time verification
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Advanced fraud detection
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Privacy-preserving checks
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Batch verification
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            Detailed audit reports
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    How SarvOne Works
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Simple, secure, and privacy-first digital identity verification with complete user control
                </p>
            </div>
            
            <div class="grid md:grid-cols-4 gap-8">
                <div class="text-center animate-fade-in-up">
                    <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse-slow">
                        <i class="fas fa-user-plus text-white text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">1. Create Your DID</h4>
                    <p class="text-gray-600">Sign up and get your unique Decentralized ID (DID) - your digital identity that you control completely.</p>
                </div>
                
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="w-20 h-20 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse-slow">
                        <i class="fas fa-id-badge text-white text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">2. Receive Credentials</h4>
                    <p class="text-gray-600">Organizations issue verifiable credentials to your DID. You store them securely in your digital wallet.</p>
                </div>
                
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse-slow">
                        <i class="fas fa-share-square text-white text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">3. Share Securely</h4>
                    <p class="text-gray-600">Share your DID with verifiers. They can instantly verify your credentials without accessing your data.</p>
                </div>
                
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.6s;">
                    <div class="w-20 h-20 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse-slow">
                        <i class="fas fa-user-lock text-white text-2xl"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900 mb-3">4. Maintain Control</h4>
                    <p class="text-gray-600">You decide who accesses your data. View access history and revoke permissions anytime.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Security Section -->
    <section id="security" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Enterprise-Grade Security
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Your data is protected with military-grade encryption and privacy-first design
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center animate-fade-in-up">
                    <div class="security-badge w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-lock text-white text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">End-to-End Encryption</h4>
                    <p class="text-gray-600 text-sm">Your data is encrypted at rest and in transit with AES-256 encryption.</p>
                </div>
                
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="security-badge w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-check text-white text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">User Consent</h4>
                    <p class="text-gray-600 text-sm">Nothing is shared without your explicit permission and control.</p>
                </div>
                
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="security-badge w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-fingerprint text-white text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Blockchain Anchoring</h4>
                    <p class="text-gray-600 text-sm">Credentials are anchored on blockchain for immutability and trust.</p>
                </div>
                
                <div class="text-center animate-fade-in-up" style="animation-delay: 0.6s;">
                    <div class="security-badge w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Complete Audit Trail</h4>
                    <p class="text-gray-600 text-sm">See who accessed your data, when, and why with complete transparency.</p>
                </div>
            </div>
            
            <!-- Security Features Grid -->
            <div class="grid md:grid-cols-3 gap-8 mt-16">
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <h4 class="font-semibold text-gray-900 mb-4">Privacy Protection</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Zero-knowledge proofs
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Selective disclosure
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Data minimization
                        </li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <h4 class="font-semibold text-gray-900 mb-4">Compliance Ready</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            GDPR compliant
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            SOC 2 Type II
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            ISO 27001 certified
                        </li>
                    </ul>
                </div>
                
                <div class="bg-white rounded-2xl p-6 shadow-lg">
                    <h4 class="font-semibold text-gray-900 mb-4">Advanced Security</h4>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Multi-factor authentication
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Biometric verification
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            Real-time threat detection
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 gradient-bg">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <div class="animate-fade-in-up">
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-6">
                    Ready to Take Control of Your Digital Identity?
                </h2>
                <p class="text-xl text-blue-100 mb-8">
                    Join thousands of users who trust SarvOne for their digital identity needs. 
                    Your data, your control, your privacy.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="{{ route('register') }}" 
                       class="btn-primary btn-large">
                        <i class="fas fa-rocket mr-2"></i>
                        Start Your Journey
                    </a>
                    <a href="{{ route('login') }}" 
                       class="btn-secondary btn-large">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </a>
                </div>
                <p class="text-blue-200 text-sm mt-6">
                    Free to start • No credit card required • Complete control over your data
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-shield-alt text-blue-400 text-2xl mr-2"></i>
                        <span class="font-bold text-xl">SarvOne</span>
                    </div>
                    <p class="text-gray-400 text-sm mb-4">
                        Your trusted partner for digital identity and verifiable credentials. 
                        Empowering individuals with complete control over their data.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-linkedin text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-github text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#features" class="hover:text-white transition">Features</a></li>
                        <li><a href="#data-ownership" class="hover:text-white transition">Data Ownership</a></li>
                        <li><a href="#security" class="hover:text-white transition">Security</a></li>
                        <li><a href="#" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">API Documentation</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition">Partners</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold mb-4">Support</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white transition">Security</a></li>
                        <li><a href="#" class="hover:text-white transition">Status</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} SarvOne. All rights reserved. Your data, your control.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all animated elements
        document.addEventListener('DOMContentLoaded', () => {
            const animatedElements = document.querySelectorAll('.animate-fade-in-up, .animate-slide-in-left, .animate-slide-in-right');
            animatedElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.8s ease-out, transform 0.8s ease-out';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
