{{--
    Shared table for a dashboard "requests still pending" widget: which
    students have been charged for one catalog service but haven't
    reached the given action's target status yet.

    Expects:
    $title        string  Widget heading, e.g. "Learner's Permit Requests"
    $requests     Collection<StudentService>  Non-empty (caller checks isNotEmpty())
    $actionLabel  string  Button text, e.g. "Mark Obtained" or "Start Processing"
    $actionStatus string  processing_status value the button submits
--}}
<div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-8 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold text-gray-800">{{ __($title) }}</h3>
        <a href="{{ route('service-reports.index', $requests->first()->service) }}" class="text-sm text-amber-600 hover:underline">{{ __('View Full Report') }}</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="pb-2 pr-4">{{ __('Student') }}</th>
                    <th class="pb-2 pr-4">{{ __('Charged') }}</th>
                    <th class="pb-2 pr-4">{{ __('Payment Status') }}</th>
                    <th class="pb-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($requests as $studentService)
                    <tr>
                        <td class="py-2 pr-4">
                            <a href="{{ route('students.show', $studentService->student_id) }}" class="text-amber-600 hover:underline">
                                {{ $studentService->student->name }}
                            </a>
                        </td>
                        <td class="py-2 pr-4">{{ $studentService->created_at->format('M j, Y') }}</td>
                        <td class="py-2 pr-4">
                            <x-badge :color="match ($studentService->status()) {
                                'paid' => 'green',
                                'part_payment' => 'amber',
                                default => 'red',
                            }">{{ __(ucwords(str_replace('_', ' ', $studentService->status()))) }}</x-badge>
                        </td>
                        <td class="py-2">
                            <form method="post" action="{{ route('student-services.processing-status.update', $studentService) }}">
                                @csrf
                                @method('patch')
                                <input type="hidden" name="processing_status" value="{{ $actionStatus }}">
                                <button type="submit" class="text-sm text-amber-600 hover:underline">{{ __($actionLabel) }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
