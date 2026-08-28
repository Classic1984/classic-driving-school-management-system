<?php

use App\Http\Controllers\Auth\StudentAuthController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;

// A student's own login flow: phone number -> OTP-verified PIN setup
// (first login only) or straight to PIN entry (every login after) -> their
// own dashboard. Entirely separate from the staff email/password login in
// routes/auth.php and the instructor phone+PIN flow in
// routes/instructor-auth.php - a student account can never authenticate
// through either (see User::isStudent()).
Route::get('student/login', [StudentAuthController::class, 'showPhoneForm'])->name('student.login');
Route::post('student/login', [StudentAuthController::class, 'sendCode'])->name('student.login.send-code');
Route::get('student/verify-otp', [StudentAuthController::class, 'showOtpForm'])->name('student.verify-otp');
Route::post('student/verify-otp', [StudentAuthController::class, 'verifyOtp'])->name('student.verify-otp.store');
Route::get('student/enter-pin', [StudentAuthController::class, 'showPinForm'])->name('student.enter-pin');
Route::post('student/enter-pin', [StudentAuthController::class, 'verifyPin'])->name('student.enter-pin.store');
Route::post('student/logout', [StudentAuthController::class, 'destroy'])->name('student.logout');

Route::middleware(['auth', 'student'])->group(function () {
    Route::get('student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
});
