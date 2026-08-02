<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Instructors') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Instructors') }}
                    </h3>

                    <a href="{{ route('instructors.create') }}">
                        <x-primary-button type="button">{{ __('Add Instructor') }}</x-primary-button>
                    </a>
                </div>

                @if (session('status') === 'instructor-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Instructor registered successfully.') }}</p>
                @elseif (session('status') === 'instructor-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Instructor updated successfully.') }}</p>
                @elseif (session('status') === 'instructor-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Instructor removed successfully.') }}</p>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Email') }}</th>
                                <th class="px-4 py-2">{{ __('Phone') }}</th>
                                <th class="px-4 py-2">{{ __('Specialization') }}</th>
                                <th class="px-4 py-2">{{ __('Status') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($instructors as $instructor)
                                <tr>
                                    <td class="px-4 py-2">{{ $instructor->name }}</td>
                                    <td class="px-4 py-2">{{ $instructor->email }}</td>
                                    <td class="px-4 py-2">{{ $instructor->phone }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $instructor->specialization }}</td>
                                    <td class="px-4 py-2 capitalize">{{ $instructor->status }}</td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('instructors.show', $instructor) }}" class="text-sm text-indigo-600 hover:underline">{{ __('View') }}</a>
                                        <a href="{{ route('instructors.edit', $instructor) }}" class="text-sm text-indigo-600 hover:underline">{{ __('Edit') }}</a>
                                        <form method="post" action="{{ route('instructors.destroy', $instructor) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this instructor?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Delete') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No instructors registered yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $instructors->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
