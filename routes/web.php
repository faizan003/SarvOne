<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ONDCController;
use App\Http\Controllers\GovernmentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Public test endpoints
Route::get('/test-ipfs', [OrganizationController::class, 'publicTestIPFS'])->name('test-ipfs');

// Authentication routes
Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisterController::class, 'show'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/did-qr', [DashboardController::class, 'didQrCode'])->name('dashboard.did-qr');
Route::get('/access-history', [DashboardController::class, 'accessHistory'])->name('access-history');
Route::get('/data-access-control', [DashboardController::class, 'dataAccessControl'])->name('data-access-control');
Route::post('/data-access-control', [DashboardController::class, 'updateDataAccessControl'])->name('data-access-control.update');
Route::get('/opportunity-hub', [DashboardController::class, 'opportunityHub'])->name('opportunity-hub');

// Public API routes (no authentication required)
Route::get('/api/government-schemes', [DashboardController::class, 'getGovernmentSchemes'])->name('api.government-schemes');
Route::get('/api/vc/status/{vcId}', [OrganizationController::class, 'getVCStatus'])->name('api.vc.status');

// API routes for mobile (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/api/my-vcs', [DashboardController::class, 'getVCs'])->name('api.my-vcs');
    Route::get('/api/my-vcs-blockchain', [DashboardController::class, 'getVCsFromBlockchain'])->name('api.my-vcs-blockchain');
    Route::post('/api/verify-vc-blockchain', [DashboardController::class, 'verifyVCOnBlockchain'])->name('api.verify-vc-blockchain');
Route::get('/api/user/access-logs', [DashboardController::class, 'getUserAccessLogs'])->name('api.user.access-logs');
    Route::get('/api/debug-user', function() {
        $user = Auth::user();
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'did' => $user->did,
                'is_verified' => $user->isVerified()
            ] : null
        ]);
    })->name('api.debug-user');
});

// Organization routes
Route::prefix('organization')->group(function () {
    Route::get('/login', [OrganizationController::class, 'showLogin'])->name('organization.login');
    Route::post('/login', [OrganizationController::class, 'login'])->name('organization.login.store');
    Route::post('/logout', [OrganizationController::class, 'logout'])->name('organization.logout');
    
    Route::get('/register', [OrganizationController::class, 'showRegister'])->name('organization.register');
    Route::post('/register', [OrganizationController::class, 'register'])->name('organization.register.store');
    
    Route::middleware('organization.auth')->group(function () {
        Route::get('/dashboard', [OrganizationController::class, 'dashboard'])->name('organization.dashboard');
        Route::get('/issue-vc', [OrganizationController::class, 'showIssueVC'])->name('organization.issue-vc');
        Route::post('/issue-vc', [OrganizationController::class, 'issueVC'])->name('organization.issue-vc.store');
        Route::get('/verify-vc', [OrganizationController::class, 'showVerifyVC'])->name('organization.verify-vc');
        Route::post('/verify-vc', [OrganizationController::class, 'verifyVC'])->name('organization.verify-vc.store');
        
        // API routes for AJAX calls (support both session and API token auth)
        Route::post('/api/lookup-user-by-did', [OrganizationController::class, 'lookupUserByDID'])->name('organization.api.lookup-user-by-did')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        Route::post('/api/issue-credential', [OrganizationController::class, 'issueCredential'])->name('organization.api.issue-credential')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        Route::get('/api/issued-credentials', [OrganizationController::class, 'getIssuedCredentials'])->name('organization.api.issued-credentials')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        Route::post('/api/verify-credential', [OrganizationController::class, 'verifyCredential'])->name('organization.api.verify-credential')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        Route::get('/api/access-logs', [OrganizationController::class, 'getAccessLogs'])->name('organization.api.access-logs')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        Route::get('/api/debug-user', [OrganizationController::class, 'debugUser'])->name('organization.debug-user');
        Route::get('/api/test-ipfs', [OrganizationController::class, 'testIPFS'])->name('organization.test-ipfs');
        Route::get('/api-documentation', [OrganizationController::class, 'apiDocumentation'])->name('organization.api-documentation');
        Route::post('/regenerate-api-key', [OrganizationController::class, 'regenerateApiKey'])->name('organization.regenerate-api-key');
        
        // VC Management routes
        Route::get('/issued-vcs', [OrganizationController::class, 'showIssuedVCs'])->name('organization.issued-vcs');
        Route::post('/revoke-vc/{vcId}', [OrganizationController::class, 'revokeVC'])->name('organization.revoke-vc');
    });
});

// Government routes
Route::prefix('government')->group(function () {
    Route::get('/login', function() {
        return view('government.login');
    })->name('government.login');
    
    Route::middleware('web')->group(function () {
        Route::get('/dashboard', [GovernmentController::class, 'dashboard'])->name('government.dashboard');
        Route::get('/opportunity-hub', [GovernmentController::class, 'opportunityHub'])->name('government.opportunity-hub');
        Route::get('/schemes', [GovernmentController::class, 'schemes'])->name('government.schemes');
        Route::get('/schemes/create', [GovernmentController::class, 'createScheme'])->name('government.create-scheme');
        Route::post('/schemes', [GovernmentController::class, 'storeScheme'])->name('government.store-scheme');
        Route::get('/schemes/{id}/edit', [GovernmentController::class, 'editScheme'])->name('government.edit-scheme');
        Route::put('/schemes/{id}', [GovernmentController::class, 'updateScheme'])->name('government.update-scheme');
        Route::delete('/schemes/{id}', [GovernmentController::class, 'deleteScheme'])->name('government.delete-scheme');
        Route::post('/schemes/{id}/toggle-status', [GovernmentController::class, 'toggleStatus'])->name('government.toggle-status');
        Route::get('/api/stats', [GovernmentController::class, 'getStats'])->name('government.api.stats');
        Route::get('/api-documentation', [GovernmentController::class, 'apiDocumentation'])->name('government.api-documentation');
        Route::get('/flagged-access', function() {
            return view('government.flagged-access');
        })->name('government.flagged-access');
    });
});

// Verification routes
Route::prefix('verification')->group(function () {
    Route::get('/start', [VerificationController::class, 'start'])->name('verification.start');
    Route::get('/selfie', [VerificationController::class, 'showSelfie'])->name('verification.selfie');
    Route::post('/selfie', [VerificationController::class, 'processSelfie'])->name('verification.selfie.process');
    Route::post('/debug-selfie', [VerificationController::class, 'debugSelfie'])->name('verification.debug-selfie');
    Route::get('/debug-otp', [VerificationController::class, 'debugOTP'])->name('verification.debug-otp');
    Route::get('/aadhaar', [VerificationController::class, 'aadhaar'])->name('verification.aadhaar');
    Route::post('/aadhaar', [VerificationController::class, 'processAadhaar'])->name('verification.aadhaar.process');
    Route::get('/otp', [VerificationController::class, 'showOTP'])->name('verification.otp');
    Route::post('/otp/verify', [VerificationController::class, 'verifyOTP'])->name('verification.otp.verify');
    Route::post('/otp/resend', [VerificationController::class, 'resendOTP'])->name('verification.otp.resend');

    Route::get('/results', [VerificationController::class, 'results'])->name('verification.results');
});

// ONDC API Routes (Public endpoints for verifiable credential lookup)
Route::prefix('api/ondc')->group(function () {
    // Lookup VC by VC ID (Privacy-preserving)
    Route::get('/vc/{vcId}', [ONDCController::class, 'lookupVC'])->name('ondc.vc.lookup');
    
    // Verify VC by VC ID and signature
    Route::post('/verify', [ONDCController::class, 'verifyVC'])->name('ondc.vc.verify');
    
    // Get VC metadata (public info only)
    Route::get('/vc/{vcId}/metadata', [ONDCController::class, 'getVCMetadata'])->name('ondc.vc.metadata');
    
    // Health check for ONDC integration
    Route::get('/health', [ONDCController::class, 'health'])->name('ondc.health');
});

// External API Routes (API Token Authentication)
Route::prefix('api/external')->middleware('api.token')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])->group(function () {
    Route::post('/verify-credential', [OrganizationController::class, 'verifyCredential'])->name('api.external.verify-credential');
    Route::post('/lookup-user', [OrganizationController::class, 'lookupUserByDID'])->name('api.external.lookup-user');
    Route::post('/issue-credential', [OrganizationController::class, 'issueCredential'])->name('api.external.issue-credential');
    Route::get('/issued-credentials', [OrganizationController::class, 'getIssuedCredentials'])->name('api.external.issued-credentials');
    Route::get('/access-logs', [OrganizationController::class, 'getAccessLogs'])->name('api.external.access-logs');
});

/*
|--------------------------------------------------------------------------
| Government Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('gov')->group(function () {
    Route::get('/approval', [App\Http\Controllers\AdminController::class, 'approvalDashboard'])->name('admin.approval.dashboard');
    Route::get('/approval/organizations/{status}', [App\Http\Controllers\AdminController::class, 'getOrganizationsByStatus'])->name('admin.organizations.by-status');
    Route::get('/approval/organization/{organization}', [App\Http\Controllers\AdminController::class, 'showOrganization'])->name('admin.organization.show');
    Route::post('/approval/organization/{organization}/approve', [App\Http\Controllers\AdminController::class, 'approveOrganization'])->name('admin.organization.approve')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    Route::post('/approval/organization/{organization}/reject', [App\Http\Controllers\AdminController::class, 'rejectOrganization'])->name('admin.organization.reject')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    Route::get('/approval/stats/types', [App\Http\Controllers\AdminController::class, 'getOrganizationTypeStats'])->name('admin.stats.types');
    
    // Government Document Simulation Routes (exclude CSRF for testing)
    Route::get('/approval/simulate-documents', [App\Http\Controllers\AdminController::class, 'showSimulateDocuments'])->name('admin.simulate.documents');
    Route::post('/approval/simulate-documents/lookup-user', [App\Http\Controllers\AdminController::class, 'lookupUserForSimulation'])->name('admin.simulate.lookup-user')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    Route::post('/approval/simulate-documents/issue', [App\Http\Controllers\AdminController::class, 'issueSimulatedDocuments'])->name('admin.simulate.issue')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
});

/*
|--------------------------------------------------------------------------
| Access Flagging Routes
|--------------------------------------------------------------------------
*/
// User Access Flagging Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/flag-access/{accessLogId}', [App\Http\Controllers\AccessFlagController::class, 'flagAccess'])->name('flag.access');
    Route::get('/user/flags', [App\Http\Controllers\AccessFlagController::class, 'getUserFlags'])->name('user.flags');
});

// Government Access Flag Review Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/government/flags', [App\Http\Controllers\AccessFlagController::class, 'getFlagsForReview'])->name('government.flags');
    Route::post('/government/review-flag/{flagId}', [App\Http\Controllers\AccessFlagController::class, 'reviewFlag'])->name('government.review-flag');
    Route::get('/government/organization-flags/{organizationId}', [App\Http\Controllers\AccessFlagController::class, 'getOrganizationFlags'])->name('government.organization-flags');
});