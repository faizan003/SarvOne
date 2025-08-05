<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\GovernmentScheme;
use Illuminate\Support\Facades\Auth;

class GovernmentController extends Controller
{
    public function __construct()
    {
        // Temporarily disabled for testing
        // $this->middleware('auth:government');
    }

    /**
     * Show government dashboard
     */
    public function dashboard()
    {
        $schemes = GovernmentScheme::orderBy('created_at', 'desc')->get();
        $stats = [
            'total_schemes' => $schemes->count(),
            'active_schemes' => $schemes->where('status', 'active')->count(),
            'education_schemes' => $schemes->where('category', 'education')->count(),
            'agriculture_schemes' => $schemes->where('category', 'agriculture')->count(),
            'employment_schemes' => $schemes->where('category', 'employment')->count(),
            'health_schemes' => $schemes->where('category', 'health')->count(),
        ];

        return view('government.dashboard', compact('schemes', 'stats'));
    }

    /**
     * Show scheme management page
     */
    public function schemes()
    {
        $schemes = GovernmentScheme::orderBy('created_at', 'desc')->get();
        return view('government.schemes', compact('schemes'));
    }

    /**
     * Show create scheme form
     */
    public function createScheme()
    {
        return view('government.create-scheme');
    }

    /**
     * Store new scheme
     */
    public function storeScheme(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scheme_name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:education,agriculture,employment,health,other',
            'max_income' => 'nullable|numeric|min:0',
            'min_age' => 'nullable|integer|min:0|max:120',
            'max_age' => 'nullable|integer|min:0|max:120',
            'required_credentials' => 'nullable|array',
            'required_credentials.*' => 'string',
            'caste_criteria' => 'nullable|array',
            'caste_criteria.*' => 'string',
            'education_criteria' => 'nullable|array',
            'education_criteria.*' => 'string',
            'employment_criteria' => 'nullable|array',
            'employment_criteria.*' => 'string',
            'benefit_amount' => 'nullable|numeric|min:0',
            'benefit_type' => 'required|string|in:scholarship,loan,subsidy,grant,other',
            'application_deadline' => 'nullable|date|after:today',
            'status' => 'required|string|in:active,inactive,draft'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $scheme = GovernmentScheme::create([
                'scheme_name' => $request->scheme_name,
                'description' => $request->description,
                'category' => $request->category,
                'max_income' => $request->max_income,
                'min_age' => $request->min_age,
                'max_age' => $request->max_age,
                'required_credentials' => $request->required_credentials,
                'caste_criteria' => $request->caste_criteria,
                'education_criteria' => $request->education_criteria,
                'employment_criteria' => $request->employment_criteria,
                'benefit_amount' => $request->benefit_amount,
                'benefit_type' => $request->benefit_type,
                'application_deadline' => $request->application_deadline,
                'status' => $request->status,
                'created_by' => 'Government Official'
            ]);

            // If scheme is active, notify eligible users
            if ($scheme->status === 'active') {
                try {
                    $notificationService = app(\App\Services\SchemeNotificationService::class);
                    $result = $notificationService->notifyEligibleUsersForNewScheme($scheme);
                    
                    \Log::info('New scheme notification triggered', [
                        'scheme_id' => $scheme->id,
                        'scheme_name' => $scheme->scheme_name,
                        'eligible_users' => $result['eligible_users'],
                        'notified_users' => $result['notified_users']
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send scheme notifications', [
                        'scheme_id' => $scheme->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return redirect()->route('government.schemes')->with('success', 'Scheme created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create scheme: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show edit scheme form
     */
    public function editScheme($id)
    {
        $scheme = GovernmentScheme::findOrFail($id);
        return view('government.edit-scheme', compact('scheme'));
    }

    /**
     * Update scheme
     */
    public function updateScheme(Request $request, $id)
    {
        $scheme = GovernmentScheme::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'scheme_name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:education,agriculture,employment,health,other',
            'max_income' => 'nullable|numeric|min:0',
            'min_age' => 'nullable|integer|min:0|max:120',
            'max_age' => 'nullable|integer|min:0|max:120',
            'required_credentials' => 'nullable|array',
            'required_credentials.*' => 'string',
            'caste_criteria' => 'nullable|array',
            'caste_criteria.*' => 'string',
            'education_criteria' => 'nullable|array',
            'education_criteria.*' => 'string',
            'employment_criteria' => 'nullable|array',
            'employment_criteria.*' => 'string',
            'benefit_amount' => 'nullable|numeric|min:0',
            'benefit_type' => 'required|string|in:scholarship,loan,subsidy,grant,other',
            'application_deadline' => 'nullable|date',
            'status' => 'required|string|in:active,inactive,draft'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $scheme->update([
                'scheme_name' => $request->scheme_name,
                'description' => $request->description,
                'category' => $request->category,
                'max_income' => $request->max_income,
                'min_age' => $request->min_age,
                'max_age' => $request->max_age,
                'required_credentials' => $request->required_credentials,
                'caste_criteria' => $request->caste_criteria,
                'education_criteria' => $request->education_criteria,
                'employment_criteria' => $request->employment_criteria,
                'benefit_amount' => $request->benefit_amount,
                'benefit_type' => $request->benefit_type,
                'application_deadline' => $request->application_deadline,
                'status' => $request->status
            ]);

            return redirect()->route('government.schemes')->with('success', 'Scheme updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update scheme: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete scheme
     */
    public function deleteScheme($id)
    {
        try {
            $scheme = GovernmentScheme::findOrFail($id);
            $scheme->delete();
            return redirect()->route('government.schemes')->with('success', 'Scheme deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete scheme: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle scheme status
     */
    public function toggleStatus($id)
    {
        try {
            $scheme = GovernmentScheme::findOrFail($id);
            $oldStatus = $scheme->status;
            $scheme->status = $scheme->status === 'active' ? 'inactive' : 'active';
            $scheme->save();
            
            // If scheme was activated (changed from inactive to active), notify eligible users
            if ($oldStatus !== 'active' && $scheme->status === 'active') {
                try {
                    $notificationService = app(\App\Services\SchemeNotificationService::class);
                    $result = $notificationService->notifyEligibleUsersForNewScheme($scheme);
                    
                    \Log::info('Scheme activation notification triggered', [
                        'scheme_id' => $scheme->id,
                        'scheme_name' => $scheme->scheme_name,
                        'eligible_users' => $result['eligible_users'],
                        'notified_users' => $result['notified_users']
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to send scheme activation notifications', [
                        'scheme_id' => $scheme->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'status' => $scheme->status,
                'message' => 'Scheme status updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update scheme status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show opportunity hub for government view
     */
    public function opportunityHub()
    {
        return view('opportunity-hub');
    }

    /**
     * Get scheme statistics
     */
    public function getStats()
    {
        $schemes = GovernmentScheme::all();
        
        $stats = [
            'total_schemes' => $schemes->count(),
            'active_schemes' => $schemes->where('status', 'active')->count(),
            'education_schemes' => $schemes->where('category', 'education')->count(),
            'agriculture_schemes' => $schemes->where('category', 'agriculture')->count(),
            'employment_schemes' => $schemes->where('category', 'employment')->count(),
            'health_schemes' => $schemes->where('category', 'health')->count(),
            'total_benefit_amount' => $schemes->where('status', 'active')->sum('benefit_amount'),
        ];

        return response()->json($stats);
    }

    /**
     * Show API documentation page
     */
    public function apiDocumentation()
    {
        // Get API keys from environment
        $apiKeys = explode(',', env('GOVERNMENT_API_KEYS', ''));
        $apiKeys = array_map('trim', $apiKeys);
        $apiKeys = array_filter($apiKeys);

        // Get base URL
        $baseUrl = url('/api/government-schemes');

        return view('government.api-documentation', compact('apiKeys', 'baseUrl'));
    }
} 