<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Management') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-1">
                    Classic Driving School & Son Nigeria Limited
                </h2>

                <p class="text-sm text-gray-600">
                    CDSMS Version 1.0
                </p>
            </div>

            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Students') }}
                    </h3>

                    <a href="{{ route('students.create') }}">
                        <x-primary-button type="button">{{ __('Add Student') }}</x-primary-button>
                    </a>
                </div>

                @if (session('status') === 'student-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Student registered successfully.') }}</p>
                @elseif (session('status') === 'student-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Student updated successfully.') }}</p>
                @elseif (session('status') === 'student-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Student removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Email') }}</th>
                                <th class="px-4 py-2">{{ __('Phone') }}</th>
                                <th class="px-4 py-2">{{ __('Course') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($students as $student)
                                <tr>
                                    <td class="px-4 py-2">{{ $student->name }}</td>
                                    <td class="px-4 py-2">{{ $student->email }}</td>
                                    <td class="px-4 py-2">{{ $student->phone }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $student->course_type }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $student->status }}</td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('students.show', $student) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('students.edit', $student) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        <form method="post" action="{{ route('students.destroy', $student) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this student?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
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
