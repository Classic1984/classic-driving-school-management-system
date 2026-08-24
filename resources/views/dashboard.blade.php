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

            @if (session('status') === 'service-status-updated')
                <p class="text-sm font-medium text-green-600 mb-4">{{ __(session('serviceStatusMessage', 'Processing status updated successfully.')) }}</p>
            @elseif (session('status') === 'service-status-unchanged')
                <p class="text-sm font-medium text-amber-600 mb-4">⚠️ {{ __(session('serviceStatusMessage', 'No change - that status was already set.')) }}</p>
            @endif

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

            @php
                $operationTileClasses = fn (string $state) => match ($state) {
                    'alert' => 'bg-red-50 border-red-200 hover:bg-red-100',
                    'warn' => 'bg-amber-50 border-amber-200 hover:bg-amber-100',
                    default => 'bg-gray-50 border-gray-200 hover:bg-gray-100',
                };
                $operationLabelClasses = fn (string $state) => match ($state) {
                    'alert' => 'text-red-600',
                    'warn' => 'text-amber-600',
                    default => 'text-gray-500',
                };
                $operationNumberClasses = fn (string $state) => match ($state) {
                    'alert' => 'text-red-700',
                    'warn' => 'text-amber-700',
                    default => 'text-gray-800',
                };
            @endphp

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __("Today's Operations") }}</h3>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @if (auth()->user()->isDirector())
                        @php $state = $todaysOperations['pending_approvals'] > 0 ? 'alert' : 'ok'; @endphp
                        <a href="{{ route('approvals.index') }}" class="rounded-lg border p-4 transition {{ $operationTileClasses($state) }}">
                            <p class="text-2xl font-bold {{ $operationNumberClasses($state) }}">{{ number_format($todaysOperations['pending_approvals']) }}</p>
                            <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses($state) }}">{{ __('Approval(s) Pending') }}</p>
                        </a>
                    @endif

                    <a href="{{ route('training-report.index', ['period' => 'today']) }}" class="rounded-lg border p-4 transition {{ $operationTileClasses('ok') }}">
                        <p class="text-2xl font-bold {{ $operationNumberClasses('ok') }}">{{ number_format($todaysOperations['students_trained']) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses('ok') }}">{{ __('Student(s) Trained Today') }}</p>
                    </a>

                    <a href="{{ route('training-report.index', ['period' => 'today']) }}" class="rounded-lg border p-4 transition {{ $operationTileClasses('ok') }}">
                        <p class="text-2xl font-bold {{ $operationNumberClasses('ok') }}">{{ number_format($todaysOperations['training_sessions']) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses('ok') }}">{{ __('Training Session(s) Logged') }}</p>
                    </a>

                    <a href="{{ route('instructor-activity-report.index', ['period' => 'today']) }}" class="rounded-lg border p-4 transition {{ $operationTileClasses('ok') }}">
                        <p class="text-2xl font-bold {{ $operationNumberClasses('ok') }}">{{ number_format($todaysOperations['instructors_active']) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses('ok') }}">{{ __('Instructor(s) Active Today') }}</p>
                    </a>

                    <a href="{{ route('vehicles.index') }}" class="rounded-lg border p-4 transition {{ $operationTileClasses('ok') }}">
                        <p class="text-2xl font-bold {{ $operationNumberClasses('ok') }}">{{ number_format($todaysOperations['vehicles_in_use']) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses('ok') }}">{{ __('Vehicle(s) In Use Today') }}</p>
                    </a>

                    <a href="{{ route('payments.index') }}" class="rounded-lg border p-4 transition {{ $operationTileClasses('ok') }}">
                        <p class="text-2xl font-bold whitespace-nowrap {{ $operationNumberClasses('ok') }}">₦{{ number_format($todaysOperations['payments_received_today'], 2) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses('ok') }}">{{ __('Received Today') }}</p>
                    </a>

                    @php $state = $todaysOperations['payments_pending_count'] > 0 ? 'alert' : 'ok'; @endphp
                    <a href="#outstanding-payments" class="rounded-lg border p-4 transition {{ $operationTileClasses($state) }}">
                        <p class="text-2xl font-bold {{ $operationNumberClasses($state) }}">{{ number_format($todaysOperations['payments_pending_count']) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses($state) }}">{{ __('Payment(s) Pending') }}</p>
                    </a>

                    @php $state = $todaysOperations['approaching_completion'] > 0 ? 'warn' : 'ok'; @endphp
                    <a href="{{ route('training-progress.index') }}" class="rounded-lg border p-4 transition {{ $operationTileClasses($state) }}">
                        <p class="text-2xl font-bold {{ $operationNumberClasses($state) }}">{{ number_format($todaysOperations['approaching_completion']) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses($state) }}">{{ __('Approaching Completion') }}</p>
                    </a>

                    @php $state = $todaysOperations['locked_students'] > 0 ? 'alert' : 'ok'; @endphp
                    <a href="#locked-students" class="rounded-lg border p-4 transition {{ $operationTileClasses($state) }}">
                        <p class="text-2xl font-bold {{ $operationNumberClasses($state) }}">{{ number_format($todaysOperations['locked_students']) }}</p>
                        <p class="text-xs uppercase tracking-wide mt-1 {{ $operationLabelClasses($state) }}">{{ __('Student(s) Locked') }}</p>
                    </a>
                </div>
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

            @if ($upcomingPayments->isNotEmpty() || $lockedEnrollments->isNotEmpty())
                <div id="outstanding-payments" class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ __('Outstanding Payments') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if ($upcomingPayments->isNotEmpty())
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'upcoming-payments-modal')" class="text-left rounded-lg p-5 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition">
                                <p class="text-xs uppercase tracking-wider text-amber-700 font-semibold">🟠 {{ __('Upcoming') }}</p>
                                <p class="text-3xl font-bold text-amber-700 mt-1">{{ $upcomingPayments->count() }}</p>
                                <p class="text-xs text-amber-600 mt-1">{{ __('Click to view names') }}</p>
                            </button>
                        @endif

                        @if ($lockedEnrollments->isNotEmpty())
                            <button type="button" id="locked-students" x-data x-on:click="$dispatch('open-modal', 'locked-students-modal')" class="text-left rounded-lg p-5 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                <p class="text-xs uppercase tracking-wider text-red-700 font-semibold">🔒 {{ __('Locked') }}</p>
                                <p class="text-3xl font-bold text-red-700 mt-1">{{ $lockedEnrollments->count() }}</p>
                                <p class="text-xs text-red-600 mt-1">{{ __('Click to view names') }}</p>
                            </button>
                        @endif
                    </div>
                </div>

                @if ($upcomingPayments->isNotEmpty())
                    <x-modal name="upcoming-payments-modal">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">🟠 {{ __('Upcoming Payments') }}</h3>
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                @foreach ($upcomingPayments as $enrollment)
                                    <div class="py-2.5 flex items-center justify-between gap-4 text-sm">
                                        <div>
                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
                                            <span class="text-gray-500"> — {{ $enrollment->course->name }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500 whitespace-nowrap">₦{{ number_format($enrollment->balance(), 2) }} · {{ optional($enrollment->due_date)->format('Y-m-d') ?? '—' }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-right">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'upcoming-payments-modal')">{{ __('Close') }}</x-secondary-button>
                            </div>
                        </div>
                    </x-modal>
                @endif

                @if ($lockedEnrollments->isNotEmpty())
                    <x-modal name="locked-students-modal">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">🔒 {{ __('Locked Students') }}</h3>
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                @foreach ($lockedEnrollments as $enrollment)
                                    <div class="py-2.5 flex items-center justify-between gap-4 text-sm">
                                        <div>
                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
                                            <span class="text-gray-500"> — {{ $enrollment->course->name }} · ₦{{ number_format($enrollment->balance(), 2) }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 whitespace-nowrap">
                                            <x-badge color="red">{{ $enrollment->lockedReasonLabel() }}</x-badge>
                                            @if ($enrollment->isLockedForExpiredTrainingPeriod() && auth()->user()->isDirector())
                                                <a href="{{ route('enrollments.reactivate.create', $enrollment->id) }}" class="text-xs text-amber-600 hover:underline">{{ __('Reactivate') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-right">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'locked-students-modal')">{{ __('Close') }}</x-secondary-button>
                            </div>
                        </div>
                    </x-modal>
                @endif
            @endif

            @if ($atRiskEnrollments->isNotEmpty())
                <div id="at-risk-students" class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-gray-800">{{ __('At-Risk Students') }}</h3>
                        <x-badge color="red">{{ $atRiskEnrollments->count() }} {{ __('Flagged') }}</x-badge>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">{{ __('Active students who still owe money and are also showing early signs of dropping out (no training in a while) or defaulting (balance due soon) — a proactive watchlist for follow-up before it locks the enrollment on its own.') }}</p>

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

            @if ($upgradeEligible->isNotEmpty() || $upgradeClosed->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ __('Programme Upgrade Window') }}</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if ($upgradeEligible->isNotEmpty())
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'upgrade-eligible-modal')" class="text-left rounded-lg p-5 bg-green-50 border border-green-200 hover:bg-green-100 transition">
                                <p class="text-xs uppercase tracking-wider text-green-700 font-semibold">🎓 {{ __('Eligible for Upgrade') }}</p>
                                <p class="text-3xl font-bold text-green-700 mt-1">{{ $upgradeEligible->count() }}</p>
                                <p class="text-xs text-green-600 mt-1">{{ __('Click to view names') }}</p>
                            </button>
                        @endif

                        @if ($upgradeClosed->isNotEmpty())
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'upgrade-closed-modal')" class="text-left rounded-lg p-5 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                                <p class="text-xs uppercase tracking-wider text-red-700 font-semibold">⛔ {{ __('Upgrade Window Closed') }}</p>
                                <p class="text-3xl font-bold text-red-700 mt-1">{{ $upgradeClosed->count() }}</p>
                                <p class="text-xs text-red-600 mt-1">{{ __('Click to view names') }}</p>
                            </button>
                        @endif
                    </div>
                </div>

                @if ($upgradeEligible->isNotEmpty())
                    <x-modal name="upgrade-eligible-modal">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">🎓 {{ __('Eligible for Upgrade') }}</h3>
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                @foreach ($upgradeEligible as $enrollment)
                                    <div class="py-2.5 flex items-center justify-between gap-4 text-sm">
                                        <div>
                                            @if ($enrollment->canUpgrade() && auth()->user()->isDirector())
                                                <a href="{{ route('enrollments.upgrade.create', $enrollment->id) }}" class="text-amber-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
                                            @else
                                                <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
                                            @endif
                                            <span class="text-gray-500"> — {{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}</span>
                                        </div>
                                        <span class="text-xs whitespace-nowrap {{ $enrollment->upgradeDaysRemaining() > 0 ? 'text-amber-600' : 'text-red-600 font-semibold' }}">
                                            @if ($enrollment->upgradeDaysRemaining() > 0)
                                                {{ $enrollment->upgradeDaysRemaining() }} {{ __('day(s) left') }}
                                            @else
                                                ⚠️ {{ __('Closes today') }}
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-right">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'upgrade-eligible-modal')">{{ __('Close') }}</x-secondary-button>
                            </div>
                        </div>
                    </x-modal>
                @endif

                @if ($upgradeClosed->isNotEmpty())
                    <x-modal name="upgrade-closed-modal">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">⛔ {{ __('Upgrade Window Closed') }}</h3>
                            <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                                @foreach ($upgradeClosed as $enrollment)
                                    <div class="py-2.5 text-sm">
                                        <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
                                        <span class="text-gray-500"> — {{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-right">
                                <x-secondary-button x-on:click="$dispatch('close-modal', 'upgrade-closed-modal')">{{ __('Close') }}</x-secondary-button>
                            </div>
                        </div>
                    </x-modal>
                @endif
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