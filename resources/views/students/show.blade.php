<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Details') }}
        </h2>
    </x-slot>

    @php
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
        $phoneIconPath = 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z';
        $mapPinIconPath = 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z';
        $bookOpenIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.25c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $lockIconPath = 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z';
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $banknotesIconPath = 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z';
        $receiptIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $documentTextIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
        $arrowLeftIconPath = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
    @endphp

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('students.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:underline mb-4">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowLeftIconPath }}" /></svg>
                {{ __('Students') }}
            </a>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="p-4 sm:p-8 space-y-4">
                @if (session('status') === 'student-created')
                    <p class="text-sm font-medium text-green-600">{{ __('Student registered successfully.') }}</p>
                @elseif (session('status') === 'student-created-discount-pending')
                    <p class="text-sm font-medium text-green-600">{{ __('Student registered successfully.') }}</p>
                    <p class="text-sm font-medium text-amber-600">{{ __('The requested discount is pending Director approval - the student is enrolled at the full fee until then.') }}</p>
                    <script>alert({!! json_encode(__('Student registered successfully. The requested discount still needs Director approval before it applies - the student is currently enrolled at the full course fee.')) !!});</script>
                @elseif (session('status') === 'student-updated')
                    <p class="text-sm font-medium text-green-600">{{ __('Student updated successfully.') }}</p>
                @elseif (session('status') === 'payment-created')
                    <p class="text-sm font-medium text-green-600">{{ __('Payment recorded successfully.') }}</p>
                @elseif (session('status') === 'correction-requested')
                    <p class="text-sm font-medium text-green-600">{{ __('Correction request submitted. A Director will review it.') }}</p>
                @elseif (session('status') === 'service-charged')
                    <p class="text-sm font-medium text-green-600">{{ __('Service charge added successfully.') }}</p>
                @elseif (session('status') === 'service-status-updated')
                    <p class="text-sm font-medium text-green-600">{{ __(session('serviceStatusMessage', 'Processing status updated successfully.')) }}</p>
                @elseif (session('status') === 'service-status-unchanged')
                    <p class="text-sm font-medium text-amber-600">⚠️ {{ __(session('serviceStatusMessage', 'No change - that status was already set.')) }}</p>
                @endif

                @php
                    $studentStatusColor = match ($student->status) {
                        'active' => 'green',
                        'completed' => 'blue',
                        'withdrawn' => 'red',
                        default => 'gray',
                    };
                    $statusDotClasses = [
                        'green' => 'bg-green-500',
                        'blue' => 'bg-blue-500',
                        'red' => 'bg-red-500',
                        'gray' => 'bg-gray-400',
                    ];
                    $statusTextClasses = [
                        'green' => 'text-green-700',
                        'blue' => 'text-blue-700',
                        'red' => 'text-red-700',
                        'gray' => 'text-gray-700',
                    ];

                    $primaryEnrolledCourse = $student->courses->first(fn ($c) => $c->pivot->status !== 'completed') ?? $student->courses->last();
                    $trainingPercent = $primaryEnrolledCourse?->pivot->trainingCompletionPercentage();
                    $currentWeek = $primaryEnrolledCourse ? min($primaryEnrolledCourse->duration_weeks, (int) ceil($trainingPercent / 100 * $primaryEnrolledCourse->duration_weeks)) : null;

                    $attendancePercent = $student->attendances->isNotEmpty()
                        ? (int) round($student->attendances->whereIn('status', ['present', 'late'])->count() / $student->attendances->count() * 100)
                        : null;

                    $transmissionLabels = ['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Auto & Manual'];
                    $transmissionLabel = $transmissionLabels[$student->course_type] ?? null;
                    $levelLabel = $primaryEnrolledCourse?->level ? ucfirst($primaryEnrolledCourse->level) : null;

                    $totalCharges = $financialOverview->sum('price');
                    $totalOverviewPaid = $financialOverview->sum('paid');
                    $totalOutstanding = $financialOverview->sum('balance');
                    $paymentStatusLabel = $financialOverview->isEmpty() ? '—' : ($totalOutstanding > 0 ? __('Due') : __('Paid'));
                    $paymentTileColor = $financialOverview->isEmpty() ? 'gray' : ($totalOutstanding > 0 ? 'red' : 'green');
                @endphp

                <div class="rounded-xl ring-1 ring-gray-200 bg-gray-50/60 p-4 flex items-center gap-4">
                    @if ($student->photo_path)
                        <img src="{{ Storage::url($student->photo_path) }}" alt="{{ __('Passport photo') }}" class="h-16 w-16 object-cover rounded-lg ring-1 ring-gray-200 shrink-0">
                    @else
                        <div class="h-16 w-16 rounded-lg ring-1 ring-gray-200 bg-amber-50 flex items-center justify-center text-amber-400 shrink-0">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 truncate">{{ $student->name }}</h3>
                        <p class="text-sm text-gray-500 font-mono">{{ $student->student_id_number }}</p>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="h-2 w-2 rounded-full {{ $statusDotClasses[$studentStatusColor] }}"></span>
                            <span class="text-sm font-medium capitalize {{ $statusTextClasses[$studentStatusColor] }}">{{ $student->status }}</span>
                        </div>
                        @if ($levelLabel || $transmissionLabel)
                            <p class="mt-1 text-sm text-gray-600">{{ collect([$levelLabel, $transmissionLabel])->filter()->implode(' • ') }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="relative overflow-hidden rounded-xl border border-amber-200 bg-amber-50/60 p-3 text-center">
                        <svg class="pointer-events-none absolute -right-3 -bottom-3 h-14 w-14 text-amber-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookOpenIconPath }}" /></svg>
                        <p class="relative text-xs uppercase tracking-wide text-amber-700">{{ __('Training') }}</p>
                        <p class="relative mt-1 text-lg font-bold text-amber-700">{{ $trainingPercent !== null ? "{$trainingPercent}%" : '—' }}</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border border-blue-200 bg-blue-50/60 p-3 text-center">
                        <svg class="pointer-events-none absolute -right-3 -bottom-3 h-14 w-14 text-blue-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                        <p class="relative text-xs uppercase tracking-wide text-blue-700">{{ __('Attend.') }}</p>
                        <p class="relative mt-1 text-lg font-bold text-blue-700">{{ $attendancePercent !== null ? "{$attendancePercent}%" : '—' }}</p>
                    </div>
                    <div class="relative overflow-hidden rounded-xl border {{ $paymentTileColor === 'red' ? 'border-red-200 bg-red-50/60' : ($paymentTileColor === 'green' ? 'border-green-200 bg-green-50/60' : 'border-gray-200 bg-gray-50/60') }} p-3 text-center">
                        <svg class="pointer-events-none absolute -right-3 -bottom-3 h-14 w-14 {{ $paymentTileColor === 'red' ? 'text-red-400/10' : ($paymentTileColor === 'green' ? 'text-green-400/10' : 'text-gray-400/10') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                        <p class="relative text-xs uppercase tracking-wide {{ $paymentTileColor === 'red' ? 'text-red-700' : ($paymentTileColor === 'green' ? 'text-green-700' : 'text-gray-500') }}">{{ __('Payment') }}</p>
                        <p class="relative mt-1 text-lg font-bold {{ $paymentTileColor === 'red' ? 'text-red-700' : ($paymentTileColor === 'green' ? 'text-green-700' : 'text-gray-900') }}">{{ $paymentStatusLabel }}</p>
                    </div>
                </div>

                <div x-data="{ tab: '{{ in_array(session('status'), ['training-logged', 'attendance-updated']) ? 'attendance' : 'overview' }}' }">
                    <nav class="inline-flex flex-wrap items-center gap-1 rounded-full bg-gray-100 p-1 mb-4">
                        <button type="button" @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-black text-amber-400' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-1.5 text-sm font-semibold rounded-full transition">{{ __('Overview') }}</button>
                        <button type="button" @click="tab = 'training'" :class="tab === 'training' ? 'bg-black text-amber-400' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-1.5 text-sm font-semibold rounded-full transition">{{ __('Training') }}</button>
                        <button type="button" @click="tab = 'attendance'" :class="tab === 'attendance' ? 'bg-black text-amber-400' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-1.5 text-sm font-semibold rounded-full transition">{{ __('Attendance') }}</button>
                        <button type="button" @click="tab = 'payments'" :class="tab === 'payments' ? 'bg-black text-amber-400' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-1.5 text-sm font-semibold rounded-full transition">{{ __('Payments') }}</button>
                        <button type="button" @click="tab = 'certificates'" :class="tab === 'certificates' ? 'bg-black text-amber-400' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-1.5 text-sm font-semibold rounded-full transition">{{ __('Certificates') }}</button>
                        <button type="button" @click="tab = 'documents'" :class="tab === 'documents' ? 'bg-black text-amber-400' : 'text-gray-600 hover:text-gray-900'" class="px-4 py-1.5 text-sm font-semibold rounded-full transition">{{ __('Documents') }}</button>
                    </nav>

                    <div x-show="tab === 'overview'" class="space-y-4">
                        @if ($primaryEnrolledCourse)
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Training Progress') }}</h3>
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="bg-black h-2.5 rounded-full" style="width: {{ $trainingPercent }}%"></div>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">{{ $trainingPercent }}% &middot; {{ __('Week :current of :total', ['current' => $currentWeek, 'total' => $primaryEnrolledCourse->duration_weeks]) }}</p>
                            </div>
                        @endif

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Personal Information') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ __('Date of Birth') }}</p>
                                        <p class="text-sm font-bold text-gray-900">{{ $student->date_of_birth->format('j M Y') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ __('Gender') }}</p>
                                        <p class="text-sm font-bold text-gray-900 capitalize">{{ $student->sex ?? '—' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ __('State') }}</p>
                                        <p class="text-sm font-bold text-gray-900">{{ $student->state_of_origin ?? '—' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ __('LGA') }}</p>
                                        <p class="text-sm font-bold text-gray-900">{{ $student->local_government_area ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Contact Information') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $phoneIconPath }}" /></svg>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ __('Phone') }}</p>
                                        <p class="text-sm font-bold text-gray-900">{{ $student->phone }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $envelopeIconPath }}" /></svg>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ __('Email') }}</p>
                                        <p class="text-sm font-bold text-gray-900 break-all">{{ $student->email }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $mapPinIconPath }}" /></svg>
                                    <div>
                                        <p class="text-xs text-gray-500">{{ __('Address') }}</p>
                                        <p class="text-sm font-bold text-gray-900">{{ $student->address ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Additional Information') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Mother Maiden Name') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $student->mother_maiden_name ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Sex') }}</p>
                                <p class="text-sm font-bold text-gray-900 capitalize">{{ $student->sex ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('State of Origin') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $student->state_of_origin ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Local Govt. Area') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $student->local_government_area ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Occupation') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ match ($student->occupation) {
                                    'student' => 'Student',
                                    'business' => 'Business',
                                    'other' => 'Others',
                                    default => '—',
                                } }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('License Number') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $student->license_number ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Course Type') }}</p>
                                <p class="text-sm font-bold text-gray-900 capitalize">{{ $student->course_type ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Vehicle Class') }}</p>
                                <p class="text-sm font-bold text-gray-900 capitalize">{{ $student->vehicle_class ?? '—' }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Previous Driving Experience') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ is_null($student->has_driving_experience) ? '—' : ($student->has_driving_experience ? __('Yes') : __('No')) }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Wears Glasses to Drive') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ is_null($student->wears_glasses) ? '—' : ($student->wears_glasses ? __('Yes') : __('No')) }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('How They Heard About Us') }}</p>
                                <p class="text-sm font-bold text-gray-900 capitalize">
                                    {{ $student->referral_source ?? '—' }}
                                    @if ($student->referral_source === 'other' && $student->referral_source_other)
                                        ({{ $student->referral_source_other }})
                                    @endif
                                </p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Enrollment Date') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $student->enrollment_date->format('Y-m-d') }}</p>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3">
                                <p class="text-xs text-gray-500">{{ __('Status') }}</p>
                                <x-badge :color="$studentStatusColor" class="capitalize mt-0.5">{{ $student->status }}</x-badge>
                            </div>
                            <div class="rounded-lg bg-gray-50 p-3 sm:col-span-2">
                                <p class="text-xs text-gray-500 mb-0.5">{{ __('App Access') }}</p>
                                @if ($student->hasAppAccess())
                                    <x-badge :color="$student->user->pin_set_at ? 'green' : 'amber'">
                                        {{ $student->user->pin_set_at ? __('Active') : __('Pending first login') }}
                                    </x-badge>
                                    @if (auth()->user()->canManageCourses())
                                        @if (! $student->user->pin_set_at)
                                            <form method="post" action="{{ route('students.access.resend', $student) }}" class="inline ms-2">
                                                @csrf
                                                <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Resend Login SMS') }}</button>
                                            </form>
                                        @endif
                                        <form method="post" action="{{ route('students.access.destroy', $student) }}" class="inline ms-2" onsubmit="return confirm('{{ __('Revoke this student\'s app access? Their PIN will stop working immediately.') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Revoke Access') }}</button>
                                        </form>
                                    @endif
                                @else
                                    <x-badge color="gray">{{ __('Not Enabled') }}</x-badge>
                                    @if (auth()->user()->canManageCourses())
                                        <form method="post" action="{{ route('students.access.store', $student) }}" class="inline ms-2">
                                            @csrf
                                            <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Enable App Access') }}</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if (session('status') === 'student-access-granted')
                            <p class="text-sm font-medium text-green-600">{{ __('App access granted - the student has been texted a login link.') }}</p>
                        @elseif (session('status') === 'student-access-revoked')
                            <p class="text-sm font-medium text-green-600">{{ __('App access revoked.') }}</p>
                        @elseif (session('status') === 'student-access-resent')
                            <p class="text-sm font-medium text-green-600">{{ __('Login instructions re-sent.') }}</p>
                        @endif
                        <x-input-error :messages="$errors->get('student')" />

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Next of Kin') }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-xs text-gray-500">{{ __('Name') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $student->next_of_kin_name ?? '—' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-xs text-gray-500">{{ __('Phone No.') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $student->next_of_kin_phone ?? '—' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3 sm:col-span-2">
                                    <p class="text-xs text-gray-500">{{ __('Address') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $student->next_of_kin_address ?? '—' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3 sm:col-span-2">
                                    <p class="text-xs text-gray-500">{{ __('Email') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $student->next_of_kin_email ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div x-show="tab === 'training'" class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Enrollments') }}</h3>
                                @if (auth()->user()->isDirector())
                                    <a href="{{ route('students.enroll.create', $student) }}" class="text-xs text-amber-600 hover:underline">{{ __('+ Enroll in a Course') }}</a>
                                @else
                                    <span class="text-xs text-gray-400">
                                        {{ __('🔒 Director-controlled') }} ·
                                        <a href="{{ route('student-correction-requests.create', ['student' => $student, 'field' => 'program']) }}" class="text-amber-600 hover:underline">{{ __('Request a Correction') }}</a>
                                    </span>
                                @endif
                            </div>

                            @if (session('status') === 'enrollment-completed')
                                <p class="mb-2 text-sm font-medium text-green-600">{{ __('Course marked as completed.') }}</p>
                            @elseif (session('status') === 'enrollment-removed')
                                <p class="mb-2 text-sm font-medium text-green-600">{{ __('Enrollment removed.') }}</p>
                            @elseif (session('status') === 'enrollment-upgraded')
                                <p class="mb-2 text-sm font-medium text-green-600">{{ __('Programme upgraded successfully.') }}</p>
                            @elseif (session('status') === 'assessment-saved')
                                <p class="mb-2 text-sm font-medium text-green-600">{{ __('Assessment saved.') }}</p>
                            @endif
                            <x-input-error class="mb-2" :messages="$errors->get('enrollment')" />

                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                                <th class="px-3 py-3">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookOpenIconPath }}" /></svg>
                                                        {{ __('Course') }}
                                                    </span>
                                                </th>
                                                <th class="px-3 py-3">{{ __('Fee') }}</th>
                                                <th class="px-3 py-3">{{ __('Discount') }}</th>
                                                <th class="px-3 py-3">{{ __('Balance') }}</th>
                                                <th class="px-3 py-3">{{ __('Due Date') }}</th>
                                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                                <th class="px-3 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse ($student->courses as $enrolledCourse)
                                                <tr>
                                                    <td class="px-3 py-3 text-sm font-semibold text-gray-900">{{ $enrolledCourse->name }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">
                                                        @if ($enrolledCourse->pivot->hasDiscount())
                                                            <span class="line-through text-gray-400">₦{{ number_format($enrolledCourse->pivot->originalFee(), 2) }}</span>
                                                            <span class="font-medium">₦{{ number_format($enrolledCourse->pivot->fee(), 2) }}</span>
                                                        @else
                                                            ₦{{ number_format($enrolledCourse->pivot->fee(), 2) }}
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3 text-sm">
                                                        @if ($enrolledCourse->pivot->hasDiscount())
                                                            <span class="text-green-600">{{ rtrim(rtrim(number_format((float) $enrolledCourse->pivot->discount_percentage, 2), '0'), '.') }}% (₦{{ number_format($enrolledCourse->pivot->discount_amount, 2) }})</span>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($enrolledCourse->pivot->balance(), 2) }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ optional($enrolledCourse->pivot->due_date)->format('Y-m-d') ?? '—' }}</td>
                                                    <td class="px-3 py-3 text-sm">
                                                        <x-badge :color="match ($enrolledCourse->pivot->statusLabel()) {
                                                            'Registered' => 'gray',
                                                            'Locked' => 'red',
                                                            'Completed' => 'blue',
                                                            'Certified' => 'amber',
                                                            default => 'green',
                                                        }">{{ __($enrolledCourse->pivot->statusLabel()) }}</x-badge>
                                                        @if ($enrolledCourse->pivot->status === 'locked')
                                                            <span class="block text-xs text-gray-500 mt-0.5">{{ $enrolledCourse->pivot->lockedReasonLabel() }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3 text-sm whitespace-nowrap">
                                                        @if ($enrolledCourse->pivot->status !== 'completed' && $enrolledCourse->pivot->hasCompletedTraining() && $enrolledCourse->pivot->balance() <= 0)
                                                            <form method="post" action="{{ route('enrollments.complete', $enrolledCourse->pivot->id) }}" class="inline">
                                                                @csrf
                                                                @method('patch')
                                                                <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __('Mark Complete') }}</button>
                                                            </form>
                                                        @endif
                                                        @if ($enrolledCourse->pivot->isLockedForExpiredTrainingPeriod() && auth()->user()->isDirector())
                                                            <a href="{{ route('enrollments.reactivate.create', $enrolledCourse->pivot->id) }}" class="text-sm text-amber-600 hover:underline">{{ __('Reactivate') }}</a>
                                                        @endif
                                                        @if ($enrolledCourse->pivot->canUpgrade() && auth()->user()->isDirector())
                                                            <a href="{{ route('enrollments.upgrade.create', $enrolledCourse->pivot->id) }}" class="text-sm text-amber-600 hover:underline">{{ __('Upgrade') }}</a>
                                                        @endif
                                                        @if (auth()->user()->isDirector() && $enrolledCourse->pivot->amountPaid() <= 0)
                                                            <form method="post" action="{{ route('enrollments.destroy', $enrolledCourse->pivot->id) }}" class="inline" onsubmit="return confirm('{{ __('Remove this enrollment? This cannot be undone.') }}');">
                                                                @csrf
                                                                @method('delete')
                                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">{{ __('Not enrolled in any courses yet.') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if ($student->courses->isNotEmpty())
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Training Progress') }}</h3>
                                <div class="space-y-4">
                                    @foreach ($student->courses as $enrolledCourse)
                                        @php
                                            $label = $enrolledCourse->pivot->trainingStatusLabel();
                                            $labelColor = match ($label) {
                                                'Completed' => 'blue',
                                                'Expired' => 'red',
                                                default => 'green',
                                            };
                                        @endphp
                                        <div class="rounded-xl ring-1 ring-gray-200 p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <h4 class="text-sm font-bold text-gray-800">{{ $enrolledCourse->name }} — {{ $enrolledCourse->duration_weeks }} {{ __('Weeks') }}</h4>
                                                <x-badge :color="$labelColor">{{ __($label) }}</x-badge>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-amber-500 h-2.5 rounded-full" style="width: {{ $enrolledCourse->pivot->trainingCompletionPercentage() }}%"></div>
                                            </div>
                                            <div class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-600">
                                                <span>{{ $enrolledCourse->pivot->attendedDays() }} / {{ $enrolledCourse->totalTrainingDays() }} {{ __('Days Completed') }}</span>
                                                <span>{{ $enrolledCourse->pivot->remainingTrainingDays() }} {{ __('Days Remaining') }}</span>
                                                <span>{{ $enrolledCourse->pivot->trainingCompletionPercentage() }}%</span>
                                            </div>
                                            <div class="mt-1 text-xs text-gray-500">
                                                {{ __('Start Date') }}: {{ optional($enrolledCourse->pivot->enrolled_at)->format('Y-m-d') ?? '—' }}
                                                &middot;
                                                {{ __('Expected Completion') }}: {{ optional($enrolledCourse->pivot->expectedCompletionDate())->format('Y-m-d') ?? '—' }}
                                            </div>
                                            @php
                                                $upgradeStatus = $enrolledCourse->pivot->upgradeStatusLabel();
                                                $upgradeStatusColor = match ($upgradeStatus) {
                                                    'Eligible' => 'green',
                                                    'Closed' => 'red',
                                                    default => 'gray',
                                                };
                                            @endphp
                                            @if ($upgradeStatus === 'Closed')
                                                <div class="mt-2 bg-red-50 border border-red-300 rounded-lg p-3">
                                                    <p class="text-sm font-bold text-red-700">⛔ {{ __('Programme Upgrade Window Closed') }}</p>
                                                    <p class="text-xs text-red-600 mt-0.5">{{ __($enrolledCourse->pivot->upgradeStatusReason()) }}</p>
                                                </div>
                                            @else
                                                <div class="mt-2 flex items-center gap-2 text-xs">
                                                    <span class="text-gray-500">{{ __('Upgrade Status') }}:</span>
                                                    <x-badge :color="$upgradeStatusColor">{{ __($upgradeStatus) }}</x-badge>
                                                    @if ($upgradeStatus === 'Eligible')
                                                        <span class="text-gray-500">{{ $enrolledCourse->pivot->upgradeDaysRemaining() }} {{ __('day(s) remaining') }}</span>
                                                    @elseif ($enrolledCourse->pivot->upgradeStatusReason())
                                                        <span class="text-gray-500">{{ __($enrolledCourse->pivot->upgradeStatusReason()) }}</span>
                                                    @endif
                                                </div>
                                            @endif

                                            @php $assessment = $enrolledCourse->pivot->assessment(); @endphp
                                            <div class="mt-3 pt-3 border-t border-gray-100" x-data="{ editingAssessment: {{ auth()->user()->canManageCourses() && ! $assessment ? 'true' : 'false' }} }">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ __('Final Assessment') }}</span>
                                                    @if (auth()->user()->canManageCourses())
                                                        <button type="button" x-show="!editingAssessment" @click="editingAssessment = true" class="text-xs text-amber-600 hover:underline">{{ $assessment ? __('Edit') : __('Record') }}</button>
                                                    @endif
                                                </div>

                                                <div x-show="!editingAssessment" class="mt-1">
                                                    @if ($assessment)
                                                        <div class="flex items-center gap-2 text-sm">
                                                            <x-badge :color="$assessment->result === 'pass' ? 'green' : 'red'" class="capitalize">{{ $assessment->result }}</x-badge>
                                                            @if ($assessment->score !== null)
                                                                <span class="text-gray-600">{{ __('Score') }}: {{ $assessment->score }}</span>
                                                            @endif
                                                        </div>
                                                        @if ($assessment->remarks)
                                                            <p class="mt-1 text-xs text-gray-500">{{ $assessment->remarks }}</p>
                                                        @endif
                                                        <p class="mt-1 text-xs text-gray-400">
                                                            {{ __('Assessed by') }} {{ $assessment->assessedBy?->name ?? '—' }} &middot; {{ $assessment->assessed_at->format('Y-m-d') }}
                                                        </p>
                                                    @else
                                                        <p class="text-xs text-gray-500">{{ __('Not yet assessed.') }}</p>
                                                    @endif
                                                </div>

                                                @if (auth()->user()->canManageCourses())
                                                    <form x-show="editingAssessment" x-cloak method="post" action="{{ route('enrollments.assessment.store', $enrolledCourse->pivot->id) }}" class="mt-2 flex flex-wrap items-end gap-3">
                                                        @csrf
                                                        <div>
                                                            <select name="result" class="rounded-md border-gray-300 shadow-sm text-sm focus:border-amber-500 focus:ring-amber-500">
                                                                <option value="pass" @selected(($assessment->result ?? null) === 'pass')>{{ __('Pass') }}</option>
                                                                <option value="fail" @selected(($assessment->result ?? null) === 'fail')>{{ __('Fail') }}</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <input type="number" name="score" min="0" max="100" placeholder="{{ __('Score') }}" value="{{ $assessment->score ?? '' }}" class="w-20 rounded-md border-gray-300 shadow-sm text-sm focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <div class="flex-1 min-w-[10rem]">
                                                            <input type="text" name="remarks" placeholder="{{ __('Remarks') }}" value="{{ $assessment->remarks ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-amber-500 focus:ring-amber-500">
                                                        </div>
                                                        <button type="submit" class="text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-md px-3 py-1.5">{{ __('Save') }}</button>
                                                        @if ($assessment)
                                                            <button type="button" @click="editingAssessment = false" class="text-sm text-amber-600 hover:underline">{{ __('Cancel') }}</button>
                                                        @endif
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Theory Progress') }}</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-xs text-gray-500">{{ __('Classes Attended') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryProgress['classes_attended'] }}/{{ $theoryProgress['classes_expected'] }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-xs text-gray-500">{{ __('Attendance') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryProgress['attendance_percentage'] }}%</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-xs text-gray-500">{{ __('Topics Completed') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryProgress['topics_completed'] }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3">
                                    <p class="text-xs text-gray-500">{{ __('Average Score') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryProgress['average_score'] ?? '—' }}</p>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-3 col-span-2 sm:col-span-1">
                                    <p class="text-xs text-gray-500">{{ __('Outstanding Topics') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $theoryProgress['outstanding_topics']->count() }}</p>
                                </div>
                            </div>
                            @if ($theoryProgress['outstanding_topics']->isNotEmpty())
                                <div class="mt-2 text-xs text-gray-500">
                                    {{ $theoryProgress['outstanding_topics']->implode(', ') }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <div x-show="tab === 'attendance'">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Student Login Training') }}</h3>

                        @if (session('status') === 'training-logged')
                            <p class="mb-2 text-sm font-medium text-green-600">{{ __('Training logged successfully.') }}</p>
                        @elseif (session('status') === 'attendance-updated')
                            <p class="mb-2 text-sm font-medium text-green-600">{{ __('Training login updated successfully.') }}</p>
                        @endif
                        <x-input-error class="mb-2" :messages="$errors->get('student_id')" />

                        <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                            <th class="px-3 py-3">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                                    {{ __('Date') }}
                                                </span>
                                            </th>
                                            <th class="px-3 py-3">{{ __('Course') }}</th>
                                            <th class="px-3 py-3">{{ __('Type') }}</th>
                                            <th class="px-3 py-3">{{ __('Duration') }}</th>
                                            <th class="px-3 py-3">{{ __('Instructor') }}</th>
                                            <th class="px-3 py-3">{{ __('Vehicle') }}</th>
                                            <th class="px-3 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @forelse ($student->attendances as $attendance)
                                            <tr>
                                                <td class="px-3 py-3 text-sm text-gray-600">{{ $attendance->date->format('Y-m-d') }}</td>
                                                <td class="px-3 py-3 text-sm text-gray-600">{{ $attendance->course->name }}</td>
                                                <td class="px-3 py-3 text-sm capitalize text-gray-600">{{ $attendance->type ?? '—' }}</td>
                                                <td class="px-3 py-3 text-sm text-gray-600">{{ $attendance->duration ?? '—' }}</td>
                                                <td class="px-3 py-3 text-sm text-gray-600">{{ $attendance->instructor?->name ?? '—' }}</td>
                                                <td class="px-3 py-3 text-sm text-gray-600">{{ $attendance->vehicle?->name ?? '—' }}</td>
                                                <td class="px-3 py-3 text-sm text-right whitespace-nowrap space-x-2">
                                                    @if (auth()->user()->canManageCourses())
                                                        <a href="{{ route('attendances.edit', $attendance) }}?redirect_to=student" class="text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                                    @endif
                                                    @if (auth()->user()->isAdmin())
                                                        <form method="post" action="{{ route('attendances.destroy', $attendance) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this training login?') }}');">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="text-red-600 hover:underline">{{ __('Delete') }}</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">{{ __('No training logins yet.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        @if (auth()->user()->canManageCourses())
                        @if ($student->courses->isNotEmpty())
                            <form method="post" action="{{ route('attendances.store') }}" class="mt-4 grid grid-cols-1 sm:grid-cols-6 gap-4 items-end">
                                @csrf
                                <input type="hidden" name="student_id" value="{{ $student->id }}">
                                <input type="hidden" name="redirect_to_student" value="1">
                                <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                                <input type="hidden" name="status" value="present">

                                <div>
                                    <x-input-label for="quick_login_course_id" :value="__('Course')" />
                                    <select id="quick_login_course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                        <option value="">{{ __('Select a course') }}</option>
                                        @foreach ($student->courses as $enrolledCourse)
                                            <option value="{{ $enrolledCourse->id }}">{{ $enrolledCourse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="quick_login_type" :value="__('Type')" />
                                    <select id="quick_login_type" name="type" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                        <option value="">{{ __('Not specified') }}</option>
                                        @foreach (['practical' => 'Practical', 'classroom' => 'Classroom'] as $value => $label)
                                            <option value="{{ $value }}">{{ __($label) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="quick_login_instructor_id" :value="__('Instructor')" />
                                    <select id="quick_login_instructor_id" name="instructor_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                        <option value="">{{ __('None') }}</option>
                                        @foreach ($instructors as $availableInstructor)
                                            <option value="{{ $availableInstructor->id }}">{{ $availableInstructor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="quick_login_vehicle_id" :value="__('Vehicle')" />
                                    <select id="quick_login_vehicle_id" name="vehicle_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                        <option value="">{{ __('None') }}</option>
                                        @foreach ($vehicles as $availableVehicle)
                                            <option value="{{ $availableVehicle->id }}">{{ $availableVehicle->name }} ({{ $availableVehicle->plate_number }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-input-label for="quick_login_duration" :value="__('Duration')" />
                                    <select id="quick_login_duration" name="duration" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                                        @foreach ([1 => '1 Day (Single Session)', 2 => '2 Days (Double Period / Saturday)', 3 => '3 Days (Sunday)'] as $value => $label)
                                            <option value="{{ $value }}">{{ __($label) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <x-primary-button type="submit">{{ __('Log Training') }}</x-primary-button>
                                </div>
                            </form>
                        @else
                            <p class="mt-4 text-sm text-gray-500">{{ __('Enroll this student in a course before logging training.') }}</p>
                        @endif
                        @endif
                    </div>

                    <div x-show="tab === 'payments'" class="space-y-4">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Financial Overview') }}</h3>
                                @if ($totalOutstanding > 0)
                                    <a href="{{ route('payments.record.create', ['student_id' => $student->id]) }}">
                                        <x-primary-button type="button">{{ __('Balance Payment') }}</x-primary-button>
                                    </a>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-4">
                                <div class="relative overflow-hidden rounded-xl bg-black p-4">
                                    <svg class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 text-amber-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $receiptIconPath }}" /></svg>
                                    <p class="relative text-xs uppercase tracking-wider text-amber-400/80">{{ __('Total Charges') }}</p>
                                    <p class="relative text-2xl font-bold text-amber-400 mt-1">₦{{ number_format($totalCharges, 2) }}</p>
                                </div>
                                <div class="relative overflow-hidden rounded-xl bg-black p-4">
                                    <svg class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 text-amber-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $banknotesIconPath }}" /></svg>
                                    <p class="relative text-xs uppercase tracking-wider text-amber-400/80">{{ __('Total Paid') }}</p>
                                    <p class="relative text-2xl font-bold text-amber-400 mt-1">₦{{ number_format($totalOverviewPaid, 2) }}</p>
                                </div>
                                <div class="relative overflow-hidden rounded-xl bg-amber-400 p-4">
                                    <svg class="pointer-events-none absolute -right-3 -bottom-3 h-16 w-16 text-black opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                                    <p class="relative text-xs uppercase tracking-wider text-black/70">{{ __('Total Outstanding') }}</p>
                                    <p class="relative text-2xl font-bold text-black mt-1">₦{{ number_format($totalOutstanding, 2) }}</p>
                                </div>
                            </div>

                            @if ($financialOverview->isNotEmpty())
                                <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead>
                                                <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                                    <th class="px-3 py-3">{{ __('Item') }}</th>
                                                    <th class="px-3 py-3">{{ __('Price') }}</th>
                                                    <th class="px-3 py-3">{{ __('Paid') }}</th>
                                                    <th class="px-3 py-3">{{ __('Balance') }}</th>
                                                    <th class="px-3 py-3">{{ __('Status') }}</th>
                                                    <th class="px-3 py-3"></th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100 bg-white">
                                                @foreach ($financialOverview as $charge)
                                                    <tr>
                                                        <td class="px-3 py-3 text-sm text-gray-900">{{ $charge['label'] }}</td>
                                                        <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($charge['price'], 2) }}</td>
                                                        <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($charge['paid'], 2) }}</td>
                                                        <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($charge['balance'], 2) }}</td>
                                                        <td class="px-3 py-3 text-sm">
                                                            <x-badge :color="match ($charge['status']) {
                                                                'paid' => 'green',
                                                                'part_payment' => 'amber',
                                                                default => 'red',
                                                            }">{{ __(ucwords(str_replace('_', ' ', $charge['status']))) }}</x-badge>
                                                        </td>
                                                        <td class="px-3 py-3 text-sm whitespace-nowrap">
                                                            @if ($charge['balance'] > 0)
                                                                <a href="{{ route('payments.record.create', ['student_id' => $student->id, 'charge_type' => $charge['type'], 'charge_id' => $charge['id']]) }}" class="text-sm text-amber-600 hover:underline">
                                                                    {{ __('Balance Payment') }}
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="bg-amber-50">
                                                    <td class="px-3 py-3 text-sm font-bold text-gray-900">{{ __('TOTAL') }}</td>
                                                    <td class="px-3 py-3 text-sm font-bold text-gray-900">{{ number_format($totalCharges, 2) }}</td>
                                                    <td class="px-3 py-3 text-sm font-bold text-gray-900">{{ number_format($totalOverviewPaid, 2) }}</td>
                                                    <td class="px-3 py-3 text-sm font-bold text-gray-900">{{ number_format($totalOutstanding, 2) }}</td>
                                                    <td class="px-3 py-3 text-sm font-bold text-gray-900">{{ $totalOutstanding > 0 ? __('Outstanding') : __('Paid') }}</td>
                                                    <td class="px-3 py-3 text-sm"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('No charges yet.') }}</p>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Payments') }}</h3>
                                <a href="{{ route('payments.record.create', ['student_id' => $student->id]) }}" class="text-sm text-amber-600 hover:underline">{{ __('Record a Payment') }}</a>
                            </div>
                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                                <th class="px-3 py-3">
                                                    <span class="inline-flex items-center gap-1.5">
                                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                                        {{ __('Date') }}
                                                    </span>
                                                </th>
                                                <th class="px-3 py-3">{{ __('Receipt') }}</th>
                                                <th class="px-3 py-3">{{ __('Description') }}</th>
                                                <th class="px-3 py-3">{{ __('Amount') }}</th>
                                                <th class="px-3 py-3">{{ __('Method') }}</th>
                                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                                <th class="px-3 py-3">{{ __('Recorded By') }}</th>
                                                <th class="px-3 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse ($student->payments as $payment)
                                                <tr>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                                    <td class="px-3 py-3 text-sm font-mono text-xs">
                                                        <a href="{{ route('payments.show', $payment) }}" class="text-amber-600 hover:underline">{{ $payment->receipt_number }}</a>
                                                    </td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ $payment->description() }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($payment->amount, 2) }}</td>
                                                    <td class="px-3 py-3 text-sm capitalize text-gray-600">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                                    <td class="px-3 py-3 text-sm">
                                                        <x-badge :color="match ($payment->status) {
                                                            'paid' => 'green',
                                                            'pending' => 'amber',
                                                            'failed' => 'red',
                                                            'refunded' => 'blue',
                                                            default => 'gray',
                                                        }" class="capitalize">{{ $payment->status }}</x-badge>
                                                    </td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ $payment->recordedBy?->name ?? '—' }}</td>
                                                    <td class="px-3 py-3 text-sm">
                                                        <a href="{{ route('payments.receipt', $payment) }}" class="text-sm text-amber-600 hover:underline">{{ __('Receipt') }}</a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-3 py-6 text-center text-sm text-gray-500">{{ __('No payments recorded yet.') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                        @if ($student->payments->where('status', 'paid')->isNotEmpty())
                                            <tfoot>
                                                <tr class="bg-amber-50">
                                                    <td colspan="3" class="px-3 py-3 text-sm font-bold text-gray-900 text-right">{{ __('Total paid') }}</td>
                                                    <td colspan="5" class="px-3 py-3 text-sm font-bold text-gray-900">{{ number_format($student->payments->where('status', 'paid')->sum('amount'), 2) }}</td>
                                                </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Services') }}</h3>

                            <x-input-error class="mb-2" :messages="$errors->get('service_id')" />

                            <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead>
                                            <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                                <th class="px-3 py-3">{{ __('Service') }}</th>
                                                <th class="px-3 py-3">{{ __('Price') }}</th>
                                                <th class="px-3 py-3">{{ __('Paid') }}</th>
                                                <th class="px-3 py-3">{{ __('Balance') }}</th>
                                                <th class="px-3 py-3">{{ __('Payment Status') }}</th>
                                                <th class="px-3 py-3">{{ __('Processing Status') }}</th>
                                                <th class="px-3 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @forelse ($student->studentServices as $studentService)
                                                <tr>
                                                    <td class="px-3 py-3 text-sm text-gray-900">{{ $studentService->service->name }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($studentService->price, 2) }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($studentService->amountPaid(), 2) }}</td>
                                                    <td class="px-3 py-3 text-sm text-gray-600">{{ number_format($studentService->balance(), 2) }}</td>
                                                    <td class="px-3 py-3 text-sm">
                                                        <x-badge :color="match ($studentService->status()) {
                                                            'paid' => 'green',
                                                            'part_payment' => 'amber',
                                                            default => 'red',
                                                        }">{{ __(ucwords(str_replace('_', ' ', $studentService->status()))) }}</x-badge>
                                                    </td>
                                                    <td class="px-3 py-3 text-sm">
                                                        <form method="post" action="{{ route('student-services.processing-status.update', $studentService) }}">
                                                            @csrf
                                                            @method('patch')
                                                            <select name="processing_status" class="border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm" onchange="this.form.submit()">
                                                                @foreach (\App\Models\StudentService::PROCESSING_STATUSES as $value)
                                                                    <option value="{{ $value }}" @selected($studentService->processing_status === $value)>{{ ucwords(str_replace('_', ' ', $value)) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </form>
                                                        @if ($studentService->processingProgressPercent() !== null)
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                {{ $studentService->processingProgressPercent() }}%
                                                                @if ($studentService->expectedReadyAt())
                                                                    &middot; {{ __('Ready by :date', ['date' => $studentService->expectedReadyAt()->format('M j, Y')]) }}
                                                                @endif
                                                                @if ($studentService->isOverdueProcessing())
                                                                    <x-badge color="red" class="ms-1">{{ __('Overdue') }}</x-badge>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-3 text-sm">
                                                        @if (auth()->user()->isDirector() && $studentService->amountPaid() <= 0 && $studentService->processing_status === 'not_started')
                                                            <form method="post" action="{{ route('student-services.destroy', $studentService) }}" class="inline" onsubmit="return confirm('{{ __('Remove this charge? This cannot be undone.') }}');">
                                                                @csrf
                                                                @method('delete')
                                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">{{ __('No service charges yet.') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <x-input-error class="mt-2" :messages="$errors->get('studentService')" />

                            @if ($availableServices->isNotEmpty())
                                <form method="post" action="{{ route('students.services.store', $student) }}" class="mt-4 flex items-end gap-4">
                                    @csrf

                                    <div>
                                        <x-input-label for="service_id" :value="__('Charge for a Service')" />
                                        <select id="service_id" name="service_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
                                            @foreach ($availableServices as $service)
                                                <option value="{{ $service->id }}">{{ $service->name }} (₦{{ number_format($service->price, 2) }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <x-primary-button>{{ __('Add Charge') }}</x-primary-button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div x-show="tab === 'certificates'">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Certificates') }}</h3>
                        <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="bg-amber-50/60 text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                            <th class="px-3 py-3">
                                                <span class="inline-flex items-center gap-1.5">
                                                    <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                                                    {{ __('Certificate #') }}
                                                </span>
                                            </th>
                                            <th class="px-3 py-3">{{ __('Course') }}</th>
                                            <th class="px-3 py-3">{{ __('Issue Date') }}</th>
                                            <th class="px-3 py-3">{{ __('Status') }}</th>
                                            <th class="px-3 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 bg-white">
                                        @forelse ($student->certificates as $certificate)
                                            <tr>
                                                <td class="px-3 py-3 text-sm font-mono text-gray-900">{{ $certificate->certificate_number }}</td>
                                                <td class="px-3 py-3 text-sm text-gray-600">{{ $certificate->course->name }}</td>
                                                <td class="px-3 py-3 text-sm text-gray-600">{{ $certificate->issue_date->format('Y-m-d') }}</td>
                                                <td class="px-3 py-3 text-sm">
                                                    <x-badge color="amber">{{ __('Certified') }}</x-badge>
                                                </td>
                                                <td class="px-3 py-3 text-sm">
                                                    <a href="{{ route('certificates.show', $certificate) }}" class="text-sm text-amber-600 hover:underline">{{ __('View / Print') }}</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="px-3 py-10 text-center">
                                                    <div class="flex flex-col items-center gap-2">
                                                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                                        </span>
                                                        <p class="text-sm text-gray-500">{{ __('No certificates issued yet.') }}</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div x-show="tab === 'documents'">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 mb-2">{{ __('Documents') }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">{{ __('Passport Photo') }}</p>
                                    @if ($student->photo_path)
                                        <img src="{{ Storage::url($student->photo_path) }}" alt="{{ __('Passport photo') }}" class="h-16 w-16 object-cover rounded-md border border-gray-200">
                                    @else
                                        <span class="text-sm text-gray-400">{{ __('Not uploaded') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Identification Document') }}</p>
                                    @if ($student->id_document_path)
                                        <p class="text-sm font-bold"><a href="{{ Storage::url($student->id_document_path) }}" target="_blank" class="text-amber-600 hover:underline">{{ __('View Document') }}</a></p>
                                    @else
                                        <p class="text-sm text-gray-400">{{ __('Not uploaded') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Licence Document') }}</p>
                                    @if ($student->license_document_path)
                                        <p class="text-sm font-bold"><a href="{{ Storage::url($student->license_document_path) }}" target="_blank" class="text-amber-600 hover:underline">{{ __('View Document') }}</a></p>
                                    @else
                                        <p class="text-sm text-gray-400">{{ __('Not uploaded') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                                <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Licence Number') }}</p>
                                    <p class="text-sm font-bold text-gray-900">{{ $student->license_number ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('students.edit', $student) }}" class="mt-4 inline-block text-sm text-amber-600 hover:underline">{{ __('Upload or replace a document') }}</a>
                    </div>
                </div>

                <div class="flex justify-end relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="inline-flex items-center gap-1 text-sm font-medium text-white bg-black hover:bg-gray-800 rounded-md px-4 py-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Actions') }}
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 bottom-full mb-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                        <a href="{{ route('students.edit', $student) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit Student') }}</a>
                        <a href="{{ route('students.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Back to List') }}</a>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
