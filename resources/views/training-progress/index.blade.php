<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Training Progress') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="pb-2 pr-4">{{ __('Student') }}</th>
                                <th class="pb-2 pr-4">{{ __('Student ID') }}</th>
                                <th class="pb-2 pr-4">{{ __('Program') }}</th>
                                <th class="pb-2 pr-4">{{ __('Start Date') }}</th>
                                <th class="pb-2 pr-4">{{ __('Total Days') }}</th>
                                <th class="pb-2 pr-4">{{ __('Days Used') }}</th>
                                <th class="pb-2 pr-4">{{ __('Days Remaining') }}</th>
                                <th class="pb-2 pr-4">{{ __('Expected Completion') }}</th>
                                <th class="pb-2 pr-4">{{ __('Completion') }}</th>
                                <th class="pb-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($enrollments as $enrollment)
                                <tr>
                                    <td class="py-2 pr-4">
                                        <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-amber-600 hover:underline">
                                            {{ $enrollment->student->name }}
                                        </a>
                                    </td>
                                    <td class="py-2 pr-4 font-mono">{{ $enrollment->student->student_id_number }}</td>
                                    <td class="py-2 pr-4">{{ $enrollment->course->name }} ({{ $enrollment->course->duration_weeks }} {{ __('Weeks') }} / {{ $enrollment->course->totalTrainingDays() }} {{ __('Days') }})</td>
                                    <td class="py-2 pr-4">{{ optional($enrollment->enrolled_at)->format('Y-m-d') ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $enrollment->course->totalTrainingDays() }}</td>
                                    <td class="py-2 pr-4">{{ $enrollment->attendedDays() }}</td>
                                    <td class="py-2 pr-4">{{ $enrollment->remainingTrainingDays() }}</td>
                                    <td class="py-2 pr-4">{{ optional($enrollment->expectedCompletionDate())->format('Y-m-d') ?? '—' }}</td>
                                    <td class="py-2 pr-4">{{ $enrollment->trainingCompletionPercentage() }}%</td>
                                    <td class="py-2">
                                        @php($label = $enrollment->trainingStatusLabel())
                                        <x-badge :color="match ($label) {
                                            'Completed' => 'blue',
                                            'Expired' => 'red',
                                            default => 'green',
                                        }">{{ __($label) }}</x-badge>
                                        @if ($enrollment->status === 'locked')
                                            <span class="block text-xs text-gray-500 mt-0.5">{{ $enrollment->lockedReasonLabel() }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-6 text-center text-sm text-gray-500">
                                        {{ __('No enrollments yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $enrollments->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
