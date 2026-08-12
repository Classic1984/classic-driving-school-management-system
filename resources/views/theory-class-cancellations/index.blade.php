<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theory Class Cancellations') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Cancel a Theory Class') }}</h3>

                <p class="text-sm text-gray-500 mb-4">
                    {{ __('Theory class holds every Thursday at 10am. Cancelling a date here sends a cancellation text to every actively enrolled student instead of the usual reminder - it does not change the weekly schedule itself.') }}
                </p>

                @if (session('status') === 'cancellation-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Theory class cancelled. Students will be texted instead of reminded.') }}</p>
                @elseif (session('status') === 'cancellation-removed')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Cancellation removed - the normal reminder will go out for that date.') }}</p>
                @endif

                <form method="post" action="{{ route('theory-class-cancellations.store') }}" class="flex flex-wrap items-end gap-4">
                    @csrf

                    <div>
                        <x-input-label for="class_date" :value="__('Date')" />
                        <x-text-input id="class_date" name="class_date" type="date" class="mt-1 block" :value="old('class_date')" :min="now()->toDateString()" required />
                        <x-input-error class="mt-2" :messages="$errors->get('class_date')" />
                    </div>

                    <div class="flex-1 min-w-[16rem]">
                        <x-input-label for="reason" :value="__('Reason (optional)')" />
                        <x-text-input id="reason" name="reason" type="text" class="mt-1 block w-full" :value="old('reason')" />
                        <x-input-error class="mt-2" :messages="$errors->get('reason')" />
                    </div>

                    <x-primary-button>{{ __('Cancel Class') }}</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Cancelled Dates') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Reason') }}</th>
                                <th class="px-4 py-2">{{ __('Cancelled By') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($cancellations as $cancellation)
                                <tr>
                                    <td class="px-4 py-2">
                                        {{ $cancellation->class_date->format('M j, Y') }}
                                        @if ($cancellation->class_date->isFuture() || $cancellation->class_date->isToday())
                                            <x-badge color="amber" class="ms-1">{{ __('Upcoming') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $cancellation->reason ?: '—' }}</td>
                                    <td class="px-4 py-2">{{ $cancellation->cancelledBy->name }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">
                                        <form method="post" action="{{ route('theory-class-cancellations.destroy', $cancellation) }}" onsubmit="return confirm('{{ __('Undo this cancellation?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Undo') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No classes have been cancelled.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
