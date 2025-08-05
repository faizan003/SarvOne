@extends('layouts.app')

@section('title', 'Create Government Scheme - SarvOne')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Breadcrumb -->
    <div class="mb-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('government.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600">
                        <i class="fas fa-home mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <a href="{{ route('government.schemes') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600">Schemes</a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="text-sm font-medium text-gray-500">Create Scheme</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Header -->
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Create Government Scheme</h1>
                    <p class="text-blue-100 text-lg">Add new government schemes with eligibility criteria</p>
                </div>
                <div class="hidden md:block">
                    <div class="h-20 w-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                        <i class="fas fa-landmark text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Scheme Form -->
    <div class="bg-white rounded-2xl shadow-xl p-8">
        <form action="{{ route('government.store-scheme') }}" method="POST">
            @csrf
            
            <!-- Basic Information -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Basic Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Scheme Name *</label>
                        <input type="text" name="scheme_name" value="{{ old('scheme_name') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Enter scheme name" required>
                        @error('scheme_name')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                        <select name="category" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select category</option>
                            <option value="education" {{ old('category') == 'education' ? 'selected' : '' }}>Education</option>
                            <option value="agriculture" {{ old('category') == 'agriculture' ? 'selected' : '' }}>Agriculture</option>
                            <option value="employment" {{ old('category') == 'employment' ? 'selected' : '' }}>Employment</option>
                            <option value="health" {{ old('category') == 'health' ? 'selected' : '' }}>Health</option>
                            <option value="other" {{ old('category') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('category')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                    <textarea name="description" rows="4" 
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Describe the scheme details, objectives, and benefits" required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Eligibility Criteria -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Eligibility Criteria</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Family Income (₹)</label>
                        <input type="number" name="max_income" value="{{ old('max_income') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 300000">
                        @error('max_income')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Age</label>
                        <input type="number" name="min_age" value="{{ old('min_age') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 18" min="0" max="120">
                        @error('min_age')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Maximum Age</label>
                        <input type="number" name="max_age" value="{{ old('max_age') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 65" min="0" max="120">
                        @error('max_age')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Caste Criteria -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Eligible Castes</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @php
                            $castes = ['General', 'OBC', 'SC', 'ST', 'EWS'];
                            $oldCastes = old('caste_criteria', []);
                        @endphp
                        @foreach($castes as $caste)
                            <label class="flex items-center">
                                <input type="checkbox" name="caste_criteria[]" value="{{ $caste }}" 
                                       {{ in_array($caste, $oldCastes) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $caste }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('caste_criteria')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Required Credentials -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Required Verifiable Credentials</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php
                            $credentials = [
                                'account_opening' => 'Bank Account',
                                'income_certificate' => 'Income Certificate',
                                'caste_certificate' => 'Caste Certificate',
                                'education_certificate' => 'Education Certificate',
                                'employment_certificate' => 'Employment Certificate',
                                'aadhaar_card' => 'Aadhaar Card',
                                'pan_card' => 'PAN Card',
                                'driving_license' => 'Driving License'
                            ];
                            $oldCredentials = old('required_credentials', []);
                        @endphp
                        @foreach($credentials as $key => $label)
                            <label class="flex items-center">
                                <input type="checkbox" name="required_credentials[]" value="{{ $key }}" 
                                       {{ in_array($key, $oldCredentials) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('required_credentials')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Benefit Information -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Benefit Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Benefit Type *</label>
                        <select name="benefit_type" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">Select benefit type</option>
                            <option value="scholarship" {{ old('benefit_type') == 'scholarship' ? 'selected' : '' }}>Scholarship</option>
                            <option value="loan" {{ old('benefit_type') == 'loan' ? 'selected' : '' }}>Loan</option>
                            <option value="subsidy" {{ old('benefit_type') == 'subsidy' ? 'selected' : '' }}>Subsidy</option>
                            <option value="grant" {{ old('benefit_type') == 'grant' ? 'selected' : '' }}>Grant</option>
                            <option value="other" {{ old('benefit_type') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('benefit_type')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Benefit Amount (₹)</label>
                        <input type="number" name="benefit_amount" value="{{ old('benefit_amount') }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 50000">
                        @error('benefit_amount')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Application Deadline</label>
                    <input type="date" name="application_deadline" value="{{ old('application_deadline') }}" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('application_deadline')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Status -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Scheme Status</h2>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                    <select name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('government.schemes') }}" 
                   class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-semibold">
                    Create Scheme
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 