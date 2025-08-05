<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'access_log_id',
        'user_id',
        'organization_id',
        'flag_type',
        'flag_reason',
        'status',
        'government_notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the access log that was flagged
     */
    public function accessLog(): BelongsTo
    {
        return $this->belongsTo(AccessLog::class);
    }

    /**
     * Get the user who created the flag
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the organization that was flagged
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the government user who reviewed the flag
     */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the flag is pending review
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the flag has been reviewed
     */
    public function isReviewed(): bool
    {
        return in_array($this->status, ['reviewed', 'resolved', 'dismissed']);
    }

    /**
     * Check if the flag is resolved
     */
    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    /**
     * Check if the flag was dismissed
     */
    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    /**
     * Get the flag type display name
     */
    public function getFlagTypeDisplayAttribute(): string
    {
        return match($this->flag_type) {
            'unauthorized_access' => 'Unauthorized Access',
            'suspicious_activity' => 'Suspicious Activity',
            'data_misuse' => 'Data Misuse',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->flag_type))
        };
    }

    /**
     * Get the status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800',
            'reviewed' => 'bg-blue-100 text-blue-800',
            'resolved' => 'bg-green-100 text-green-800',
            'dismissed' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Scope to get pending flags
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get flags by organization
     */
    public function scopeByOrganization($query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Scope to get flags by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get flags by type
     */
    public function scopeByType($query, $flagType)
    {
        return $query->where('flag_type', $flagType);
    }

    /**
     * Scope to get recent flags
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
} 