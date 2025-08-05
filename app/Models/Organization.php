<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Organization extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'legal_name',
        'organization_type',
        'registration_number',
        'official_email',
        'official_phone',
        'website_url',
        'head_office_address',
        'branch_address',
        'signatory_name',
        'signatory_designation',
        'signatory_email',
        'signatory_phone',
        'wallet_address',
        'technical_contact_name',
        'technical_contact_email',
        'write_scopes',
        'read_scopes',
        'expected_volume',
        'use_case_description',
        'password',
        'verification_status',
        'verified_at',
        'verification_notes',
        'blockchain_tx_hash',
        'did',
        'api_key',
        'trust_score',
        'terms_agreement',
    ];

    protected $hidden = [
        'password',
        'private_key',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'trust_score' => 'decimal:2',
        'write_scopes' => 'array',
        'read_scopes' => 'array',
        'terms_agreement' => 'boolean',
        'vcs_issued' => 'integer',
        'vcs_verified' => 'integer',
    ];

    protected $attributes = [
        'verification_status' => 'pending', // Pending government approval
        'is_active' => true,
        'vcs_issued' => 0,
        'vcs_verified' => 0,
        'trust_score' => 0.00,
        'key_algorithm' => 'RSA-2048',
        'terms_agreement' => false,
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'official_email';
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the column name for the "remember me" token.
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // DID Generation and Management
    public function generateDID()
    {
        if ($this->did) {
            return $this->did;
        }

        // NOTE: Temporary DID generation for registration
        // Final W3C compliant DID will be generated during government approval
        // This is just a placeholder until government approves the organization
        $tempId = 'temp_' . Str::random(16);
        $did = "did:sarvone:pending:{$tempId}";
        
        $this->did = $did;
        $this->save();
        
        return $did;
    }

    public function generateKeyPair()
    {
        if ($this->public_key && $this->private_key) {
            return [
                'public_key' => $this->public_key,
                'private_key' => $this->private_key
            ];
        }

        try {
            \Log::info('Starting key pair generation for organization', ['id' => $this->id]);

            // Try phpseclib first (more reliable on Windows)
            if (class_exists('\phpseclib\Crypt\RSA')) {
                return $this->generateKeyPairWithPhpseclib();
            }

            // Fallback to OpenSSL with Windows compatibility
            return $this->generateKeyPairWithOpenSSL();

        } catch (\Exception $e) {
            \Log::error('Key pair generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw the exception - just log it and continue
            // The organization will be created without keys
            \Log::warning('Organization created without keys due to key generation failure');
            
            return [
                'public_key' => null,
                'private_key' => null
            ];
        }
    }

    private function generateKeyPairWithPhpseclib()
    {
        \Log::info('Using phpseclib v2 for key generation');
        
        $rsa = new \phpseclib\Crypt\RSA();
        
        // Generate key pair - phpseclib v2 API
        $keys = $rsa->createKey(2048);
        
        $privateKey = $keys['privatekey'];
        $publicKey = $keys['publickey'];

        \Log::info('Key pair generated successfully with phpseclib v2');

        // Try to encrypt private key before storing
        try {
            $encryptedPrivateKey = encrypt($privateKey);
            \Log::info('Private key encrypted successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to encrypt private key', ['error' => $e->getMessage()]);
            // Store unencrypted as fallback
            $encryptedPrivateKey = $privateKey;
            \Log::warning('Storing unencrypted private key as fallback');
        }

        $this->public_key = $publicKey;
        $this->private_key = $encryptedPrivateKey;
        $this->save();

        \Log::info('Key pair saved to database successfully');

        return [
            'public_key' => $publicKey,
            'private_key' => $privateKey
        ];
    }

    private function generateKeyPairWithOpenSSL()
    {
        \Log::info('Using OpenSSL for key generation');
        
        // Windows-compatible RSA key generation
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "config" => null,
        ];

        // Set environment variable for Windows compatibility
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            putenv('OPENSSL_CONF=');
        }

        $res = openssl_pkey_new($config);
        if (!$res) {
            $error = openssl_error_string();
            \Log::error('Failed to generate key pair', ['error' => $error]);
            
            // Fallback: Try without config
            $res = openssl_pkey_new();
            if (!$res) {
                $error = openssl_error_string();
                \Log::error('Fallback key generation also failed', ['error' => $error]);
                throw new \Exception('Failed to generate key pair: ' . $error);
            }
        }

        // Extract private key
        $success = openssl_pkey_export($res, $privateKey);
        if (!$success) {
            $error = openssl_error_string();
            \Log::error('Failed to export private key', ['error' => $error]);
            throw new \Exception('Failed to export private key: ' . $error);
        }
        
        // Extract public key
        $publicKeyDetails = openssl_pkey_get_details($res);
        if (!$publicKeyDetails) {
            $error = openssl_error_string();
            \Log::error('Failed to get public key details', ['error' => $error]);
            throw new \Exception('Failed to get public key details: ' . $error);
        }
        
        $publicKey = $publicKeyDetails["key"];

        \Log::info('Key pair generated successfully with OpenSSL');

        // Try to encrypt private key before storing
        try {
            $encryptedPrivateKey = encrypt($privateKey);
            \Log::info('Private key encrypted successfully');
        } catch (\Exception $e) {
            \Log::error('Failed to encrypt private key', ['error' => $e->getMessage()]);
            // Store unencrypted as fallback
            $encryptedPrivateKey = $privateKey;
            \Log::warning('Storing unencrypted private key as fallback');
        }

        $this->public_key = $publicKey;
        $this->private_key = $encryptedPrivateKey;
        $this->save();

        \Log::info('Key pair saved to database successfully');

        return [
            'public_key' => $publicKey,
            'private_key' => $privateKey
        ];
    }

    public function getDecryptedPrivateKey()
    {
        if (!$this->private_key) {
            return null;
        }

        try {
            // Try to decrypt first
            return decrypt($this->private_key);
        } catch (\Exception $e) {
            \Log::warning('Failed to decrypt private key, returning as-is', ['error' => $e->getMessage()]);
            // If decryption fails, assume it's stored unencrypted (fallback)
            return $this->private_key;
        }
    }

    // Sign data using organization's private key
    public function signData($data)
    {
        $privateKey = $this->getDecryptedPrivateKey();
        if (!$privateKey) {
            throw new \Exception('Private key not available');
        }

        $signature = '';
        if (!openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \Exception('Failed to sign data');
        }

        return base64_encode($signature);
    }

    // Verify signature using organization's public key
    public static function verifySignature($data, $signature, $publicKey)
    {
        $decodedSignature = base64_decode($signature);
        $result = openssl_verify($data, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);
        
        return $result === 1;
    }

    // Status and Verification Methods
    public function isVerified()
    {
        return $this->verification_status === 'approved';
    }

    public function isPending()
    {
        return $this->verification_status === 'pending';
    }

    public function isRejected()
    {
        return $this->verification_status === 'rejected';
    }

    public function markAsVerified($notes = null)
    {
        $this->verification_status = 'verified';
        $this->verified_at = now();
        $this->verification_notes = $notes;
        $this->save();
    }

    public function markAsRejected($notes = null)
    {
        $this->verification_status = 'rejected';
        $this->verification_notes = $notes;
        $this->save();
    }

    // Statistics Methods
    public function incrementVCsIssued()
    {
        $this->increment('vcs_issued');
    }

    public function incrementVCsVerified()
    {
        $this->increment('vcs_verified');
    }

    public function updateTrustScore($score)
    {
        $this->trust_score = $score;
        $this->save();
    }

    // Relationships
    public function issuedCredentials()
    {
        return $this->hasMany(VerifiableCredential::class, 'issuer_id');
    }

    public function verifiedCredentials()
    {
        return $this->hasMany(VerificationLog::class, 'verifier_id');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('organization_type', $type);
    }

    // Accessors
    public function getTypeDisplayAttribute()
    {
        return match($this->organization_type) {
            'bank' => 'Bank',
            'company' => 'Company/Corporation',
            'school' => 'School',
            'college' => 'College/University',
            'hospital' => 'Hospital/Healthcare',
            'government' => 'Government Agency',
            'ngo' => 'NGO/Non-Profit',
            'fintech' => 'Fintech Company',
            'scholarship_board' => 'Scholarship Board',
            'welfare_board' => 'Social Welfare Board',
            'scheme_partner' => 'Government Scheme Partner',
            'hr_agency' => 'HR/Recruitment Agency',
            'training_provider' => 'Skill Training Provider',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->organization_type))
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->verification_status) {
            'verified' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    // Boot method to auto-generate DID and keys
    protected static function boot()
    {
        parent::boot();

        static::created(function ($organization) {
            $organization->generateDID();
            $organization->generateKeyPair();
            
            // Keep organization as pending - will be approved by government admin
            // No auto-verification for SarvOne
            $organization->save();
        });
    }

    /**
     * Generate a unique API key for the organization
     */
    public function generateApiKey()
    {
        if ($this->api_key) {
            return $this->api_key;
        }

        // Generate a unique API key
        do {
            $apiKey = 'org_' . Str::random(32);
        } while (static::where('api_key', $apiKey)->exists());

        $this->api_key = $apiKey;
        $this->save();

        return $apiKey;
    }

    /**
     * Check if the organization has a valid API key
     */
    public function hasApiKey()
    {
        return !empty($this->api_key);
    }
}
