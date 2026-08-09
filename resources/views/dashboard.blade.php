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

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                    <div class="bg-black text-amber-400 p-6 rounded-lg">
                        <h3 class="text-xl font-bold">Students</h3>
                        <p class="text-3xl mt-3">{{ number_format($stats['students']) }}</p>
                    </div>

                    <div class="bg-amber-500 text-black p-6 rounded-lg">
                        <h3 class="text-xl font-bold">Payments Today</h3>
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

                </div>

                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mt-6">{{ __('New Students') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">

                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __('Today') }}</h4>
                        <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($newStudentTotals['today']) }}</p>
                    </div>

                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __('This Week') }}</h4>
                        <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($newStudentTotals['week']) }}</p>
                    </div>

                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __('This Month') }}</h4>
                        <p class="text-xl font-bold text-gray-800 mt-1">{{ number_format($newStudentTotals['month']) }}</p>
                    </div>

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

                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __('This Week') }}</h4>
                            <p class="text-xl font-bold text-gray-800 mt-1">₦{{ number_format($paymentTotals['week'], 2) }}</p>
                        </div>

                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __('This Month') }}</h4>
                            <p class="text-xl font-bold text-gray-800 mt-1">₦{{ number_format($paymentTotals['month'], 2) }}</p>
                        </div>

                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">{{ __('All Time') }}</h4>
                            <p class="text-xl font-bold text-gray-800 mt-1">₦{{ number_format($paymentTotals['all_time'], 2) }}</p>
                        </div>

                    </div>
                @endif

            </div>

            @if ($outstandingPayments->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
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
                                            <x-badge color="gray">{{ __('Upcoming') }}</x-badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if ($lockedEnrollments->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
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

            @if ($trainingProgress->isNotEmpty())
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">{{ __('Student Training Progress') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <th class="pb-2 pr-4">{{ __('Student') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Student ID') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Program') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Total Days') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Days Used') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Days Remaining') }}</th>
                                    <th class="pb-2 pr-4">{{ __('Completion') }}</th>
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
                                        <td class="py-2 pr-4 font-mono">{{ $enrollment->student->student_id_number }}</td>
                                        <td class="py-2 pr-4">{{ $enrollment->course->name }}</td>
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