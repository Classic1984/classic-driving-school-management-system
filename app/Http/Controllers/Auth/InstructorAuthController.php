<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Services\InstructorOtpService;
use App\Services\TermiiSmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class InstructorAuthController extends Controller
{
    public function __construct(protected TermiiSmsService $sms, protected InstructorOtpService $otp) {}

    /**
     * Look up the instructor whose app access was granted for this phone
     * number, normalizing both sides the same way so it doesn't matter
     * which format either was typed in. Loaded in full rather than
     * queried directly, since a driving school's instructor roster is
     * small enough that this is cheap, and a raw SQL comparison couldn't
     * account for format differences anyway.
     */
    protected function findInstructorByPhone(string $phone): ?Instructor
    {
        $normalized = $this->sms->normalize($phone);

        if (! $normalized) {
            return null;
        }

        return Instructor::whereNotNull('user_id')
            ->with('user')
            ->get()
            ->first(fn (Instructor $instructor) => $this->sms->normalize($instructor->phone) === $normalized);
    }

    public function showPhoneForm(): View
    {
        return view('auth.instructor.phone');
    }

    /**
     * Route the submitted phone number to whichever next step applies:
     * first-time PIN setup (via OTP) if this is the instructor's first
     * login since access was granted, or straight to PIN entry otherwise.
     */
    public function sendCode(Request $request): RedirectResponse
    {
        $data = $request->validate(['phone' => ['required', 'string']]);

        $instructor = $this->findInstructorByPhone($data['phone']);

        if (! $instructor) {
            return Redirect::route('instructor.login')->withErrors(['phone' => 'No instructor account was found for that phone number.']);
        }

        $request->session()->put('instructor-login.phone', $data['phone']);

        if ($instructor->user->pin_set_at !== null) {
            return Redirect::route('instructor.enter-pin');
        }

        $normalized = $this->sms->normalize($data['phone']);
        $code = $this->otp->generate($normalized);
        $this->sms->send($instructor->phone, "Classic Driving School: Your CDSMS verification code is {$code}. It expires in 10 minutes.");

        return Redirect::route('instructor.verify-otp');
    }

    public function showOtpForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('instructor-login.phone')) {
            return Redirect::route('instructor.login');
        }

        return view('auth.instructor.verify-otp');
    }

    /**
     * Complete first-login setup: a correct one-time code proves phone
     * ownership, which is what lets the instructor choose their own PIN
     * right here rather than one being assigned to them.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('instructor-login.phone');

        if (! $phone) {
            return Redirect::route('instructor.login');
        }

        $data = $request->validate([
            'otp' => ['required', 'string'],
            'pin' => ['required', 'string', 'digits_between:4,6', 'confirmed'],
        ]);

        $throttleKey = 'instructor-otp:'.$this->sms->normalize($phone).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return Redirect::back()->withErrors(['otp' => 'Too many attempts. Please try again in a few minutes.']);
        }

        $instructor = $this->findInstructorByPhone($phone);
        $normalized = $this->sms->normalize($phone);

        if (! $instructor || ! $this->otp->verify($normalized, $data['otp'])) {
            RateLimiter::hit($throttleKey);

            return Redirect::back()->withErrors(['otp' => 'That code is incorrect or has expired.']);
        }

        RateLimiter::clear($throttleKey);

        $instructor->user->forceFill([
            'pin' => $data['pin'],
            'pin_set_at' => now(),
        ])->save();

        $request->session()->forget('instructor-login.phone');
        Auth::login($instructor->user);
        $request->session()->regenerate();

        return Redirect::intended(route('instructor.dashboard', absolute: false));
    }

    public function showPinForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('instructor-login.phone')) {
            return Redirect::route('instructor.login');
        }

        return view('auth.instructor.enter-pin');
    }

    public function verifyPin(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('instructor-login.phone');

        if (! $phone) {
            return Redirect::route('instructor.login');
        }

        $data = $request->validate(['pin' => ['required', 'string']]);

        $throttleKey = 'instructor-pin:'.$this->sms->normalize($phone).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return Redirect::back()->withErrors(['pin' => 'Too many attempts. Please try again in a few minutes.']);
        }

        $instructor = $this->findInstructorByPhone($phone);

        if (! $instructor || ! $instructor->user->pin || ! Hash::check($data['pin'], $instructor->user->pin)) {
            RateLimiter::hit($throttleKey);

            return Redirect::back()->withErrors(['pin' => 'Incorrect PIN.']);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->forget('instructor-login.phone');
        Auth::login($instructor->user);
        $request->session()->regenerate();

        return Redirect::intended(route('instructor.dashboard', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::route('instructor.login');
    }
}
