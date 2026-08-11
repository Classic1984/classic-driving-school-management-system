<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CertificateReportController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrolledTraineeController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InstructorActivityReportController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PaymentAllocationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentCorrectionRequestController;
use App\Http\Controllers\StudentRegistrationReportController;
use App\Http\Controllers\StudentServiceController;
use App\Http\Controllers\TrainingProgressController;
use App\Http\Controllers\TrainingReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Two factor authentication is available to any user as an opt-in
    // security feature, managed from the card on their Profile page. It
    // is not required to log in.
    Route::post('two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])->name('two-factor.enable');
    Route::post('two-factor-authentication/confirm', [TwoFactorAuthenticationController::class, 'confirm'])->name('two-factor.confirm');
    Route::post('two-factor-authentication/recovery-codes', [TwoFactorAuthenticationController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');
    Route::delete('two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])->name('two-factor.disable');

    // Registered before the public index/show routes below: Route::resource() normally
    // orders "create" ahead of "show" so that "courses/create" isn't swallowed by the
    // "courses/{course}" pattern. Splitting the resource across groups must preserve
    // that ordering, so these restricted "create" routes have to come first here.
    Route::middleware('course-manager')->group(function () {
        Route::resource('courses', CourseController::class)->except(['index', 'show', 'destroy']);
        Route::resource('instructors', InstructorController::class)->except(['index', 'show', 'destroy']);
        Route::resource('attendances', AttendanceController::class)->except(['index', 'show', 'destroy']);
    });

    Route::middleware('admin')->group(function () {
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::delete('instructors/{instructor}', [InstructorController::class, 'destroy'])->name('instructors.destroy');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::delete('certificates/{certificate}', [CertificateController::class, 'destroy'])->name('certificates.destroy');
    });

    // Finance is exclusively for the Director: not just delete-restricted like the
    // admin-only actions above, the whole section (including viewing it) is hidden
    // from Admin and Secretary alike.
    Route::middleware('director')->group(function () {
        Route::resource('expenses', ExpenseController::class);
        Route::get('finance', [FinanceController::class, 'summary'])->name('finance.summary');
        Route::get('finance/export', [FinanceController::class, 'export'])->name('finance.export');
        Route::get('finance/export-pdf', [FinanceController::class, 'exportPdf'])->name('finance.export-pdf');
        Route::get('enrollments/{enrollment}/reactivate', [EnrollmentController::class, 'showReactivateForm'])->name('enrollments.reactivate.create');
        Route::post('enrollments/{enrollment}/reactivate', [EnrollmentController::class, 'reactivate'])->name('enrollments.reactivate');
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::resource('users', UserController::class)->except(['show']);

        // Reviewing and resolving/rejecting correction requests is
        // Director-only; submitting one (below, outside this group) is
        // open to any authenticated user.
        Route::get('correction-requests', [StudentCorrectionRequestController::class, 'index'])->name('student-correction-requests.index');
        Route::patch('correction-requests/{correctionRequest}/resolve', [StudentCorrectionRequestController::class, 'resolve'])->name('student-correction-requests.resolve');
        Route::patch('correction-requests/{correctionRequest}/reject', [StudentCorrectionRequestController::class, 'reject'])->name('student-correction-requests.reject');
    });

    Route::get('enrolled-trainees', [EnrolledTraineeController::class, 'index'])->name('enrolled-trainees.index');
    Route::get('students/{student}/training-record', [StudentController::class, 'trainingRecord'])->name('students.training-record');
    Route::get('students/{student}/correction-requests/create', [StudentCorrectionRequestController::class, 'create'])->name('student-correction-requests.create');
    Route::post('students/{student}/correction-requests', [StudentCorrectionRequestController::class, 'store'])->name('student-correction-requests.store');
    Route::post('students/{student}/services', [StudentServiceController::class, 'store'])->name('students.services.store');
    Route::resource('students', StudentController::class)->except(['destroy']);
    Route::resource('courses', CourseController::class)->only(['index', 'show']);
    Route::resource('instructors', InstructorController::class)->only(['index', 'show']);
    Route::resource('attendances', AttendanceController::class)->only(['index', 'show']);
    Route::get('training-progress', [TrainingProgressController::class, 'index'])->name('training-progress.index');
    Route::get('training-report', [TrainingReportController::class, 'index'])->name('training-report.index');
    Route::get('training-report/export', [TrainingReportController::class, 'export'])->name('training-report.export');
    Route::get('training-report/export-pdf', [TrainingReportController::class, 'exportPdf'])->name('training-report.export-pdf');
    Route::get('student-registration-report', [StudentRegistrationReportController::class, 'index'])->name('student-registration-report.index');
    Route::get('student-registration-report/export', [StudentRegistrationReportController::class, 'export'])->name('student-registration-report.export');
    Route::get('student-registration-report/export-pdf', [StudentRegistrationReportController::class, 'exportPdf'])->name('student-registration-report.export-pdf');
    Route::get('certificate-report', [CertificateReportController::class, 'index'])->name('certificate-report.index');
    Route::get('certificate-report/export', [CertificateReportController::class, 'export'])->name('certificate-report.export');
    Route::get('certificate-report/export-pdf', [CertificateReportController::class, 'exportPdf'])->name('certificate-report.export-pdf');
    Route::get('instructor-activity-report', [InstructorActivityReportController::class, 'index'])->name('instructor-activity-report.index');
    Route::get('instructor-activity-report/export', [InstructorActivityReportController::class, 'export'])->name('instructor-activity-report.export');
    Route::get('instructor-activity-report/export-pdf', [InstructorActivityReportController::class, 'exportPdf'])->name('instructor-activity-report.export-pdf');
    // Registered before the resource below for the same reason as the admin-only group
    // above: "payments/export" and "payments/record" would otherwise be swallowed by
    // "payments/{payment}".
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/record', [PaymentAllocationController::class, 'create'])->name('payments.record.create');
    Route::post('payments/record', [PaymentAllocationController::class, 'store'])->name('payments.record.store');
    Route::resource('payments', PaymentController::class)->except(['destroy']);
    Route::resource('certificates', CertificateController::class)->except(['destroy']);
    Route::patch('enrollments/{enrollment}/complete', [EnrollmentController::class, 'complete'])->name('enrollments.complete');
});
require __DIR__.'/auth.php';
