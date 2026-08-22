<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-amber-500">
                Classic Driving School & Son Nigeria Limited
            </h2>
            <span class="text-gray-500">
                CDSMS Version 1.0
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @php
                $greeting = match (true) {
                    now()->hour < 12 => __('Good Morning'),
                    now()->hour < 17 => __('Good Afternoon'),
                    default => __('Good Evening'),
                };
                $firstName = \Illuminate\Support\Str::of(auth()->user()->name)->before(' ');
            @endphp

            <div class="bg-black text-white rounded-xl p-8 mb-6">
                <h1 class="text-2xl font-bold text-amber-400">{{ $greeting }}, {{ $firstName }}</h1>
                <p class="mt-1 text-sm text-gray-300">{{ now()->format('l, F j, Y') }}</p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div class="bg-gray-900 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-400">{{ __('Active Students') }}</p>
                        <p class="text-2xl font-bold mt-1 text-amber-400">{{ number_format($kpis['active_students']) }}</p>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-400">{{ __('Training Today') }}</p>
                        <p class="text-2xl font-bold mt-1 text-amber-400">{{ number_format($kpis['training_today']) }}</p>
                    </div>
                    <div class="rounded-lg p-4 {{ $kpis['pending_payments'] > 0 ? 'bg-amber-500 text-black' : 'bg-gray-900' }}">
                        <p class="text-xs uppercase tracking-wider {{ $kpis['pending_payments'] > 0 ? 'text-black/70' : 'text-gray-400' }}">{{ __('Pending Payments') }}</p>
                        <p class="text-base font-bold mt-1 whitespace-nowrap {{ $kpis['pending_payments'] > 0 ? '' : 'text-amber-400' }}">₦{{ number_format($kpis['pending_payments'], 2) }}</p>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-400">{{ __('Completed Training') }}</p>
                        <p class="text-2xl font-bold mt-1 text-amber-400">{{ number_format($kpis['completed_training']) }}</p>
                    </div>
                    <div class="bg-gray-900 rounded-lg p-4">
                        <p class="text-xs uppercase tracking-wider text-gray-400">{{ __('Active Vehicles') }}</p>
                        <p class="text-2xl font-bold mt-1 text-amber-400">{{ number_format($kpis['active_vehicles']) }}</p>
                    </div>
                    <div class="rounded-lg p-4 {{ $kpis['certificates_due'] > 0 ? 'bg-amber-500 text-black' : 'bg-gray-900' }}">
                        <p class="text-xs uppercase tracking-wider {{ $kpis['certificates_due'] > 0 ? 'text-black/70' : 'text-gray-400' }}">{{ __('Certificates Due') }}</p>
                        <p class="text-2xl font-bold mt-1 {{ $kpis['certificates_due'] > 0 ? '' : 'text-amber-400' }}">{{ number_format($kpis['certificates_due']) }}</p>
                    </div>
                    <div class="rounded-lg p-4 {{ $kpis['revenue_leakage'] > 0 ? 'bg-red-600 text-white' : 'bg-gray-900' }}">
                        <p class="text-xs uppercase tracking-wider {{ $kpis['revenue_leakage'] > 0 ? 'text-white/70' : 'text-gray-400' }}">{{ __('Revenue Leakage') }}</p>
                        <p class="text-base font-bold mt-1 whitespace-nowrap {{ $kpis['revenue_leakage'] > 0 ? '' : 'text-amber-400' }}">₦{{ number_format($kpis['revenue_leakage'], 2) }}</p>
                    </div>
                    <div class="rounded-lg p-4 {{ $kpis['at_risk_students'] > 0 ? 'bg-red-600 text-white' : 'bg-gray-900' }}">
                        <p class="text-xs uppercase tracking-wider {{ $kpis['at_risk_students'] > 0 ? 'text-white/70' : 'text-gray-400' }}">{{ __('At-Risk Students') }}</p>
                        <p class="text-2xl font-bold mt-1 {{ $kpis['at_risk_students'] > 0 ? '' : 'text-amber-400' }}">{{ number_format($kpis['at_risk_students']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __("Today's Operations") }}</h3>

                <ul class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm text-gray-700">
                    @if (auth()->user()->isDirector())
                        <li>
                            <a href="{{ route('approvals.index') }}" class="{{ $todaysOperations['pending_approvals'] > 0 ? 'text-red-600 font-medium' : 'text-gray-700' }} hover:underline">
                                {{ __(':count approval(s) pending', ['count' => $todaysOperations['pending_approvals']]) }}
                            </a>
                        </li>
                    @endif
                    <li>
                        <a href="{{ route('training-report.index', ['period' => 'today']) }}" class="hover:text-amber-600 hover:underline">
                            {{ __(':count student(s) trained today', ['count' => $todaysOperations['students_trained']]) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('training-report.index', ['period' => 'today']) }}" class="hover:text-amber-600 hover:underline">
                            {{ __(':count training session(s) logged today', ['count' => $todaysOperations['training_sessions']]) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('instructor-activity-report.index', ['period' => 'today']) }}" class="hover:text-amber-600 hover:underline">
                            {{ __(':count instructor(s) active today', ['count' => $todaysOperations['instructors_active']]) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('vehicles.index') }}" class="hover:text-amber-600 hover:underline">
                            {{ __(':count vehicle(s) in use today', ['count' => $todaysOperations['vehicles_in_use']]) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('payments.index') }}" class="hover:text-amber-600 hover:underline">
                            {{ __('₦:amount received today', ['amount' => number_format($todaysOperations['payments_received_today'], 2)]) }}
                        </a>
                    </li>
                    <li>
                        <a href="#outstanding-payments" class="{{ $todaysOperations['payments_pending_count'] > 0 ? 'text-red-600 font-medium' : 'text-gray-700' }} hover:underline">
                            {{ __(':count payment(s) pending', ['count' => $todaysOperations['payments_pending_count']]) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('training-progress.index') }}" class="{{ $todaysOperations['approaching_completion'] > 0 ? 'text-amber-600 font-medium' : 'text-gray-700' }} hover:underline">
                            {{ __(':count student(s) approaching completion', ['count' => $todaysOperations['approaching_completion']]) }}
                        </a>
                    </li>
                    <li>
                        <a href="#locked-students" class="{{ $todaysOperations['locked_students'] > 0 ? 'text-red-600 font-medium' : 'text-gray-700' }} hover:underline">
                            {{ __(':count student(s) locked', ['count' => $todaysOperations['locked_students']]) }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8">

                <h1 class="text-3xl font-bold text-gray-800">
                    Welcome to CDSMS
                </h1>

                <p class="mt-3 text-gray-600">
                    Classic Driving School Management System
                </p>

                <form method="get" action="{{ route('students.index') }}" class="mt-6 flex gap-2 max-w-xl">
                    <input type="text" name="search" placeholder="{{ __('Search students by name, email, or phone') }}" class="flex-1 border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                    <x-primary-button type="submit">{{ __('Search') }}</x-primary-button>
                </form>

                <hr class="my-6">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">

                    <div class="bg-black text-amber-400 p-6 rounded-lg">
                        <h3 class="text-xl font-bold">Students</h3>
                        <p class="text-3xl mt-3">{{ number_format($stats['students']) }}</p>
                    </div>

                    <div class="bg-amber-500 text-black p-6 rounded-lg">
                        <h3 class="text-xl font-bold">Paid Today</h3>
                        <p class="text-3xl mt-3">₦{{ number_format($stats['payments'], 2) }}</p>
                    </div>

                    <div class="bg-black text-amber-400 p-6 rounded-lg">
                        <h3 class="text-xl font-bold">Instructors</h3>
                        <p class="text-3xl mt-3">{{ number_format($stats['instructors']) }}</p>
                    </div>

                    <div class="bg-amber-500 text-black p-6 rounded-lg">
                        <h3 class="text-xl font-bold">Certificates</h3>
                        <p class="text-3xl mt-3">{{ number_format($stats['certificates']) }}</p>
                    </div>

                    <a href="{{ route('leads.index', ['status' => 'new']) }}" class="bg-black text-amber-400 p-6 rounded-lg block hover:bg-gray-900">
                        <h3 class="text-xl font-bold">{{ __('New Leads') }}</h3>
                        <p class="text-3xl mt-3">{{ number_format($stats['new_leads']) }}</p>
                    </a>

                </div>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-6">{{ __('New Students') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">

                    @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $period => $periodLabel)
                        <a href="{{ route('student-registration-report.index', ['period' => $period]) }}" class="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg block">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __($periodLabel) }}</h4>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($newStudentTotals[$period]) }}</p>
                        </a>
                    @endforeach

                </div>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-6">{{ __('Training Statistics') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">

                    @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $period => $periodLabel)
                        <a href="{{ route('training-report.index', ['period' => $period]) }}" class="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg block">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __($periodLabel) }}</h4>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($trainingStats[$period]) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ __('students trained') }}</p>
                        </a>
                    @endforeach

                </div>

                @if ($paymentTotals)
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-6">{{ __('Total Payments') }}</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">

                        @foreach (['week' => 'This Week', 'month' => 'This Month', 'all_time' => 'All Time'] as $period => $periodLabel)
                            <a href="{{ route('payments.index', ['period' => $period]) }}" class="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg block">
                                <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __($periodLabel) }}</h4>
                                <p class="text-xl font-bold text-gray-800 mt-1">₦{{ number_format($paymentTotals[$period], 2) }}</p>
                            </a>
                        @endforeach

                    </div>
                @endif

            </div>

            @if ($outstandingPayments->isNotEmpty())
                <div id="outstanding-payments" class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ __('Outstanding Payments') }}</h3>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="pb-2">{{ __('Student') }}</th>
                                <th class="pb-2">{{ __('Course') }}</th>
                                <th class="pb-2">{{ __('Balance') }}</th>
                                <th class="pb-2">{{ __('Due Date') }}</th>
                                <th class="pb-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($outstandingPayments as $enrollment)
                                <tr>
                                    <td class="py-2">
                                        <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline">
                                            {{ $enrollment->student->name }}
                                        </a>
                                    </td>
                                    <td class="py-2">{{ $enrollment->course->name }}</td>
                                    <td class="py-2">₦{{ number_format($enrollment->balance(), 2) }}</td>
                                    <td class="py-2">{{ optional($enrollment->due_date)->format('Y-m-d') ?? '—' }}</td>
                                    <td class="py-2">
                                        @if ($enrollment->isOverdue())
                                            <x-badge color="red">{{ __('Overdue') }}</x-badge>
                                        @else
                                            <x-badge color="amber">{{ __('Upcoming') }}</x-badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($atRiskEnrollments->isNotEmpty())
                <div id="at-risk-students" class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">{{ __('At-Risk Students') }}</h3>
                        <x-badge color="red">{{ $atRiskEnrollments->count() }} {{ __('Flagged') }}</x-badge>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">{{ __('Active students showing early signs of dropping out (no training in a while) or defaulting (balance due soon) — a proactive watchlist for follow-up before it locks the enrollment on its own.') }}</p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="pb-2 pr-4">{{ __('Student') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Course') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Risk') }}</th>
                                    <th class="pb-2">{{ __('Reason(s)') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($atRiskEnrollments as $enrollment)
                                    <tr>
                                        <td class="py-2 pr-4">
                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline">
                                                {{ $enrollment->student->name }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-4">{{ $enrollment->course->name }}</td>
                                        <td class="py-2 pr-4">
                                            <x-badge :color="$enrollment->riskLevel() === 'high' ? 'red' : 'amber'">{{ __(ucfirst($enrollment->riskLevel())) }}</x-badge>
                                        </td>
                                        <td class="py-2">{{ implode(' · ', $enrollment->riskReasons()) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($revenueLeakage->isNotEmpty())
                <div id="revenue-leakage" class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">{{ __('Revenue Leakage') }}</h3>
                        <x-badge color="red">₦{{ number_format($revenueLeakage->sum('balance'), 2) }} {{ __('Uncollected') }}</x-badge>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">{{ __('Certificate fees on completed enrollments, and services already delivered, that are still unpaid — the work is done, so nothing will prompt collection unless staff act on it.') }}</p>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="pb-2">{{ __('Student') }}</th>
                                <th class="pb-2">{{ __('Item') }}</th>
                                <th class="pb-2">{{ __('Balance') }}</th>
                                <th class="pb-2">{{ __('Delivered') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($revenueLeakage as $leak)
                                <tr>
                                    <td class="py-2">
                                        <a href="{{ route('students.show', $leak['student']->id) }}" class="text-amber-600 hover:underline">
                                            {{ $leak['student']->name }}
                                        </a>
                                    </td>
                                    <td class="py-2">{{ $leak['label'] }}</td>
                                    <td class="py-2">₦{{ number_format($leak['balance'], 2) }}</td>
                                    <td class="py-2">{{ $leak['since']->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @php($upgradeEligible = $upgradeAlerts->filter(fn ($enrollment) => $enrollment->isWithinUpgradeWindow()))
            @if ($upgradeAlerts->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">{{ __('Programme Upgrade Window') }}</h3>
                        @if ($upgradeEligible->isNotEmpty())
                            <x-badge color="amber">🔔 {{ $upgradeEligible->count() }} {{ __('Student(s) Currently Eligible for Upgrade') }}</x-badge>
                        @endif
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="pb-2 pr-4">{{ __('Student') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Programme') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Days Completed') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Status') }}</th>
                                    <th class="pb-2">{{ __('Days Left') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($upgradeAlerts as $enrollment)
                                    @php($daysCompleted = $enrollment->attendedDays())
                                    <tr>
                                        <td class="py-2 pr-4">
                                            @if ($enrollment->canUpgrade() && auth()->user()->isDirector())
                                                <a href="{{ route('enrollments.upgrade.create', $enrollment->id) }}" class="text-amber-600 hover:underline">{{ $enrollment->student->name }}</a>
                                            @else
                                                <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline">{{ $enrollment->student->name }}</a>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}</td>
                                        <td class="py-2 pr-4">{{ $daysCompleted }} / {{ $enrollment->course->totalTrainingDays() }}</td>
                                        <td class="py-2 pr-4">
                                            @if ($daysCompleted < \App\Models\Enrollment::UPGRADE_WINDOW_DAYS)
                                                <x-badge color="green">🟢 {{ __('Eligible') }}</x-badge>
                                            @elseif ($daysCompleted === \App\Models\Enrollment::UPGRADE_WINDOW_DAYS)
                                                <x-badge color="amber">🟠 {{ __('Last Day') }}</x-badge>
                                                <span class="block text-xs text-red-600 mt-0.5">⚠️ {{ __('Upgrade window closes today') }}</span>
                                            @else
                                                <x-badge color="red">🔴 {{ __('Closed') }}</x-badge>
                                            @endif
                                        </td>
                                        <td class="py-2">{{ $enrollment->isWithinUpgradeWindow() ? $enrollment->upgradeDaysRemaining() : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($upgradeEligible->isNotEmpty())
                        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-amber-800 mb-2">📢 {{ __('Students Who Need Upgrade Reminder') }}</h4>
                            <ul class="text-sm text-amber-800 space-y-1 list-disc list-inside">
                                @foreach ($upgradeEligible as $enrollment)
                                    <li>
                                        {{ $enrollment->student->name }} —
                                        @if ($enrollment->upgradeDaysRemaining() > 0)
                                            {{ $enrollment->upgradeDaysRemaining() }} {{ __('day(s) remaining') }}
                                        @else
                                            {{ __('upgrade closes today') }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            @if ($lockedEnrollments->isNotEmpty())
                <div id="locked-students" class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ __('Locked Students') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="pb-2 pr-4">{{ __('Student') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Course') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Reason') }}</th>
                                    <th class="pb-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($lockedEnrollments as $enrollment)
                                    <tr>
                                        <td class="py-2 pr-4">
                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline">
                                                {{ $enrollment->student->name }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-4">{{ $enrollment->course->name }}</td>
                                        <td class="py-2 pr-4">
                                            <x-badge color="red">{{ $enrollment->lockedReasonLabel() }}</x-badge>
                                        </td>
                                        <td class="py-2">
                                            @if ($enrollment->isLockedForExpiredTrainingPeriod() && auth()->user()->isDirector())
                                                <a href="{{ route('enrollments.reactivate.create', $enrollment->id) }}" class="text-sm text-amber-600 hover:underline">{{ __('Reactivate') }}</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($serviceProcessing->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ __('Service Processing') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="pb-2 pr-4">{{ __('Student') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Service') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Started') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Expected Ready') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Progress') }}</th>
                                    <th class="pb-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($serviceProcessing as $studentService)
                                    <tr>
                                        <td class="py-2 pr-4">
                                            <a href="{{ route('students.show', $studentService->student_id) }}" class="text-amber-600 hover:underline">
                                                {{ $studentService->student->name }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-4">{{ $studentService->service->name }}</td>
                                        <td class="py-2 pr-4">{{ $studentService->processing_started_at?->format('M j, Y') ?? '—' }}</td>
                                        <td class="py-2 pr-4">{{ $studentService->expectedReadyAt()?->format('M j, Y') ?? '—' }}</td>
                                        <td class="py-2 pr-4 w-40">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $studentService->processingProgressPercent() ?? 0 }}%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500 whitespace-nowrap">{{ $studentService->processingProgressPercent() ?? 0 }}%</span>
                                            </div>
                                        </td>
                                        <td class="py-2">
                                            @if ($studentService->isOverdueProcessing())
                                                <x-badge color="red">{{ __('Overdue') }}</x-badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if ($learnersPermitRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => "Learner's Permit Requests",
                    'requests' => $learnersPermitRequests,
                    'actionLabel' => 'Mark Obtained',
                    'actionStatus' => 'completed',
                ])
            @endif

            @if ($onlineCertificateRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => 'Online Certificate Requests',
                    'requests' => $onlineCertificateRequests,
                    'actionLabel' => 'Mark Obtained',
                    'actionStatus' => 'completed',
                ])
            @endif

            @if ($driversLicenseRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => "Driver's License Requests",
                    'requests' => $driversLicenseRequests,
                    'actionLabel' => 'Start Processing',
                    'actionStatus' => 'processing',
                ])
            @endif

            @if ($trainingProgress->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">{{ __('Student Training Progress') }}</h3>
                        <a href="{{ route('training-progress.index') }}" class="text-sm text-amber-600 hover:underline">{{ __('View Full List') }}</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="pb-2 pr-4">{{ __('Student') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Programme') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Required') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Completed') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Remaining') }}</th>
                                    <th class="pb-2 pr-4">%</th>
                                    <th class="pb-2">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($trainingProgress as $enrollment)
                                    <tr>
                                        <td class="py-2 pr-4">
                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline">
                                                {{ $enrollment->student->name }}
                                            </a>
                                        </td>
                                        <td class="py-2 pr-4">{{ $enrollment->course->name }} ({{ $enrollment->course->duration_weeks }} {{ __('Weeks') }})</td>
                                        <td class="py-2 pr-4">{{ $enrollment->course->totalTrainingDays() }}</td>
                                        <td class="py-2 pr-4">{{ $enrollment->attendedDays() }}</td>
                                        <td class="py-2 pr-4">{{ $enrollment->remainingTrainingDays() }}</td>
                                        <td class="py-2 pr-4">{{ $enrollment->trainingCompletionPercentage() }}%</td>
                                        <td class="py-2">
                                            @php($label = $enrollment->trainingStatusLabel())
                                            <x-badge :color="match ($label) {
                                                'Completed' => 'blue',
                                                'Expired' => 'red',
                                                default => 'green',
                                            }">{{ __($label) }}</x-badge>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>