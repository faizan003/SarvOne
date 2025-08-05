<?php

namespace App\Http\Controllers;

use App\Models\AccessFlag;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccessFlagController extends Controller
{
    /**
     * Flag an access log entry as unauthorized
     */
    public function flagAccess(Request $request, $accessLogId)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'flag_type' => 'required|in:unauthorized_access,suspicious_activity,data_misuse,other',
                'flag_reason' => 'required|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get the authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Find the access log
            $accessLog = AccessLog::find($accessLogId);
            if (!$accessLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access log not found'
                ], 404);
            }

            // Check if user owns this access log
            if ($accessLog->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only flag access to your own data'
                ], 403);
            }

            // Check if already flagged
            $existingFlag = AccessFlag::where('access_log_id', $accessLogId)
                ->where('user_id', $user->id)
                ->first();

            if ($existingFlag) {
                return response()->json([
                    'success' => false,
                    'message' => 'This access has already been flagged'
                ], 400);
            }

            // Create the flag
            $flag = AccessFlag::create([
                'access_log_id' => $accessLogId,
                'user_id' => $user->id,
                'organization_id' => $accessLog->organization_id,
                'flag_type' => $request->flag_type,
                'flag_reason' => $request->flag_reason,
                'status' => 'pending'
            ]);

            // Log the flag creation
            \Log::info('Access flagged by user', [
                'user_id' => $user->id,
                'access_log_id' => $accessLogId,
                'organization_id' => $accessLog->organization_id,
                'flag_type' => $request->flag_type,
                'flag_id' => $flag->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Access has been flagged for review',
                'flag' => $flag
            ]);

        } catch (\Exception $e) {
            \Log::error('Error flagging access', [
                'error' => $e->getMessage(),
                'access_log_id' => $accessLogId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to flag access: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get flags for government review
     */
    public function getFlagsForReview(Request $request)
    {
        try {
            // Check if user is government
            $user = Auth::user();
            if (!$user || $user->role !== 'government') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $query = AccessFlag::with(['user', 'organization', 'accessLog'])
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by organization
            if ($request->has('organization_id')) {
                $query->where('organization_id', $request->organization_id);
            }

            // Filter by flag type
            if ($request->has('flag_type')) {
                $query->where('flag_type', $request->flag_type);
            }

            $flags = $query->paginate(20);

            return response()->json([
                'success' => true,
                'flags' => $flags
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting flags for review', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get flags: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Review and update flag status (government only)
     */
    public function reviewFlag(Request $request, $flagId)
    {
        try {
            // Check if user is government
            $user = Auth::user();
            if (!$user || $user->role !== 'government') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:reviewed,resolved,dismissed',
                'government_notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find the flag
            $flag = AccessFlag::find($flagId);
            if (!$flag) {
                return response()->json([
                    'success' => false,
                    'message' => 'Flag not found'
                ], 404);
            }

            // Update the flag
            $flag->update([
                'status' => $request->status,
                'government_notes' => $request->government_notes,
                'reviewed_by' => $user->id,
                'reviewed_at' => now()
            ]);

            // Log the review
            \Log::info('Access flag reviewed by government', [
                'flag_id' => $flagId,
                'reviewed_by' => $user->id,
                'status' => $request->status,
                'organization_id' => $flag->organization_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flag has been reviewed and updated',
                'flag' => $flag->load(['user', 'organization', 'accessLog'])
            ]);

        } catch (\Exception $e) {
            \Log::error('Error reviewing flag', [
                'error' => $e->getMessage(),
                'flag_id' => $flagId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to review flag: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get flags for a specific organization (for government dashboard)
     */
    public function getOrganizationFlags($organizationId)
    {
        try {
            // Check if user is government
            $user = Auth::user();
            if (!$user || $user->role !== 'government') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $flags = AccessFlag::with(['user', 'accessLog'])
                ->where('organization_id', $organizationId)
                ->orderBy('created_at', 'desc')
                ->get();

            $stats = [
                'total_flags' => $flags->count(),
                'pending_flags' => $flags->where('status', 'pending')->count(),
                'resolved_flags' => $flags->where('status', 'resolved')->count(),
                'dismissed_flags' => $flags->where('status', 'dismissed')->count(),
            ];

            return response()->json([
                'success' => true,
                'flags' => $flags,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting organization flags', [
                'error' => $e->getMessage(),
                'organization_id' => $organizationId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get organization flags: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's own flags
     */
    public function getUserFlags(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $flags = AccessFlag::with(['organization', 'accessLog'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'flags' => $flags
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting user flags', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user flags: ' . $e->getMessage()
            ], 500);
        }
    }
} 