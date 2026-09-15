{{--
    Shared applicant-list page for both the Driver's License and
    Learner's Permit sections. Rendered by
    ServiceApplicationController::applicants() with
    $title/$service/$routePrefix varying per section - one file instead of
    two near-identical blade views. The landing page for each section is
    the combined register-and-pay form (index.blade.php) instead - see
    the "Register Applicant" link above for why.
--}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    @php
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';

        $paymentStatusAccent = [
            'paid' => ['color' => 'green', 'border' => 'border-green-500'],
            'part_payment' => ['color' => 'amber', 'border' => 'border-amber-500'],
            'unpaid' => ['color' => 'red', 'border' => 'border-red-500'],
        ];
        $processingStatusColor = [
            'not_started' => 'gray',
            'processing' => 'blue',
            'completed' => 'green',
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ $title }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Walk-in applicants and students paying for this service, tracked separately from training') }}</p>
                    </div>
                </div>

                <a href="{{ route("{$routePrefix}.index") }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('Register Applicant') }}
                </a>
            </div>

            <div class="relative overflow-hidden rounded-2xl bg-black p-6 sm:p-8 mb-6">
                <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-amber-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>

                <div class="relative grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach ([
                        ['value' => $stats['total'], 'label' => 'Total Applicants', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z', 'color' => 'purple'],
                        ['value' => $stats['walk_in'], 'label' => 'Walk-in Customers', 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21', 'color' => 'blue'],
                        ['value' => $stats['existing_student'], 'label' => 'Existing Students', 'icon' => 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84A50.654 50.654 0 0 0 19.74 10.147m-15.482 0a50.717 50.717 0 0 1 7.74-3.342 50.717 50.717 0 0 1 7.74 3.342', 'color' => 'green'],
                        ['value' => $stats['pending_processing'], 'label' => 'Pending Processing', 'icon' => 'M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z', 'color' => 'amber'],
                    ] as $tile)
                        @php
                            $tileAccent = [
                                'purple' => ['icon' => 'bg-purple-500/15 text-purple-400', 'text' => 'text-purple-400', 'ring' => 'ring-purple-400/30'],
                                'blue' => ['icon' => 'bg-blue-500/15 text-blue-400', 'text' => 'text-blue-400', 'ring' => 'ring-blue-400/30'],
                                'green' => ['icon' => 'bg-green-500/15 text-green-400', 'text' => 'text-green-400', 'ring' => 'ring-green-400/30'],
                                'amber' => ['icon' => 'bg-amber-500/15 text-amber-400', 'text' => 'text-amber-400', 'ring' => 'ring-amber-400/30'],
                            ][$tile['color']];
                        @endphp
                        <div class="flex flex-col items-center text-center rounded-xl bg-white/5 ring-1 {{ $tileAccent['ring'] }} p-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg {{ $tileAccent['icon'] }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['icon'] }}" /></svg>
                            </span>
                            <p class="mt-2 text-2xl font-extrabold {{ $tileAccent['text'] }}">{{ $tile['value'] }}</p>
                            <p class="text-xs text-gray-400">{{ __($tile['label']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-amber-50/40 ring-1 ring-amber-200 border-l-4 border-amber-500 rounded-xl p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Filter Applicants') }}</h3>

                <form method="get" action="{{ route("{$routePrefix}.applicants") }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <x-input-label for="period" :value="__('Period')" />
                        <select id="period" name="period" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            @foreach (['all_time' => 'All Time', 'today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $value => $label)
                                <option value="{{ $value }}" @selected($period === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="date" :value="__('Specific Date')" />
                        <input type="date" id="date" name="date" value="{{ $date }}" max="{{ now()->format('Y-m-d') }}" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                        <p class="mt-1 text-xs text-gray-500">{{ __('Overrides Period when set.') }}</p>
                    </div>

                    <div>
                        <x-input-label for="source" :value="__('Source')" />
                        <select id="source" name="source" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            <option value="walk_in" @selected($source === 'walk_in')>{{ __('Walk-in') }}</option>
                            <option value="existing_student" @selected($source === 'existing_student')>{{ __('Existing Student') }}</option>
                        </select>
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            <optgroup label="{{ __('Payment') }}">
                                <option value="paid" @selected($status === 'paid')>{{ __('Paid') }}</option>
                                <option value="part_payment" @selected($status === 'part_payment')>{{ __('Part Payment') }}</option>
                                <option value="unpaid" @selected($status === 'unpaid')>{{ __('Unpaid') }}</option>
                            </optgroup>
                            <optgroup label="{{ __('Processing') }}">
                                <option value="not_started" @selected($status === 'not_started')>{{ __('Not Started') }}</option>
                                <option value="processing" @selected($status === 'processing')>{{ __('Processing') }}</option>
                                <option value="completed" @selected($status === 'completed')>{{ __('Completed') }}</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="flex items-end gap-4">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-800 px-4 py-2 text-sm font-semibold text-white transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                            {{ __('Apply Filters') }}
                        </button>
                        <a href="{{ route("{$routePrefix}.applicants") }}" class="text-sm text-gray-600 hover:underline">{{ __('Reset') }}</a>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-3 py-3">{{ __('Applicant') }}</th>
                                <th class="px-3 py-3">{{ __('Source') }}</th>
                                <th class="px-3 py-3">{{ __('Date') }}</th>
                                <th class="px-3 py-3">{{ __('Price') }}</th>
                                <th class="px-3 py-3">{{ __('Payment') }}</th>
                                <th class="px-3 py-3">{{ __('Processing') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($applications as $application)
                                @php
                                    $paymentStatus = $application->status();
                                    $paymentAccent = $paymentStatusAccent[$paymentStatus];
                                    $isWalkIn = $application->student->courses->isEmpty();
                                    $initials = collect(explode(' ', $application->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                @endphp
                                <tr class="border-l-4 {{ $paymentAccent['border'] }}">
                                    <td class="px-3 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400 text-xs font-bold">{{ $initials }}</span>
                                            <div>
                                                <p class="font-semibold text-gray-800">{{ $application->student->name }}</p>
                                                <p class="text-xs font-mono text-gray-400">{{ $application->student->student_id_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$isWalkIn ? 'gray' : 'blue'">{{ $isWalkIn ? __('Walk-in') : __('Existing Student') }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-gray-600">{{ $application->created_at->format('M j, Y') }}</td>
                                    <td class="px-3 py-3 text-sm align-top font-semibold text-gray-800">₦{{ number_format($application->price, 2) }}</td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$paymentAccent['color']">{{ __(ucwords(str_replace('_', ' ', $paymentStatus))) }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$processingStatusColor[$application->processing_status]">{{ $application->processingStatusLabel() }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-right">
                                        <a href="{{ route('students.show', $application->student_id) }}" class="text-sm font-semibold text-amber-600 hover:underline">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No applicants yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
