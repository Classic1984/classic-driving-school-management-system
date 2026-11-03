<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Services') }}
        </h2>
    </x-slot>

    @php
        $gearIconPath = ['M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z', 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'];
        $documentTextIconPath = 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z';
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $pencilSquareIconPath = 'm16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10';
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $academicCapIconPath = 'M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5';
        $cubeIconPath = 'M21 7.5 12 2.25 3 7.5m18 0-9 5.25M21 7.5v9L12 21.75M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9';
        $checkCircleIconPath = 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $pauseCircleIconPath = 'M15 9.75v4.5m-6-4.5v4.5m11.25-2.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $clockIconPath = 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';

        $serviceIcon = function (string $name) use ($idCardIconPath, $pencilSquareIconPath, $shieldCheckIconPath, $academicCapIconPath, $documentTextIconPath) {
            return match (true) {
                str_contains($name, 'License') => $idCardIconPath,
                str_contains($name, 'Permit') => $pencilSquareIconPath,
                str_contains($name, 'School') => $academicCapIconPath,
                str_contains($name, 'Certificate') => $shieldCheckIconPath,
                default => $documentTextIconPath,
            };
        };
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Services') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Manage all billable services') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        @foreach ($gearIconPath as $path)
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                        @endforeach
                    </svg>
                </span>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $documentTextIconPath }}" /></svg>
                        </span>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ __('Billable Services') }}</h4>
                            <p class="text-sm text-gray-500 max-w-md">
                                {{ __('Active services below appear as billable rows on the Record Payment screen for any student who has not already been charged for them.') }}
                            </p>
                        </div>
                    </div>

                    <a href="{{ route('services.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ __('Add Service') }}
                    </a>
                </div>

                @if (session('status') === 'service-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Service added successfully.') }}</p>
                @elseif (session('status') === 'service-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Service updated successfully.') }}</p>
                @elseif (session('status') === 'service-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Service deleted successfully.') }}</p>
                @elseif (session('status') === 'service-in-use')
                    <div class="mb-4 text-sm font-medium text-red-600">
                        <p>{{ __('This service has already been charged to the student(s) below, so it cannot be deleted. Mark it inactive instead to hide it from new registrations.') }}</p>
                        <ul class="list-disc list-inside mt-1">
                            @foreach (session('serviceInUseStudents', []) as $chargedStudent)
                                <li><a href="{{ route('students.show', $chargedStudent['id']) }}" class="underline hover:no-underline">{{ $chargedStudent['name'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-4 py-3">
                                    <a href="{{ route('services.index', ['sort' => $sort === 'asc' ? 'desc' : 'asc']) }}" class="inline-flex items-center gap-1.5 hover:text-amber-900">
                                        {{ __('Name') }}
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sort === 'asc' ? 'm4.5 15.75 7.5-7.5 7.5 7.5' : 'm19.5 8.25-7.5 7.5-7.5-7.5' }}" /></svg>
                                    </a>
                                </th>
                                <th class="px-4 py-3">{{ __('Price (₦)') }}</th>
                                <th class="px-4 py-3">{{ __('Processing Days') }}</th>
                                <th class="px-4 py-3">{{ __('Status') }}</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($services as $service)
                                <tr>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-500">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $serviceIcon($service->name) }}" /></svg>
                                            </span>
                                            <span class="font-semibold text-gray-800">{{ $service->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-700">{{ number_format($service->price, 2) }}</td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $service->processing_days ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        @if ($service->is_active)
                                            <x-badge color="green">{{ __('Active') }}</x-badge>
                                        @else
                                            <x-badge color="gray">{{ __('Inactive') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-right">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-36 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                                                <a href="{{ route('services.edit', $service) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                                <form method="post" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this service?') }}');">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">{{ __('Delete') }}</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No services yet. Add one to make it billable to students.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl bg-amber-50/60 ring-1 ring-amber-100 p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
                    <div>
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-amber-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cubeIconPath }}" /></svg>
                        </span>
                        <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ number_format($totalServices) }}</p>
                        <p class="text-xs text-gray-500">{{ __('Total Services') }}</p>
                    </div>
                    <div>
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-green-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $checkCircleIconPath }}" /></svg>
                        </span>
                        <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ number_format($activeServices) }}</p>
                        <p class="text-xs text-gray-500">{{ trans_choice('Active Service|Active Services', $activeServices) }}</p>
                    </div>
                    <div>
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-gray-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $pauseCircleIconPath }}" /></svg>
                        </span>
                        <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ number_format($inactiveServices) }}</p>
                        <p class="text-xs text-gray-500">{{ trans_choice('Inactive Service|Inactive Services', $inactiveServices) }}</p>
                    </div>
                    <div>
                        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-indigo-500">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $clockIconPath }}" /></svg>
                        </span>
                        <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ $averageProcessingDays ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ __('Avg. Processing Days (Active)') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
