<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Instructors') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Instructors') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Manage every instructor on staff') }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('instructor-activity-report.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                        {{ __('View Activity Report') }}
                    </a>
                    @if (auth()->user()->canManageCourses())
                        <a href="{{ route('instructors.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                            {{ __('Add Instructor') }}
                        </a>
                    @endif
                </div>
            </div>

            @if (session('status') === 'instructor-created')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Instructor registered successfully.') }}</p>
            @elseif (session('status') === 'instructor-updated')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Instructor updated successfully.') }}</p>
            @elseif (session('status') === 'instructor-deleted')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Instructor removed successfully.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Instructor Records') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        {{ __('Name') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">{{ __('Email') }}</th>
                                <th class="px-3 py-3">{{ __('Phone') }}</th>
                                <th class="px-3 py-3">{{ __('Specialization') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3">{{ __('App Access') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($instructors as $instructor)
                                @php
                                    $initials = collect(explode(' ', $instructor->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                @endphp
                                <tr class="border-l-4 {{ $instructor->status === 'active' ? 'border-green-500' : 'border-gray-300' }}">
                                    <td class="px-3 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400 text-xs font-bold">{{ $initials }}</span>
                                            <span class="font-semibold text-gray-800">{{ $instructor->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $instructor->email }}</td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $instructor->phone }}</td>
                                    <td class="px-3 py-3 text-sm align-top capitalize text-gray-600">{{ $instructor->specialization }}</td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$instructor->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $instructor->status }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        @if ($instructor->hasAppAccess())
                                            <x-badge :color="$instructor->user->pin_set_at ? 'green' : 'amber'">
                                                {{ $instructor->user->pin_set_at ? __('Active') : __('Pending first login') }}
                                            </x-badge>
                                        @else
                                            <x-badge color="gray">{{ __('Not Enabled') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-right">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                                                <a href="{{ route('instructors.show', $instructor) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('View') }}</a>
                                                @if (auth()->user()->canManageCourses())
                                                    <a href="{{ route('instructors.edit', $instructor) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                                @endif
                                                @if (auth()->user()->isAdmin())
                                                    <form method="post" action="{{ route('instructors.destroy', $instructor) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this instructor?') }}');">
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
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No instructors registered yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $instructors->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
