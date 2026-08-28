<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Instructor;
use App\Models\MessageLog;
use App\Models\User;
use App\Services\TermiiSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class InstructorAccessController extends Controller
{
    public function __construct(protected TermiiSmsService $sms) {}

    /**
     * Grant this instructor app access: create their login account (no
     * password usable via the normal staff login form, no PIN until
     * they complete first-login OTP verification themselves) and text
     * them where to log in.
     */
    public function store(Instructor $instructor): RedirectResponse
    {
        if ($instructor->hasAppAccess()) {
            return Redirect::back()->withErrors(['instructor' => 'This instructor already has app access.']);
        }

        $user = User::create([
            'name' => $instructor->name,
            // Never actually sent to or used by the instructor - they log
            // in by phone + PIN, not email. Generated purely to satisfy
            // the users table's own unique/required email column.
            'email' => 'instructor-'.$instructor->id.'-'.Str::random(8).'@instructors.cdsms.internal',
            // Deliberately not Hash::make() - the User model's own
            // 'password' cast already hashes on save, so hashing it here
            // too would just double-hash it. Either way the result is
            // unusable since nobody ever learns this random value, but
            // there's no reason to hash it twice.
            'password' => Str::random(40),
            'role' => 'instructor',
        ]);

        $instructor->forceFill(['user_id' => $user->id])->save();

        $message = 'Classic Driving School: App access has been enabled for your instructor account. Log in at '.url('/instructor/login').' with your phone number to set up your PIN.';
        $sent = $this->sms->send($instructor->phone, $message);

        MessageLog::create([
            'recipient_type' => 'instructor',
            'recipient_id' => $instructor->id,
            'recipient_name' => $instructor->name,
            'recipient_phone' => $instructor->phone,
            'purpose' => 'instructor_access_granted',
            'channel' => $sent ? 'sms' : null,
            'status' => $sent ? 'sent' : 'failed',
            'message' => $message,
        ]);

        ActivityLog::record("Granted app access to instructor {$instructor->name}");

        return Redirect::back()->with('status', 'instructor-access-granted');
    }

    /**
     * Revoke this instructor's app access by deleting their login
     * account outright, rather than just clearing the link - a
     * half-revoked account with a still-valid PIN sitting unused would
     * be a real loose end. instructors.user_id nulls itself out
     * automatically (nullOnDelete).
     */
    public function destroy(Instructor $instructor): RedirectResponse
    {
        if (! $instructor->hasAppAccess()) {
            return Redirect::back()->withErrors(['instructor' => 'This instructor does not have app access.']);
        }

        $instructor->user->delete();

        ActivityLog::record("Revoked app access from instructor {$instructor->name}");

        return Redirect::back()->with('status', 'instructor-access-revoked');
    }
}
