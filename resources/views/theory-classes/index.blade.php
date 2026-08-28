<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Theory Classes') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'theory-class-created')
                <p class="mb-4 text-sm font-medium text-green-600">{{ __("Today's class was created.") }}</p>
            @elseif (session('status') === 'theory-class-cancelled-today')
                <p class="mb-4 text-sm font-medium text-amber-600">{{ __("Today's theory class is cancelled - see Theory Class Cancellations.") }}</p>
            @endif

            @if (auth()->user()->canManageCourses() && ! $todaysClassExists && ! $todaysClassCancelled)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-center justify-between gap-4">
                    <p class="text-sm text-amber-800">
                        {{ __("Today's class hasn't been created yet - it's normally auto-created at 8am. If the reminder run was missed, create it here instead.") }}
                    </p>
                    <form method="post" action="{{ route('theory-classes.create-today') }}">
                        @csrf
                        <x-primary-button type="submit">{{ __("Create Today's Class") }}</x-primary-button>
                    </form>
                </div>
            @endif

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
                                    <td class="px-4 py-2">
                                        {{ $theoryClass->topic ?: '—' }}
                                        @if ($theoryClass->materials_path)
                                            <a href="{{ $theoryClass->materialsUrl() }}" target="_blank" title="{{ __('Lecture material') }}" class="ms-1 text-gray-400 hover:text-amber-600">📎</a>
                                        @endif
                                    </td>
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
