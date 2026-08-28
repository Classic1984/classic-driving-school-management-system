<?php

use App\Http\Controllers\Auth\InstructorAuthController;
use App\Http\Controllers\InstructorAssessmentRequestController;
use App\Http\Controllers\InstructorAttendanceController;
use App\Http\Controllers\InstructorDashboardController;
use App\Http\Controllers\InstructorTheoryAttendanceController;
use Illuminate\Support\Facades\Route;

// An instructor's own login flow: phone number -> OTP-verified PIN setup
// (first login only) or straight to PIN entry (every login after) -> their
// own dashboard. Entirely separate from the staff email/password login in
// routes/auth.php, since an instructor account can never authenticate
// through that form (see User::isInstructor()).
Route::get('instructor/login', [InstructorAuthController::class, 'showPhoneForm'])->name('instructor.login');
Route::post('instructor/login', [InstructorAuthController::class, 'sendCode'])->name('instructor.login.send-code');
Route::get('instructor/verify-otp', [InstructorAuthController::class, 'showOtpForm'])->name('instructor.verify-otp');
Route::post('instructor/verify-otp', [InstructorAuthController::class, 'verifyOtp'])->name('instructor.verify-otp.store');
Route::get('instructor/enter-pin', [InstructorAuthController::class, 'showPinForm'])->name('instructor.enter-pin');
Route::post('instructor/enter-pin', [InstructorAuthController::class, 'verifyPin'])->name('instructor.enter-pin.store');
Route::post('instructor/logout', [InstructorAuthController::class, 'destroy'])->name('instructor.logout');

Route::middleware(['auth', 'instructor'])->group(function () {
    Route::get('instructor/dashboard', [InstructorDashboardController::class, 'index'])->name('instructor.dashboard');
    Route::post('instructor/attendance', [InstructorAttendanceController::class, 'store'])->name('instructor.attendance.store');
    Route::post('instructor/theory-classes/{theoryClass}/attendance', [InstructorTheoryAttendanceController::class, 'store'])->name('instructor.theory-attendance.store');
    Route::post('instructor/enrollments/{enrollment}/assessment-request', [InstructorAssessmentRequestController::class, 'store'])->name('instructor.assessment-request.store');
});
