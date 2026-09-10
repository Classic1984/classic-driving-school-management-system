<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theory Classes') }}
        </h2>
    </x-slot>

    @php
        $bookIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $clipboardIconPath = 'M9 4.5h6M9 4.5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 4.5M9 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v12A2.25 2.25 0 0 0 6.75 21h10.5a2.25 2.25 0 0 0 2.25-2.25v-12A2.25 2.25 0 0 0 17.25 4.5H15M9 12.75l2.25 2.25L15 10.5';
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $trashIconPath = 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0';
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Theory Classes') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Manage and track theory class sessions') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookIconPath }}" /></svg>
                </span>
            </div>

            @if (session('status') === 'theory-class-created')
                <p class="text-sm font-medium text-green-600">{{ __("Today's class was created.") }}</p>
            @elseif (session('status') === 'theory-class-cancelled-today')
                <p class="text-sm font-medium text-amber-600">{{ __("Today's theory class is cancelled - see Theory Class Cancellations.") }}</p>
            @elseif (session('status') === 'theory-class-deleted')
                <p class="text-sm font-medium text-green-600">{{ __('Theory class deleted.') }}</p>
            @endif

            @if (auth()->user()->canManageCourses() && ! $todaysClassExists && ! $todaysClassCancelled)
                <div class="relative overflow-hidden rounded-xl bg-gradient-to-r from-amber-50 via-amber-50 to-white ring-1 ring-amber-100 p-6">
                    <svg class="pointer-events-none absolute -right-4 -bottom-4 h-28 w-28 text-amber-500 opacity-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>

                    <div class="relative flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <div class="flex flex-1 items-start gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            </span>
                            <div>
                                <h4 class="text-lg font-bold text-amber-900">{{ __('No class created for today') }}</h4>
                                <p class="mt-1 text-sm text-amber-800">
                                    {{ __("Today's class hasn't been created yet - it's normally auto-created at 8am. If the reminder run was missed, create it here instead.") }}
                                </p>
                            </div>
                        </div>

                        <form method="post" action="{{ route('theory-classes.create-today') }}" class="shrink-0">
                            @csrf
                            <button type="submit" class="flex flex-col items-center justify-center gap-2 rounded-xl bg-black hover:bg-gray-900 px-6 py-5 text-center transition">
                                <svg class="h-6 w-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                <span class="text-xs font-bold uppercase tracking-wide text-amber-400 leading-tight">{{ __("Create Today's Class") }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-black p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-amber-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardIconPath }}" /></svg>
                    <div class="relative flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-400/30">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clipboardIconPath }}" /></svg>
                        </span>
                        <div>
                            <h3 class="text-xl font-bold text-white">{{ __('Class Roster History') }}</h3>
                            <p class="text-sm text-gray-400">{{ __('Overview of recent theory classes') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 sm:p-8">
                    <div class="overflow-hidden rounded-xl ring-1 ring-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-black">
                                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-amber-400">
                                        <th class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1.5">
                                                <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                                {{ __('Date') }}
                                            </span>
                                        </th>
                                        <th class="px-4 py-3">{{ __('Topic') }}</th>
                                        <th class="px-4 py-3">{{ __('Instructor') }}</th>
                                        <th class="px-4 py-3">{{ __('Present') }}</th>
                                        <th class="px-4 py-3">{{ __('Absent') }}</th>
                                        <th class="px-4 py-3">{{ __('Attendance') }}</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($theoryClasses as $theoryClass)
                                        <tr class="hover:bg-amber-50/40 transition">
                                            <td class="px-4 py-3 text-sm align-top">
                                                <div class="flex items-center gap-2">
                                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-500">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                                                    </span>
                                                    <div>
                                                        <p class="font-semibold text-gray-800">
                                                            {{ $theoryClass->class_date->format('l, M j, Y') }}
                                                            @if ($theoryClass->class_date->isToday())
                                                                <x-badge color="amber" class="ms-1">{{ __('Today') }}</x-badge>
                                                            @endif
                                                        </p>
                                                        <p class="text-xs text-gray-400">{{ $theoryClass->class_date->format('D') }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm align-top text-gray-600">
                                                {{ $theoryClass->topic ?: '—' }}
                                                @if ($theoryClass->materials_path)
                                                    <a href="{{ $theoryClass->materialsUrl() }}" target="_blank" title="{{ __('Lecture material') }}" class="ms-1 text-gray-400 hover:text-amber-600">📎</a>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $theoryClass->instructor?->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm align-top">
                                                <div class="flex items-center gap-1.5 text-green-600 font-bold">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                                                    {{ $theoryClass->presentCount() }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm align-top">
                                                <div class="flex items-center gap-1.5 text-red-600 font-bold">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                                    {{ $theoryClass->absentCount() }}
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $theoryClass->attendancePercentage() }}%</td>
                                            <td class="px-4 py-3 text-sm align-top text-right whitespace-nowrap">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('theory-classes.show', $theoryClass) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition" title="{{ __('View Roster') }}">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                                    </a>
                                                    @if (auth()->user()->isAdmin())
                                                        <form method="post" action="{{ route('theory-classes.destroy', $theoryClass) }}" onsubmit="return confirm('{{ __('Delete this theory class and its attendance records? This cannot be undone.') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-red-100 hover:text-red-600 transition" title="{{ __('Delete') }}">
                                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $trashIconPath }}" /></svg>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                                {{ __('No theory classes have been held yet.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-4">
                        {{ $theoryClasses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
