<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class OrganizationAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if this is an API request
        $isApiRequest = $request->expectsJson() || 
                       $request->is('api/*') || 
                       $request->is('*/api/*') ||
                       $request->header('Accept') === 'application/json' ||
                       $request->header('Content-Type') === 'application/json';

        // First, try session authentication
        if (Auth::guard('organization')->check()) {
            return $next($request);
        }

        // If session auth fails, try API token authentication
        $token = $request->bearerToken() ?? $request->header('X-API-Key');
        
        if ($token) {
            // Find organization by API key
            $organization = Organization::where('api_key', $token)->first();
            
            if ($organization && $organization->verification_status === 'approved') {
                // Set the authenticated organization
                Auth::guard('organization')->setUser($organization);
                return $next($request);
            }
        }

        // If both authentication methods fail, return error for API requests
        if ($isApiRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please provide valid session or API token.',
                'debug' => [
                    'has_token' => !empty($token),
                    'token_provided' => $token ? 'yes' : 'no',
                    'is_api_request' => $isApiRequest,
                    'url' => $request->url()
                ]
            ], 401);
        }

        // For web requests, redirect to login
        return redirect()->route('organization.login');
    }
} 