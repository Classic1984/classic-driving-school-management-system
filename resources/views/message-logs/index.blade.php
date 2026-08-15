<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Message Delivery Log') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Message Delivery Log') }}</h3>

                @if (session('status'))
                    <p class="mb-4 text-sm font-medium text-green-600">{{ session('status') }}</p>
                @endif

                <div class="mb-6">
                    <p class="text-sm font-medium text-gray-500 mb-2">{{ __('Send Now') }}</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ([
                            'balance_reminder' => 'Balance Reminder',
                            'theory_class_reminder' => 'Theory Class Reminder',
                            'lead_follow_up' => 'Lead Follow-Up',
                            'absence_check_in' => 'Absence Check-In',
                        ] as $type => $label)
                            <form method="post" action="{{ route('reminders.send', $type) }}" onsubmit="return confirm('{{ __('This will immediately text every eligible recipient. Continue?') }}');">
                                @csrf
                                <x-secondary-button type="submit">{{ __('Send :label Now', ['label' => $label]) }}</x-secondary-button>
                            </form>
                        @endforeach
                    </div>
                </div>

                <form method="get" action="{{ route('message-log.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" placeholder="{{ __('Recipient name') }}" :value="request('search')" />
                    </div>

                    <div>
                        <x-input-label for="purpose" :value="__('Purpose')" />
                        <select id="purpose" name="purpose" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach (\App\Models\MessageLog::PURPOSES as $value => $label)
                                <option value="{{ $value }}" @selected(request('purpose') === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            <option value="sent" @selected(request('status') === 'sent')>{{ __('Sent') }}</option>
                            <option value="failed" @selected(request('status') === 'failed')>{{ __('Failed') }}</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <x-primary-button type="submit">{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('message-log.index') }}" class="ms-3 text-sm text-gray-600 hover:underline">{{ __('Reset') }}</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Date & Time') }}</th>
                                <th class="px-4 py-2">{{ __('Recipient') }}</th>
                                <th class="px-4 py-2">{{ __('Purpose') }}</th>
                                <th class="px-4 py-2">{{ __('Channel') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2">{{ __('Message') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($messageLogs as $log)
                                <tr>
                                    <td class="px-4 py-2 text-sm whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $log->recipient_name }}
                                        <span class="text-xs text-gray-400 capitalize">({{ $log->recipient_type }})</span>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ __(\App\Models\MessageLog::PURPOSES[$log->purpose] ?? $log->purpose) }}</td>
                                    <td class="px-4 py-2 text-sm uppercase">{{ $log->channel ?? '—' }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="$log->status === 'sent' ? 'green' : 'red'" class="capitalize">{{ $log->status }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2 text-sm text-gray-600 max-w-md truncate" title="{{ $log->message }}">{{ $log->message ?? '—' }}</td>
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

                <div class="mt-4">
                    {{ $messageLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
