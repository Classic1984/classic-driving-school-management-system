<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Correction Requests') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">
                    {{ __('Pending Correction Requests') }}
                </h3>

                @if (session('status') === 'correction-request-resolved')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Correction request marked resolved.') }}</p>
                @elseif (session('status') === 'correction-request-rejected')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Correction request rejected.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Field') }}</th>
                                <th class="px-4 py-2">{{ __('Current') }}</th>
                                <th class="px-4 py-2">{{ __('Requested') }}</th>
                                <th class="px-4 py-2">{{ __('Reason') }}</th>
                                <th class="px-4 py-2">{{ __('Requested By') }}</th>
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($correctionRequests as $correctionRequest)
                                <tr>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('students.show', $correctionRequest->student) }}" class="text-amber-600 hover:underline">{{ $correctionRequest->student->name }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $correctionRequest->fieldLabel() }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $correctionRequest->current_value }}</td>
                                    <td class="px-4 py-2 text-sm font-medium">{{ $correctionRequest->requested_value }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $correctionRequest->reason ?? '—' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $correctionRequest->requestedBy->name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $correctionRequest->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap space-x-2">
                                        <a href="{{ route('students.edit', $correctionRequest->student) }}" class="text-sm text-amber-600 hover:underline">{{ __('Edit Student') }}</a>
                                        <form method="post" action="{{ route('student-correction-requests.resolve', $correctionRequest) }}" class="inline">
                                            @csrf
                                            @method('patch')
                                            <button type="submit" class="text-sm text-green-600 hover:underline">{{ __('Mark Resolved') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('student-correction-requests.reject', $correctionRequest) }}" class="inline">
                                            @csrf
                                            @method('patch')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Reject') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No pending correction requests.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $correctionRequests->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
