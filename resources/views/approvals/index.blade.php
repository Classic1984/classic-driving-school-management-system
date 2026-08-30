<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Approval Centre') }}
        </h2>
    </x-slot>

    @php
        $typeAccent = [
            'discount' => ['border' => 'border-amber-500', 'badge' => 'bg-amber-100 text-amber-800'],
            'assessment' => ['border' => 'border-blue-500', 'badge' => 'bg-blue-100 text-blue-800'],
            'correction' => ['border' => 'border-purple-500', 'badge' => 'bg-purple-100 text-purple-800'],
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center gap-4 mb-6">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                </span>
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Approval Centre') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Everything waiting on your sign-off, across the whole system, in one place.') }}</p>
                </div>
            </div>

            @if (session('status') === 'discount-request-approved')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Discount approved and applied.') }}</p>
            @elseif (session('status') === 'discount-request-rejected')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Discount request rejected.') }}</p>
            @elseif (session('status') === 'correction-request-resolved')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Correction request marked resolved.') }}</p>
            @elseif (session('status') === 'correction-request-rejected')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Correction request rejected.') }}</p>
            @elseif (session('status') === 'assessment-request-approved')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Assessment confirmed.') }}</p>
            @elseif (session('status') === 'assessment-request-rejected')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __('Assessment recommendation rejected.') }}</p>
            @endif

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6 mb-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-black text-amber-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                    </span>
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ __('Pending Approvals') }} — {{ $approvals->count() }}
                    </h3>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4">
                @forelse ($approvals as $index => $approval)
                    @php
                        $item = $approval['model'];
                        $accent = $typeAccent[$approval['type']];
                        $initials = collect(explode(' ', $item->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                    @endphp
                    <div class="rounded-xl bg-white ring-1 ring-gray-200 border-l-4 {{ $accent['border'] }} p-6 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $accent['badge'] }}">
                                    @if ($approval['type'] === 'discount')
                                        {{ __('Discount Request') }}
                                    @elseif ($approval['type'] === 'assessment')
                                        {{ __('Assessment Recommendation') }}
                                    @else
                                        {{ __('Student Information Change') }}
                                    @endif
                                </span>

                                <div class="mt-2 flex items-center gap-2">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400 text-xs font-bold">{{ $initials }}</span>
                                    <p class="text-sm font-semibold text-gray-800">
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
                                </div>

                                @if ($approval['type'] === 'discount')
                                    <p class="mt-2 text-sm text-gray-600">
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
                                    <p class="mt-2 text-sm text-gray-600">
                                        {{ __('Course') }}: {{ $item->course->name }}
                                        @if ($item->score !== null)
                                            &middot; {{ __('Score') }}: {{ $item->score }}
                                        @endif
                                    </p>
                                    @if ($item->remarks)
                                        <p class="mt-1 text-sm text-gray-500">{{ __('Remarks') }}: {{ $item->remarks }}</p>
                                    @endif
                                @else
                                    <p class="mt-2 text-sm text-gray-600">
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

                            <div class="flex flex-col items-stretch gap-2 shrink-0 whitespace-nowrap">
                                @if ($approval['type'] === 'discount')
                                    <form method="post" action="{{ route('discount-requests.approve', $item) }}">
                                        @csrf
                                        @method('patch')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-50 hover:bg-green-100 px-3 py-1.5 text-sm font-semibold text-green-700 transition">{{ __('Approve') }}</button>
                                    </form>
                                    <form method="post" action="{{ route('discount-requests.reject', $item) }}">
                                        @csrf
                                        @method('patch')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-50 hover:bg-red-100 px-3 py-1.5 text-sm font-semibold text-red-600 transition">{{ __('Reject') }}</button>
                                    </form>
                                @elseif ($approval['type'] === 'assessment')
                                    <form method="post" action="{{ route('assessment-requests.approve', $item) }}">
                                        @csrf
                                        @method('patch')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-50 hover:bg-green-100 px-3 py-1.5 text-sm font-semibold text-green-700 transition">{{ __('Confirm') }}</button>
                                    </form>
                                    <form method="post" action="{{ route('assessment-requests.reject', $item) }}">
                                        @csrf
                                        @method('patch')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-50 hover:bg-red-100 px-3 py-1.5 text-sm font-semibold text-red-600 transition">{{ __('Reject') }}</button>
                                    </form>
                                @else
                                    <a href="{{ route('students.edit', $item->student) }}" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg ring-1 ring-amber-300 hover:bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700 transition">{{ __('Edit Student') }}</a>
                                    <form method="post" action="{{ route('student-correction-requests.resolve', $item) }}">
                                        @csrf
                                        @method('patch')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-green-50 hover:bg-green-100 px-3 py-1.5 text-sm font-semibold text-green-700 transition">{{ __('Mark Resolved') }}</button>
                                    </form>
                                    <form method="post" action="{{ route('student-correction-requests.reject', $item) }}">
                                        @csrf
                                        @method('patch')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-red-50 hover:bg-red-100 px-3 py-1.5 text-sm font-semibold text-red-600 transition">{{ __('Reject') }}</button>
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
    </div>
</x-app-layout>
