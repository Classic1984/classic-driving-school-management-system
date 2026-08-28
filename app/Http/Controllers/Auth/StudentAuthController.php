<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\StudentOtpService;
use App\Services\TermiiSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    public function __construct(protected TermiiSmsService $sms, protected StudentOtpService $otp) {}

    /**
     * Look up the student whose app access was granted for this phone
     * number, normalizing both sides the same way so it doesn't matter
     * which format either was typed in. Mirrors
     * InstructorAuthController::findInstructorByPhone().
     */
    protected function findStudentByPhone(string $phone): ?Student
    {
        $normalized = $this->sms->normalize($phone);

        if (! $normalized) {
            return null;
        }

        return Student::whereNotNull('user_id')
            ->with('user')
            ->get()
            ->first(fn (Student $student) => $this->sms->normalize($student->phone) === $normalized);
    }

    public function showPhoneForm(): View
    {
        return view('auth.student.phone');
    }

    /**
     * Route the submitted phone number to whichever next step applies:
     * first-time PIN setup (via OTP) if this is the student's first login
     * since access was granted, or straight to PIN entry otherwise.
     */
    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', 'string']]);

        $student = $this->findStudentByPhone($data['phone']);

        if (! $student) {
            return Redirect::route('student.login')->withErrors(['phone' => 'No student account was found for that phone number.']);
        }

        $request->session()->put('student-login.phone', $data['phone']);

        if ($student->user->pin_set_at !== null) {
            return Redirect::route('student.enter-pin');
        }

        $normalized = $this->sms->normalize($data['phone']);
        $code = $this->otp->generate($normalized);
        $this->sms->send($student->phone, "Classic Driving School: Your CDSMS verification code is {$code}. It expires in 10 minutes.");

        return Redirect::route('student.verify-otp');
    }

    public function showOtpForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('student-login.phone')) {
            return Redirect::route('student.login');
        }

        return view('auth.student.verify-otp');
    }

    /**
     * Complete first-login setup: a correct one-time code proves phone
     * ownership, which is what lets the student choose their own PIN
     * right here rather than one being assigned to them.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('student-login.phone');

        if (! $phone) {
            return Redirect::route('student.login');
        }

        $data = $request->validate([
            'otp' => ['required', 'string'],
            'pin' => ['required', 'string', 'digits_between:4,6', 'confirmed'],
        ]);

        $throttleKey = 'student-otp:'.$this->sms->normalize($phone).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return Redirect::back()->withErrors(['otp' => 'Too many attempts. Please try again in a few minutes.']);
        }

        $student = $this->findStudentByPhone($phone);
        $normalized = $this->sms->normalize($phone);

        if (! $student || ! $this->otp->verify($normalized, $data['otp'])) {
            RateLimiter::hit($throttleKey);

            return Redirect::back()->withErrors(['otp' => 'That code is incorrect or has expired.']);
        }

        RateLimiter::clear($throttleKey);

        $student->user->forceFill([
            'pin' => $data['pin'],
            'pin_set_at' => now(),
        ])->save();

        $request->session()->forget('student-login.phone');
        Auth::login($student->user);
        $request->session()->regenerate();

        return Redirect::intended(route('student.dashboard', absolute: false));
    }

    public function showPinForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('student-login.phone')) {
            return Redirect::route('student.login');
        }

        return view('auth.student.enter-pin');
    }

    public function verifyPin(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('student-login.phone');

        if (! $phone) {
            return Redirect::route('student.login');
        }

        $data = $request->validate(['pin' => ['required', 'string']]);

        $throttleKey = 'student-pin:'.$this->sms->normalize($phone).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return Redirect::back()->withErrors(['pin' => 'Too many attempts. Please try again in a few minutes.']);
        }

        $student = $this->findStudentByPhone($phone);

        if (! $student || ! $student->user->pin || ! Hash::check($data['pin'], $student->user->pin)) {
            RateLimiter::hit($throttleKey);

            return Redirect::back()->withErrors(['pin' => 'Incorrect PIN.']);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->forget('student-login.phone');
        Auth::login($student->user);
        $request->session()->regenerate();

        return Redirect::intended(route('student.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('student.login');
    }
}
