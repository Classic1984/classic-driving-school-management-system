<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="border-l-2 border-amber-500 pl-3">
                <h2 class="font-extrabold text-2xl text-gray-900 leading-tight">
                    Classic Driving School & Son Nigeria Limited
                </h2>
                <p class="text-sm text-gray-500">{{ __('Safety. Skill. Confidence.') }}</p>
            </div>
            <div class="flex items-center gap-3 rounded-xl bg-amber-50 ring-1 ring-amber-200 px-4 py-2">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-black text-amber-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                </span>
                <div>
                    <p class="text-sm font-bold text-gray-900 leading-none">{{ __('CDSMS') }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('Version 1.0') }}</p>
                </div>
            </div>
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
                $greetingIcon = match (true) {
                    now()->hour < 17 => 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z',
                    default => 'M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z',
                };
                $firstName = \Illuminate\Support\Str::of(auth()->user()->name)->before(' ');

                $kpiColors = [
                    'purple' => ['icon' => 'bg-purple-500/10 text-purple-400', 'value' => 'text-purple-400'],
                    'blue' => ['icon' => 'bg-blue-500/10 text-blue-400', 'value' => 'text-blue-400'],
                    'amber' => ['icon' => 'bg-amber-500/10 text-amber-400', 'value' => 'text-amber-400'],
                    'green' => ['icon' => 'bg-green-500/10 text-green-400', 'value' => 'text-green-400'],
                    'teal' => ['icon' => 'bg-teal-500/10 text-teal-400', 'value' => 'text-teal-400'],
                    'indigo' => ['icon' => 'bg-indigo-500/10 text-indigo-400', 'value' => 'text-indigo-400'],
                    'red' => ['icon' => 'bg-red-500/10 text-red-400', 'value' => 'text-red-400'],
                ];

                $kpiCards = [
                    [
                        'key' => 'active_students', 'label' => 'Active Students', 'color' => 'purple', 'currency' => false,
                        'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
                        'subtext' => $newStudentTotals['month'] > 0 ? trans_choice('{1} :count new student this month|[2,*] :count new students this month', $newStudentTotals['month'], ['count' => $newStudentTotals['month']]) : __('No new students this month'),
                    ],
                    [
                        'key' => 'training_today', 'label' => 'Training Today', 'color' => 'blue', 'currency' => false,
                        'icon' => 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
                        'subtext' => $kpis['training_today'] > 0 ? trans_choice('{1} :count student trained so far today|[2,*] :count students trained so far today', $kpis['training_today'], ['count' => $kpis['training_today']]) : __('No training scheduled'),
                    ],
                    [
                        'key' => 'pending_payments', 'label' => 'Pending Payments', 'color' => 'amber', 'currency' => true,
                        'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z',
                        'subtext' => $todaysOperations['payments_pending_count'] > 0 ? trans_choice('{1} :count enrollment with a balance|[2,*] :count enrollments with a balance', $todaysOperations['payments_pending_count'], ['count' => $todaysOperations['payments_pending_count']]) : __('No pending payments'),
                    ],
                    [
                        'key' => 'completed_training', 'label' => 'Completed Training', 'color' => 'green', 'currency' => false,
                        'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                        'subtext' => $kpis['completed_training'] > 0 ? trans_choice('{1} :count enrollment completed overall|[2,*] :count enrollments completed overall', $kpis['completed_training'], ['count' => $kpis['completed_training']]) : __('No training completed yet'),
                    ],
                    [
                        'key' => 'active_vehicles', 'label' => 'Active Vehicles', 'color' => 'teal', 'currency' => false,
                        'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12',
                        'subtext' => $todaysOperations['vehicles_in_use'] > 0 ? trans_choice('{1} :count in use today|[2,*] :count in use today', $todaysOperations['vehicles_in_use'], ['count' => $todaysOperations['vehicles_in_use']]) : __('None in use today'),
                    ],
                    [
                        'key' => 'certificates_due', 'label' => 'Certificates Due', 'color' => 'indigo', 'currency' => false,
                        'icon' => 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0',
                        'subtext' => $kpis['certificates_due'] > 0 ? trans_choice('{1} :count enrollment awaiting a certificate|[2,*] :count enrollments awaiting a certificate', $kpis['certificates_due'], ['count' => $kpis['certificates_due']]) : __('No certificates due'),
                    ],
                    [
                        'key' => 'revenue_leakage', 'label' => 'Revenue Leakage', 'color' => 'red', 'currency' => true,
                        'icon' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941',
                        'subtext' => $revenueLeakage->isNotEmpty() ? trans_choice('{1} :count uncollected item|[2,*] :count uncollected items', $revenueLeakage->count(), ['count' => $revenueLeakage->count()]) : __('No revenue leakage'),
                    ],
                    [
                        'key' => 'at_risk_students', 'label' => 'At-Risk Students', 'color' => 'amber', 'currency' => false,
                        'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
                        'subtext' => $kpis['at_risk_students'] > 0 ? trans_choice('{1} :count student flagged for follow-up|[2,*] :count students flagged for follow-up', $kpis['at_risk_students'], ['count' => $kpis['at_risk_students']]) : __('No at-risk students'),
                    ],
                ];
            @endphp

            @if (session('status') === 'service-status-updated')
                <p class="text-sm font-medium text-green-600 mb-4">{{ __(session('serviceStatusMessage', 'Processing status updated successfully.')) }}</p>
            @elseif (session('status') === 'service-status-unchanged')
                <p class="text-sm font-medium text-amber-600 mb-4">⚠️ {{ __(session('serviceStatusMessage', 'No change - that status was already set.')) }}</p>
            @endif

            <div class="bg-black text-white rounded-xl p-8 mb-6">
                <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $greetingIcon }}" /></svg>
                    </span>
                    <h1 class="text-2xl font-bold">{{ $greeting }}, <span class="text-amber-400">{{ $firstName }}</span> 👋</h1>
                </div>
                <p class="mt-3 flex items-center gap-1.5 text-sm text-gray-300">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    {{ now()->format('l, F j, Y') }}
                </p>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    @foreach ($kpiCards as $card)
                        @php $accent = $kpiColors[$card['color']]; @endphp
                        <div class="bg-gray-900 rounded-lg p-4 ring-1 ring-amber-400/40">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" /></svg>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($card['label']) }}</p>
                            </div>
                            <p class="text-2xl font-bold mt-3 whitespace-nowrap {{ $accent['value'] }}">
                                @if ($card['currency'])
                                    ₦{{ number_format($kpis[$card['key']], 2) }}
                                @else
                                    {{ number_format($kpis[$card['key']]) }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs font-medium text-gray-300">{{ $card['subtext'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @php
                $academicCapPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';

                $quickStatsAccent = [
                    'purple' => ['icon' => 'bg-purple-500/10 text-purple-400', 'value' => 'text-purple-400'],
                    'blue' => ['icon' => 'bg-blue-500/10 text-blue-400', 'value' => 'text-blue-400'],
                    'teal' => ['icon' => 'bg-teal-500/10 text-teal-400', 'value' => 'text-teal-400'],
                    'indigo' => ['icon' => 'bg-indigo-500/10 text-indigo-400', 'value' => 'text-indigo-400'],
                    'amber' => ['icon' => 'bg-amber-500/10 text-amber-400', 'value' => 'text-amber-400'],
                    'sky' => ['icon' => 'bg-sky-500/10 text-sky-400', 'value' => 'text-sky-400'],
                    'orange' => ['icon' => 'bg-orange-500/10 text-orange-400', 'value' => 'text-orange-400'],
                    'red' => ['icon' => 'bg-red-500/10 text-red-400', 'value' => 'text-red-400'],
                ];

                $quickStats = [
                    [
                        'title' => 'Students', 'subtitle' => 'Total registered students', 'href' => route('students.index'),
                        'value' => number_format($stats['students']), 'color' => 'purple',
                        'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
                    ],
                    [
                        'title' => 'Instructors', 'subtitle' => 'Total active instructors', 'href' => route('instructors.index'),
                        'value' => number_format($stats['instructors']), 'color' => 'blue',
                        'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z',
                    ],
                    [
                        'title' => 'Vehicles', 'subtitle' => 'Total active vehicles', 'href' => route('vehicles.index'),
                        'value' => number_format($kpis['active_vehicles']), 'color' => 'teal',
                        'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12',
                    ],
                    [
                        'title' => 'Certificates', 'subtitle' => 'Total certificates issued', 'href' => route('certificates.index'),
                        'value' => number_format($stats['certificates']), 'color' => 'indigo',
                        'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                    ],
                    [
                        'title' => 'Paid Today', 'subtitle' => 'Total amount received today', 'href' => route('payments.index'),
                        'value' => '₦'.number_format($stats['payments'], 2), 'color' => 'amber',
                        'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z',
                    ],
                    [
                        'title' => 'Trainings Today', 'subtitle' => 'Training sessions logged today', 'href' => route('training-report.index', ['period' => 'today']),
                        'value' => number_format($todaysOperations['training_sessions']), 'color' => 'sky',
                        'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
                    ],
                    [
                        'title' => 'Pending Payments', 'subtitle' => 'Total pending payments', 'href' => '#outstanding-payments',
                        'value' => '₦'.number_format($kpis['pending_payments'], 2), 'color' => 'orange',
                        'icon' => 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5',
                    ],
                    [
                        'title' => 'At-Risk Students', 'subtitle' => 'Students needing attention', 'href' => '#at-risk-students',
                        'value' => number_format($kpis['at_risk_students']), 'color' => 'red',
                        'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z',
                    ],
                ];
            @endphp

            <div class="bg-black text-white rounded-xl p-8 mt-6">
                <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 13.5 3l-1.5 7.5h8.25L10.5 21l1.5-7.5H3.75Z" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-bold">{{ __('Quick Stats') }}</h3>
                        <p class="text-sm font-medium text-gray-200">{{ __('Key numbers at a glance') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    @foreach ($quickStats as $card)
                        @php $accent = $quickStatsAccent[$card['color']]; @endphp
                        <a href="{{ $card['href'] }}" class="flex flex-col text-left bg-gray-900 rounded-lg p-4 ring-1 ring-amber-400/40 transition hover:ring-amber-400/70">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" /></svg>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($card['title']) }}</p>
                            </div>

                            <p class="text-2xl font-bold mt-3 whitespace-nowrap {{ $accent['value'] }}">{{ $card['value'] }}</p>

                            <p class="mt-1 text-xs font-medium text-gray-300">{{ __($card['subtitle']) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8">

                <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-amber-50 via-amber-50 to-white p-8">
                    <a href="{{ route('leads.index', ['status' => 'new']) }}" class="relative inline-flex items-center gap-2 rounded-full bg-white ring-1 ring-amber-200 px-4 py-1.5 text-sm font-semibold text-amber-700 hover:bg-amber-50 transition mb-4">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        {{ __('New Leads') }}: {{ number_format($stats['new_leads']) }}
                    </a>

                    <h1 class="relative text-3xl font-bold text-gray-800">
                        {{ __('Welcome to') }} <span class="text-amber-500">CDSMS</span>
                    </h1>

                    <p class="relative mt-3 text-gray-600">
                        {{ __('Classic Driving School Management System') }}
                    </p>

                    <form method="get" action="{{ route('students.index') }}" class="relative mt-6 flex gap-2 max-w-xl">
                        <div class="relative flex-1">
                            <svg class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <input type="text" name="search" placeholder="{{ __('Search students by name, email, or phone') }}" class="w-full rounded-full border-gray-300 pl-11 focus:border-amber-500 focus:ring-amber-500 shadow-sm">
                        </div>
                        <button type="submit" class="rounded-full bg-black px-6 py-2 text-sm font-bold text-amber-400 hover:bg-gray-800 transition">{{ __('Search') }}</button>
                    </form>
                </div>

            </div>

            <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4 mt-8 mb-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Needs Attention Today') }}</h2>
            </div>

            @php
                $operationIcons = [
                    'pending_approvals' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
                    'students_trained' => 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
                    'training_sessions' => 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5',
                    'instructors_active' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z',
                    'vehicles_in_use' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12',
                    'payments_received_today' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z',
                    'payments_pending_count' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z',
                    'approaching_completion' => 'M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5',
                    'locked_students' => 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z',
                ];

                $operationColors = [
                    'indigo' => ['icon' => 'bg-indigo-500/10 text-indigo-400', 'value' => 'text-indigo-400', 'ring' => 'ring-indigo-400/60'],
                    'blue' => ['icon' => 'bg-blue-500/10 text-blue-400', 'value' => 'text-blue-400', 'ring' => 'ring-blue-400/60'],
                    'green' => ['icon' => 'bg-green-500/10 text-green-400', 'value' => 'text-green-400', 'ring' => 'ring-green-400/60'],
                    'orange' => ['icon' => 'bg-orange-500/10 text-orange-400', 'value' => 'text-orange-400', 'ring' => 'ring-orange-400/60'],
                    'sky' => ['icon' => 'bg-sky-500/10 text-sky-400', 'value' => 'text-sky-400', 'ring' => 'ring-sky-400/60'],
                    'purple' => ['icon' => 'bg-purple-500/10 text-purple-400', 'value' => 'text-purple-400', 'ring' => 'ring-purple-400/60'],
                    'red' => ['icon' => 'bg-red-500/10 text-red-400', 'value' => 'text-red-400', 'ring' => 'ring-red-400/60', 'dot' => 'bg-red-400'],
                    'amber' => ['icon' => 'bg-amber-500/10 text-amber-400', 'value' => 'text-amber-400', 'ring' => 'ring-amber-400/60', 'dot' => 'bg-amber-400'],
                    'emerald' => ['icon' => 'bg-emerald-500/10 text-emerald-400', 'value' => 'text-emerald-400', 'ring' => 'ring-emerald-400/70'],
                ];

                $operationRows = collect([
                    [
                        'key' => 'pending_approvals', 'show' => auth()->user()->isDirector(),
                        'label' => 'Approval(s) Pending', 'description' => 'Applications awaiting approval',
                        'value' => number_format($todaysOperations['pending_approvals']),
                        'state' => $todaysOperations['pending_approvals'] > 0 ? 'alert' : 'ok', 'color' => 'indigo',
                        'href' => route('approvals.index'),
                    ],
                    [
                        'key' => 'students_trained', 'show' => true,
                        'label' => 'Student(s) Trained Today', 'description' => 'Students who completed training',
                        'value' => number_format($todaysOperations['students_trained']),
                        'state' => 'ok', 'color' => 'blue',
                        'href' => route('training-report.index', ['period' => 'today']),
                    ],
                    [
                        'key' => 'training_sessions', 'show' => true,
                        'label' => 'Training Session(s) Logged', 'description' => 'Total training sessions recorded',
                        'value' => number_format($todaysOperations['training_sessions']),
                        'state' => 'ok', 'color' => 'green',
                        'href' => route('training-report.index', ['period' => 'today']),
                    ],
                    [
                        'key' => 'instructors_active', 'show' => true,
                        'label' => 'Instructor(s) Active Today', 'description' => 'Instructors who are actively training',
                        'value' => number_format($todaysOperations['instructors_active']),
                        'state' => 'ok', 'color' => 'orange',
                        'href' => route('instructor-activity-report.index', ['period' => 'today']),
                    ],
                    [
                        'key' => 'vehicles_in_use', 'show' => true,
                        'label' => 'Vehicle(s) In Use Today', 'description' => 'Vehicles currently in use',
                        'value' => number_format($todaysOperations['vehicles_in_use']),
                        'state' => 'ok', 'color' => 'sky',
                        'href' => route('vehicles.index'),
                    ],
                    [
                        'key' => 'payments_pending_count', 'show' => true,
                        'label' => 'Payment(s) Pending', 'description' => 'Enrollments with an outstanding balance',
                        'value' => number_format($todaysOperations['payments_pending_count']),
                        'state' => $todaysOperations['payments_pending_count'] > 0 ? 'alert' : 'ok', 'color' => 'red',
                        'href' => '#outstanding-payments',
                    ],
                    [
                        'key' => 'approaching_completion', 'show' => true,
                        'label' => 'Approaching Completion', 'description' => 'Students nearing their training-day limit',
                        'value' => number_format($todaysOperations['approaching_completion']),
                        'state' => $todaysOperations['approaching_completion'] > 0 ? 'warn' : 'ok', 'color' => 'amber',
                        'modal' => $approachingCompletionEnrollments->isNotEmpty() ? 'approaching-completion-modal' : null,
                    ],
                    [
                        'key' => 'locked_students', 'show' => true,
                        'label' => 'Student(s) Locked', 'description' => 'Students currently locked out',
                        'value' => number_format($todaysOperations['locked_students']),
                        'state' => $todaysOperations['locked_students'] > 0 ? 'alert' : 'ok', 'color' => 'purple',
                        'href' => '#locked-students',
                    ],
                    [
                        'key' => 'payments_received_today', 'show' => true, 'highlight' => true,
                        'label' => 'Revenue Today', 'description' => 'Total revenue generated today',
                        'value' => '₦'.number_format($todaysOperations['payments_received_today'], 2),
                        'state' => 'ok', 'color' => 'emerald',
                        'href' => route('payments.index'),
                    ],
                ])->filter(fn (array $row) => $row['show'])->values();

                // The bento layout below gives Revenue Today a large hero cell,
                // puts the two attention-needing metrics in medium cards, and
                // keeps the rest as a quiet supporting strip.
                $heroRow = $operationRows->firstWhere('key', 'payments_received_today');
                $midKeys = ['payments_pending_count', 'approaching_completion'];
                $midRows = $operationRows->whereIn('key', $midKeys)->values();
                $smallRows = $operationRows->reject(
                    fn (array $row) => $row['key'] === 'payments_received_today' || in_array($row['key'], $midKeys, true)
                )->values();

                $operationAccent = fn (array $row) => $operationColors[$row['state'] === 'alert' ? 'red' : ($row['state'] === 'warn' ? 'amber' : $row['color'])];
                $operationTag = fn (array $row) => ! empty($row['modal']) ? 'button' : (array_key_exists('modal', $row) ? 'div' : 'a');
            @endphp

            <div class="bg-black text-white rounded-xl p-8 mb-6">
                <div class="flex flex-wrap items-start justify-between gap-4 border-b border-white/10 pb-5">
                    <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 13.5 3l-1.5 7.5h8.25L10.5 21l1.5-7.5H3.75Z" /></svg>
                        </span>
                        <div>
                            <h3 class="text-2xl font-bold">{{ __("Today's Operations") }}</h3>
                            <p class="text-sm font-medium text-gray-200">{{ __('Real-time overview of key activities') }}</p>
                        </div>
                    </div>
                    <div
                        x-data="{
                            now: new Date(),
                            months: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                            get dateLabel() {
                                return this.months[this.now.getMonth()] + ' ' + this.now.getDate() + ', ' + this.now.getFullYear();
                            },
                            get timeLabel() {
                                let hours = this.now.getHours();
                                const minutes = String(this.now.getMinutes()).padStart(2, '0');
                                const suffix = hours >= 12 ? 'PM' : 'AM';
                                hours = hours % 12 || 12;
                                return String(hours).padStart(2, '0') + ':' + minutes + ' ' + suffix;
                            },
                        }"
                        x-init="setInterval(() => now = new Date(), 1000)"
                        class="flex items-center gap-2 pt-1 text-xs font-semibold uppercase tracking-wider text-gray-300"
                    >
                        <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-400 animate-pulse"></span>
                        <span x-text="dateLabel">{{ now()->format('M j, Y') }}</span>
                        <span class="text-gray-600">&middot;</span>
                        <span x-text="timeLabel">{{ now()->format('h:i A') }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 lg:grid-rows-2 gap-3 mt-6">
                    @php $heroAccent = $operationColors['emerald']; $heroTag = $operationTag($heroRow); @endphp
                    <{{ $heroTag }}
                        @if ($heroTag === 'a') href="{{ $heroRow['href'] }}" @endif
                        @if ($heroTag === 'button') type="button" x-data x-on:click="$dispatch('open-modal', '{{ $heroRow['modal'] }}')" @endif
                        class="group relative flex flex-col justify-between overflow-hidden rounded-xl bg-gradient-to-br from-emerald-500/15 via-gray-900 to-gray-900 p-5 text-left ring-1 ring-emerald-400/40 transition hover:ring-emerald-400/70 sm:col-span-2 lg:row-span-2"
                    >
                        {{-- Emitted before the label row so the amount precedes the label in the
                             page source (screen readers / text search read amount-then-label),
                             then repositioned visually above/below via flex `order`. --}}
                        <p class="order-2 mt-3 text-3xl font-extrabold whitespace-nowrap {{ $heroAccent['value'] }}">{{ $heroRow['value'] }}</p>

                        <div class="order-1 flex items-center gap-2.5">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $heroAccent['icon'] }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons[$heroRow['key']] }}" /></svg>
                            </span>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($heroRow['label']) }}</p>
                        </div>

                        <p class="order-3 mt-2 text-xs font-medium text-gray-300">{{ __($heroRow['description']) }}</p>
                    </{{ $heroTag }}>

                    @foreach ($midRows as $row)
                        @php $accent = $operationAccent($row); $tag = $operationTag($row); @endphp
                        <{{ $tag }}
                            @if ($tag === 'a') href="{{ $row['href'] }}" @endif
                            @if ($tag === 'button') type="button" x-data x-on:click="$dispatch('open-modal', '{{ $row['modal'] }}')" @endif
                            class="flex items-center gap-3 rounded-xl bg-gray-900 p-4 text-left ring-1 {{ $accent['ring'] }} transition hover:ring-2 sm:col-span-2"
                        >
                            <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons[$row['key']] }}" /></svg>
                                @if ($row['state'] !== 'ok')
                                    <span class="absolute -top-1 -right-1 h-2.5 w-2.5 rounded-full {{ $accent['dot'] }} ring-2 ring-gray-900 animate-pulse"></span>
                                @endif
                            </span>
                            <div class="flex min-w-0 flex-col">
                                <p class="order-2 text-xl font-bold whitespace-nowrap {{ $accent['value'] }}">{{ $row['value'] }}</p>
                                <p class="order-1 truncate text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($row['label']) }}</p>
                            </div>
                        </{{ $tag }}>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mt-3">
                    @foreach ($smallRows as $row)
                        @php $accent = $operationAccent($row); $tag = $operationTag($row); @endphp
                        <{{ $tag }}
                            @if ($tag === 'a') href="{{ $row['href'] }}" @endif
                            @if ($tag === 'button') type="button" x-data x-on:click="$dispatch('open-modal', '{{ $row['modal'] }}')" @endif
                            class="flex flex-col text-left rounded-lg bg-white/5 p-3 ring-1 ring-white/10 transition hover:bg-white/10"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md {{ $accent['icon'] }}">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons[$row['key']] }}" /></svg>
                            </span>
                            <p class="mt-2 text-lg font-bold whitespace-nowrap {{ $accent['value'] }}">{{ $row['value'] }}</p>
                            <p class="mt-0.5 text-[11px] font-semibold uppercase tracking-wide leading-tight text-gray-300">{{ __($row['label']) }}</p>
                        </{{ $tag }}>
                    @endforeach
                </div>
            </div>

            @php
                $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
                $upArrowIconPath = 'M8.25 6.75 12 3m0 0 3.75 3.75M12 3v18';
                $minusCircleIconPath = 'M15 12H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
            @endphp

            <div class="bg-black text-white rounded-xl p-8 mt-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-2xl font-bold">{{ __("Today's Attendance") }}</h3>
                            <p class="text-sm font-medium text-gray-200">{{ __('See how many students are present and absent today.') }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-900 ring-1 ring-amber-400/40 px-4 py-2 text-sm font-semibold text-white">
                        <svg class="h-4 w-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        {{ now()->format('M j, Y') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'present-today-modal')" class="flex flex-col text-left bg-gray-900 rounded-lg p-4 ring-1 ring-emerald-400/50 transition hover:ring-emerald-400/80">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                            </span>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __('Present') }}</p>
                        </div>

                        <p class="text-2xl font-bold mt-3 whitespace-nowrap text-emerald-400">{{ $presentToday->count() }}</p>

                        <p class="mt-1 text-xs font-medium text-gray-300">{{ __('Click to view names') }}</p>
                    </button>

                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'absent-today-modal')" class="flex flex-col text-left bg-gray-900 rounded-lg p-4 ring-1 ring-red-400/50 transition hover:ring-red-400/80">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-red-500/10 text-red-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                            </span>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __('Absent') }}</p>
                        </div>

                        <p class="text-2xl font-bold mt-3 whitespace-nowrap text-red-400">{{ $absentToday->count() }}</p>

                        <p class="mt-1 text-xs font-medium text-gray-300">{{ __('Click to view names') }}</p>
                    </button>
                </div>
            </div>

            <x-modal name="present-today-modal">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">🟢 {{ __('Students Present Today') }}</h3>
                    @if ($presentToday->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('No one has checked in yet today.') }}</p>
                    @else
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                            @foreach ($presentToday as $attendance)
                                <div class="py-2.5 text-sm">
                                    <div class="flex items-center justify-between gap-4">
                                        <a href="{{ route('students.show', $attendance->student_id) }}" class="text-amber-600 hover:underline font-medium">{{ $attendance->student->name }}</a>
                                        <span class="text-xs text-gray-500 whitespace-nowrap">{{ __('Checked in') }}: {{ $attendance->created_at->format('g:i A') }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ $attendance->course->name }}
                                        · {{ $attendance->type ? ucfirst($attendance->type) : '—' }} {{ __('Training') }}
                                        · {{ __('Instructor') }}: {{ $attendance->instructor->name ?? '—' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4 text-right">
                        <x-secondary-button x-on:click="$dispatch('close-modal', 'present-today-modal')">{{ __('Close') }}</x-secondary-button>
                    </div>
                </div>
            </x-modal>

            <x-modal name="absent-today-modal">
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">🔴 {{ __('Students Absent Today') }}</h3>
                    @if ($absentToday->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('Everyone expected today has checked in.') }}</p>
                    @else
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                            @foreach ($absentToday as $enrollment)
                                <div class="py-2.5 flex items-center justify-between gap-4 text-sm">
                                    <div>
                                        <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
                                        <span class="text-gray-500"> — {{ $enrollment->course->name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4 text-right">
                        <x-secondary-button x-on:click="$dispatch('close-modal', 'absent-today-modal')">{{ __('Close') }}</x-secondary-button>
                    </div>
                </div>
            </x-modal>

            @if ($upcomingPayments->isNotEmpty() || $lockedEnrollments->isNotEmpty())
                <div id="outstanding-payments" class="bg-white shadow-sm ring-1 ring-amber-200 rounded-xl p-8 mt-6">
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

            @if ($approachingCompletionEnrollments->isNotEmpty())
                <x-modal name="approaching-completion-modal">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">⏳ {{ __('Approaching Completion') }}</h3>
                        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100">
                            @foreach ($approachingCompletionEnrollments as $enrollment)
                                <div class="py-2.5 flex items-center justify-between gap-4 text-sm">
                                    <div>
                                        <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
                                        <span class="text-gray-500"> — {{ $enrollment->course->name }}</span>
                                    </div>
                                    <div class="whitespace-nowrap text-gray-500">
                                        {{ trans_choice('{1} :count training day remaining|[2,*] :count training days remaining', $enrollment->remainingDays, ['count' => $enrollment->remainingDays]) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-right">
                            <x-secondary-button x-on:click="$dispatch('close-modal', 'approaching-completion-modal')">{{ __('Close') }}</x-secondary-button>
                        </div>
                    </div>
                </x-modal>
            @endif

            @if ($atRiskEnrollments->isNotEmpty())
                <div id="at-risk-students" class="bg-white shadow-sm ring-1 ring-amber-200 rounded-xl p-8 mt-6">
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
                <div id="revenue-leakage" class="bg-white shadow-sm ring-1 ring-amber-200 rounded-xl p-8 mt-6">
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
                <div class="bg-white shadow-sm ring-1 ring-amber-200 rounded-xl p-8 mt-6">
                    <div class="flex items-center gap-4 mb-5">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-white">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                        </span>
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Programme Upgrade Window') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Check eligible students for programme upgrades.') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if ($upgradeEligible->isNotEmpty())
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'upgrade-eligible-modal')" class="group relative flex items-center justify-between gap-4 overflow-hidden rounded-2xl text-left bg-green-50/60 ring-1 ring-green-100 border-l-4 border-green-500 p-6 transition hover:shadow-md">
                                <svg class="pointer-events-none absolute -right-4 -bottom-4 h-28 w-28 text-green-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $upArrowIconPath }}" /></svg>
                                <div class="relative flex flex-1 items-center gap-4 min-w-0">
                                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-green-500 text-white">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $upArrowIconPath }}" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wide text-green-700">{{ __('Eligible for Upgrade') }}</p>
                                        <p class="text-3xl font-extrabold break-words text-green-700 mt-1">{{ $upgradeEligible->count() }}</p>
                                        <p class="mt-1 text-sm text-green-600">{{ __('Click to view names') }}</p>
                                    </div>
                                </div>
                                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/70 text-green-600 transition group-hover:translate-x-0.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                </span>
                            </button>
                        @endif

                        @if ($upgradeClosed->isNotEmpty())
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'upgrade-closed-modal')" class="group relative flex items-center justify-between gap-4 overflow-hidden rounded-2xl text-left bg-red-50/60 ring-1 ring-red-100 border-l-4 border-red-500 p-6 transition hover:shadow-md">
                                <svg class="pointer-events-none absolute -right-4 -bottom-4 h-28 w-28 text-red-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $minusCircleIconPath }}" /></svg>
                                <div class="relative flex flex-1 items-center gap-4 min-w-0">
                                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-red-500 text-white">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $minusCircleIconPath }}" /></svg>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wide text-red-700">{{ __('Upgrade Window Closed') }}</p>
                                        <p class="text-3xl font-extrabold break-words text-red-700 mt-1">{{ $upgradeClosed->count() }}</p>
                                        <p class="mt-1 text-sm text-red-600">{{ __('Click to view names') }}</p>
                                    </div>
                                </div>
                                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/70 text-red-600 transition group-hover:translate-x-0.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                </span>
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

            <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4 mt-8 mb-2">
                <h2 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Detailed Breakdown') }}</h2>
            </div>

            @php
                $periodIllustration = [
                    'today' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z',
                    'week' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941',
                    'month' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                    'year' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
                ];
            @endphp

            @php
                $newStudentsDarkAccent = [
                    'today' => ['icon' => 'bg-violet-500/10 text-violet-400', 'value' => 'text-violet-400'],
                    'week' => ['icon' => 'bg-green-500/10 text-green-400', 'value' => 'text-green-400'],
                    'month' => ['icon' => 'bg-amber-500/10 text-amber-400', 'value' => 'text-amber-400'],
                    'year' => ['icon' => 'bg-blue-500/10 text-blue-400', 'value' => 'text-blue-400'],
                ];
                $newStudentIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
            @endphp

            <div class="bg-black text-white rounded-xl p-8 mt-8">
                <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-bold">{{ __('New Students') }}</h3>
                        <p class="text-sm font-medium text-gray-200">{{ __('Track new student registrations') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $period => $periodLabel)
                        @php $accent = $newStudentsDarkAccent[$period]; @endphp
                        <a href="{{ route('student-registration-report.index', ['period' => $period]) }}" class="flex flex-col text-left bg-gray-900 rounded-lg p-4 ring-1 ring-amber-400/40 transition hover:ring-amber-400/70">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $newStudentIconPath }}" /></svg>
                                </span>
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($periodLabel) }}</p>
                            </div>

                            <p class="text-2xl font-bold mt-3 whitespace-nowrap {{ $accent['value'] }}">{{ number_format($newStudentTotals[$period]) }}</p>

                            <p class="mt-1 text-xs font-medium text-gray-300">{{ __('New students registered :period', ['period' => strtolower($periodLabel)]) }}</p>
                        </a>
                    @endforeach
                </div>
            </div>

            @php
                $absenceIcon = [
                    'today' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                    'week' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941',
                    'month' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
                    'year' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
                ];
                $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';

                $periodDarkAccent = [
                    'today' => ['icon' => 'bg-violet-500/10 text-violet-400', 'value' => 'text-violet-400'],
                    'week' => ['icon' => 'bg-green-500/10 text-green-400', 'value' => 'text-green-400'],
                    'month' => ['icon' => 'bg-amber-500/10 text-amber-400', 'value' => 'text-amber-400'],
                    'year' => ['icon' => 'bg-blue-500/10 text-blue-400', 'value' => 'text-blue-400'],
                ];

                $periodTables = [
                    [
                        'title' => 'Training Statistics', 'subtitle' => 'Overview of student training performance',
                        'headerIcon' => 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
                        'countLabel' => 'Students Trained', 'route' => 'training-report.index', 'rowIcon' => $calendarIconPath, 'stats' => $trainingStats,
                        'describe' => fn (string $periodLabel) => __('Total students trained :period', ['period' => strtolower($periodLabel)]),
                    ],
                    [
                        'title' => 'Absences', 'subtitle' => 'Track student absences over time',
                        'headerIcon' => 'M17 20h5v-1a4 4 0 0 0-3-3.87M9 20H4v-1a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm7-4h.008v.008H19V9.87Zm-1.5 1.5 3 3m0-3-3 3',
                        'countLabel' => 'Students Absent', 'route' => 'absence-report.index', 'rowIcon' => null, 'stats' => $absenceStats,
                        'describe' => fn (string $periodLabel) => __('Total students absent :period', ['period' => strtolower($periodLabel)]),
                    ],
                ];
            @endphp

            @foreach ($periodTables as $table)
                <div class="bg-black text-white rounded-xl p-8 mt-6">
                    <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $table['headerIcon'] }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-2xl font-bold">{{ __($table['title']) }}</h3>
                            <p class="text-sm font-medium text-gray-200">{{ __($table['subtitle']) }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $period => $periodLabel)
                            @php $accent = $periodDarkAccent[$period]; @endphp
                            <a href="{{ route($table['route'], ['period' => $period]) }}" class="flex flex-col text-left bg-gray-900 rounded-lg p-4 ring-1 ring-amber-400/40 transition hover:ring-amber-400/70">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $table['rowIcon'] ?? $absenceIcon[$period] }}" /></svg>
                                    </span>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($periodLabel) }}</p>
                                </div>

                                <p class="text-2xl font-bold mt-3 whitespace-nowrap {{ $accent['value'] }}">{{ number_format($table['stats'][$period]) }}</p>

                                <p class="mt-1 text-xs font-medium text-gray-300">{{ $table['describe']($periodLabel) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if ($paymentTotals)
                @php
                    $paymentDarkAccent = [
                        'week' => ['icon' => 'bg-amber-500/10 text-amber-400', 'value' => 'text-amber-400'],
                        'month' => ['icon' => 'bg-green-500/10 text-green-400', 'value' => 'text-green-400'],
                        'all_time' => ['icon' => 'bg-violet-500/10 text-violet-400', 'value' => 'text-violet-400'],
                    ];
                    $paymentPeriodIcon = [
                        'week' => $periodIllustration['month'],
                        'month' => $periodIllustration['month'],
                        'all_time' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                    ];
                @endphp

                <div class="bg-black text-white rounded-xl p-8 mt-8">
                    <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                        </span>
                        <div>
                            <h3 class="text-2xl font-bold">{{ __('Total Payments') }}</h3>
                            <p class="text-sm font-medium text-gray-200">{{ __('Overview of all payments received') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                        @foreach (['week' => 'This Week', 'month' => 'This Month', 'all_time' => 'All Time'] as $period => $periodLabel)
                            @php $accent = $paymentDarkAccent[$period]; @endphp
                            <a href="{{ route('payments.index', ['period' => $period]) }}" class="flex flex-col text-left bg-gray-900 rounded-lg p-4 ring-1 ring-amber-400/40 transition hover:ring-amber-400/70">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $paymentPeriodIcon[$period] }}" /></svg>
                                    </span>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($periodLabel) }}</p>
                                </div>

                                <p class="text-2xl font-bold mt-3 whitespace-nowrap {{ $accent['value'] }}">₦{{ number_format($paymentTotals[$period], 2) }}</p>

                                <p class="mt-1 text-xs font-medium text-gray-300">{{ __('Total payments received') }}</p>
                            </a>
                        @endforeach
                    </div>

                    <div class="mt-6 rounded-xl bg-gray-900 ring-1 ring-gray-800 p-5">
                        <div class="flex flex-col sm:flex-row items-stretch gap-4 sm:gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-700">
                            <a href="{{ route('payment-reports.index') }}" class="flex items-center gap-3 sm:pr-6 hover:opacity-80 transition">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-400/10 text-amber-400">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-wide text-amber-400">{{ __('Payments Summary') }}</p>
                                    <p class="text-sm font-medium text-gray-200">{{ __('All amounts are in Nigerian Naira (₦) — view the full financial reports') }}</p>
                                </div>
                            </a>
                            <button type="button" x-data x-on:click="$dispatch('open-modal', 'payment-security-modal')" class="flex items-center gap-3 pt-4 sm:pt-0 sm:pl-6 text-left hover:opacity-80 transition">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-white">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                                </span>
                                <div>
                                    <p class="text-sm font-bold text-white">{{ __('Secure & Verified') }}</p>
                                    <p class="text-sm font-medium text-gray-200">{{ __('All transactions are secure and verified') }}</p>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <x-modal name="payment-security-modal">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">🛡️ {{ __('Secure & Verified') }}</h3>
                        <p class="text-sm text-gray-600 mb-4">{{ __('What keeps every payment on this dashboard accurate and accountable:') }}</p>
                        <ul class="space-y-3 text-sm text-gray-700">
                            <li class="flex gap-2">
                                <span class="text-green-600">✓</span>
                                <span>{{ __('Every payment is stamped with the staff member who recorded it and a unique receipt number.') }}</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-green-600">✓</span>
                                <span>{{ __('A payment can only be reversed by a director or admin, and requires a reason on record.') }}</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-green-600">✓</span>
                                <span>{{ __('Any correction to how a payment is split across charges is logged in a permanent audit trail.') }}</span>
                            </li>
                            <li class="flex gap-2">
                                <span class="text-green-600">✓</span>
                                <span>{{ __('Every action - registrations, payments, discounts - is recorded in the system Activity Log.') }}</span>
                            </li>
                        </ul>
                        <div class="mt-4 text-right">
                            <x-secondary-button x-on:click="$dispatch('close-modal', 'payment-security-modal')">{{ __('Close') }}</x-secondary-button>
                        </div>
                    </div>
                </x-modal>
            @endif

            @if ($serviceProcessing->isNotEmpty())
                @php
                    $serviceProcessingNames = $serviceProcessing->pluck('service.name')->unique()->values();

                    $pendingPaymentCount = $serviceProcessing->filter(fn (\App\Models\StudentService $studentService) => $studentService->balance() > 0)->count();
                    $overdueCount = $serviceProcessing->filter(fn (\App\Models\StudentService $studentService) => $studentService->isOverdueProcessing())->count();
                    // Not yet overdue, but due within the next 3 days - a
                    // heads-up before a service tips over into Overdue.
                    $needsAttentionCount = $serviceProcessing->filter(function (\App\Models\StudentService $studentService) {
                        $expectedReadyAt = $studentService->expectedReadyAt();

                        return $expectedReadyAt !== null && ! $studentService->isOverdueProcessing() && now()->diffInDays($expectedReadyAt, false) <= 3;
                    })->count();

                    $serviceStatCards = [
                        ['label' => 'Total Services', 'value' => $serviceProcessing->count(), 'description' => 'All services in progress', 'color' => 'amber', 'icon' => 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5'],
                        ['label' => 'In Progress', 'value' => $serviceProcessing->count(), 'description' => 'Currently being processed', 'color' => 'green', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        ['label' => 'Pending Payment', 'value' => $pendingPaymentCount, 'description' => 'Awaiting payments', 'color' => 'indigo', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z'],
                        ['label' => 'Overdue', 'value' => $overdueCount, 'description' => 'Overdue services', 'color' => 'orange', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        ['label' => 'Needs Attention', 'value' => $needsAttentionCount, 'description' => 'Due within 3 days', 'color' => 'red', 'icon' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'],
                    ];
                    $serviceStatColors = [
                        'amber' => ['icon' => 'bg-amber-500/10 text-amber-400', 'value' => 'text-amber-400'],
                        'green' => ['icon' => 'bg-green-500/10 text-green-400', 'value' => 'text-green-400'],
                        'indigo' => ['icon' => 'bg-indigo-500/10 text-indigo-400', 'value' => 'text-indigo-400'],
                        'orange' => ['icon' => 'bg-orange-500/10 text-orange-400', 'value' => 'text-orange-400'],
                        'red' => ['icon' => 'bg-red-500/10 text-red-400', 'value' => 'text-red-400'],
                    ];
                @endphp

                <div class="bg-black text-white rounded-xl p-8 mt-6">
                    <div class="flex items-center gap-3 border-l-2 border-amber-500 pl-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-black">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5" /></svg>
                        </span>
                        <div>
                            <h3 class="text-2xl font-bold">{{ __('Service Processing') }}</h3>
                            <p class="text-sm font-medium text-gray-200">{{ __('Track and manage all ongoing services') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mt-6">
                        @foreach ($serviceStatCards as $card)
                            @php $accent = $serviceStatColors[$card['color']]; @endphp
                            <div class="flex flex-col bg-gray-900 rounded-lg p-4 ring-1 ring-amber-400/40">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" /></svg>
                                    </span>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($card['label']) }}</p>
                                </div>

                                <p class="text-2xl font-bold mt-3 whitespace-nowrap {{ $accent['value'] }}">{{ $card['value'] }}</p>

                                <p class="mt-1 text-xs font-medium text-gray-300">{{ __($card['description']) }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6" x-data="{ activeFilter: 'all', overdueOnly: false }">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 8.25h16.5M5.25 19.5h13.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H5.25A1.5 1.5 0 0 0 3.75 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                        </span>
                        <h4 class="text-lg font-bold text-gray-900">{{ __('Service List') }}</h4>
                        <span class="text-sm text-gray-500">{{ trans_choice('{1} :count service in progress|[2,*] :count services in progress', $serviceProcessing->count(), ['count' => $serviceProcessing->count()]) }}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mb-5">
                        <button
                            type="button"
                            @click="activeFilter = 'all'"
                            :class="activeFilter === 'all' ? 'bg-amber-100 text-amber-800 ring-amber-300' : 'bg-gray-50 text-gray-600 ring-gray-200 hover:bg-gray-100'"
                            class="inline-flex items-center gap-2 rounded-full ring-1 px-4 py-2 text-sm font-semibold transition"
                        >
                            {{ __('All Services') }}
                            <span class="inline-flex items-center justify-center h-5 min-w-[1.25rem] px-1 rounded-full bg-black text-amber-400 text-xs">{{ $serviceProcessing->count() }}</span>
                        </button>
                        @foreach ($serviceProcessingNames as $serviceName)
                            <button
                                type="button"
                                @click="activeFilter = @js($serviceName)"
                                :class="activeFilter === @js($serviceName) ? 'bg-amber-100 text-amber-800 ring-amber-300' : 'bg-gray-50 text-gray-600 ring-gray-200 hover:bg-gray-100'"
                                class="inline-flex items-center gap-2 rounded-full ring-1 px-4 py-2 text-sm font-semibold transition"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0" /></svg>
                                {{ $serviceName }}
                            </button>
                        @endforeach
                        <button
                            type="button"
                            @click="overdueOnly = !overdueOnly"
                            :class="overdueOnly ? 'bg-amber-100 text-amber-800 ring-amber-300' : 'bg-gray-50 text-gray-600 ring-gray-200 hover:bg-gray-100'"
                            class="inline-flex items-center gap-2 rounded-full ring-1 px-4 py-2 text-sm font-semibold transition"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                            {{ __('Overdue Only') }}
                        </button>
                    </div>

                    <div class="space-y-3">
                        @foreach ($serviceProcessing as $studentService)
                            @php
                                $percent = $studentService->processingProgressPercent() ?? 0;
                                $overdue = $studentService->isOverdueProcessing();
                                $edge = $overdue ? 'border-red-500' : 'border-amber-400';
                                $bar = $overdue ? 'from-red-500 to-red-600' : 'from-amber-400 to-amber-500';
                                $initials = collect(explode(' ', $studentService->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                            @endphp
                            <a
                                href="{{ route('students.show', $studentService->student_id) }}"
                                x-show="(activeFilter === 'all' || activeFilter === @js($studentService->service->name)) && (!overdueOnly || {{ $overdue ? 'true' : 'false' }})"
                                class="group flex flex-wrap items-center gap-4 rounded-xl bg-white ring-1 ring-gray-200 border-l-4 {{ $edge }} p-4 transition hover:shadow-md"
                            >
                                <div class="flex items-center gap-3 min-w-[14rem] flex-1">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-800">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="truncate font-bold text-gray-900 group-hover:text-amber-600">{{ $studentService->student->name }}</p>
                                            @if ($overdue)
                                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                                                    <svg class="h-2.5 w-2.5 fill-current" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" /></svg>
                                                    {{ __('Overdue') }}
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-500 truncate">{{ $studentService->service->name }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-1.5 text-xs">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                    <div>
                                        <p class="text-gray-400">{{ __('Started') }}</p>
                                        <p class="font-semibold text-gray-700">{{ $studentService->processing_started_at?->format('M j, Y') ?? '—' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-1.5 text-xs">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                    <div>
                                        <p class="text-gray-400">{{ __('Expected Ready') }}</p>
                                        <p class="font-semibold text-gray-700">{{ $studentService->expectedReadyAt()?->format('M j, Y') ?? '—' }}</p>
                                    </div>
                                </div>

                                <div class="w-full sm:w-40 shrink-0">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-xs font-medium text-gray-500">{{ __('Progress') }}</span>
                                        <span class="text-sm font-bold text-gray-800">{{ $percent }}%</span>
                                    </div>
                                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-gradient-to-r {{ $bar }} transition-all" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>

                                <span class="hidden sm:flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400 group-hover:bg-amber-100 group-hover:text-amber-600 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @php
                $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
                $vehicleIconPath = 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12';
                $certificateIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
            @endphp

            @if ($learnersPermitRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => "Learner's Permit Requests",
                    'subtitle' => "Track and manage learner's permit applications",
                    'requests' => $learnersPermitRequests,
                    'stats' => $learnersPermitStats,
                    'completedLabel' => 'Permit Obtained',
                    'actionLabel' => 'Mark Obtained',
                    'actionStatus' => 'completed',
                    'pageName' => 'permit_page',
                    'iconPaths' => [$idCardIconPath, $vehicleIconPath],
                ])
            @endif

            @if ($onlineCertificateRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => 'Online Certificate Requests',
                    'subtitle' => 'Track and manage online certificate requests',
                    'requests' => $onlineCertificateRequests,
                    'stats' => $onlineCertificateStats,
                    'completedLabel' => 'Certificate Obtained',
                    'actionLabel' => 'Mark Obtained',
                    'actionStatus' => 'completed',
                    'pageName' => 'certificate_page',
                    'iconPaths' => [$certificateIconPath],
                ])
            @endif

            @if ($driversLicenseRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => "Driver's License Requests",
                    'subtitle' => "Track and manage driver's license applications",
                    'requests' => $driversLicenseRequests,
                    'stats' => $driversLicenseStats,
                    'completedLabel' => 'Licenses Obtained',
                    'actionLabel' => 'Start Processing',
                    'actionStatus' => 'processing',
                    'pageName' => 'license_page',
                    'iconPaths' => [$idCardIconPath],
                ])
            @endif

            @if ($trainingProgress->isNotEmpty())
                @php
                    $trainingStatCards = [
                        ['value' => $trainingProgressStats['total_students'], 'label' => 'Total Students', 'description' => 'All enrolled students', 'color' => 'rose', 'icon' => ['M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z']],
                        ['value' => $trainingProgressStats['in_progress'], 'label' => 'In Progress', 'description' => 'Currently training', 'color' => 'blue', 'icon' => ['M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z']],
                        ['value' => $trainingProgressStats['completed'], 'label' => 'Completed', 'description' => 'Training completed', 'color' => 'green', 'icon' => ['M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z']],
                        ['value' => $trainingProgressStats['not_started'], 'label' => 'Not Started', 'description' => 'Yet to begin training', 'color' => 'purple', 'icon' => ['M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'M9.75 9v6M14.25 9v6']],
                        ['value' => $trainingProgressStats['overall_progress'], 'label' => 'Overall Progress', 'description' => 'Avg. active progress', 'color' => 'amber', 'icon' => ['M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z']],
                        ['value' => $trainingProgressStats['non_experience'], 'label' => 'Non-Experience', 'description' => '(Auto & Manual)', 'color' => 'teal', 'icon' => ['M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0']],
                        ['value' => $trainingProgressStats['auto_programs'], 'label' => 'Auto Programs', 'description' => 'Students in Auto', 'color' => 'orange', 'icon' => [$vehicleIconPath]],
                        ['value' => $trainingProgressStats['manual_programs'], 'label' => 'Manual Programs', 'description' => 'Students in Manual', 'color' => 'pink', 'icon' => ['M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.43.991a6.932 6.932 0 0 1 0 .255c-.008.38.137.751.43.992l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.213-1.28Z', 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z']],
                    ];
                    $trainingStatColors = [
                        'rose' => ['icon' => 'bg-rose-500/10 text-rose-400', 'text' => 'text-rose-400'],
                        'blue' => ['icon' => 'bg-blue-500/10 text-blue-400', 'text' => 'text-blue-400'],
                        'green' => ['icon' => 'bg-green-500/10 text-green-400', 'text' => 'text-green-400'],
                        'purple' => ['icon' => 'bg-purple-500/10 text-purple-400', 'text' => 'text-purple-400'],
                        'amber' => ['icon' => 'bg-amber-500/10 text-amber-400', 'text' => 'text-amber-400'],
                        'teal' => ['icon' => 'bg-teal-500/10 text-teal-400', 'text' => 'text-teal-400'],
                        'orange' => ['icon' => 'bg-orange-500/10 text-orange-400', 'text' => 'text-orange-400'],
                        'pink' => ['icon' => 'bg-pink-500/10 text-pink-400', 'text' => 'text-pink-400'],
                    ];
                    $avatarColors = ['bg-amber-100 text-amber-800', 'bg-blue-100 text-blue-800', 'bg-purple-100 text-purple-800', 'bg-green-100 text-green-800', 'bg-rose-100 text-rose-800'];
                    $statusAccents = [
                        'Active' => ['bar' => 'bg-amber-500', 'chip' => 'bg-amber-50 text-amber-700', 'dot' => 'bg-amber-500'],
                        'Completed' => ['bar' => 'bg-green-500', 'chip' => 'bg-green-50 text-green-700', 'dot' => 'bg-green-500'],
                        'Expired' => ['bar' => 'bg-red-500', 'chip' => 'bg-red-50 text-red-700', 'dot' => 'bg-red-500'],
                    ];
                @endphp
                <div class="bg-amber-400 ring-2 ring-black/30 rounded-xl p-8 mt-6">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <div class="flex items-center gap-4 border-l-2 border-black/40 pl-4">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-black text-amber-400">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-2xl font-extrabold text-black">{{ __('Student Training Progress') }}</h3>
                                <p class="text-sm text-black/70">{{ __('Monitor and track student training in real-time') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-lg ring-1 ring-black/30 bg-black/10 px-4 py-2 text-sm font-semibold text-black">
                            <svg class="h-4 w-4 text-black/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            {{ __('This Month') }}
                            <svg class="h-4 w-4 text-black/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                        @foreach ($trainingStatCards as $card)
                            @php $accent = $trainingStatColors[$card['color']]; @endphp
                            <div class="flex flex-col bg-gray-900 rounded-lg p-4 ring-1 ring-black/40">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            @foreach ($card['icon'] as $d)
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}" />
                                            @endforeach
                                        </svg>
                                    </span>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-200">{{ __($card['label']) }}</p>
                                </div>

                                <p class="text-2xl font-bold mt-3 {{ $accent['text'] }}">{{ $card['value'] }}</p>

                                <p class="mt-1 text-xs font-medium text-gray-300">{{ __($card['description']) }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-black text-amber-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 8.25h16.5M5.25 19.5h13.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H5.25A1.5 1.5 0 0 0 3.75 6v12a1.5 1.5 0 0 0 1.5 1.5Z" /></svg>
                            </span>
                            <div>
                                <h4 class="text-lg font-bold text-black">{{ __('Training Overview') }}</h4>
                                <p class="text-xs text-black/70">{{ __('Detailed progress of all students') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('training-progress.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-black/40 bg-black/5 px-4 py-2 text-sm font-semibold text-black hover:bg-black/10 transition">
                            {{ __('View All Students') }}
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 8.25 21 12m0 0-3.75 3.75M21 12H3" /></svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <div class="w-full min-w-[880px]">
                            <div class="grid grid-cols-[2.4fr_1.5fr_110px_1.5fr_130px_40px] gap-4 rounded-t-xl bg-black/10 px-4 py-3 text-[11px] font-bold uppercase tracking-wide text-black/60" role="row">
                                <span class="[grid-area:1/1]">{{ __('Student') }}</span>
                                <span class="[grid-area:1/2]">{{ __('Program') }}</span>
                                <span class="[grid-area:1/3]">{{ __('Duration') }}</span>
                                <span class="[grid-area:1/4]">{{ __('Overall Progress') }}</span>
                                <span class="[grid-area:1/5]">{{ __('Status') }}</span>
                                <span class="[grid-area:1/6]"></span>
                            </div>
                            <div class="divide-y divide-black/10 ring-1 ring-t-0 ring-black/10 rounded-b-xl overflow-hidden">
                                @foreach ($trainingProgress as $enrollment)
                                    @php
                                        $label = $enrollment->trainingStatusLabel();
                                        $percent = $enrollment->trainingCompletionPercentage();
                                        $accent = $statusAccents[$label] ?? $statusAccents['Active'];
                                        $initials = collect(explode(' ', $enrollment->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                        $avatarColor = $avatarColors[$loop->index % count($avatarColors)];
                                        $experienceLabel = is_null($enrollment->student->has_driving_experience) ? null : ($enrollment->student->has_driving_experience ? __('Partial Experience') : __('Non-Experience'));
                                        $transmissionLabels = ['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Auto & Manual'];
                                        $transmissionLabel = $transmissionLabels[$enrollment->course->course_type] ?? null;
                                    @endphp
                                    <a
                                        href="{{ route('students.show', $enrollment->student_id) }}"
                                        class="group grid grid-cols-[2.4fr_1.5fr_110px_1.5fr_130px_40px] gap-4 items-center px-4 py-4 bg-white hover:bg-amber-50 transition"
                                        role="row"
                                    >
                                        <div class="[grid-area:1/1] flex items-center gap-3 min-w-0">
                                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-bold {{ $avatarColor }}">{{ $initials }}</span>
                                            <span class="truncate font-semibold text-gray-900 group-hover:text-amber-600">{{ $enrollment->student->name }}</span>
                                        </div>
                                        <div class="[grid-area:1/2] min-w-0 text-sm">
                                            <p class="font-semibold text-gray-900">{{ $experienceLabel ?? $enrollment->course->name }}</p>
                                            @if ($transmissionLabel)
                                                <p class="text-gray-500">{{ $transmissionLabel }}</p>
                                            @endif
                                        </div>
                                        <div class="[grid-area:1/3] flex items-center gap-1.5 text-sm text-gray-600">
                                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                            {{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}
                                        </div>
                                        <div class="[grid-area:1/4]">
                                            <p class="text-sm font-bold {{ $accent['dot'] === 'bg-red-500' ? 'text-red-600' : ($accent['dot'] === 'bg-green-500' ? 'text-green-600' : 'text-amber-600') }}">{{ $percent }}%</p>
                                            <div class="mt-1 h-1.5 w-full max-w-[8rem] overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full rounded-full {{ $accent['bar'] }}" style="width: {{ $percent }}%"></div>
                                            </div>
                                        </div>
                                        <div class="[grid-area:1/5]">
                                            <span class="inline-flex items-center gap-1.5 rounded-full {{ $accent['chip'] }} px-3 py-1 text-xs font-semibold">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $accent['dot'] }}"></span>
                                                {{ __($label) }}
                                            </span>
                                        </div>
                                        <div class="[grid-area:1/6] flex justify-end">
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400 group-hover:bg-amber-100 group-hover:text-amber-600 transition">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                            </span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-6 rounded-xl bg-gray-900 ring-1 ring-black/40 px-6 py-5">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                            </span>
                            <div>
                                <h4 class="text-base font-bold text-white">{{ __('Progress Summary') }}</h4>
                                <p class="text-xs font-medium text-gray-200">{{ __('Real-time overview of student training') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-8">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-500/10 text-green-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                                </span>
                                <div>
                                    <p class="text-lg font-extrabold text-green-400 leading-none">{{ $trainingProgressStats['highest_progress'] }}%</p>
                                    <p class="text-xs font-medium text-gray-300 whitespace-nowrap">{{ __('Highest Progress') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-amber-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                                </span>
                                <div>
                                    <p class="text-lg font-extrabold text-amber-400 leading-none">{{ $trainingProgressStats['average_progress'] }}%</p>
                                    <p class="text-xs font-medium text-gray-300 whitespace-nowrap">{{ __('Average Progress') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-rose-500/10 text-rose-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.306-4.306a11.95 11.95 0 0 1 5.814 5.518l2.74 1.22m0 0v-5.94m0 5.94h-5.94" /></svg>
                                </span>
                                <div>
                                    <p class="text-lg font-extrabold text-rose-400 leading-none">{{ $trainingProgressStats['lowest_progress'] }}%</p>
                                    <p class="text-xs font-medium text-gray-300 whitespace-nowrap">{{ __('Lowest Progress') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>