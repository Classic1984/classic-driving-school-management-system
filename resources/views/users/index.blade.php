<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Staff') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">
                        {{ __('Staff') }}
                    </h3>

                    <a href="{{ route('users.create') }}">
                        <x-primary-button type="button">{{ __('Add Staff') }}</x-primary-button>
                    </a>
                </div>

                @if (session('status') === 'user-created')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Staff account created successfully.') }}</p>
                @elseif (session('status') === 'user-updated')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Staff account updated successfully.') }}</p>
                @elseif (session('status') === 'user-deleted')
                    <p class="mb-4 text-sm font-medium text-green-600">{{ __('Staff account removed successfully.') }}</p>
                @endif
                <x-input-error class="mb-4" :messages="$errors->get('user')" />

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-2">{{ __('Name') }}</th>
                                <th class="px-4 py-2">{{ __('Email') }}</th>
                                <th class="px-4 py-2">{{ __('Role') }}</th>
                                <th class="px-4 py-2"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                <tr>
                                    <td class="px-4 py-2">{{ $user->name }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2">
                                        <x-badge :color="match ($user->role) {
                                            'director' => 'amber',
                                            'secretary' => 'blue',
                                            default => 'gray',
                                        }" class="capitalize">{{ $user->role }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2 text-right space-x-2 whitespace-nowrap">
                                        <a href="{{ route('users.edit', $user) }}" class="text-sm text-amber-600 hover:underline">{{ __('Edit') }}</a>
                                        @if ($user->id !== auth()->id())
                                            <form method="post" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to remove this staff account?') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Remove') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                                        {{ __('No staff accounts yet.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
