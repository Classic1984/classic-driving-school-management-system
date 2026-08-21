<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __("Learner's Permit Report") }} — {{ __($label) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4 print:hidden">
                    <div class="flex items-center gap-2">
                        @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all_time' => 'All Time'] as $value => $tabLabel)
                            <a
                                href="{{ route('learners-permit-report.index', ['period' => $value]) }}"
                                class="px-3 py-1.5 text-sm rounded-md {{ $period === $value ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()">
                            <x-secondary-button type="button">{{ __('Print') }}</x-secondary-button>
                        </button>
                        <a href="{{ route('learners-permit-report.export', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('learners-permit-report.export-pdf', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                        </a>
                    </div>
                </div>

                <div class="bg-black text-amber-400 rounded-lg p-4 mb-6 inline-block">
                    <p class="text-xs uppercase tracking-wider">{{ __("Learner's Permits Obtained") }}</p>
                    <p class="text-2xl font-bold mt-1">{{ $obtained->count() }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Student ID') }}</th>
                                <th class="px-4 py-2">{{ __('Student Name') }}</th>
                                <th class="px-4 py-2">{{ __('Charged Date') }}</th>
                                <th class="px-4 py-2">{{ __('Obtained Date') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($obtained as $studentService)
                                <tr>
                                    <td class="px-4 py-2 text-sm font-mono">{{ $studentService->student->student_id_number }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('students.show', $studentService->student_id) }}" class="text-amber-600 hover:underline">{{ $studentService->student->name }}</a>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $studentService->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $studentService->updated_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __("No Learner's Permits were obtained during this period.") }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
