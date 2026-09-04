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

            @php
                $courseStatCards = [
                    ['label' => 'Total Courses', 'value' => $courseStats['total'], 'icon' => 'bg-amber-50 text-amber-500', 'path' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
                    ['label' => 'Total Students', 'value' => $courseStats['total_students'], 'icon' => 'bg-blue-50 text-blue-500', 'path' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
                    ['label' => 'Instructors', 'value' => $courseStats['instructors'], 'icon' => 'bg-amber-50 text-amber-600', 'path' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
                    ['label' => 'Active Courses', 'value' => $courseStats['active'], 'icon' => 'bg-green-100 text-green-600', 'path' => 'M4.5 12.75l6 6 9-13.5'],
                ];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                @foreach ($courseStatCards as $card)
                    <div class="flex items-center gap-3 bg-white ring-1 ring-gray-200 rounded-xl p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $card['icon'] }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['path'] }}" /></svg>
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $card['value'] }}</p>
                            <p class="mt-1 text-xs text-gray-500 whitespace-nowrap">{{ __($card['label']) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-amber-400 to-amber-500 px-6 py-4">
                    <div class="flex items-center gap-2.5">
                        <svg class="h-5 w-5 text-black" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-1.519-3.75L12 17.25m0 0-1.481-3.75M12 17.25V21m-7.5-3.75h15A2.25 2.25 0 0 0 21 15V6.75A2.25 2.25 0 0 0 18.75 4.5H5.25A2.25 2.25 0 0 0 3 6.75v8.5a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        <h3 class="text-base font-bold text-black">{{ __('Course Records') }}</h3>
                    </div>

                    <form method="GET" action="{{ route('courses.index') }}" class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search courses...') }}" class="w-48 sm:w-64 rounded-lg border-0 pl-9 pr-3 py-2 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-2 focus:ring-black">
                        </div>
                        <button type="submit" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-black text-amber-400 hover:bg-gray-800 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                        </button>
                    </form>
                </div>

                <div class="p-6">

                <div class="sm:hidden space-y-3">
                    @forelse ($courses as $course)
                        @php
                            $accent = $statusAccent[$course->status] ?? ['color' => 'gray', 'border' => 'border-gray-300'];
                        @endphp
                        <div class="rounded-xl ring-1 ring-gray-200 border-l-4 {{ $accent['border'] }} p-4">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-gray-800">{{ $course->name }}</p>
                                @include('courses.partials.actions-dropdown', ['course' => $course])
                            </div>
                            <p class="text-xs capitalize text-gray-500 mt-0.5">{{ $course->course_type }} &middot; {{ $course->duration_hours }}h</p>

                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <x-badge :color="$course->isWeekend() ? 'blue' : 'gray'" class="capitalize">{{ $course->schedule }}</x-badge>
                                <x-badge :color="$accent['color']" class="capitalize">{{ $course->status }}</x-badge>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Fee') }}</p>
                                    <p class="font-semibold text-gray-800">₦{{ number_format($course->fee, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">{{ __('Students') }}</p>
                                    <p class="text-gray-600">{{ $course->students->count() }}</p>
                                </div>
                                <div class="col-span-2">
                                    <p class="text-xs text-gray-500">{{ __('Instructors') }}</p>
                                    <p class="text-gray-600">{{ $course->instructors->pluck('name')->join(', ') ?: '—' }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-xl ring-1 ring-gray-200 p-6 text-center text-sm text-gray-500">
                            {{ __('No courses created yet.') }}
                        </div>
                    @endforelse
                </div>

                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-3 py-3">#</th>
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
                                    <td class="px-3 py-3 text-sm align-top text-gray-500">{{ $courses->firstItem() + $loop->index }}</td>
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
                                        @include('courses.partials.actions-dropdown', ['course' => $course])
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-sm text-gray-500">
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
    </div>
</x-app-layout>
