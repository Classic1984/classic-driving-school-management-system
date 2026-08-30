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
                        <div class="bg-gray-900 rounded-lg p-4">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $accent['icon'] }}">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}" /></svg>
                                </span>
                                <p class="text-xs uppercase tracking-wider text-gray-400">{{ __($card['label']) }}</p>
                            </div>
                            <p class="text-2xl font-bold mt-3 whitespace-nowrap {{ $accent['value'] }}">
                                @if ($card['currency'])
                                    ₦{{ number_format($kpis[$card['key']], 2) }}
                                @else
                                    {{ number_format($kpis[$card['key']]) }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-gray-500">{{ $card['subtext'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            @php
                $operationTileClasses = fn (string $state) => match ($state) {
                    'alert' => 'rounded-xl ring-2 ring-red-300 bg-red-50 p-5 transition hover:shadow-md',
                    'warn' => 'rounded-xl ring-2 ring-amber-300 bg-amber-50 p-5 transition hover:shadow-md',
                    default => 'rounded-xl ring-1 ring-gray-200 bg-white p-5 transition hover:shadow-md',
                };
                $operationLabelClasses = fn (string $state) => match ($state) {
                    'alert' => 'text-red-700',
                    'warn' => 'text-amber-700',
                    default => 'text-gray-700',
                };
                $operationIconAccent = [
                    'pending_approvals' => 'bg-indigo-50 text-indigo-500',
                    'students_trained' => 'bg-blue-50 text-blue-500',
                    'training_sessions' => 'bg-green-50 text-green-500',
                    'instructors_active' => 'bg-orange-50 text-orange-500',
                    'vehicles_in_use' => 'bg-sky-50 text-sky-500',
                    'payments_received_today' => 'bg-emerald-50 text-emerald-600',
                    'payments_pending_count' => 'bg-red-50 text-red-500',
                    'approaching_completion' => 'bg-amber-50 text-amber-500',
                    'locked_students' => 'bg-purple-50 text-purple-500',
                ];
                $operationNumberAccent = [
                    'pending_approvals' => 'text-indigo-600',
                    'students_trained' => 'text-blue-600',
                    'training_sessions' => 'text-green-600',
                    'instructors_active' => 'text-orange-600',
                    'vehicles_in_use' => 'text-sky-600',
                    'payments_received_today' => 'text-emerald-700',
                    'payments_pending_count' => 'text-red-600',
                    'approaching_completion' => 'text-amber-600',
                    'locked_students' => 'text-purple-600',
                ];
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
            @endphp

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ __("Today's Operations") }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Real-time overview of key activities') }}</p>
                    </div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700">
                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        {{ now()->format('M j, Y') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if (auth()->user()->isDirector())
                        @php $state = $todaysOperations['pending_approvals'] > 0 ? 'alert' : 'ok'; @endphp
                        <a href="{{ route('approvals.index') }}" class="{{ $operationTileClasses($state) }}">
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['pending_approvals'] }}">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['pending_approvals'] }}" /></svg>
                                </span>
                                <p class="text-3xl font-extrabold {{ $operationNumberAccent['pending_approvals'] }}">{{ number_format($todaysOperations['pending_approvals']) }}</p>
                            </div>
                            <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses($state) }}">{{ __('Approval(s) Pending') }}</p>
                        </a>
                    @endif

                    <a href="{{ route('training-report.index', ['period' => 'today']) }}" class="{{ $operationTileClasses('ok') }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['students_trained'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['students_trained'] }}" /></svg>
                            </span>
                            <p class="text-3xl font-extrabold {{ $operationNumberAccent['students_trained'] }}">{{ number_format($todaysOperations['students_trained']) }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses('ok') }}">{{ __('Student(s) Trained Today') }}</p>
                    </a>

                    <a href="{{ route('training-report.index', ['period' => 'today']) }}" class="{{ $operationTileClasses('ok') }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['training_sessions'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['training_sessions'] }}" /></svg>
                            </span>
                            <p class="text-3xl font-extrabold {{ $operationNumberAccent['training_sessions'] }}">{{ number_format($todaysOperations['training_sessions']) }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses('ok') }}">{{ __('Training Session(s) Logged') }}</p>
                    </a>

                    <a href="{{ route('instructor-activity-report.index', ['period' => 'today']) }}" class="{{ $operationTileClasses('ok') }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['instructors_active'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['instructors_active'] }}" /></svg>
                            </span>
                            <p class="text-3xl font-extrabold {{ $operationNumberAccent['instructors_active'] }}">{{ number_format($todaysOperations['instructors_active']) }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses('ok') }}">{{ __('Instructor(s) Active Today') }}</p>
                    </a>

                    <a href="{{ route('vehicles.index') }}" class="{{ $operationTileClasses('ok') }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['vehicles_in_use'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['vehicles_in_use'] }}" /></svg>
                            </span>
                            <p class="text-3xl font-extrabold {{ $operationNumberAccent['vehicles_in_use'] }}">{{ number_format($todaysOperations['vehicles_in_use']) }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses('ok') }}">{{ __('Vehicle(s) In Use Today') }}</p>
                    </a>

                    <a href="{{ route('payments.index') }}" class="{{ $operationTileClasses('ok') }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['payments_received_today'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['payments_received_today'] }}" /></svg>
                            </span>
                            <p class="text-3xl font-extrabold whitespace-nowrap {{ $operationNumberAccent['payments_received_today'] }}">₦{{ number_format($todaysOperations['payments_received_today'], 2) }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses('ok') }}">{{ __('Received Today') }}</p>
                    </a>

                    @php $state = $todaysOperations['payments_pending_count'] > 0 ? 'alert' : 'ok'; @endphp
                    <a href="#outstanding-payments" class="{{ $operationTileClasses($state) }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['payments_pending_count'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['payments_pending_count'] }}" /></svg>
                            </span>
                            <p class="text-3xl font-extrabold {{ $operationNumberAccent['payments_pending_count'] }}">{{ number_format($todaysOperations['payments_pending_count']) }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses($state) }}">{{ __('Payment(s) Pending') }}</p>
                    </a>

                    @php $state = $todaysOperations['approaching_completion'] > 0 ? 'warn' : 'ok'; @endphp
                    @if ($approachingCompletionEnrollments->isNotEmpty())
                        <button type="button" x-data x-on:click="$dispatch('open-modal', 'approaching-completion-modal')" class="text-left {{ $operationTileClasses($state) }}">
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['approaching_completion'] }}">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['approaching_completion'] }}" /></svg>
                                </span>
                                <p class="text-3xl font-extrabold {{ $operationNumberAccent['approaching_completion'] }}">{{ number_format($todaysOperations['approaching_completion']) }}</p>
                            </div>
                            <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses($state) }}">{{ __('Approaching Completion') }}</p>
                        </button>
                    @else
                        <div class="{{ $operationTileClasses($state) }}">
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['approaching_completion'] }}">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['approaching_completion'] }}" /></svg>
                                </span>
                                <p class="text-3xl font-extrabold {{ $operationNumberAccent['approaching_completion'] }}">{{ number_format($todaysOperations['approaching_completion']) }}</p>
                            </div>
                            <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses($state) }}">{{ __('Approaching Completion') }}</p>
                        </div>
                    @endif

                    @php $state = $todaysOperations['locked_students'] > 0 ? 'alert' : 'ok'; @endphp
                    <a href="#locked-students" class="{{ $operationTileClasses($state) }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $operationIconAccent['locked_students'] }}">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $operationIcons['locked_students'] }}" /></svg>
                            </span>
                            <p class="text-3xl font-extrabold {{ $operationNumberAccent['locked_students'] }}">{{ number_format($todaysOperations['locked_students']) }}</p>
                        </div>
                        <p class="mt-3 text-xs font-bold uppercase tracking-wide {{ $operationLabelClasses($state) }}">{{ __('Student(s) Locked') }}</p>
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

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-6">{{ __('Absences') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-2">

                    @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $period => $periodLabel)
                        <a href="{{ route('absence-report.index', ['period' => $period]) }}" class="bg-gray-100 hover:bg-gray-200 p-4 rounded-lg block">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __($periodLabel) }}</h4>
                            <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($absenceStats[$period]) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ __('students absent') }}</p>
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

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                <h3 class="text-xl font-bold text-gray-800 mb-4">{{ __("Today's Attendance") }}</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'present-today-modal')" class="text-left rounded-lg p-5 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition">
                        <p class="text-xs uppercase tracking-wider text-emerald-700 font-semibold">🟢 {{ __('Present') }}</p>
                        <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $presentToday->count() }}</p>
                        <p class="text-xs text-emerald-600 mt-1">{{ __('Click to view names') }}</p>
                    </button>

                    <button type="button" x-data x-on:click="$dispatch('open-modal', 'absent-today-modal')" class="text-left rounded-lg p-5 bg-red-50 border border-red-200 hover:bg-red-100 transition">
                        <p class="text-xs uppercase tracking-wider text-red-700 font-semibold">🔴 {{ __('Absent') }}</p>
                        <p class="text-3xl font-bold text-red-700 mt-1">{{ $absentToday->count() }}</p>
                        <p class="text-xs text-red-600 mt-1">{{ __('Click to view names') }}</p>
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
                @php
                    $serviceProcessingNames = $serviceProcessing->pluck('service.name')->unique()->values();
                @endphp
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6" x-data="{ activeFilter: 'all', overdueOnly: false }">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                        <div>
                            <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Service Processing') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Track and manage all ongoing services') }}</p>
                        </div>
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50 ring-1 ring-gray-200 px-4 py-2 shrink-0">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            <div>
                                <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $serviceProcessing->count() }}</p>
                                <p class="mt-0.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wide whitespace-nowrap">{{ __('In Progress') }}</p>
                            </div>
                        </div>
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

                    <div class="grid grid-cols-1 gap-4">
                        @foreach ($serviceProcessing as $studentService)
                            @php
                                $percent = $studentService->processingProgressPercent() ?? 0;
                                $overdue = $studentService->isOverdueProcessing();
                                $accent = match (true) {
                                    $overdue => ['ring' => 'ring-red-200', 'bar' => 'from-red-500 to-red-600', 'edge' => 'bg-red-500'],
                                    $percent >= 80 => ['ring' => 'ring-emerald-200', 'bar' => 'from-emerald-400 to-emerald-500', 'edge' => 'bg-emerald-500'],
                                    $percent >= 40 => ['ring' => 'ring-amber-200', 'bar' => 'from-amber-400 to-amber-500', 'edge' => 'bg-amber-500'],
                                    default => ['ring' => 'ring-gray-200', 'bar' => 'from-gray-400 to-gray-500', 'edge' => 'bg-gray-400'],
                                };
                                $initials = collect(explode(' ', $studentService->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                            @endphp
                            <a
                                href="{{ route('students.show', $studentService->student_id) }}"
                                x-show="(activeFilter === 'all' || activeFilter === @js($studentService->service->name)) && (!overdueOnly || {{ $overdue ? 'true' : 'false' }})"
                                class="group relative flex flex-col overflow-hidden rounded-xl bg-white p-5 ring-1 {{ $accent['ring'] }} shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                            >
                                <span class="absolute inset-y-0 left-0 w-1 {{ $accent['edge'] }}"></span>

                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-black text-sm font-bold text-amber-400">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="truncate font-semibold text-gray-800 group-hover:text-amber-600">{{ $studentService->student->name }}</p>
                                            @if ($overdue)
                                                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700">
                                                    <svg class="h-2.5 w-2.5 fill-current" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" /></svg>
                                                    {{ __('Overdue') }}
                                                </span>
                                            @endif
                                        </div>
                                        <span class="inline-block mt-0.5 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 truncate max-w-full">
                                            {{ $studentService->service->name }}
                                        </span>
                                    </div>
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400 group-hover:bg-amber-100 group-hover:text-amber-600 transition">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-xs font-medium text-gray-500">{{ __('Progress') }}</span>
                                        <span class="text-sm font-bold text-gray-800">{{ $percent }}%</span>
                                    </div>
                                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-gradient-to-r {{ $accent['bar'] }} transition-all" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                        <div>
                                            <p class="text-gray-400">{{ __('Started') }}</p>
                                            <p class="font-medium text-gray-700">{{ $studentService->processing_started_at?->format('M j, Y') ?? '—' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-right">
                                        <svg class="h-4 w-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                        <div>
                                            <p class="text-gray-400">{{ __('Expected Ready') }}</p>
                                            <p class="font-medium text-gray-700">{{ $studentService->expectedReadyAt()?->format('M j, Y') ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($learnersPermitRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => "Learner's Permit Requests",
                    'subtitle' => "Track and manage learner's permit applications",
                    'requests' => $learnersPermitRequests,
                    'actionLabel' => 'Mark Obtained',
                    'actionStatus' => 'completed',
                ])
            @endif

            @if ($onlineCertificateRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => 'Online Certificate Requests',
                    'subtitle' => 'Track and manage online certificate requests',
                    'requests' => $onlineCertificateRequests,
                    'actionLabel' => 'Mark Obtained',
                    'actionStatus' => 'completed',
                ])
            @endif

            @if ($driversLicenseRequests->isNotEmpty())
                @include('dashboard.partials.service-requests', [
                    'title' => "Driver's License Requests",
                    'subtitle' => "Track and manage driver's license applications",
                    'requests' => $driversLicenseRequests,
                    'actionLabel' => 'Start Processing',
                    'actionStatus' => 'processing',
                ])
            @endif

            @if ($trainingProgress->isNotEmpty())
                    <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                            <div>
                                <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Student Training Progress') }}</h3>
                                <p class="text-sm text-gray-500">{{ __('Monitor and track student training in real-time') }}</p>
                            </div>
                            <a href="{{ route('training-progress.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-amber-300 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50 transition">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                                {{ __('View Full List') }}
                            </a>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100 rounded-xl ring-1 ring-gray-200 mb-6">
                            <div class="flex items-center gap-3 p-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                                </span>
                                <div>
                                    <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['total_students'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('Total Students') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                                </span>
                                <div>
                                    <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['in_progress'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('In Progress') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                                </span>
                                <div>
                                    <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['completed'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('Completed') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                </span>
                                <div>
                                    <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['not_started'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('Not Started') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            @foreach ($trainingProgress as $enrollment)
                                @php
                                    $label = $enrollment->trainingStatusLabel();
                                    $percent = $enrollment->trainingCompletionPercentage();
                                    $accent = match ($label) {
                                        'Expired' => ['ring' => 'ring-red-200', 'bar' => 'bg-red-500', 'edge' => 'bg-red-500', 'dot' => 'bg-red-500', 'text' => 'text-red-700'],
                                        'Completed' => ['ring' => 'ring-blue-200', 'bar' => 'bg-blue-500', 'edge' => 'bg-blue-500', 'dot' => 'bg-blue-500', 'text' => 'text-blue-700'],
                                        default => ['ring' => 'ring-amber-200', 'bar' => 'bg-amber-500', 'edge' => 'bg-amber-500', 'dot' => 'bg-green-500', 'text' => 'text-green-700'],
                                    };
                                    $initials = collect(explode(' ', $enrollment->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                    $experienceLabel = is_null($enrollment->student->has_driving_experience) ? null : ($enrollment->student->has_driving_experience ? __('Partial Experience') : __('Non-Experience'));
                                    $transmissionLabels = ['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Auto & Manual'];
                                    $transmissionLabel = $transmissionLabels[$enrollment->course->course_type] ?? null;
                                @endphp
                                <a
                                    href="{{ route('students.show', $enrollment->student_id) }}"
                                    class="group relative flex flex-col overflow-hidden rounded-xl bg-white p-5 ring-1 {{ $accent['ring'] }} shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                                >
                                    <span class="absolute inset-y-0 left-0 w-1 {{ $accent['edge'] }}"></span>

                                    <div class="flex items-start gap-3">
                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-black text-sm font-bold text-amber-400">
                                            {{ $initials }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="truncate font-semibold text-gray-900 group-hover:text-amber-600">{{ $enrollment->student->name }}</p>
                                                <span class="inline-flex shrink-0 items-center gap-1.5">
                                                    <span class="h-2 w-2 rounded-full {{ $accent['dot'] }}"></span>
                                                    <span class="text-sm font-medium {{ $accent['text'] }}">{{ __($label) }}</span>
                                                </span>
                                            </div>
                                            <p class="mt-0.5 inline-flex items-center gap-1.5 text-sm text-amber-700">
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125v-.174c0-.399.078-.795.23-1.163l1.317-3.162c.19-.456.573-.809 1.058-.96l2.6-.813a.75.75 0 0 1 .223-.033h9.644a.75.75 0 0 1 .223.033l2.6.813c.485.151.868.504 1.058.96l1.317 3.162c.152.368.23.764.23 1.163v.174c0 .621-.504 1.125-1.125 1.125H18.75m-9 0h9m0 0a1.5 1.5 0 0 0 3 0m-3 0a1.5 1.5 0 0 1 3 0m-13.5-9.75L5.106 5.272c.19-.456.573-.809 1.058-.96l2.6-.813a.75.75 0 0 1 .223-.033h6.026a.75.75 0 0 1 .223.033l2.6.813c.485.151.868.504 1.058.96l1.144 2.728" /></svg>
                                                @if ($experienceLabel && $transmissionLabel)
                                                    {{ __(':experience (:transmission)', ['experience' => $experienceLabel, 'transmission' => $transmissionLabel]) }}
                                                @elseif ($transmissionLabel)
                                                    {{ $transmissionLabel }}
                                                @endif
                                                &middot; {{ $enrollment->course->duration_weeks }} {{ __('Weeks') }}
                                            </p>
                                        </div>
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400 group-hover:bg-amber-100 group-hover:text-amber-600 transition">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                        </span>
                                    </div>

                                    <div class="mt-4">
                                        <div class="flex items-baseline justify-between">
                                            <span class="text-xs font-medium text-gray-500">{{ __('Overall Progress') }}</span>
                                            <span class="text-sm font-bold text-gray-800">{{ $percent }}%</span>
                                        </div>
                                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full rounded-full {{ $accent['bar'] }} transition-all" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-3 gap-3 border-t border-gray-100 pt-3 text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                                            </span>
                                            <div>
                                                <p class="font-bold text-gray-800">{{ $enrollment->course->totalTrainingDays() }}</p>
                                                <p class="text-xs text-gray-400">{{ __('Required') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            </span>
                                            <div>
                                                <p class="font-bold text-gray-800">{{ $enrollment->attendedDays() }}</p>
                                                <p class="text-xs text-gray-400">{{ __('Completed') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            </span>
                                            <div>
                                                <p class="font-bold text-gray-800">{{ $enrollment->remainingTrainingDays() }}</p>
                                                <p class="text-xs text-gray-400">{{ __('Remaining') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
            @endif

        </div>
    </div>
</x-app-layout>