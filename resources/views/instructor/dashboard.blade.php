<x-guest-layout>
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="text-lg font-semibold text-gray-800">{{ __('Welcome, :name', ['name' => $instructor->name]) }}</p>
            <p class="text-sm text-gray-500">{{ now()->format('l, F j, Y') }}</p>
        </div>
        <form method="post" action="{{ route('instructor.logout') }}">
            @csrf
            <x-secondary-button type="submit">{{ __('Log Out') }}</x-secondary-button>
        </form>
    </div>

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">📘 {{ __("Today's Theory Class") }}</h3>
            @if ($todaysTheoryClass)
                <p class="text-sm text-gray-800 font-medium">{{ $todaysTheoryClass->topic }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Starts at') }} {{ \Carbon\Carbon::createFromFormat('H:i', $todaysTheoryClass->start_time)->format('g:i A') }}</p>
            @else
                <p class="text-sm text-gray-500">{{ __("No theory class assigned to you today.") }}</p>
            @endif
        </div>

        @if (now()->isSunday())
            <div class="rounded-lg border border-gray-200 p-4">
                <p class="text-sm text-gray-500">{{ __('The school is closed on Sundays - no practical training expected today.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg p-4 bg-emerald-50 border border-emerald-200">
                    <p class="text-xs uppercase tracking-wider text-emerald-700 font-semibold">🟢 {{ __('Present') }}</p>
                    <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $presentToday->count() }}</p>
                </div>
                <div class="rounded-lg p-4 bg-red-50 border border-red-200">
                    <p class="text-xs uppercase tracking-wider text-red-700 font-semibold">🔴 {{ __('Absent') }}</p>
                    <p class="text-3xl font-bold text-red-700 mt-1">{{ $absentToday->count() }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">🟢 {{ __('Present Today') }}</h3>
                @if ($presentToday->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('No one has checked in yet today.') }}</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($presentToday as $attendance)
                            <div class="py-2 text-sm">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="font-medium text-gray-800">{{ $attendance->student->name }}</span>
                                    <span class="text-xs text-gray-500 whitespace-nowrap">{{ $attendance->created_at->format('g:i A') }}</span>
                                </div>
                                <p class="text-xs text-gray-500">{{ $attendance->course->name }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">🔴 {{ __('Absent Today') }}</h3>
                @if ($absentToday->isEmpty())
                    <p class="text-sm text-gray-500">{{ __('Everyone expected today has checked in.') }}</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach ($absentToday as $enrollment)
                            <div class="py-2 text-sm">
                                <span class="font-medium text-gray-800">{{ $enrollment->student->name }}</span>
                                <span class="text-gray-500"> — {{ $enrollment->course->name }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-guest-layout>
