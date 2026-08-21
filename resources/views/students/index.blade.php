<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Management') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <h2 class="text-2xl font-bold mb-1">
                    Classic Driving School & Son Nigeria Limited
                </h2>

                <p class="text-sm text-gray-600">
                    CDSMS Version 1.0
                </p>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Students') }}
                    </h3>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('referral-source-report.index') }}">
                            <x-secondary-button type="button">{{ __('View Referral Source Report') }}</x-secondary-button>
                        </a>
                        <a href="{{ route('students.create') }}">
                            <x-primary-button type="button">{{ __('Add Student') }}</x-primary-button>
                        </a>
                    </div>
                </div>

                @if (session('status') === 'student-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Student registered successfully.') }}</p>
                @elseif (session('status') === 'student-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Student updated successfully.') }}</p>
                @elseif (session('status') === 'student-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Student removed successfully.') }}</p>
                @endif

                <form method="get" action="{{ route('students.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <x-input-label for="search" :value="__('Search')" />
                        <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" placeholder="{{ __('Name, email, or phone') }}" :value="request('search')" />
                    </div>

                    <div>
                        <x-input-label for="status" :value="__('Status')" />
                        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach (['active' => 'Active', 'completed' => 'Completed', 'withdrawn' => 'Withdrawn'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="course_id" :value="__('Course')" />
                        <select id="course_id" name="course_id" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="payment" :value="__('Payment')" />
                        <select id="payment" name="payment" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
                            <option value="">{{ __('All') }}</option>
                            <option value="clear" @selected(request('payment') === 'clear')>{{ __('Clear') }}</option>
                            <option value="locked" @selected(request('payment') === 'locked')>{{ __('Locked') }}</option>
                        </select>
                    </div>

                    <div class="md:col-span-4 flex items-center gap-3">
                        <x-primary-button type="submit">{{ __('Filter') }}</x-primary-button>
                        <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:underline">{{ __('Reset') }}</a>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Student ID') }}</th>
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Email') }}</th>
                                <th class="px-4 py-2">{{ __('Phone') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2">{{ __('Payment') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $student->student_id_number }}</td>
                                    <td class="px-4 py-2">{{ $student->name }}</td>
                                    <td class="px-4 py-2">{{ $student->email }}</td>
                                    <td class="px-4 py-2">{{ $student->phone }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $student->course_type }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="match ($student->status) {
                                            'active' => 'green',
                                            'completed' => 'blue',
                                            'withdrawn' => 'red',
                                            default => 'gray',
                                        }" class="capitalize">{{ $student->status }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2">
                                        @if ($student->courses->contains(fn ($enrolledCourse) => $enrolledCourse->pivot->status === 'locked'))
                                            <x-badge color="red">{{ __('Locked') }}</x-badge>
                                        @else
                                            <x-badge color="green">{{ __('Clear') }}</x-badge>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('students.show', $student) }}" class="text-sm text-amber-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('students.edit', $student) }}" class="text-sm text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                        @if (auth()->user()->isAdmin())
                                            <form method="post" action="{{ route('students.destroy', $student) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this student?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No students registered yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $students->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
