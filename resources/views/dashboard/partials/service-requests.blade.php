{{--
    Shared widget for a dashboard "requests still pending" list: which
    students have been charged for one catalog service but haven't
    reached the given action's target status yet.

    Expects:
    $title         string  Widget heading, e.g. "Learner's Permit Requests"
    $subtitle      string  Widget subheading, e.g. "Track and manage learner's permit applications"
    $requests      LengthAwarePaginator<StudentService>  Non-empty (caller checks isNotEmpty())
    $stats         array{total_students: int, charged: int, paid: int, completed: int}
    $completedLabel string  Label for the 4th summary tile, e.g. "Permit Obtained"
    $actionLabel   string  Button text, e.g. "Mark Obtained" or "Start Processing"
    $actionStatus  string  processing_status value the button submits
    $pageName      string  Pagination/sort query-string prefix, e.g. "permit_page"
    $iconPaths     array<string>  One or more SVG path "d" strings for the header icon
--}}
@php
    $sort = request()->query("{$pageName}_sort", 'oldest');
@endphp
<div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden mt-6">
    <div class="flex flex-col sm:flex-row sm:items-start gap-4 p-8 pb-5">
        <div class="flex items-start gap-4 flex-1 min-w-0">
            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-black">
                <svg class="h-8 w-8 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    @foreach ($iconPaths as $path)
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                    @endforeach
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-xl font-bold text-gray-900">{{ __($title) }}</h3>
                <p class="text-sm text-gray-500">{{ __($subtitle) }}</p>
            </div>
        </div>
        <a href="{{ route('service-reports.index', $requests->first()->service) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-400 hover:bg-amber-500 px-5 py-2.5 text-sm font-bold text-black transition shrink-0">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6 12v.375a.375.375 0 0 1-.375.375h-6a.375.375 0 0 1-.375-.375v-.375m6.75 0h-6.75m6.75 0v-.375a.375.375 0 0 0-.375-.375h-6a.375.375 0 0 0-.375.375v.375m6.75 0H15m-6.75 3h6.75m-6.75 0v.375a.375.375 0 0 0 .375.375h6a.375.375 0 0 0 .375-.375v-.375m0 0H15M9.75 5.25v-.375A1.125 1.125 0 0 1 10.875 3.75h.375c1.5 0 2.812.86 3.444 2.115M9.75 5.25v2.625a1.125 1.125 0 0 1-1.125 1.125h-.375m0 0h-1.5A2.625 2.625 0 0 0 4.125 11.625v9.75c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125v-2.625" /></svg>
            {{ __('View Full Report') }}
        </a>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 px-8">
        @foreach ([
            ['value' => $stats['total_students'], 'label' => 'Total Students', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z'],
            ['value' => $stats['charged'], 'label' => 'Charged', 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5'],
            ['value' => $stats['paid'], 'label' => 'Paid', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z'],
            ['value' => $stats['completed'], 'label' => $completedLabel, 'icon' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z'],
        ] as $tile)
            <div class="rounded-xl bg-amber-50 p-4 text-center">
                <svg class="h-6 w-6 mx-auto text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tile['icon'] }}" /></svg>
                <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ $tile['value'] }}</p>
                <p class="text-xs text-gray-600">{{ __($tile['label']) }}</p>
            </div>
        @endforeach
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4 px-8 pt-6">
        <div>
            <h4 class="text-lg font-bold text-gray-900">{{ __('Recent :title', ['title' => $title]) }}</h4>
            <p class="text-sm text-gray-500">{{ __('Latest applications and payment status') }}</p>
        </div>
        <div class="relative inline-block text-left" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                {{ $sort === 'latest' ? __('Latest First') : __('Oldest First') }}
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" transform="rotate(90 12 12)" /></svg>
            </button>
            <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg ring-1 ring-gray-200 py-1 z-10">
                <a href="{{ request()->fullUrlWithQuery(["{$pageName}_sort" => 'oldest']) }}" class="block px-4 py-2 text-sm {{ $sort === 'oldest' ? 'font-semibold text-amber-600' : 'text-gray-700 hover:bg-gray-50' }}">{{ __('Oldest First') }}</a>
                <a href="{{ request()->fullUrlWithQuery(["{$pageName}_sort" => 'latest']) }}" class="block px-4 py-2 text-sm {{ $sort === 'latest' ? 'font-semibold text-amber-600' : 'text-gray-700 hover:bg-gray-50' }}">{{ __('Latest First') }}</a>
            </div>
        </div>
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
            <div class="rounded-2xl ring-1 ring-gray-200 p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-100 text-lg font-bold text-amber-700">
                            {{ $initials }}
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('students.show', $studentService->student_id) }}" class="block truncate font-bold text-gray-900 hover:text-amber-600">{{ $studentService->student->name }}</a>
                            <p class="text-sm font-mono text-gray-400">{{ $studentService->student->student_id_number }}</p>
                        </div>
                    </div>
                    <x-badge :color="$statusMeta['color']" class="inline-flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $statusMeta['icon'] }}" /></svg>
                        {{ __(ucwords(str_replace('_', ' ', $status))) }}
                    </x-badge>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
                    <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                        <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Charged Date') }}</p>
                            <p class="text-sm font-bold text-gray-900">{{ $studentService->created_at->format('M j, Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $studentService->created_at->format('g:i A') }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 rounded-lg bg-gray-50 p-3">
                        <svg class="h-4 w-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-9-10.5h16.5a1.5 1.5 0 0 1 1.5 1.5v9a1.5 1.5 0 0 1-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5v-9a1.5 1.5 0 0 1 1.5-1.5Z" /></svg>
                        <div>
                            <p class="text-xs text-gray-500">{{ __('Amount Paid') }}</p>
                            <p class="text-sm font-bold text-gray-900">₦{{ number_format($studentService->amountPaid(), 0) }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 rounded-lg {{ $statusMeta['classes'] }} p-3">
                        <svg class="h-4 w-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        <div>
                            <p class="text-xs opacity-80">{{ __('Status') }}</p>
                            <p class="text-sm font-bold">{{ $studentService->processingStatusLabel() }}</p>
                        </div>
                    </div>
                </div>

                <form method="post" action="{{ route('student-services.processing-status.update', $studentService) }}" class="mt-4">
                    @csrf
                    @method('patch')
                    <input type="hidden" name="processing_status" value="{{ $actionStatus }}">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg ring-1 ring-amber-400 px-4 py-2 text-sm font-bold text-amber-600 hover:bg-amber-50 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 21.14a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" /></svg>
                        {{ __($actionLabel) }}
                    </button>
                </form>
            </div>
        @endforeach
    </div>

    <div class="flex items-start gap-3 mx-8 mb-8 rounded-lg bg-blue-50 ring-1 ring-blue-100 p-4">
        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-500 text-white">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
        </span>
        <p class="text-sm text-blue-800">{{ __('Keep your records up to date for accurate reporting.') }}</p>
    </div>

    <div class="px-8 pb-8">
        {{ $requests->links() }}
    </div>
</div>
