<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ApprovalCentreController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CertificateReportController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiscountRequestController;
use App\Http\Controllers\EnrolledTraineeController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\InstructorActivityReportController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadConversionReportController;
use App\Http\Controllers\LearnersPermitReportController;
use App\Http\Controllers\MessageLogController;
use App\Http\Controllers\PaymentAllocationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentCorrectionController;
use App\Http\Controllers\PaymentReportController;
use App\Http\Controllers\PaymentReversalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReferralSourceReportController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentCorrectionRequestController;
use App\Http\Controllers\StudentRegistrationReportController;
use App\Http\Controllers\StudentServiceController;
use App\Http\Controllers\TheoryClassCancellationController;
use App\Http\Controllers\TrainingProgressController;
use App\Http\Controllers\TrainingReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
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
        Route::resource('vehicles', VehicleController::class)->except(['index', 'show', 'destroy']);
        Route::resource('attendances', AttendanceController::class)->except(['index', 'show', 'destroy']);
    });

    Route::middleware('admin')->group(function () {
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::delete('instructors/{instructor}', [InstructorController::class, 'destroy'])->name('instructors.destroy');
        Route::delete('vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
        Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('payments/{payment}/reverse', [PaymentReversalController::class, 'create'])->name('payments.reverse.create');
        Route::post('payments/{payment}/reverse', [PaymentReversalController::class, 'store'])->name('payments.reverse.store');
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
        Route::get('payment-reports', [PaymentReportController::class, 'index'])->name('payment-reports.index');
        Route::get('payment-reports/export', [PaymentReportController::class, 'export'])->name('payment-reports.export');
        Route::get('payment-reports/export-pdf', [PaymentReportController::class, 'exportPdf'])->name('payment-reports.export-pdf');
        Route::get('payments/{payment}/correct', [PaymentCorrectionController::class, 'edit'])->name('payments.correct.edit');
        Route::put('payments/{payment}/correct', [PaymentCorrectionController::class, 'update'])->name('payments.correct.update');
        Route::get('enrollments/{enrollment}/reactivate', [EnrollmentController::class, 'showReactivateForm'])->name('enrollments.reactivate.create');
        Route::post('enrollments/{enrollment}/reactivate', [EnrollmentController::class, 'reactivate'])->name('enrollments.reactivate');
        Route::get('students/{student}/enroll', [EnrollmentController::class, 'create'])->name('students.enroll.create');
        Route::post('students/{student}/enroll', [EnrollmentController::class, 'store'])->name('students.enroll.store');
        Route::delete('enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');
        Route::get('enrollments/{enrollment}/upgrade', [EnrollmentController::class, 'showUpgradeForm'])->name('enrollments.upgrade.create');
        Route::post('enrollments/{enrollment}/upgrade', [EnrollmentController::class, 'upgrade'])->name('enrollments.upgrade.store');
        Route::delete('student-services/{studentService}', [StudentServiceController::class, 'destroy'])->name('student-services.destroy');
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
        Route::post('backups/send', [BackupController::class, 'send'])->name('backups.send');
        Route::get('message-log', [MessageLogController::class, 'index'])->name('message-log.index');
        Route::post('reminders/{type}/send', [ReminderController::class, 'send'])
            ->whereIn('type', ['balance_reminder', 'theory_class_reminder', 'lead_follow_up', 'absence_check_in'])
            ->name('reminders.send');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('theory-class-cancellations', TheoryClassCancellationController::class)->only(['index', 'store', 'destroy']);

        // Unified inbox aggregating every pending approval type below into
        // one screen, so a Director doesn't have to visit each type's own
        // page to see what's waiting.
        Route::get('approvals', [ApprovalCentreController::class, 'index'])->name('approvals.index');

        // Resolving/rejecting correction requests is Director-only;
        // submitting one (below, outside this group) is open to any
        // authenticated user. The pending inbox itself lives at
        // approvals.index above, not a dedicated listing route.
        Route::patch('correction-requests/{correctionRequest}/resolve', [StudentCorrectionRequestController::class, 'resolve'])->name('student-correction-requests.resolve');
        Route::patch('correction-requests/{correctionRequest}/reject', [StudentCorrectionRequestController::class, 'reject'])->name('student-correction-requests.reject');

        // Approving/rejecting a pending discount request is Director-only,
        // same as correction requests above; raising one in the first
        // place happens inline during registration (open to any
        // authenticated role) rather than through a dedicated route.
        Route::patch('discount-requests/{discountRequest}/approve', [DiscountRequestController::class, 'approve'])->name('discount-requests.approve');
        Route::patch('discount-requests/{discountRequest}/reject', [DiscountRequestController::class, 'reject'])->name('discount-requests.reject');
    });

    Route::resource('leads', LeadController::class)->except(['show']);
    Route::get('enrolled-trainees', [EnrolledTraineeController::class, 'index'])->name('enrolled-trainees.index');
    Route::get('students/{student}/training-record', [StudentController::class, 'trainingRecord'])->name('students.training-record');
    Route::get('students/{student}/correction-requests/create', [StudentCorrectionRequestController::class, 'create'])->name('student-correction-requests.create');
    Route::post('students/{student}/correction-requests', [StudentCorrectionRequestController::class, 'store'])->name('student-correction-requests.store');
    Route::post('students/{student}/services', [StudentServiceController::class, 'store'])->name('students.services.store');
    Route::patch('student-services/{studentService}/processing-status', [StudentServiceController::class, 'updateProcessingStatus'])->name('student-services.processing-status.update');
    Route::resource('students', StudentController::class)->except(['destroy']);
    Route::resource('courses', CourseController::class)->only(['index', 'show']);
    Route::resource('instructors', InstructorController::class)->only(['index', 'show']);
    Route::resource('vehicles', VehicleController::class)->only(['index', 'show']);
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
    Route::get('lead-conversion-report', [LeadConversionReportController::class, 'index'])->name('lead-conversion-report.index');
    Route::get('lead-conversion-report/export', [LeadConversionReportController::class, 'export'])->name('lead-conversion-report.export');
    Route::get('lead-conversion-report/export-pdf', [LeadConversionReportController::class, 'exportPdf'])->name('lead-conversion-report.export-pdf');
    Route::get('referral-source-report', [ReferralSourceReportController::class, 'index'])->name('referral-source-report.index');
    Route::get('referral-source-report/export', [ReferralSourceReportController::class, 'export'])->name('referral-source-report.export');
    Route::get('referral-source-report/export-pdf', [ReferralSourceReportController::class, 'exportPdf'])->name('referral-source-report.export-pdf');
    Route::get('learners-permit-report', [LearnersPermitReportController::class, 'index'])->name('learners-permit-report.index');
    Route::get('learners-permit-report/export', [LearnersPermitReportController::class, 'export'])->name('learners-permit-report.export');
    Route::get('learners-permit-report/export-pdf', [LearnersPermitReportController::class, 'exportPdf'])->name('learners-permit-report.export-pdf');
    // Registered before the resource below for the same reason as the admin-only group
    // above: "payments/export" and "payments/record" would otherwise be swallowed by
    // "payments/{payment}".
    Route::get('payments/export', [PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/record', [PaymentAllocationController::class, 'create'])->name('payments.record.create');
    Route::post('payments/record', [PaymentAllocationController::class, 'store'])->name('payments.record.store');
    Route::resource('payments', PaymentController::class)->except(['destroy']);
    Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    Route::resource('certificates', CertificateController::class)->except(['destroy']);
    Route::patch('enrollments/{enrollment}/complete', [EnrollmentController::class, 'complete'])->name('enrollments.complete');
});
require __DIR__.'/auth.php';
