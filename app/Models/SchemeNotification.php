<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchemeNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'scheme_id',
        'notification_type', // 'new_scheme', 'eligibility_update', 'deadline_reminder'
        'message',
        'sent_at',
        'sms_sent',
        'sms_status',
        'eligibility_details'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'sms_sent' => 'boolean',
        'eligibility_details' => 'array'
    ];

    /**
     * Get the user that received the notification
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the scheme that was notified about
     */
    public function scheme()
    {
        return $this->belongsTo(GovernmentScheme::class, 'scheme_id');
    }

    /**
     * Check if notification was already sent to user for this scheme
     */
    public static function alreadyNotified($userId, $schemeId, $notificationType = 'new_scheme')
    {
        return self::where('user_id', $userId)
            ->where('scheme_id', $schemeId)
            ->where('notification_type', $notificationType)
            ->exists();
    }
} 