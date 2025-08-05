<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'verification_status',
        'verification_step',
        'selfie_path',
        'trust_score',
        'did',
        'aadhaar_number',
        'verified_at',
        'ipfs_metadata_cid',
        'blockchain_hash',
        'blockchain_tx_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'verified_at' => 'datetime',
            'password' => 'hashed',
            'trust_score' => 'integer',
        ];
    }

    /**
     * Check if user is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified' && !is_null($this->verified_at);
    }

    /**
     * Check if user has completed phone verification
     */
    public function isPhoneVerified(): bool
    {
        return !is_null($this->phone_verified_at);
    }

    /**
     * Generate DID format: did:sarvone:z6Mk...
     */
    public function generateDID(): string
    {
        return 'did:sarvone:z6Mk' . substr(hash('sha256', $this->phone . $this->name . time()), 0, 40);
    }

    /**
     * Get formatted trust score with percentage
     */
    public function getTrustScoreFormatted(): string
    {
        return $this->trust_score ? $this->trust_score . '%' : 'Not calculated';
    }

    /**
     * Get user's data access preferences
     */
    public function dataAccessPreferences()
    {
        return $this->hasMany(UserDataAccessPreference::class);
    }

    /**
     * Get or create data access preference for organization type
     */
    public function getDataAccessPreference($organizationType)
    {
        return $this->dataAccessPreferences()->firstOrCreate(
            ['organization_type' => $organizationType],
            [
                'allowed_data_types' => UserDataAccessPreference::getOrganizationTypes()[$organizationType]['mandatory'] ?? [],
                'mandatory_data_types' => UserDataAccessPreference::getOrganizationTypes()[$organizationType]['mandatory'] ?? [],
                'is_active' => true
            ]
        );
    }

    /**
     * Get user's verifiable credentials
     */
    public function verifiableCredentials()
    {
        return $this->hasMany(VerifiableCredential::class, 'subject_did', 'did');
    }

    /**
     * Get user's Aadhaar credential
     */
    public function getAadhaarCredential()
    {
        return $this->verifiableCredentials()
            ->where('vc_type', 'aadhaar_card')
            ->where('status', 'active')
            ->first();
    }

    /**
     * Calculate user's age from Aadhaar credential
     */
    public function getAgeFromAadhaar(): ?int
    {
        $aadhaarVC = $this->getAadhaarCredential();
        
        if (!$aadhaarVC) {
            return null;
        }

        try {
            $credentialData = is_string($aadhaarVC->credential_data) 
                ? json_decode($aadhaarVC->credential_data, true) 
                : $aadhaarVC->credential_data;

            if (!$credentialData) {
                return null;
            }

            // Check for nested structure: aadhaar_card.aadhaar_card.date_of_birth
            $dob = null;
            if (isset($credentialData['aadhaar_card']['aadhaar_card']['date_of_birth'])) {
                $dob = $credentialData['aadhaar_card']['aadhaar_card']['date_of_birth'];
            } elseif (isset($credentialData['aadhaar_card']['date_of_birth'])) {
                $dob = $credentialData['aadhaar_card']['date_of_birth'];
            }

            if (!$dob) {
                return null;
            }
            
            // Parse the date (assuming format like "1995-03-15" or "15-03-1995")
            $date = \DateTime::createFromFormat('Y-m-d', $dob);
            if (!$date) {
                $date = \DateTime::createFromFormat('d-m-Y', $dob);
            }
            if (!$date) {
                $date = \DateTime::createFromFormat('d/m/Y', $dob);
            }

            if (!$date) {
                return null;
            }

            $now = new \DateTime();
            $age = $now->diff($date)->y;
            
            return $age;
        } catch (\Exception $e) {
            \Log::error('Error calculating age from Aadhaar VC: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user's current age (from Aadhaar or fallback)
     */
    public function getCurrentAge(): ?int
    {
        $age = $this->getAgeFromAadhaar();
        
        if ($age !== null) {
            return $age;
        }

        // Fallback: if we have aadhaar_number in user table, try to calculate from there
        if ($this->aadhaar_number) {
            // This would need to be implemented based on how Aadhaar number encodes DOB
            // For now, return null
            return null;
        }

        return null;
    }

    /**
     * Get formatted age string
     */
    public function getAgeFormatted(): string
    {
        $age = $this->getCurrentAge();
        
        if ($age === null) {
            return 'Not available';
        }

        return $age . ' years';
    }
}
