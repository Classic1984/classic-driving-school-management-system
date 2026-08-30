<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Enrolled Trainees') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8">
                <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Enrolled Trainees') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Monitor and track student training in real-time') }}</p>
                    </div>
                    <a href="{{ route('attendances.index') }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-amber-300 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                        {{ __('View All Training Logins') }}
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100 rounded-xl ring-1 ring-gray-200 mb-6">
                    <div class="flex items-center gap-3 p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" /></svg>
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['total_students'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('Total Students') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['in_progress'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('In Progress') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['completed'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('Completed') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-purple-100 text-purple-500">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </span>
                        <div>
                            <p class="text-xl font-extrabold text-gray-900 leading-none">{{ $trainingProgressStats['not_started'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 whitespace-nowrap">{{ __('Not Started') }}</p>
                        </div>
                    </div>
                </div>

                @if (session('status') === 'training-logged')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Training logged successfully.') }}</p>
                @endif

                <form method="get" action="{{ route('enrolled-trainees.index') }}" class="flex gap-2 max-w-xl mb-6">
                    <x-text-input name="search" type="text" class="flex-1" placeholder="{{ __('Name, email, or phone') }}" :value="request('search')" />
                    <x-primary-button type="submit">{{ __('Search') }}</x-primary-button>
                </form>

                <div class="grid grid-cols-1 gap-4">
                    @forelse ($trainees as $trainee)
                        @php
                            $lastLogin = $trainee->attendances->first();
                            $primaryEnrolledCourse = $trainee->courses->first(fn ($c) => $c->pivot->status !== 'completed') ?? $trainee->courses->last();
                            $label = $primaryEnrolledCourse?->pivot->trainingStatusLabel() ?? 'Active';
                            $percent = $primaryEnrolledCourse?->pivot->trainingCompletionPercentage();
                            $accent = match ($label) {
                                'Expired' => ['ring' => 'ring-red-200', 'bar' => 'bg-red-500', 'edge' => 'bg-red-500', 'dot' => 'bg-red-500', 'text' => 'text-red-700'],
                                'Completed' => ['ring' => 'ring-blue-200', 'bar' => 'bg-blue-500', 'edge' => 'bg-blue-500', 'dot' => 'bg-blue-500', 'text' => 'text-blue-700'],
                                default => ['ring' => 'ring-amber-200', 'bar' => 'bg-amber-500', 'edge' => 'bg-amber-500', 'dot' => 'bg-green-500', 'text' => 'text-green-700'],
                            };
                            $initials = collect(explode(' ', $trainee->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                            $transmissionLabels = ['manual' => 'Manual', 'automatic' => 'Automatic', 'both' => 'Auto & Manual'];
                            $transmissionLabel = $primaryEnrolledCourse ? ($transmissionLabels[$primaryEnrolledCourse->course_type] ?? null) : null;
                        @endphp
                        <div class="group relative flex flex-col overflow-hidden rounded-xl bg-white p-5 ring-1 {{ $accent['ring'] }} shadow-sm">
                            <span class="absolute inset-y-0 left-0 w-1 {{ $accent['edge'] }}"></span>

                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-black text-sm font-bold text-amber-400">
                                    {{ $initials }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <a href="{{ route('students.training-record', $trainee) }}" class="truncate font-semibold text-gray-900 hover:text-amber-600">{{ $trainee->name }}</a>
                                        <span class="inline-flex shrink-0 items-center gap-1.5">
                                            <span class="h-2 w-2 rounded-full {{ $accent['dot'] }}"></span>
                                            <span class="text-sm font-medium {{ $accent['text'] }}">{{ __($label) }}</span>
                                        </span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-amber-700">
                                        {{ $trainee->courses->pluck('name')->implode(', ') ?: '—' }}
                                        @if ($transmissionLabel)
                                            &middot; {{ $transmissionLabel }}
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('students.training-record', $trainee) }}" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-gray-400 group-hover:bg-amber-100 group-hover:text-amber-600 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                </a>
                            </div>

                            @if ($primaryEnrolledCourse)
                                <div class="mt-4">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-xs font-medium text-gray-500">{{ __('Overall Progress') }}</span>
                                        <span class="text-sm font-bold text-gray-800">{{ $percent }}%</span>
                                    </div>
                                    <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full {{ $accent['bar'] }} transition-all" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 border-t border-gray-100 pt-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $trainee->attendances->count() }}</p>
                                        <p class="text-xs text-gray-400">{{ __('Training Sessions') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $lastLogin?->updated_at->format('M j, Y g:i A') ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">{{ __('Last Modified') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                    </span>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $lastLogin?->loggedBy?->name ?? '—' }}</p>
                                        <p class="text-xs text-gray-400">{{ __('Modified By') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-1 border-t border-gray-100 pt-3 text-xs text-gray-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                                    {{ __('Applied') }} {{ $trainee->enrollment_date->format('M j, Y') }}
                                </span>
                                @if ($trainee->date_of_birth)
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        {{ __('DOB') }} {{ $trainee->date_of_birth->format('M j, Y') }}
                                    </span>
                                @endif
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                                    {{ $trainee->phone }}
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                                    {{ $trainee->email }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No enrolled trainees yet.') }}</p>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $trainees->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
