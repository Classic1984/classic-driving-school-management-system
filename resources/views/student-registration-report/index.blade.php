<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Registration Report') }} — {{ __($label) }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4 print:hidden">
                    <div class="flex items-center gap-2">
                        @foreach (['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year'] as $value => $tabLabel)
                            <a
                                href="{{ route('student-registration-report.index', ['period' => $value]) }}"
                                class="px-3 py-1.5 text-sm rounded-md {{ $period === $value ? 'bg-black text-amber-400' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                            >{{ __($tabLabel) }}</a>
                        @endforeach
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.print()">
                            <x-secondary-button type="button">{{ __('Print') }}</x-secondary-button>
                        </button>
                        <a href="{{ route('student-registration-report.export', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Export Excel') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('student-registration-report.export-pdf', ['period' => $period]) }}">
                            <x-secondary-button type="button">{{ __('Download PDF') }}</x-secondary-button>
                        </a>
                    </div>
                </div>

                <p class="text-sm text-gray-500 mb-4">
                    {{ __('New students') }}: <span class="font-semibold text-gray-900">{{ $students->count() }}</span>
                </p>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Student ID') }}</th>
                                <th class="px-4 py-2">{{ __('Student Name') }}</th>
                                <th class="px-4 py-2">{{ __('Email') }}</th>
                                <th class="px-4 py-2">{{ __('Phone') }}</th>
                                <th class="px-4 py-2">{{ __('Course(s)') }}</th>
                                <th class="px-4 py-2">{{ __('Registration Date') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-sm">{{ $student->student_id_number }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <a href="{{ route('students.show', $student) }}" class="text-amber-600 hover:underline print:text-gray-900 print:no-underline">
                                            {{ $student->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $student->email }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $student->phone }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $student->courses->pluck('name')->implode(', ') ?: '—' }}</td>
                                    <td class="px-4 py-2 text-sm">{{ $student->enrollment_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <x-badge :color="match ($student->status) {
                                            'active' => 'green',
                                            'completed' => 'blue',
                                            'withdrawn' => 'red',
                                            default => 'gray',
                                        }" class="capitalize">{{ $student->status }}</x-badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No students registered during this period.') }}
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
