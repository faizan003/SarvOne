<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'access_type',
        'details',
        'access_reason',
        'organization_type',
        'organization_name',
        'user_did',
        'organization_did',
        'accessed_vcs',
        'scheme_id',
        'action',
    ];

    protected $casts = [
        'accessed_vcs' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // Static method to log access
    public static function logAccess($user, $organization, $accessType, $details = null, $accessedVCs = [], $accessReason = null)
    {
        return self::create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'access_type' => $accessType,
            'details' => $details,
            'access_reason' => $accessReason,
            'organization_type' => $organization->type,
            'organization_name' => $organization->name,
            'user_did' => $user->did,
            'organization_did' => $organization->did,
            'accessed_vcs' => $accessedVCs,
        ]);
    }

    // Get formatted access type
    public function getFormattedAccessTypeAttribute()
    {
        $types = [
            'profile' => 'Profile Information',
            'vc_details' => 'Verifiable Credentials',
            'employment_data' => 'Employment Data',
            'education_data' => 'Education Data',
            'health_data' => 'Health Data',
            'bank_data' => 'Bank Data',
            'comprehensive_data_access' => 'Comprehensive Data Access (Employment, Education, Financial)',
        ];

        return $types[$this->access_type] ?? ucfirst(str_replace('_', ' ', $this->access_type));
    }

    // Get formatted organization type
    public function getFormattedOrganizationTypeAttribute()
    {
        return ucfirst($this->organization_type);
    }
} 