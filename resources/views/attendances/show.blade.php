<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Training Login Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white shadow-sm ring-1 ring-gray-200 sm:rounded-xl space-y-4">
                <dl class="divide-y divide-gray-100">
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Date') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $attendance->date->format('Y-m-d') }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Student') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $attendance->student->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Course') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $attendance->course->name }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Session') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2 capitalize">{{ $attendance->session ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Instructor') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $attendance->instructor?->name ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Vehicle') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $attendance->vehicle ?? '—' }}</dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">
                            <x-badge :color="match ($attendance->status) {
                                'present' => 'green',
                                'absent' => 'red',
                                'late' => 'amber',
                                'excused' => 'blue',
                                default => 'gray',
                            }" class="capitalize">{{ $attendance->status }}</x-badge>
                        </dd>
                    </div>
                    <div class="py-2 grid grid-cols-3 gap-4">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Notes') }}</dt>
                        <dd class="text-sm text-gray-900 col-span-2">{{ $attendance->notes ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="flex items-center gap-4">
                    <a href="{{ route('attendances.edit', $attendance) }}">
                        <x-secondary-button type="button">{{ __('Edit') }}</x-secondary-button>
                    </a>
                    <a href="{{ route('attendances.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Back to list') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
