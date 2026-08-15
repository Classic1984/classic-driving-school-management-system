<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leads') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Leads') }}
                    </h3>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('lead-conversion-report.index') }}">
                            <x-secondary-button type="button">{{ __('View Conversion Report') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('leads.create') }}">
                            <x-primary-button type="button">{{ __('Log an Inquiry') }}</x-primary-button>
                        </a>
                    </div>
                </div>

                @if (session('status') === 'lead-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Inquiry logged successfully.') }}</p>
                @elseif (session('status') === 'lead-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Inquiry updated successfully.') }}</p>
                @elseif (session('status') === 'lead-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Inquiry removed successfully.') }}</p>
                @endif

                <form method="get" action="{{ route('leads.index') }}" class="mb-4 flex items-center gap-2">
                    <select name="status" onchange="this.form.submit()" class="border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm text-sm">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (\App\Models\Lead::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ __($label) }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Phone') }}</th>
                                <th class="px-4 py-2">{{ __('Course Interested In') }}</th>
                                <th class="px-4 py-2">{{ __('Source') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($leads as $lead)
                                <tr>
                                    <td class="px-4 py-2">{{ $lead->name }}</td>
                                    <td class="px-4 py-2">{{ $lead->phone }}</td>
                                    <td class="px-4 py-2">{{ $lead->course_interested ?: '—' }}</td>
                                    <td class="px-4 py-2">{{ $lead->source ?: '—' }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="match ($lead->status) {
                                            'new' => 'blue',
                                            'contacted' => 'amber',
                                            'converted' => 'green',
                                            'lost' => 'gray',
                                            default => 'gray',
                                        }">{{ \App\Models\Lead::STATUSES[$lead->status] ?? $lead->status }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('leads.edit', $lead) }}" class="text-sm text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                        <form method="post" action="{{ route('leads.destroy', $lead) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this inquiry?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No inquiries logged yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
