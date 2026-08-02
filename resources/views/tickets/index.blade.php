<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Tickets') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Tickets') }}
                    </h3>

                    <a href="{{ route('tickets.create') }}">
                        <x-primary-button type="button">{{ __('Issue Ticket') }}</x-primary-button>
                    </a>
                </div>

                @if (session('status') === 'ticket-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Ticket issued successfully.') }}</p>
                @elseif (session('status') === 'ticket-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Ticket updated successfully.') }}</p>
                @elseif (session('status') === 'ticket-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Ticket removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Ticket #') }}</th>
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Instructor') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tickets as $ticket)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $ticket->ticket_number }}</td>
                                    <td class="px-4 py-2">{{ $ticket->date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $ticket->student->name }}</td>
                                    <td class="px-4 py-2">{{ $ticket->course->name }}</td>
                                    <td class="px-4 py-2">{{ $ticket->instructor?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('tickets.edit', $ticket) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        @if (auth()->user()->isAdmin())
                                            <form method="post" action="{{ route('tickets.destroy', $ticket) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this ticket?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No tickets issued yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
