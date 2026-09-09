<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Training Progress') }}
        </h2>
    </x-slot>

    @php
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';

        $rowPalette = [
            ['border' => 'border-amber-400', 'bg' => 'bg-amber-50/70', 'avatar' => 'bg-amber-400'],
            ['border' => 'border-blue-400', 'bg' => 'bg-blue-50/70', 'avatar' => 'bg-blue-400'],
            ['border' => 'border-purple-400', 'bg' => 'bg-purple-50/70', 'avatar' => 'bg-purple-400'],
            ['border' => 'border-green-400', 'bg' => 'bg-green-50/70', 'avatar' => 'bg-green-400'],
            ['border' => 'border-rose-400', 'bg' => 'bg-rose-50/70', 'avatar' => 'bg-rose-400'],
        ];
        $gridCols = 'grid-cols-[40px_1.8fr_110px_1.5fr_140px_90px_90px_110px_140px_100px_130px]';
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-gradient-to-r from-blue-950 via-blue-900 to-blue-800 p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-blue-400/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-500/15 text-blue-300 ring-1 ring-blue-400/30">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ __('Student Training Progress') }}</h3>
                                <p class="text-sm text-blue-200">{{ __('Every active enrollment and how far along it is') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-rose-500 px-4 py-2 text-sm font-bold text-white">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                            {{ $enrollments->total() }} {{ __('Enrollments') }}
                        </span>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="overflow-x-auto">
                        <div class="w-full min-w-[1400px]">
                            <div class="grid {{ $gridCols }} gap-4 rounded-t-lg bg-blue-950 px-4 py-3 text-[11px] font-bold uppercase tracking-wide text-blue-200">
                                <span>#</span>
                                <span>{{ __('Student') }}</span>
                                <span>{{ __('Student ID') }}</span>
                                <span>{{ __('Program') }}</span>
                                <span>{{ __('Start Date') }}</span>
                                <span>{{ __('Total Days') }}</span>
                                <span>{{ __('Days Used') }}</span>
                                <span>{{ __('Days Remaining') }}</span>
                                <span>{{ __('Expected Completion') }}</span>
                                <span>{{ __('Completion') }}</span>
                                <span>{{ __('Status') }}</span>
                            </div>

                            <div class="space-y-2 pt-2">
                                @forelse ($enrollments as $enrollment)
                                    @php
                                        $trainingProgressInitials = collect(explode(' ', $enrollment->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                        $rowAccent = $rowPalette[$loop->index % count($rowPalette)];
                                        $rowNumber = $enrollments->firstItem() + $loop->index;
                                        $label = $enrollment->trainingStatusLabel();
                                    @endphp
                                    <div class="grid {{ $gridCols }} gap-4 items-center rounded-lg border-l-4 {{ $rowAccent['border'] }} {{ $rowAccent['bg'] }} px-4 py-4">
                                        <div class="text-sm font-semibold text-gray-400">{{ $rowNumber }}</div>
                                        <div class="min-w-0">
                                            <a href="{{ route('students.show', $enrollment->student_id) }}" class="group flex items-center gap-2.5">
                                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold text-white {{ $rowAccent['avatar'] }}">{{ $trainingProgressInitials }}</span>
                                                <span class="truncate font-semibold text-gray-900 group-hover:text-amber-600">{{ $enrollment->student->name }}</span>
                                            </a>
                                        </div>
                                        <div class="text-sm font-mono text-gray-600">{{ $enrollment->student->student_id_number }}</div>
                                        <div class="min-w-0 text-sm text-gray-600">{{ $enrollment->course->name }} ({{ $enrollment->course->duration_weeks }} {{ __('Weeks') }} / {{ $enrollment->course->totalTrainingDays() }} {{ __('Days') }})</div>
                                        <div class="text-sm text-gray-600">{{ optional($enrollment->enrolled_at)->format('l, M j, Y') ?? '—' }}</div>
                                        <div class="text-sm text-gray-600">{{ $enrollment->course->totalTrainingDays() }}</div>
                                        <div class="text-sm text-gray-600">{{ $enrollment->attendedDays() }}</div>
                                        <div class="text-sm text-gray-600">{{ $enrollment->remainingTrainingDays() }}</div>
                                        <div class="text-sm text-gray-600">{{ optional($enrollment->expectedCompletionDate())->format('l, M j, Y') ?? '—' }}</div>
                                        <div class="text-sm font-bold text-gray-900">{{ $enrollment->trainingCompletionPercentage() }}%</div>
                                        <div>
                                            <x-badge :color="match ($label) {
                                                'Completed' => 'blue',
                                                'Expired' => 'red',
                                                default => 'green',
                                            }">{{ __($label) }}</x-badge>
                                            @if ($enrollment->status === 'locked')
                                                <span class="block text-xs text-gray-500 mt-0.5">{{ $enrollment->lockedReasonLabel() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-4 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                            </span>
                                            <p class="text-sm text-gray-500">{{ __('No enrollments yet.') }}</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        {{ $enrollments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
