<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Staff') }}
        </h2>
    </x-slot>

    @php
        $usersIconPath = 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z';
        $personIconPath = 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 22.5c-2.676 0-5.216-.584-7.499-1.632Z';
        $userCheckIconPath = [$personIconPath, 'm16.5 15.75 1.5 1.5 3-3'];
        $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
        $envelopeIconPath = 'M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75';
        $gearIconPath = ['M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28Z', 'M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'];

        $roleBadge = fn (string $role) => match ($role) {
            'director' => 'amber',
            'secretary' => 'blue',
            'instructor' => 'green',
            default => 'gray',
        };
    @endphp

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-extrabold text-gray-900">{{ __('Staff') }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Manage your team members') }}</p>
                </div>
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-50">
                    <svg class="h-7 w-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                </span>
            </div>

            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                        </span>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ __('Staff Members') }}</h4>
                            <p class="text-sm text-gray-500">{{ __('View and manage all staff') }}</p>
                        </div>
                    </div>

                    <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition shrink-0">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ __('Add Staff') }}
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
                            <tr class="bg-amber-50/60 rounded-xl text-left text-xs font-semibold uppercase tracking-wider text-amber-800">
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $personIconPath }}" /></svg>
                                        {{ __('Name') }}
                                    </span>
                                </th>
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $envelopeIconPath }}" /></svg>
                                        {{ __('Email') }}
                                    </span>
                                </th>
                                <th class="px-4 py-3">{{ __('Role') }}</th>
                                <th class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5">
                                        <svg class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            @foreach ($gearIconPath as $path)
                                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                                            @endforeach
                                        </svg>
                                        {{ __('Actions') }}
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                                @php
                                    $initials = collect(explode(' ', $user->name))->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-black text-amber-400 text-xs font-bold">{{ $initials }}</span>
                                            <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <x-badge :color="$roleBadge($user->role)" class="capitalize">{{ $user->role }}</x-badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm align-top">
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('users.edit', $user) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition" title="{{ __('Edit') }}">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </a>
                                            @if ($user->id !== auth()->id())
                                                <form method="post" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this staff account?') }}');">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition" title="{{ __('Remove') }}">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
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

                <div class="rounded-xl bg-amber-50/60 ring-1 ring-amber-100 p-6 mt-6">
                    <div class="grid grid-cols-3 gap-6 text-center">
                        <div>
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-indigo-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $usersIconPath }}" /></svg>
                            </span>
                            <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ number_format($totalStaff) }}</p>
                            <p class="text-xs text-gray-500">{{ __('Total Staff') }}</p>
                        </div>
                        <div>
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-green-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    @foreach ($userCheckIconPath as $path)
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
                                    @endforeach
                                </svg>
                            </span>
                            <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ number_format($instructorCount) }}</p>
                            <p class="text-xs text-gray-500">{{ __('Instructors') }}</p>
                        </div>
                        <div>
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-white text-blue-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                            </span>
                            <p class="mt-2 text-2xl font-extrabold text-gray-900">{{ number_format($administratorCount) }}</p>
                            <p class="text-xs text-gray-500">{{ __('Administrators') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                    {{ $users->links() }}

                    <form method="get" action="{{ route('users.index') }}" class="flex items-center gap-2">
                        <select name="per_page" onchange="this.form.submit()" class="rounded-lg border-gray-300 focus:border-amber-500 focus:ring-amber-500 text-sm font-medium">
                            @foreach ([10, 25, 50] as $option)
                                <option value="{{ $option }}" @selected($perPage === $option)>{{ __(':count / page', ['count' => $option]) }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
