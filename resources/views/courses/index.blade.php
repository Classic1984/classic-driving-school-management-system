<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Courses') }}
        </h2>
    </x-slot>

    @php
        $statusAccent = [
            'active' => ['color' => 'green', 'border' => 'border-green-500'],
            'inactive' => ['color' => 'gray', 'border' => 'border-gray-300'],
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Courses') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Manage every training programme this school offers') }}</p>
                    </div>
                </div>

                @if (auth()->user()->canManageCourses())
                    <a href="{{ route('courses.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Add Course') }}
                    </a>
                @endif
            </div>

            @if (session('status') === 'course-created')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Course created successfully.') }}</p>
            @elseif (session('status') === 'course-updated')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Course updated successfully.') }}</p>
            @elseif (session('status') === 'course-deleted')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Course removed successfully.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Course Records') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                                        {{ __('Name') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">{{ __('Type') }}</th>
                                <th class="px-3 py-3">{{ __('Schedule') }}</th>
                                <th class="px-3 py-3">{{ __('Duration') }}</th>
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                                        {{ __('Fee') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        {{ __('Instructors') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                                        {{ __('Students') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($courses as $course)
                                @php
                                    $accent = $statusAccent[$course->status] ?? ['color' => 'gray', 'border' => 'border-gray-300'];
                                @endphp
                                <tr class="border-l-4 {{ $accent['border'] }}">
                                    <td class="px-3 py-3 text-sm align-top font-semibold text-gray-800">{{ $course->name }}</td>
                                    <td class="px-3 py-3 text-sm align-top capitalize text-gray-600">{{ $course->course_type }}</td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$course->isWeekend() ? 'blue' : 'gray'" class="capitalize">{{ $course->schedule }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $course->duration_hours }}h</td>
                                    <td class="px-3 py-3 text-sm align-top font-semibold text-gray-800">₦{{ number_format($course->fee, 2) }}</td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $course->instructors->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $course->students->count() }}</td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$accent['color']" class="capitalize">{{ $course->status }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-right">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                                                <a href="{{ route('courses.show', $course) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('View') }}</a>
                                                @if (auth()->user()->canManageCourses())
                                                    <a href="{{ route('courses.edit', $course) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                                @endif
                                                @if (auth()->user()->isAdmin())
                                                    <form method="post" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this course?') }}');">
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
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No courses created yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $courses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
