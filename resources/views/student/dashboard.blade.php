<x-guest-layout>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="text-lg font-semibold text-gray-800">{{ __('Welcome, :name', ['name' => $student->name]) }}</p>
            <p class="text-sm text-gray-500">{{ $student->student_id_number }}</p>
        </div>
        <form method="post" action="{{ route('student.logout') }}">
            @csrf
            <x-secondary-button type="submit">{{ __('Log Out') }}</x-secondary-button>
        </form>
    </div>

    <div class="space-y-6">
        @forelse ($enrollments as $row)
            <div class="rounded-lg border border-gray-200 p-4">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-800">{{ $row['course']->name }}</h3>
                    <x-badge :color="match ($row['statusLabel']) {
                        'Registered' => 'gray',
                        'Locked' => 'red',
                        'Completed' => 'blue',
                        'Certified' => 'amber',
                        default => 'green',
                    }">{{ __($row['statusLabel']) }}</x-badge>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">{{ __('Practical Training') }}</p>
                        <p class="text-gray-800">{{ __(':attended of :total days', ['attended' => $row['attendedDays'], 'total' => $row['totalDays']]) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">{{ __('Balance Owed') }}</p>
                        <p class="text-gray-800">₦{{ number_format($row['balance'], 2) }}</p>
                    </div>
                </div>

                @if ($row['enrollment']->status === 'locked')
                    <p class="mt-2 text-xs text-red-600">{{ $row['enrollment']->lockedReasonLabel() }}</p>
                @endif

                @if ($row['certificate'])
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-xs text-gray-500">{{ __('Certificate') }}: {{ $row['certificate']->certificate_number }}</p>
                        <a href="{{ $row['certificate']->verificationUrl() }}" class="text-xs text-amber-600 hover:underline" target="_blank" rel="noopener">{{ __('Verify Certificate') }}</a>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">{{ __('No course enrollments on file yet.') }}</p>
            </div>
        @endforelse

        <div class="rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">📘 {{ __('Theory Class Progress') }}</h3>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-gray-500">{{ __('Classes Attended') }}</p>
                    <p class="text-gray-800">{{ $theoryProgress['classes_attended'] }} / {{ $theoryProgress['classes_expected'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Attendance') }}</p>
                    <p class="text-gray-800">{{ $theoryProgress['attendance_percentage'] }}%</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Average Score') }}</p>
                    <p class="text-gray-800">{{ $theoryProgress['average_score'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">{{ __('Topics Completed') }}</p>
                    <p class="text-gray-800">{{ $theoryProgress['topics_completed'] }}</p>
                </div>
            </div>

            @if ($theoryProgress['outstanding_topics']->isNotEmpty())
                <p class="mt-3 text-xs text-gray-500">
                    {{ __('Missed topics to catch up on') }}: {{ $theoryProgress['outstanding_topics']->implode(', ') }}
                </p>
            @endif
        </div>
    </div>
</x-guest-layout>
