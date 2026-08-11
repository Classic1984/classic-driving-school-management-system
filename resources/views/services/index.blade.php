<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Services') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Billable Services') }}
                    </h3>

                    <a href="{{ route('services.create') }}">
                        <x-primary-button type="button">{{ __('Add Service') }}</x-primary-button>
                    </a>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    {{ __('Active services below appear as billable rows on the Record Payment screen for any student who has not already been charged for them.') }}
                </p>

                @if (session('status') === 'service-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Service added successfully.') }}</p>
                @elseif (session('status') === 'service-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Service updated successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Price') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($services as $service)
                                <tr>
                                    <td class="px-4 py-2">{{ $service->name }}</td>
                                    <td class="px-4 py-2">{{ number_format($service->price, 2) }}</td>
                                    <td class="px-4 py-2">
                                        @if ($service->is_active)
                                            <x-badge color="green">{{ __('Active') }}</x-badge>
                                        @else
                                            <x-badge color="gray">{{ __('Inactive') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('services.edit', $service) }}" class="text-sm text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No services yet. Add one to make it billable to students.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
