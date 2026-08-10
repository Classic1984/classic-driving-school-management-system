<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Activity Log') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Activity Log') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Date & Time') }}</th>
                                <th class="px-4 py-2">{{ __('User') }}</th>
                                <th class="px-4 py-2">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($activityLogs as $log)
                                <tr>
                                    <td class="px-4 py-2 text-sm whitespace-nowrap">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $log->user?->name ?? __('System') }}
                                        @if ($log->user)
                                            <span class="text-xs text-gray-400 capitalize">({{ $log->user->role }})</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $log->description }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No activity recorded yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $activityLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
