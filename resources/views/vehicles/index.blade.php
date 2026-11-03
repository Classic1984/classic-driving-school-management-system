<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vehicles') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div class="flex items-center gap-4">
                    <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                        <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12" /></svg>
                    </span>
                    <div>
                        <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Vehicles') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('Manage every vehicle in the training fleet') }}</p>
                    </div>
                </div>

                @if (auth()->user()->canManageCourses())
                    <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 px-4 py-2.5 text-sm font-bold text-black transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        {{ __('Add Vehicle') }}
                    </a>
                @endif
            </div>

            @if (session('status') === 'vehicle-created')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Vehicle registered successfully.') }}</p>
            @elseif (session('status') === 'vehicle-updated')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Vehicle updated successfully.') }}</p>
            @elseif (session('status') === 'vehicle-deleted')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Vehicle removed successfully.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Vehicle Records') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-3 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12" /></svg>
                                        {{ __('Name') }}
                                    </span>
                                </th>
                                <th class="px-3 py-3">{{ __('Plate Number') }}</th>
                                <th class="px-3 py-3">{{ __('Status') }}</th>
                                <th class="px-3 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($vehicles as $vehicle)
                                <tr class="border-l-4 {{ $vehicle->status === 'active' ? 'border-green-500' : 'border-gray-300' }}">
                                    <td class="px-3 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 0h-12" /></svg>
                                            </span>
                                            <span class="font-semibold text-gray-800">{{ $vehicle->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top font-mono text-gray-600">{{ $vehicle->plate_number }}</td>
                                    <td class="px-3 py-3 text-sm align-top">
                                        <x-badge :color="$vehicle->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $vehicle->status }}</x-badge>
                                    </td>
                                    <td class="px-3 py-3 text-sm align-top text-right">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-amber-100 hover:text-amber-600 transition">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75c.621 0 1.125-.504 1.125-1.125S12.621 4.5 12 4.5s-1.125.504-1.125 1.125S11.379 6.75 12 6.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 10.5 12 10.5s-1.125.504-1.125 1.125S11.379 12.75 12 12.75Zm0 6c.621 0 1.125-.504 1.125-1.125S12.621 16.5 12 16.5s-1.125.504-1.125 1.125S11.379 18.75 12 18.75Z" /></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                                                <a href="{{ route('vehicles.show', $vehicle) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('View') }}</a>
                                                @if (auth()->user()->canManageCourses())
                                                    <a href="{{ route('vehicles.edit', $vehicle) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">{{ __('Edit') }}</a>
                                                @endif
                                                @if (auth()->user()->isAdmin())
                                                    <form method="post" action="{{ route('vehicles.destroy', $vehicle) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this vehicle?') }}');">
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
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No vehicles registered yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $vehicles->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
