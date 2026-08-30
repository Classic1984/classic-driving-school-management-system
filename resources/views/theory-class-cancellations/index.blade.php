<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theory Class Cancellations') }}
        </h2>
    </x-slot>

    @php
        $calendarIconPath = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5';
        $calendarXIconPath = [$calendarIconPath, 'm13.5 15 3 3m0-3-3 3'];
        $infoIconPath = 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z';
        $clockIconPath = 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $noSymbolIconPath = 'M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636';
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Theory Class Cancellations') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Manage and notify students about cancelled theory classes') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        @foreach ($calendarXIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>
                </span>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center gap-4 mb-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            @foreach ($calendarXIconPath as $path)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                            @endforeach
                        </svg>
                    </span>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ __('Cancel a Theory Class') }}</h4>
                    </div>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    {{ __('Theory class holds every Thursday at 10am. Cancelling a date here sends a cancellation text to every actively enrolled student instead of the usual reminder - it does not change the weekly schedule itself.') }}
                </p>

                <div class="flex items-start gap-3 rounded-lg bg-amber-50 ring-1 ring-amber-100 p-4 mb-5">
                    <svg class="h-5 w-5 shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $infoIconPath }}" /></svg>
                    <p class="text-sm text-amber-800">{{ __('A cancellation SMS will be sent to all actively enrolled students for the selected date.') }}</p>
                </div>

                @if (session('status') === 'cancellation-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Theory class cancelled. Students will be texted instead of reminded.') }}</p>
                @elseif (session('status') === 'cancellation-removed')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Cancellation removed - the normal reminder will go out for that date.') }}</p>
                @endif

                <form method="post" action="{{ route('theory-class-cancellations.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="class_date" :value="__('Date')" />
                        <div class="relative mt-1 max-w-xs">
                            <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIconPath }}" /></svg>
                            <x-text-input id="class_date" name="class_date" type="date" class="block w-full pl-9" :value="old('class_date')" :min="now()->toDateString()" required />
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('class_date')" />
                    </div>

                    <div>
                        <x-input-label for="reason" :value="__('Reason (optional)')" />
                        <textarea id="reason" name="reason" rows="3" placeholder="{{ __('Enter reason for cancellation (optional)') }}" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm">{{ old('reason') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                    </div>

                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            @foreach ($calendarXIconPath as $path)
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                            @endforeach
                        </svg>
                        {{ __('Cancel Class') }}
                    </button>
                </form>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                    </span>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ __('Cancelled Dates') }}</h4>
                        <p class="text-sm text-gray-500">{{ __('View all theory class cancellations') }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-indigo-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-indigo-700">
                                <th class="px-4 py-3">
                                    <a href="{{ route('theory-class-cancellations.index', ['sort' => $sort === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center gap-1.5 hover:text-indigo-900">
                                        {{ __('Date') }}
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sort === 'asc' ? 'm4.5 15.75 7.5-7.5 7.5 7.5' : 'm19.5 8.25-7.5 7.5-7.5-7.5' }}" /></svg>
                                    </a>
                                </th>
                                <th class="px-4 py-3">{{ __('Reason') }}</th>
                                <th class="px-4 py-3">{{ __('Cancelled By') }}</th>
                                <th class="px-4 py-3">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($cancellations as $cancellation)
                                <tr>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <span class="font-semibold text-gray-800">{{ $cancellation->class_date->format('M j, Y') }}</span>
                                        @if ($cancellation->class_date->isFuture() || $cancellation->class_date->isToday())
                                            <x-badge color="amber" class="ms-1">{{ __('Upcoming') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $cancellation->reason ?: '—' }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $cancellation->cancelledBy->name }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-right">
                                        <form method="post" action="{{ route('theory-class-cancellations.destroy', $cancellation) }}" onsubmit="return confirm('{{ __('Undo this cancellation?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Undo') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-10 text-center">
                                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $noSymbolIconPath }}" /></svg>
                                        </span>
                                        <p class="mt-3 text-sm font-bold text-gray-800">{{ __('No classes have been cancelled.') }}</p>
                                        <p class="mt-1 text-sm text-gray-500">{{ __('Cancelled theory classes will appear here.') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
