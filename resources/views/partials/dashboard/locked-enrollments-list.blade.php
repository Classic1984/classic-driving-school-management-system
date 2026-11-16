@foreach ($enrollments as $enrollment)
    <div class="py-2.5 flex items-center justify-between gap-4 text-sm">
        <div>
            <a href="{{ route('students.show', $enrollment->student_id) }}" class="text-gold-600 hover:underline font-medium">{{ $enrollment->student->name }}</a>
            <span class="text-gray-500"> — {{ $enrollment->course->name }}@if (auth()->user()->isDirector()) · ₦{{ number_format($enrollment->balance(), 2) }}@endif</span>
        </div>
        <div class="flex items-center gap-2 whitespace-nowrap">
            <x-badge color="red">{{ $enrollment->lockedReasonLabel() }}</x-badge>
            @if ($enrollment->isLockedForExpiredTrainingPeriod() && auth()->user()->isDirector())
                <a href="{{ route('enrollments.reactivate.create', $enrollment->id) }}" class="text-xs text-gold-600 hover:underline">{{ __('Reactivate') }}</a>
            @endif
        </div>
    </div>
@endforeach
