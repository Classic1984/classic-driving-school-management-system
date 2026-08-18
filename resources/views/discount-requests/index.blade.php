<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Discount Requests') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">
                    {{ __('Pending Discount Requests') }}
                </h3>

                @if (session('status') === 'discount-request-approved')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Discount approved and applied.') }}</p>
                @elseif (session('status') === 'discount-request-rejected')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Discount request rejected.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Original Fee') }}</th>
                                <th class="px-4 py-2">{{ __('Discount') }}</th>
                                <th class="px-4 py-2">{{ __('Final Fee') }}</th>
                                <th class="px-4 py-2">{{ __('Reason') }}</th>
                                <th class="px-4 py-2">{{ __('Requested By') }}</th>
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($discountRequests as $discountRequest)
                                <tr>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('students.show', $discountRequest->student) }}" class="text-amber-600 hover:underline">{{ $discountRequest->student->name }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $discountRequest->course->name }}</td>
                                    <td class="px-4 py-2 text-sm">₦{{ number_format($discountRequest->original_fee, 2) }}</td>
                                    <td class="px-4 py-2 text-sm font-medium">₦{{ number_format($discountRequest->discount_amount, 2) }}</td>
                                    <td class="px-4 py-2 text-sm">₦{{ number_format($discountRequest->final_fee, 2) }}</td>
                                    <td class="px-4 py-2 text-sm">{{ config("discounts.reasons.{$discountRequest->reason}", $discountRequest->reason ?? '—') }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $discountRequest->requestedBy->name }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $discountRequest->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap space-x-2">
                                        <form method="post" action="{{ route('discount-requests.approve', $discountRequest) }}" class="inline">
                                            @csrf
                                            @method('patch')
                                            <button type="submit" class="text-sm text-green-600 hover:underline">{{ __('Approve') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('discount-requests.reject', $discountRequest) }}" class="inline">
                                            @csrf
                                            @method('patch')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Reject') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No pending discount requests.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $discountRequests->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
