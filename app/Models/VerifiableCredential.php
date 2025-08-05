<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VerifiableCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'vc_id',
        'vc_type',
        'issuer_organization_id',
        'issuer_did',
        'subject_did',
        'subject_name',
        'credential_data',
        'credential_hash',
        'blockchain_hash',
        'blockchain_tx_hash',
        'blockchain_network',
        'ipfs_hash',
        'ipfs_gateway_url',
        'digital_signature',
        'signature_algorithm',
        'issued_at',
        'expires_at',
        'status',
        'revoked_at',
        'revocation_reason',
        'verification_count',
        'last_verified_at',
        'metadata',
    ];

    protected $casts = [
        'credential_data' => 'array',
        'metadata' => 'array',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'last_verified_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'active',
        'verification_count' => 0,
        'blockchain_network' => 'amoy',
        'signature_algorithm' => 'RSA-SHA256',
    ];

    // Relationships
    public function issuer()
    {
        return $this->belongsTo(Organization::class, 'issuer_organization_id');
    }

    public function subject()
    {
        return $this->belongsTo(User::class, 'subject_did', 'did');
    }

    // Static Methods
    public static function generateVCId()
    {
        return 'urn:uuid:' . Str::uuid();
    }

    public static function generateCredentialHash($credentialData)
    {
        return hash('sha256', json_encode($credentialData, JSON_UNESCAPED_SLASHES));
    }

    // Instance Methods
    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isRevoked()
    {
        return $this->status === 'revoked';
    }

    public function revoke($reason = null)
    {
        $this->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revocation_reason' => $reason,
        ]);
    }

    public function incrementVerificationCount()
    {
        $this->increment('verification_count');
        $this->update(['last_verified_at' => now()]);
    }

    public function getIPFSUrl()
    {
        if (!$this->ipfs_hash) {
            return null;
        }

        return $this->ipfs_gateway_url ?: "https://ipfs.io/ipfs/{$this->ipfs_hash}";
    }

    public function getBlockchainExplorerUrl()
    {
        if (!$this->blockchain_tx_hash) {
            return null;
        }

        switch ($this->blockchain_network) {
            case 'polygon':
                return "https://polygonscan.com/tx/{$this->blockchain_tx_hash}";
            case 'amoy':
                return "https://amoy.polygonscan.com/tx/{$this->blockchain_tx_hash}";
            case 'ethereum':
                return "https://etherscan.io/tx/{$this->blockchain_tx_hash}";
            default:
                return null;
        }
    }

    public function toW3CFormat()
    {
        return [
            '@context' => [
                'https://www.w3.org/2018/credentials/v1',
                'https://secureverify.in/credentials/v1'
            ],
            'id' => $this->vc_id,
            'type' => ['VerifiableCredential', ucfirst(str_replace('_', '', $this->vc_type)) . 'Credential'],
            'issuer' => [
                'id' => $this->issuer_did,
                'name' => $this->issuer->name,
                'type' => $this->issuer->type
            ],
            'credentialSubject' => [
                'id' => $this->subject_did,
                'data' => $this->credential_data
            ],
            'issuanceDate' => $this->issued_at->toISOString(),
            'expirationDate' => $this->expires_at?->toISOString(),
            'proof' => [
                'type' => 'RsaSignature2018',
                'created' => $this->issued_at->toISOString(),
                'verificationMethod' => $this->issuer_did . '#key-1',
                'proofPurpose' => 'assertionMethod',
                'jws' => $this->digital_signature
            ],
            'credentialStatus' => [
                'id' => route('api.vc.status', $this->vc_id),
                'type' => 'RevocationList2020Status'
            ]
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('vc_type', $type);
    }

    public function scopeByIssuer($query, $issuerDid)
    {
        return $query->where('issuer_did', $issuerDid);
    }

    public function scopeBySubject($query, $subjectDid)
    {
        return $query->where('subject_did', $subjectDid);
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }
}
