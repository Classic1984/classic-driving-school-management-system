<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Absence Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4 print:hidden">
                    <div class="flex items-center gap-2">
                        @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $value => $tabLabel)
                            <a
                                href="{{ route('absence-report.index', ['period' => $value]) }}"
                                class="px-3 py-1.5 text-sm rounded-md {{ $period === $value ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()">
                            <x-secondary-button type="button">{{ __('Print') }}</x-secondary-button>
                        </button>
                        <a href="{{ route('absence-report.export', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('absence-report.export-pdf', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                        </a>
                    </div>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    {{ __('Students absent') }}: <span class="font-semibold text-gray-900">{{ $attendances->pluck('student_id')->unique()->count() }}</span>
                    &middot; {{ __('Absences logged') }}: <span class="font-semibold text-gray-900">{{ $attendances->count() }}</span>
                </p>

                @forelse ($attendancesByDate as $date => $dayAttendances)
                    <div class="mb-6 last:mb-0">
                        <h4 class="text-sm font-semibold text-gray-800 mb-2">
                            {{ \Illuminate\Support\Carbon::parse($date)->format('l, j F Y') }}
                            <span class="font-normal text-gray-500">&middot; {{ trans_choice('{0} :count students|{1} :count student|[2,*] :count students', $dayAttendances->count(), ['count' => $dayAttendances->count()]) }}</span>
                        </h4>
                        <div class="overflow-x-auto rounded-md ring-1 ring-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                                        <th class="px-4 py-2">{{ __('Student ID') }}</th>
                                        <th class="px-4 py-2">{{ __('Student Name') }}</th>
                                        <th class="px-4 py-2">{{ __('Course') }}</th>
                                        <th class="px-4 py-2">{{ __('Training Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($dayAttendances as $attendance)
                                        <tr>
                                            <td class="px-4 py-2 font-mono text-sm">{{ $attendance->student->student_id_number }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                <a href="{{ route('students.show', $attendance->student_id) }}" class="text-amber-600 hover:underline print:text-gray-900 print:no-underline">
                                                    {{ $attendance->student->name }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 text-sm">{{ $attendance->course->name }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @php $trainingStatus = $enrollmentStatuses["{$attendance->student_id}:{$attendance->course_id}"] ?? null; @endphp
                                                @if ($trainingStatus)
                                                    <x-badge :color="match ($trainingStatus) {
                                                        'Completed' => 'blue',
                                                        'Expired' => 'red',
                                                        default => 'green',
                                                    }">{{ $trainingStatus }}</x-badge>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-6 text-center text-sm text-gray-500">
                        {{ __('No students were absent during this period.') }}
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
