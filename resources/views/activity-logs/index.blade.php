<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Activity Log') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                </span>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Activity Log') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Track all system activities and important events') }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
                    <h3 class="text-xl font-bold text-gray-900">{{ __('Activity Log') }}</h3>

                    <form method="post" action="{{ route('backups.send') }}" onsubmit="return confirm('{{ __('Run the database backup now and email it to every Director?') }}');">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" /></svg>
                            {{ __('Send Backup Now') }}
                        </button>
                    </form>
                </div>

                @if (session('status'))
                    <p class="mb-4 text-sm font-medium {{ str_starts_with(session('status'), 'Backup email failed') ? 'text-red-600' : 'text-green-600' }}">{{ session('status') }}</p>
                @endif

                <div class="flex items-center gap-3 mb-6">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a7.723 7.723 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a7.65 7.65 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                    </span>
                    <div>
                        <span class="flex items-center gap-2 text-sm">
                            <span class="font-medium text-gray-500">{{ __('Background Scheduler') }}:</span>
                            @if ($schedulerStatus['state'] === 'running')
                                <x-badge color="green">{{ __('Running') }}</x-badge>
                            @elseif ($schedulerStatus['state'] === 'stale')
                                <x-badge color="red">{{ __('Not Running') }}</x-badge>
                            @else
                                <x-badge color="red">{{ __('Never Detected') }}</x-badge>
                            @endif
                        </span>
                        <p class="mt-0.5 text-sm text-gray-500">
                            @if ($schedulerStatus['state'] === 'running')
                                {{ __('last seen :time ago', ['time' => $schedulerStatus['last_seen_at']->diffForHumans(null, true)]) }}
                            @elseif ($schedulerStatus['state'] === 'stale')
                                {{ __('last seen :time ago - reminders and backups are not firing automatically', ['time' => $schedulerStatus['last_seen_at']->diffForHumans(null, true)]) }}
                            @else
                                {{ __('Reminders and backups are not firing automatically') }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-x-8 gap-y-1 rounded-xl bg-amber-50/60 px-5 py-3 mb-4 text-xs font-semibold uppercase tracking-wider text-amber-800">
                    <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>{{ __('Date & Time') }}</span>
                    <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>{{ __('User') }}</span>
                    <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 9 9l3 3 3.75-3.75M15 6h3.75V9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>{{ __('Action') }}</span>
                </div>

                @forelse ($activityLogs as $log)
                    @php
                        $iconMeta = $log->iconMeta();
                        $iconColorClasses = match ($iconMeta['color']) {
                            'sky' => 'bg-sky-100 text-sky-600',
                            'purple' => 'bg-purple-100 text-purple-600',
                            'red' => 'bg-red-100 text-red-600',
                            'green' => 'bg-green-100 text-green-600',
                            'blue' => 'bg-blue-100 text-blue-600',
                            'amber' => 'bg-amber-100 text-amber-600',
                            'indigo' => 'bg-indigo-100 text-indigo-600',
                            'orange' => 'bg-orange-100 text-orange-600',
                            'teal' => 'bg-teal-100 text-teal-600',
                            default => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <div class="relative flex gap-4 pb-6 last:pb-0">
                        @unless ($loop->last)
                            <span class="absolute left-[1.375rem] top-11 bottom-0 w-px bg-gray-200"></span>
                        @endunless
                        <span class="relative z-10 flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $iconColorClasses }}">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconMeta['icon'] }}" /></svg>
                        </span>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $log->created_at->format('Y-m-d') }}</p>
                                <p class="text-xs text-gray-400">{{ $log->created_at->format('g:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800 uppercase">{{ $log->user?->name ?? __('System') }}</p>
                                @if ($log->user)
                                    <p class="text-xs text-gray-400 capitalize">({{ $log->user->role }})</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm text-gray-800">{{ $log->description }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">{{ __('No activity recorded yet.') }}</p>
                @endforelse

                <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-gray-100 pt-5">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                        </span>
                        <span class="text-sm text-gray-600">{{ __('Total Activities') }}</span>
                        <span class="text-xl font-extrabold text-amber-600">{{ $activityLogs->total() }}</span>
                    </div>
                    <form method="get" action="{{ route('activity-log.index') }}">
                        <select name="period" class="rounded-lg ring-1 ring-gray-300 border-0 focus:border-amber-500 focus:ring-amber-500 text-sm font-semibold" onchange="this.form.submit()">
                            @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all_time' => 'All Time'] as $value => $optionLabel)
                                <option value="{{ $value }}" @selected($period === $value)>{{ __($optionLabel) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="mt-4">
                    {{ $activityLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
