<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccessLog extends Model
{
    use HasFactory;

    protected $table = 'credential_access_logs';

    protected $fillable = [
        'user_id',
        'organization_id',
        'organization_name',
        'user_did',
        'user_name',
        'credential_type',
        'status',
        'purpose',
        'verification_details',
    ];

    protected $casts = [
        'accessed_vcs' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the access flags for this log entry
     */
    public function accessFlags(): HasMany
    {
        return $this->hasMany(AccessFlag::class);
    }

    /**
     * Check if this access log has been flagged
     */
    public function hasFlags(): bool
    {
        return $this->accessFlags()->exists();
    }

    /**
     * Check if this access log has pending flags
     */
    public function hasPendingFlags(): bool
    {
        return $this->accessFlags()->pending()->exists();
    }

    // Static method to log access
    public static function logAccess($user, $organization, $credentialType, $purpose = null, $verificationDetails = null)
    {
        return self::create([
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'organization_name' => $organization->legal_name ?? $organization->name,
            'user_did' => $user->did,
            'user_name' => $user->name,
            'credential_type' => $credentialType,
            'status' => 'success',
            'purpose' => $purpose,
            'verification_details' => $verificationDetails ? json_encode($verificationDetails) : null,
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

        return $types[$this->credential_type] ?? ucfirst(str_replace('_', ' ', $this->credential_type));
    }

    // Get formatted organization type
    public function getFormattedOrganizationTypeAttribute()
    {
        return ucfirst($this->organization_type);
    }
} 