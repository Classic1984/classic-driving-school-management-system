{{--
    Shared widget for a dashboard "requests still pending" list: which
    students have been charged for one catalog service but haven't
    reached the given action's target status yet.

    Expects:
    $title        string  Widget heading, e.g. "Learner's Permit Requests"
    $subtitle     string  Widget subheading, e.g. "Track and manage learner's permit applications"
    $requests     LengthAwarePaginator<StudentService>  Non-empty (caller checks isNotEmpty())
    $actionLabel  string  Button text, e.g. "Mark Obtained" or "Start Processing"
    $actionStatus string  processing_status value the button submits
--}}
<div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden mt-6">
    <div class="flex flex-wrap items-start gap-4 p-8 pb-5">
        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-black border-l-4 border-amber-500">
            <x-application-logo class="h-10 w-10" />
        </div>
        <div class="min-w-0 flex-1">
            <h3 class="text-xl font-bold text-gray-900">{{ __($title) }}</h3>
            <p class="text-sm text-gray-500">{{ __($subtitle) }}</p>
        </div>
        <a href="{{ route('service-reports.index', $requests->first()->service) }}" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-amber-300 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6 12v.375a.375.375 0 0 1-.375.375h-6a.375.375 0 0 1-.375-.375v-.375m6.75 0h-6.75m6.75 0v-.375a.375.375 0 0 0-.375-.375h-6a.375.375 0 0 0-.375.375v.375m6.75 0H15m-6.75 3h6.75m-6.75 0v.375a.375.375 0 0 0 .375.375h6a.375.375 0 0 0 .375-.375v-.375m0 0H15M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
            {{ __('View Full Report') }}
        </a>
    </div>

    <div class="mx-8 flex flex-wrap items-center gap-x-8 gap-y-1 rounded-xl bg-amber-50/60 px-5 py-3 text-xs font-semibold uppercase tracking-wider text-amber-800">
        <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>{{ __('Student') }}</span>
        <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>{{ __('Charged') }}</span>
        <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>{{ __('Payment Status') }}</span>
        <span class="inline-flex items-center gap-1.5"><svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>{{ __('Status') }}</span>
    </div>

    <div class="p-8 pt-4 space-y-4">
        @foreach ($requests as $studentService)
            @php
                $status = $studentService->status();
                $statusMeta = match ($status) {
                    'paid' => ['color' => 'green', 'classes' => 'bg-green-100 text-green-600', 'icon' => 'M4.5 12.75l6 6 9-13.5'],
                    'part_payment' => ['color' => 'amber', 'classes' => 'bg-amber-100 text-amber-600', 'icon' => 'M12 6v6l4 2'],
                    default => ['color' => 'red', 'classes' => 'bg-red-100 text-red-600', 'icon' => 'M6 18 18 6M6 6l12 12'],
                };
                $initials = collect(explode(' ', $studentService->student->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
            @endphp
            <div class="flex flex-wrap items-center gap-x-6 gap-y-3 rounded-xl ring-1 ring-gray-200 p-4">
                <div class="flex items-center gap-3 min-w-[12rem]">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-amber-100 text-sm font-bold text-amber-700">
                        {{ $initials }}
                    </div>
                    <div class="min-w-0">
                        <a href="{{ route('students.show', $studentService->student_id) }}" class="block truncate font-semibold text-gray-900 hover:text-amber-600">{{ $studentService->student->name }}</a>
                        <p class="text-xs font-mono text-gray-400">{{ $studentService->student->student_id_number }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-1.5 min-w-[8rem]">
                    <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $studentService->created_at->format('M j, Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $studentService->created_at->format('g:i A') }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 min-w-[9rem]">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full {{ $statusMeta['classes'] }}">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $statusMeta['icon'] }}" /></svg>
                    </span>
                    <div>
                        <x-badge :color="$statusMeta['color']">{{ __(ucwords(str_replace('_', ' ', $status))) }}</x-badge>
                        <p class="mt-0.5 text-xs text-gray-500">{{ __('Amount') }}: ₦{{ number_format($studentService->price, 0) }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 ms-auto ps-6 border-s border-gray-100">
                    <svg class="h-4 w-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                    <form method="post" action="{{ route('student-services.processing-status.update', $studentService) }}">
                        @csrf
                        @method('patch')
                        <input type="hidden" name="processing_status" value="{{ $actionStatus }}">
                        <button type="submit" class="text-sm font-semibold text-amber-600 hover:underline">{{ __($actionLabel) }}</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="px-8 pb-8">
        {{ $requests->links() }}
    </div>
</div>
