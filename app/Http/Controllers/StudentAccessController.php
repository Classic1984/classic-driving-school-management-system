<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\MessageLog;
use App\Models\Student;
use App\Models\User;
use App\Services\TermiiSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class StudentAccessController extends Controller
{
    public function __construct(protected TermiiSmsService $sms) {}

    /**
     * Grant this student app access: create their login account (no
     * password usable via the normal staff login form, no PIN until
     * they complete first-login OTP verification themselves) and text
     * them where to log in. Mirrors InstructorAccessController::store().
     */
    public function store(Student $student): RedirectResponse
    {
        if ($student->hasAppAccess()) {
            return Redirect::back()->withErrors(['student' => 'This student already has app access.']);
        }

        $user = User::create([
            'name' => $student->name,
            // Never actually sent to or used by the student - they log in
            // by phone + PIN, not email. Generated purely to satisfy the
            // users table's own unique/required email column.
            'email' => 'student-'.$student->id.'-'.Str::random(8).'@students.cdsms.internal',
            // Deliberately not Hash::make() - the User model's own
            // 'password' cast already hashes on save, so hashing it here
            // too would just double-hash it.
            'password' => Str::random(40),
            'role' => 'student',
        ]);

        $student->forceFill(['user_id' => $user->id])->save();

        $this->sendLoginText($student);

        ActivityLog::record("Granted app access to student {$student->name}");

        return Redirect::back()->with('status', 'student-access-granted');
    }

    /**
     * Re-send the login instructions to a student who already has app
     * access but hasn't completed first-login PIN setup yet. Mirrors
     * InstructorAccessController::resend().
     */
    public function resend(Student $student): RedirectResponse
    {
        if (! $student->hasAppAccess()) {
            return Redirect::back()->withErrors(['student' => 'This student does not have app access yet.']);
        }

        if ($student->user->pin_set_at !== null) {
            return Redirect::back()->withErrors(['student' => 'This student has already completed their first login.']);
        }

        $this->sendLoginText($student);

        ActivityLog::record("Re-sent app access login instructions to student {$student->name}");

        return Redirect::back()->with('status', 'student-access-resent');
    }

    /**
     * Revoke this student's app access by deleting their login account
     * outright, rather than just clearing the link - a half-revoked
     * account with a still-valid PIN sitting unused would be a real loose
     * end. students.user_id nulls itself out automatically (nullOnDelete).
     */
    public function destroy(Student $student): RedirectResponse
    {
        if (! $student->hasAppAccess()) {
            return Redirect::back()->withErrors(['student' => 'This student does not have app access.']);
        }

        $student->user->delete();

        ActivityLog::record("Revoked app access from student {$student->name}");

        return Redirect::back()->with('status', 'student-access-revoked');
    }

    protected function sendLoginText(Student $student): void
    {
        $message = 'Classic Driving School: App access has been enabled for your student account. Log in at '.url('/student/login').' with your phone number to set up your PIN.';
        $sent = $this->sms->send($student->phone, $message);

        MessageLog::create([
            'recipient_type' => 'student',
            'recipient_id' => $student->id,
            'recipient_name' => $student->name,
            'recipient_phone' => $student->phone,
            'purpose' => 'student_access_granted',
            'channel' => $sent ? 'sms' : null,
            'status' => $sent ? 'sent' : 'failed',
            'message' => $message,
        ]);
    }
}
