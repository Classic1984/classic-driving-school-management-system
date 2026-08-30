<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Login Training') }}
        </h2>
    </x-slot>

    @php
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
        $clipboardIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $loginIconPath = 'M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $clockIconPath = 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $sunIconPath = 'M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z';
        $moonIconPath = 'M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z';
        $chartBarIconPath = 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z';

        $sessionAccent = [
            'Morning' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => $sunIconPath],
            'Afternoon' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'icon' => $sunIconPath],
            'Evening' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => $moonIconPath],
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $academicCapIconPath }}" /></svg>
                </span>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Student Login Training') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('View and manage student training login records.') }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-black text-amber-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardIconPath }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ __('Training Login Records') }}</h3>
                            <p class="text-sm text-gray-500">{{ __('Track student training logins.') }}</p>
                        </div>
                    </div>

                    @if (auth()->user()->canManageCourses())
                        <a href="{{ route('attendances.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-4 py-2.5 text-sm font-bold text-amber-400 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $loginIconPath }}" /></svg>
                            {{ __('Log Training') }}
                        </a>
                    @endif
                </div>

                @if (session('status') === 'attendance-created' || session('status') === 'training-logged')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Training logged successfully.') }}</p>
                @elseif (session('status') === 'attendance-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Training login updated successfully.') }}</p>
                @elseif (session('status') === 'attendance-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Training login removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                        {{ __('Date') }}
                                    </span>
                                </th>
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                                        {{ __('Time') }}
                                    </span>
                                </th>
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                                        {{ __('Session') }}
                                    </span>
                                </th>
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                        {{ __('Student') }}
                                    </span>
                                </th>
                                <th class="px-4 py-3">{{ __('Course') }}</th>
                                <th class="px-4 py-3">{{ __('Type') }}</th>
                                <th class="px-4 py-3">{{ __('Instructor') }}</th>
                                <th class="px-4 py-3">{{ __('Vehicle') }}</th>
                                <th class="px-4 py-3">{{ __('Duration') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($attendances as $attendance)
                                @php
                                    $session = $attendance->sessionPeriod();
                                    $accent = $sessionAccent[$session] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'icon' => $clockIconPath];
                                    $initials = collect(explode(' ', $attendance->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex flex-col items-center justify-center rounded-lg bg-amber-50 px-2 py-1 leading-none">
                                                <span class="text-[10px] font-bold uppercase text-amber-600">{{ $attendance->date->format('M') }}</span>
                                                <span class="text-sm font-extrabold text-gray-900">{{ $attendance->date->format('j') }}</span>
                                            </span>
                                            <span class="text-gray-600">{{ $attendance->date->format('Y-m-d') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-1.5 {{ $accent['text'] }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                                            <span class="text-gray-800">{{ $attendance->created_at->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        @if ($session)
                                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $accent['bg'] }} {{ $accent['text'] }}">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $accent['icon'] }}" /></svg>
                                                {{ __($session) }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">{{ $initials }}</span>
                                            <span class="font-semibold text-gray-800">{{ $attendance->student->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $attendance->course->name }}</td>
                                    <td class="px-4 py-3 text-sm align-top capitalize text-gray-600">{{ $attendance->type ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $attendance->instructor?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $attendance->vehicle?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $attendance->duration ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <x-badge :color="match ($attendance->status) {
                                            'present' => 'green',
                                            'absent' => 'red',
                                            'late' => 'amber',
                                            'excused' => 'blue',
                                            default => 'gray',
                                        }" class="capitalize">{{ $attendance->status }}</x-badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-right">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                                                <a href="{{ route('attendances.show', $attendance) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('View') }}</a>
                                                @if (auth()->user()->canManageCourses())
                                                    <a href="{{ route('attendances.edit', $attendance) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                                @endif
                                                @if (auth()->user()->isAdmin())
                                                    <form method="post" action="{{ route('attendances.destroy', $attendance) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this training login?') }}');">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No training logins yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $chartBarIconPath }}" /></svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Total Records') }}</p>
                            <p class="text-lg font-extrabold text-gray-900">{{ number_format($attendances->total()) }}</p>
                        </div>
                    </div>

                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
