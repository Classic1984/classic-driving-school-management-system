<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theory Classes') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Class Roster History') }}</h3>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Topic') }}</th>
                                <th class="px-4 py-2">{{ __('Instructor') }}</th>
                                <th class="px-4 py-2">{{ __('Present') }}</th>
                                <th class="px-4 py-2">{{ __('Absent') }}</th>
                                <th class="px-4 py-2">{{ __('Attendance') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($theoryClasses as $theoryClass)
                                <tr>
                                    <td class="px-4 py-2">
                                        {{ $theoryClass->class_date->format('M j, Y') }}
                                        @if ($theoryClass->class_date->isToday())
                                            <x-badge color="amber" class="ms-1">{{ __('Today') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $theoryClass->topic ?: '—' }}</td>
                                    <td class="px-4 py-2">{{ $theoryClass->instructor?->name ?? '—' }}</td>
                                    <td class="px-4 py-2">{{ $theoryClass->presentCount() }}</td>
                                    <td class="px-4 py-2">{{ $theoryClass->absentCount() }}</td>
                                    <td class="px-4 py-2">{{ $theoryClass->attendancePercentage() }}%</td>
                                    <td class="px-4 py-2 text-right whitespace-nowrap">
                                        <a href="{{ route('theory-classes.show', $theoryClass) }}" class="text-sm text-amber-600 hover:underline">{{ __('View Roster') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No theory classes have been held yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $theoryClasses->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
