<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approval Centre') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status') === 'discount-request-approved')
                <p class="text-sm font-medium text-green-600">{{ __('Discount approved and applied.') }}</p>
            @elseif (session('status') === 'discount-request-rejected')
                <p class="text-sm font-medium text-green-600">{{ __('Discount request rejected.') }}</p>
            @elseif (session('status') === 'correction-request-resolved')
                <p class="text-sm font-medium text-green-600">{{ __('Correction request marked resolved.') }}</p>
            @elseif (session('status') === 'correction-request-rejected')
                <p class="text-sm font-medium text-green-600">{{ __('Correction request rejected.') }}</p>
            @elseif (session('status') === 'assessment-request-approved')
                <p class="text-sm font-medium text-green-600">{{ __('Assessment confirmed.') }}</p>
            @elseif (session('status') === 'assessment-request-rejected')
                <p class="text-sm font-medium text-green-600">{{ __('Assessment recommendation rejected.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold">
                    {{ __('Pending Approvals') }} — {{ $approvals->count() }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">{{ __('Everything waiting on your sign-off, across the whole system, in one place.') }}</p>
            </div>

            @forelse ($approvals as $index => $approval)
                @php($item = $approval['model'])
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                @if ($approval['type'] === 'discount')
                                    {{ __('Discount Request') }}
                                @elseif ($approval['type'] === 'assessment')
                                    {{ __('Assessment Recommendation') }}
                                @else
                                    {{ __('Student Information Change') }}
                                @endif
                            </span>
                            <p class="mt-2 text-sm font-semibold text-gray-800">
                                {{ $index + 1 }}.
                                <a href="{{ route('students.show', $item->student) }}" class="text-amber-600 hover:underline">{{ $item->student->name }}</a>
                                @if ($approval['type'] === 'discount')
                                    — {{ __('Discount on :course', ['course' => $item->course->name]) }}
                                @elseif ($approval['type'] === 'assessment')
                                    — {{ __(':result on :course', ['result' => ucfirst($item->result), 'course' => $item->course->name]) }}
                                @else
                                    — {{ __('Change :field', ['field' => $item->fieldLabel()]) }}
                                @endif
                            </p>

                            @if ($approval['type'] === 'discount')
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Course') }}: {{ $item->course->name }}
                                    &middot;
                                    {{ __('Original Fee') }}: ₦{{ number_format($item->original_fee, 2) }}
                                    &middot;
                                    {{ __('Discount') }}: ₦{{ number_format($item->discount_amount, 2) }}
                                    &middot;
                                    {{ __('Final Fee') }}: ₦{{ number_format($item->final_fee, 2) }}
                                </p>
                                @if ($item->reason)
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Reason') }}: {{ config("discounts.reasons.{$item->reason}", $item->reason) }}</p>
                                @endif
                            @elseif ($approval['type'] === 'assessment')
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __('Course') }}: {{ $item->course->name }}
                                    @if ($item->score !== null)
                                        &middot; {{ __('Score') }}: {{ $item->score }}
                                    @endif
                                </p>
                                @if ($item->remarks)
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Remarks') }}: {{ $item->remarks }}</p>
                                @endif
                            @else
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ $item->fieldLabel() }}: <span class="line-through text-gray-400">{{ $item->current_value }}</span> → <span class="font-medium">{{ $item->requested_value }}</span>
                                </p>
                                @if ($item->reason)
                                    <p class="mt-1 text-sm text-gray-500">{{ __('Reason') }}: {{ $item->reason }}</p>
                                @endif
                            @endif

                            <p class="mt-2 text-xs text-gray-400">
                                {{ __('Requested by :name on :date', ['name' => $item->requestedBy->name, 'date' => $item->created_at->format('Y-m-d H:i')]) }}
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-2 shrink-0 whitespace-nowrap">
                            @if ($approval['type'] === 'discount')
                                <form method="post" action="{{ route('discount-requests.approve', $item) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="text-sm font-medium text-green-600 hover:underline">{{ __('Approve') }}</button>
                                </form>
                                <form method="post" action="{{ route('discount-requests.reject', $item) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Reject') }}</button>
                                </form>
                            @elseif ($approval['type'] === 'assessment')
                                <form method="post" action="{{ route('assessment-requests.approve', $item) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="text-sm font-medium text-green-600 hover:underline">{{ __('Confirm') }}</button>
                                </form>
                                <form method="post" action="{{ route('assessment-requests.reject', $item) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Reject') }}</button>
                                </form>
                            @else
                                <a href="{{ route('students.edit', $item->student) }}" class="text-sm font-medium text-amber-600 hover:underline">{{ __('Edit Student') }}</a>
                                <form method="post" action="{{ route('student-correction-requests.resolve', $item) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="text-sm font-medium text-green-600 hover:underline">{{ __('Mark Resolved') }}</button>
                                </form>
                                <form method="post" action="{{ route('student-correction-requests.reject', $item) }}">
                                    @csrf
                                    @method('patch')
                                    <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Reject') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 text-center text-sm text-gray-500">
                    {{ __('Nothing pending — all caught up.') }}
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
