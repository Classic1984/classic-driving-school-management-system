<?php

namespace App\Models;

use Database\Factories\MessageLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A record of every automatic SMS/WhatsApp reminder the system has
 * attempted to send (balance, theory class, lead follow-up, absence
 * check-in, training days remaining, training completed), regardless of
 * whether it succeeded - giving staff visibility into what actually went
 * out.
 */
class MessageLog extends Model
{
    /** @use HasFactory<MessageLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'recipient_type',
        'recipient_id',
        'recipient_name',
        'recipient_phone',
        'purpose',
        'channel',
        'status',
        'message',
    ];

    /**
     * Human-readable labels for each reminder type this log covers.
     *
     * @var array<string, string>
     */
    public const PURPOSES = [
        'balance_reminder' => 'Balance Reminder',
        'theory_class_reminder' => 'Theory Class Reminder',
        'theory_class_cancellation' => 'Theory Class Cancellation',
        'lead_follow_up' => 'Lead Follow-Up',
        'absence_check_in' => 'Absence Check-In',
        'training_days_remaining' => 'Training Days Remaining',
        'training_completed' => 'Training Completed',
        'certificate_ready' => 'Certificate Ready',
        'instructor_access_granted' => 'Instructor Access Granted',
    ];
}
