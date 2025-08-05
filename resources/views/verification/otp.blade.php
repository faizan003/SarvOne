<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>OTP Verification - SarvOne</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .input-focus:focus {
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            border-color: #6366f1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        
        .progress-bar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        }

        .otp-input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <div class="h-10 w-10 gradient-bg rounded-xl flex items-center justify-center mr-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">SarvOne</span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">Step 3 of 3</div>
                    <div class="w-20 h-2 bg-gray-200 rounded-full">
                        <div class="progress-bar w-full h-2 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-20 w-20 gradient-bg rounded-full flex items-center justify-center mb-6">
                    <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Verify Your Mobile</h2>
                <p class="text-gray-600 text-base">Enter the 6-digit OTP sent to your mobile number</p>
                <p class="text-sm text-gray-500 mt-2">{{ session('masked_phone') ?? '+91 ****' }}</p>
            </div>

            <!-- OTP Status Messages -->
            <div id="otp-messages"></div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl card-shadow p-8">
                <form id="otpForm" class="space-y-6">
                    @csrf
                    
                    <!-- OTP Input -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-4 text-center">
                            Enter 6-Digit OTP
                        </label>
                        <div class="flex justify-center space-x-3 mb-4">
                            <input type="text" maxlength="1" class="otp-input border border-gray-300 rounded-xl focus:outline-none input-focus transition-all duration-200" id="otp1" oninput="moveToNext(this, 'otp2')" onkeydown="moveToPrev(this, null, event)">
                            <input type="text" maxlength="1" class="otp-input border border-gray-300 rounded-xl focus:outline-none input-focus transition-all duration-200" id="otp2" oninput="moveToNext(this, 'otp3')" onkeydown="moveToPrev(this, 'otp1', event)">
                            <input type="text" maxlength="1" class="otp-input border border-gray-300 rounded-xl focus:outline-none input-focus transition-all duration-200" id="otp3" oninput="moveToNext(this, 'otp4')" onkeydown="moveToPrev(this, 'otp2', event)">
                            <input type="text" maxlength="1" class="otp-input border border-gray-300 rounded-xl focus:outline-none input-focus transition-all duration-200" id="otp4" oninput="moveToNext(this, 'otp5')" onkeydown="moveToPrev(this, 'otp3', event)">
                            <input type="text" maxlength="1" class="otp-input border border-gray-300 rounded-xl focus:outline-none input-focus transition-all duration-200" id="otp5" oninput="moveToNext(this, 'otp6')" onkeydown="moveToPrev(this, 'otp4', event)">
                            <input type="text" maxlength="1" class="otp-input border border-gray-300 rounded-xl focus:outline-none input-focus transition-all duration-200" id="otp6" oninput="moveToNext(this, null)" onkeydown="moveToPrev(this, 'otp5', event)">
                        </div>
                        <input type="hidden" id="otp_code" name="otp_code">
                    </div>

                    <!-- Timer -->
                    <div class="text-center">
                        <div id="timer" class="text-sm text-gray-500 mb-4">
                            Resend OTP in <span id="countdown">60</span> seconds
                        </div>
                        <button type="button" id="resendBtn" class="text-sm text-blue-600 hover:text-blue-500 font-medium" style="display: none;" onclick="resendOTP()">
                            Resend OTP
                        </button>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button 
                            type="submit" 
                            id="verifyBtn"
                            class="w-full btn-primary text-white font-semibold py-3 px-6 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            disabled
                        >
                            <span id="verifyBtnText">Verify OTP</span>
                            <svg id="verifyBtnSpinner" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Info -->
                    <div class="text-center pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            <svg class="inline h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Didn't receive the OTP? Check your spam folder or try resending.
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let countdown = 60;
        let countdownTimer;

        // Start countdown
        function startCountdown() {
            countdownTimer = setInterval(() => {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
                
                if (countdown <= 0) {
                    clearInterval(countdownTimer);
                    document.getElementById('timer').style.display = 'none';
                    document.getElementById('resendBtn').style.display = 'inline-block';
                }
            }, 1000);
        }

        // Move to next input
        function moveToNext(current, nextId) {
            if (current.value.length === 1 && nextId) {
                document.getElementById(nextId).focus();
            }
            updateOTPCode();
            checkOTPComplete();
        }

        // Move to previous input on backspace
        function moveToPrev(current, prevId, event) {
            if (event.key === 'Backspace' && current.value === '' && prevId) {
                document.getElementById(prevId).focus();
            }
        }

        // Update hidden OTP code field
        function updateOTPCode() {
            let otp = '';
            for (let i = 1; i <= 6; i++) {
                otp += document.getElementById('otp' + i).value;
            }
            document.getElementById('otp_code').value = otp;
        }

        // Check if OTP is complete and enable/disable verify button
        function checkOTPComplete() {
            const otp = document.getElementById('otp_code').value;
            const verifyBtn = document.getElementById('verifyBtn');
            
            if (otp.length === 6) {
                verifyBtn.disabled = false;
                verifyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                verifyBtn.disabled = true;
                verifyBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Resend OTP
        function resendOTP() {
            fetch('{{ route("verification.otp.resend") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('OTP resent successfully!', 'success');
                    countdown = 60;
                    document.getElementById('timer').style.display = 'block';
                    document.getElementById('resendBtn').style.display = 'none';
                    startCountdown();
                } else {
                    showMessage(data.message || 'Failed to resend OTP', 'error');
                }
            })
            .catch(error => {
                showMessage('Network error. Please try again.', 'error');
            });
        }

        // Show message
        function showMessage(message, type) {
            const messageDiv = document.getElementById('otp-messages');
            const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            
            messageDiv.innerHTML = `
                <div class="${alertClass} px-4 py-3 rounded-lg mb-4">
                    ${message}
                </div>
            `;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageDiv.innerHTML = '';
            }, 5000);
        }

        // Form submission
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const verifyBtn = document.getElementById('verifyBtn');
            const verifyBtnText = document.getElementById('verifyBtnText');
            const verifyBtnSpinner = document.getElementById('verifyBtnSpinner');
            const otpCode = document.getElementById('otp_code').value;
            
            if (otpCode.length !== 6) {
                showMessage('Please enter a complete 6-digit OTP', 'error');
                return;
            }
            
            // Show loading state
            verifyBtn.disabled = true;
            verifyBtnText.textContent = 'Verifying...';
            verifyBtnSpinner.classList.remove('hidden');
            
            fetch('{{ route("verification.otp.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    otp_code: otpCode
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('OTP verified successfully! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = data.next_step || '{{ route("dashboard") }}';
                    }, 1500);
                } else {
                    showMessage(data.message || 'Invalid OTP. Please try again.', 'error');
                    
                    // Reset loading state
                    verifyBtn.disabled = false;
                    verifyBtnText.textContent = 'Verify OTP';
                    verifyBtnSpinner.classList.add('hidden');
                    
                    // Clear OTP inputs
                    for (let i = 1; i <= 6; i++) {
                        document.getElementById('otp' + i).value = '';
                    }
                    document.getElementById('otp1').focus();
                    updateOTPCode();
                    checkOTPComplete();
                }
            })
            .catch(error => {
                showMessage('Network error. Please try again.', 'error');
                
                // Reset loading state
                verifyBtn.disabled = false;
                verifyBtnText.textContent = 'Verify OTP';
                verifyBtnSpinner.classList.add('hidden');
            });
        });

        // Only allow numbers in OTP inputs
        document.querySelectorAll('.otp-input').forEach(input => {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                const numbers = paste.replace(/[^0-9]/g, '').slice(0, 6);
                
                for (let i = 0; i < numbers.length && i < 6; i++) {
                    document.getElementById('otp' + (i + 1)).value = numbers[i];
                }
                
                updateOTPCode();
                checkOTPComplete();
                
                // Focus on next empty input or last input
                const nextEmpty = numbers.length < 6 ? numbers.length + 1 : 6;
                document.getElementById('otp' + nextEmpty).focus();
            });
        });

        // Start countdown on page load
        startCountdown();
        
        // Focus first input on page load
        document.getElementById('otp1').focus();
    </script>
</body>
</html>