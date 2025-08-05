<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class ApiTokenAuth
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
        $token = $request->bearerToken() ?? $request->header('X-API-Key');
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API token required'
            ], 401);
        }

        // Find organization by API key
        $organization = Organization::where('api_key', $token)->first();
        
        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API token'
            ], 401);
        }

        // Check if organization is approved
        if ($organization->verification_status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Organization not approved'
            ], 403);
        }

        // Set the authenticated organization
        Auth::guard('organization')->setUser($organization);
        
        return $next($request);
    }
} 