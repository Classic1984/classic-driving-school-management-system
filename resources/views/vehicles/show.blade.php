<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vehicle Details') }}
        </h2>
    </x-slot>

    @php
        $vehicleIconPath = 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12';
        $idCardIconPath = 'M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6.75-10.5a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-4.5 4.5a4.5 4.5 0 0 1 4.5 0';
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $arrowLeftIconPath = 'M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18';
    @endphp

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                            <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $vehicleIconPath }}" /></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-2xl font-extrabold text-gray-900 truncate">{{ $vehicle->name }}</h3>
                            <p class="text-sm font-mono text-gray-500">{{ $vehicle->plate_number }}</p>
                        </div>
                        <x-badge :color="$vehicle->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $vehicle->status }}</x-badge>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $vehicleIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Name') }}</p>
                                <p class="text-sm font-bold text-gray-900">{{ $vehicle->name }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $idCardIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Plate Number') }}</p>
                                <p class="text-sm font-bold font-mono text-gray-900">{{ $vehicle->plate_number }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3 sm:col-span-2">
                            <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                            <div>
                                <p class="text-xs text-gray-500">{{ __('Status') }}</p>
                                <x-badge :color="$vehicle->status === 'active' ? 'green' : 'gray'" class="capitalize mt-0.5">{{ $vehicle->status }}</x-badge>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        @if (auth()->user()->canManageCourses())
                            <a href="{{ route('vehicles.edit', $vehicle) }}">
                                <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                            </a>
                        @endif
                        <a href="{{ route('vehicles.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:underline">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $arrowLeftIconPath }}" /></svg>
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
