<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Attendance Records') }}
                    </h3>

                    <a href="{{ route('attendances.create') }}">
                        <x-primary-button type="button">{{ __('Record Attendance') }}</x-primary-button>
                    </a>
                </div>

                @if (session('status') === 'attendance-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Attendance recorded successfully.') }}</p>
                @elseif (session('status') === 'attendance-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Attendance updated successfully.') }}</p>
                @elseif (session('status') === 'attendance-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Attendance record removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Date') }}</th>
                                <th class="px-4 py-2">{{ __('Student') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Instructor') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($attendances as $attendance)
                                <tr>
                                    <td class="px-4 py-2">{{ $attendance->date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2">{{ $attendance->student->name }}</td>
                                    <td class="px-4 py-2">{{ $attendance->course->name }}</td>
                                    <td class="px-4 py-2">{{ $attendance->instructor?->name ?? '—' }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $attendance->status }}</td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('attendances.show', $attendance) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('attendances.edit', $attendance) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        <form method="post" action="{{ route('attendances.destroy', $attendance) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this attendance record?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No attendance records yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $attendances->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
