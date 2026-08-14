<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vehicles') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Vehicles') }}
                    </h3>

                    @if (auth()->user()->canManageCourses())
                        <a href="{{ route('vehicles.create') }}">
                            <x-primary-button type="button">{{ __('Add Vehicle') }}</x-primary-button>
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

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Plate Number') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($vehicles as $vehicle)
                                <tr>
                                    <td class="px-4 py-2">{{ $vehicle->name }}</td>
                                    <td class="px-4 py-2 font-mono">{{ $vehicle->plate_number }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="$vehicle->status === 'active' ? 'green' : 'gray'" class="capitalize">{{ $vehicle->status }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('vehicles.show', $vehicle) }}" class="text-sm text-amber-600 hover:underline">{{ __('View') }}</a>
                                        @if (auth()->user()->canManageCourses())
                                            <a href="{{ route('vehicles.edit', $vehicle) }}" class="text-sm text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                        @endif
                                        @if (auth()->user()->isAdmin())
                                            <form method="post" action="{{ route('vehicles.destroy', $vehicle) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this vehicle?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
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
