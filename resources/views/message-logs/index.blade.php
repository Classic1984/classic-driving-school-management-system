<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Message Delivery Log') }}
        </h2>
    </x-slot>

    @php
        $reminderTypes = [
            'balance_reminder' => ['label' => 'Balance Reminder', 'icon' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0'],
            'theory_class_reminder' => ['label' => 'Theory Class Reminder', 'icon' => 'M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25'],
            'lead_follow_up' => ['label' => 'Lead Follow-Up', 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75'],
            'absence_check_in' => ['label' => 'Absence Check-In', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z'],
        ];

        $purposeMeta = [
            'balance_reminder' => ['color' => 'green', 'icon' => $reminderTypes['balance_reminder']['icon']],
            'theory_class_reminder' => ['color' => 'purple', 'icon' => $reminderTypes['theory_class_reminder']['icon']],
            'theory_class_cancellation' => ['color' => 'rose', 'icon' => $reminderTypes['theory_class_reminder']['icon']],
            'lead_follow_up' => ['color' => 'amber', 'icon' => $reminderTypes['lead_follow_up']['icon']],
            'absence_check_in' => ['color' => 'blue', 'icon' => $reminderTypes['absence_check_in']['icon']],
            'training_days_remaining' => ['color' => 'teal', 'icon' => 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            'training_completed' => ['color' => 'emerald', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
            'certificate_ready' => ['color' => 'indigo', 'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
            'instructor_access_granted' => ['color' => 'sky', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z'],
            'student_access_granted' => ['color' => 'cyan', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z'],
        ];

        $colorClasses = [
            'green' => ['badge' => 'bg-green-100 text-green-700', 'icon' => 'bg-green-100 text-green-500', 'border' => 'border-green-500'],
            'purple' => ['badge' => 'bg-purple-100 text-purple-700', 'icon' => 'bg-purple-100 text-purple-500', 'border' => 'border-purple-500'],
            'rose' => ['badge' => 'bg-rose-100 text-rose-700', 'icon' => 'bg-rose-100 text-rose-500', 'border' => 'border-rose-500'],
            'amber' => ['badge' => 'bg-amber-100 text-amber-700', 'icon' => 'bg-amber-100 text-amber-500', 'border' => 'border-amber-500'],
            'blue' => ['badge' => 'bg-blue-100 text-blue-700', 'icon' => 'bg-blue-100 text-blue-500', 'border' => 'border-blue-500'],
            'teal' => ['badge' => 'bg-teal-100 text-teal-700', 'icon' => 'bg-teal-100 text-teal-500', 'border' => 'border-teal-500'],
            'emerald' => ['badge' => 'bg-emerald-100 text-emerald-700', 'icon' => 'bg-emerald-100 text-emerald-500', 'border' => 'border-emerald-500'],
            'indigo' => ['badge' => 'bg-indigo-100 text-indigo-700', 'icon' => 'bg-indigo-100 text-indigo-500', 'border' => 'border-indigo-500'],
            'sky' => ['badge' => 'bg-sky-100 text-sky-700', 'icon' => 'bg-sky-100 text-sky-500', 'border' => 'border-sky-500'],
            'cyan' => ['badge' => 'bg-cyan-100 text-cyan-700', 'icon' => 'bg-cyan-100 text-cyan-500', 'border' => 'border-cyan-500'],
            'gray' => ['badge' => 'bg-gray-100 text-gray-700', 'icon' => 'bg-gray-100 text-gray-500', 'border' => 'border-gray-400'],
        ];

        $sortIcon = fn (string $column) => $sort === $column
            ? ($direction === 'asc' ? 'm4.5 15.75 7.5-7.5 7.5 7.5' : 'm19.5 8.25-7.5 7.5-7.5-7.5')
            : 'M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5';

        $sortUrl = fn (string $column) => route('message-log.index', array_merge(request()->query(), [
            'sort' => $column,
            'direction' => $sort === $column && $direction === 'asc' ? 'desc' : 'asc',
        ]));
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.126A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.876L5.999 12Zm0 0h7.5" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Message Delivery Log') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Track and manage all messages sent to students, leads and staff') }}</p>
                    </div>
                </div>

                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ __('Send New Message') }}
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                        @foreach ($reminderTypes as $type => $meta)
                            <form method="post" action="{{ route('reminders.send', $type) }}" onsubmit="return confirm('{{ __('This will immediately text every eligible recipient. Continue?') }}');">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Send :label Now', ['label' => $meta['label']]) }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            @if (session('status'))
                <p class="mb-4 text-sm font-medium text-green-600">{{ session('status') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900">{{ __('Quick Actions') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('Send messages instantly') }}</p>

                <div class="flex gap-4 overflow-x-auto pb-1">
                    @foreach ($reminderTypes as $type => $meta)
                        @php $accent = $colorClasses[$purposeMeta[$type]['color']]; @endphp
                        <form method="post" action="{{ route('reminders.send', $type) }}" onsubmit="return confirm('{{ __('This will immediately text every eligible recipient. Continue?') }}');" class="shrink-0 w-44">
                            @csrf
                            <div class="rounded-xl ring-1 ring-gray-200 border-b-4 {{ $accent['border'] }} p-4 text-center hover:shadow-md transition">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full {{ $accent['icon'] }}">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}" /></svg>
                                </span>
                                <p class="mt-3 text-sm font-semibold text-gray-800">{{ __($meta['label']) }}</p>
                                <button type="submit" class="mt-1 text-sm font-medium text-amber-600 hover:underline">{{ __('Send Now') }} →</button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>

            <div class="bg-amber-50/40 ring-1 ring-amber-200 border-l-4 border-amber-500 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Filter Messages') }}</h3>

                <form method="get" action="{{ route('message-log.index') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="search" :value="__('Search Recipient')" />
                        <div class="relative mt-1">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                            <x-text-input id="search" name="search" type="text" class="block w-full pl-9" placeholder="{{ __('Search by recipient name, phone or email') }}" :value="request('search')" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="purpose" :value="__('Purpose')" />
                        <select id="purpose" name="purpose" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All Purposes') }}</option>
                            @foreach (\App\Models\MessageLog::PURPOSES as $value => $label)
                                <option value="{{ $value }}" @selected(request('purpose') === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="sent" @selected(request('status') === 'sent')>{{ __('Delivered') }}</option>
                            <option value="failed" @selected(request('status') === 'failed')>{{ __('Failed') }}</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label :value="__('Date Range')" />
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm">
                        </div>
                    </div>

                    <div class="md:col-span-2 flex items-center gap-4">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                            {{ __('Apply Filters') }}
                        </button>
                        <a href="{{ route('message-log.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">{{ __('Message Delivery History') }}</h3>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">{{ __('Showing :first to :last of :total entries', ['first' => $messageLogs->firstItem() ?? 0, 'last' => $messageLogs->lastItem() ?? 0, 'total' => $messageLogs->total()]) }}</span>
                        <a href="{{ route('message-log.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            {{ __('Export') }}
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-3 py-3">
                                    <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center gap-1.5 hover:text-amber-900">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                        {{ __('Date & Time') }}
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortIcon('created_at') }}" /></svg>
                                    </a>
                                </th>
                                <th class="px-3 py-3">
                                    <a href="{{ $sortUrl('recipient_name') }}" class="inline-flex items-center gap-1.5 hover:text-amber-900">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                        {{ __('Recipient') }}
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortIcon('recipient_name') }}" /></svg>
                                    </a>
                                </th>
                                <th class="px-3 py-3">
                                    <a href="{{ $sortUrl('purpose') }}" class="inline-flex items-center gap-1.5 hover:text-amber-900">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.83.699 2.528 0l4.318-4.318a1.79 1.79 0 0 0 0-2.528L10.505 3.66A2.25 2.25 0 0 0 9.568 3Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" /></svg>
                                        {{ __('Purpose') }}
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortIcon('purpose') }}" /></svg>
                                    </a>
                                </th>
                                <th class="px-3 py-3">
                                    <a href="{{ $sortUrl('status') }}" class="inline-flex items-center gap-1.5 hover:text-amber-900">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12" /></svg>
                                        {{ __('Status') }}
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sortIcon('status') }}" /></svg>
                                    </a>
                                </th>
                                <th class="px-3 py-3">{{ __('Channel') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($messageLogs as $log)
                                @php
                                    $meta = $purposeMeta[$log->purpose] ?? ['color' => 'gray', 'icon' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z'];
                                    $accent = $colorClasses[$meta['color']];
                                    $initials = collect(explode(' ', $log->recipient_name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                    $profileRoute = match (true) {
                                        $log->recipient_type === 'student' && $log->recipient_id => route('students.show', $log->recipient_id),
                                        $log->recipient_type === 'lead' && $log->recipient_id => route('leads.edit', $log->recipient_id),
                                        $log->recipient_type === 'instructor' && $log->recipient_id => route('instructors.show', $log->recipient_id),
                                        default => null,
                                    };
                                @endphp
                                <tr class="border-l-4 {{ $accent['border'] }}">
                                    <td class="px-3 py-3 text-sm align-top">
                                        <p class="font-semibold text-gray-800">{{ $log->created_at->format('M j, Y') }}</p>
                                        <p class="text-xs text-gray-400">{{ $log->created_at->format('g:i A') }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $accent['icon'] }} text-xs font-bold">{{ $initials }}</span>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-800 truncate">{{ $log->recipient_name }}</p>
                                                <p class="text-xs text-gray-400 capitalize">({{ $log->recipient_type }})</p>
                                                @if ($log->recipient_phone)
                                                    <p class="text-xs text-gray-400">{{ $log->recipient_phone }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $accent['badge'] }}">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}" /></svg>
                                            {{ __(\App\Models\MessageLog::PURPOSES[$log->purpose] ?? $log->purpose) }}
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500 max-w-xs truncate" title="{{ $log->message }}">{{ $log->message ?? '—' }}</p>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        @if ($log->status === 'sent')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">{{ __('Delivered') }}</span>
                                            <p class="mt-1 text-xs text-gray-400">{{ __(':channel sent', ['channel' => $log->channel ? strtoupper($log->channel) : __('Message')]) }}</p>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">{{ __('Failed') }}</span>
                                            <p class="mt-1 text-xs text-gray-400">{{ __('Delivery failed') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        @if ($log->channel === 'whatsapp')
                                            <span class="inline-flex items-center gap-1.5 text-green-600">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347Z"/><path d="M12.001 2C6.478 2 2 6.478 2 12c0 1.821.487 3.53 1.338 5.003L2 22l5.13-1.318A9.958 9.958 0 0 0 12.001 22C17.523 22 22 17.523 22 12S17.523 2 12.001 2Zm0 18.111c-1.643 0-3.17-.463-4.469-1.264l-.32-.19-3.146.809.827-3.07-.207-.325A8.09 8.09 0 0 1 3.889 12c0-4.474 3.639-8.111 8.112-8.111 4.474 0 8.111 3.637 8.111 8.111 0 4.474-3.637 8.111-8.111 8.111Z"/></svg>
                                                {{ __('WhatsApp') }}
                                            </span>
                                        @elseif ($log->channel)
                                            <span class="inline-flex items-center gap-1.5 text-gray-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" /></svg>
                                                {{ strtoupper($log->channel) }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-right">
                                        @if ($profileRoute)
                                            <a href="{{ $profileRoute }}" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No messages logged yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                    <form method="get" action="{{ route('message-log.index') }}" class="flex items-center gap-2 text-sm text-gray-600">
                        @foreach (request()->except('per_page', 'page') as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        {{ __('Show') }}
                        <select name="per_page" class="rounded-md border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm" onchange="this.form.submit()">
                            @foreach ([10, 25, 50, 100] as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                        {{ __('entries') }}
                    </form>

                    {{ $messageLogs->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
