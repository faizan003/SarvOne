@extends('layouts.app')

@section('title', 'Government Dashboard - SarvOne')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Government Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage government schemes and monitor their performance</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-list text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Schemes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_schemes'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Active Schemes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_schemes'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-graduation-cap text-purple-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Education Schemes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['education_schemes'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="h-12 w-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-seedling text-orange-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-600">Agriculture Schemes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['agriculture_schemes'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm mb-8 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('government.create-scheme') }}" 
               class="flex items-center p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition duration-200">
                <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <div>
                    <p class="font-medium">Add New Scheme</p>
                    <p class="text-sm opacity-90">Create government schemes</p>
                </div>
            </a>
            
            <a href="{{ route('government.schemes') }}" 
               class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition duration-200">
                <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-list text-xl"></i>
                </div>
                <div>
                    <p class="font-medium">Manage Schemes</p>
                    <p class="text-sm opacity-90">View and edit schemes</p>
                </div>
            </a>
            
            <a href="{{ route('government.opportunity-hub') }}" 
               class="flex items-center p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition duration-200">
                <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-lightbulb text-xl"></i>
                </div>
                <div>
                    <p class="font-medium">View Opportunity Hub</p>
                    <p class="text-sm opacity-90">See user experience</p>
                </div>
            </a>
            
            <a href="{{ route('government.flagged-access') }}" 
               class="flex items-center p-4 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition duration-200">
                <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-flag text-xl"></i>
                </div>
                <div>
                    <p class="font-medium">Review Flagged Access</p>
                    <p class="text-sm opacity-90">Handle unauthorized access reports</p>
                </div>
            </a>
            
            <a href="{{ route('government.api-documentation') }}" 
               class="flex items-center p-4 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition duration-200">
                <div class="h-10 w-10 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                    <i class="fas fa-code text-xl"></i>
                </div>
                <div>
                    <p class="font-medium">API Documentation</p>
                    <p class="text-sm opacity-90">Integration guide & keys</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Schemes -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Recent Schemes</h3>
            <a href="{{ route('government.schemes') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scheme Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($schemes->take(5) as $scheme)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $scheme->scheme_name }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($scheme->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucfirst($scheme->category) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($scheme->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @elseif($scheme->status === 'inactive')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inactive
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Draft
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $scheme->created_at->format('M d, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                            No schemes found. <a href="{{ route('government.create-scheme') }}" class="text-indigo-600 hover:text-indigo-700">Create your first scheme</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection 