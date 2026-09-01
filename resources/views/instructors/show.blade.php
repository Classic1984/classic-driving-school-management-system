<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Instructor Details') }}
        </h2>
    </x-slot>

    @php
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
        $phoneIconPath = 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z';
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $bookOpenIconPath = 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.25c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25';
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $lockIconPath = 'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z';
        $arrowLeftIconPath = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                            <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-2xl font-extrabold text-gray-900 truncate">{{ $instructor->name }}</h3>
                            <p class="text-sm text-gray-500 capitalize">{{ $instructor->specialization }}</p>
                        </div>
                        <x-badge :color="$instructor->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $instructor->status }}</x-badge>
                    </div>

                    @if (session('status') === 'instructor-access-granted')
                        <p class="text-sm font-medium text-green-600 mb-4">{{ __('App access granted - the instructor has been texted a login link.') }}</p>
                    @elseif (session('status') === 'instructor-access-revoked')
                        <p class="text-sm font-medium text-green-600 mb-4">{{ __('App access revoked.') }}</p>
                    @elseif (session('status') === 'instructor-access-resent')
                        <p class="text-sm font-medium text-green-600 mb-4">{{ __('Login instructions re-sent.') }}</p>
                    @endif
                    <x-input-error :messages="$errors->get('instructor')" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $envelopeIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Email') }}</p>
                                <p class="text-sm font-bold text-gray-900 break-all">{{ $instructor->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $phoneIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Phone') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $instructor->phone }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('License Number') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $instructor->license_number ?? '—' }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Hire Date') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $instructor->hire_date->format('Y-m-d') }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $bookOpenIconPath }}" /></svg>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500">{{ __('Courses') }}</p>
                                @forelse ($instructor->courses as $course)
                                    <p class="text-sm font-bold text-gray-900">{{ $course->name }}</p>
                                @empty
                                    <p class="text-sm font-bold text-gray-900">—</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-start gap-3 rounded-lg bg-amber-50 ring-1 ring-amber-100 p-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lockIconPath }}" /></svg>
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 mb-1.5">{{ __('App Access') }}</p>
                            @if ($instructor->hasAppAccess())
                                <x-badge :color="$instructor->user->pin_set_at ? 'green' : 'amber'">
                                    {{ $instructor->user->pin_set_at ? __('Active') : __('Pending first login') }}
                                </x-badge>
                                @if (auth()->user()->canManageCourses())
                                    @if (! $instructor->user->pin_set_at)
                                        <form method="post" action="{{ route('instructors.access.resend', $instructor) }}" class="inline ms-2">
                                            @csrf
                                            <button type="submit" class="text-sm text-amber-700 hover:underline">{{ __('Resend Login SMS') }}</button>
                                        </form>
                                    @endif
                                    <form method="post" action="{{ route('instructors.access.destroy', $instructor) }}" class="inline ms-2" onsubmit="return confirm('{{ __('Revoke this instructor\'s app access? Their PIN will stop working immediately.') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Revoke Access') }}</button>
                                    </form>
                                @endif
                            @else
                                <x-badge color="gray">{{ __('Not Enabled') }}</x-badge>
                                @if (auth()->user()->canManageCourses())
                                    <form method="post" action="{{ route('instructors.access.store', $instructor) }}" class="inline ms-2">
                                        @csrf
                                        <button type="submit" class="text-sm text-amber-700 hover:underline">{{ __('Enable App Access') }}</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        @if (auth()->user()->canManageCourses())
                            <a href="{{ route('instructors.edit', $instructor) }}">
                                <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                            </a>
                        @endif
                        <a href="{{ route('instructors.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowLeftIconPath }}" /></svg>
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
