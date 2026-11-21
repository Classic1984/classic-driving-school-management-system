<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Desk Stations') }}
        </h2>
    </x-slot>

    @php
        $cameraIconPath = 'M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.823 1.316Z M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z';
        $plusIconPath = 'M12 9v3.75m0 0v3.75m0-3.75h3.75m-3.75 0h-3.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $keyIconPath = 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z';
    @endphp

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm ring-1 ring-gray-200 rounded-xl overflow-hidden">
                <div class="relative overflow-hidden bg-navy p-6 sm:p-8">
                    <svg class="pointer-events-none absolute -right-8 -top-8 h-48 w-48 text-gold-500/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.75"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cameraIconPath }}" /></svg>

                    <div class="relative flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gold-500/15 text-gold-400 ring-1 ring-gold-400/30">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cameraIconPath }}" /></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ __('Desk Stations') }}</h3>
                                <p class="text-sm text-gray-400">{{ __('Live occupancy from each camera-monitored desk - not a recording, just whether someone is there right now') }}</p>
                            </div>
                        </div>
                        <a href="{{ route('desk-stations.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gold-500 hover:bg-gold-400 px-5 py-2.5 text-sm font-bold text-navy transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $plusIconPath }}" /></svg>
                            {{ __('Add Station') }}
                        </a>
                    </div>
                </div>

                @if (session('status') === 'station-created')
                    <p class="px-8 pt-6 text-sm font-medium text-green-600">{{ __('Station added. Copy its token into the on-site agent\'s config to start reporting its status.') }}</p>
                @elseif (session('status') === 'station-deleted')
                    <p class="px-8 pt-6 text-sm font-medium text-green-600">{{ __('Station removed.') }}</p>
                @elseif (session('status') === 'token-regenerated')
                    <p class="px-8 pt-6 text-sm font-medium text-green-600">{{ __('Token regenerated. Update the on-site agent\'s config with the new token below - the old one no longer works.') }}</p>
                @endif

                <div class="p-6 sm:p-8"
                    x-data="{
                        stations: {{ Illuminate\Support\Js::from($stations->map(fn ($station) => [
                            'id' => $station->id,
                            'name' => $station->name,
                            'token' => $station->api_token,
                            'display_status' => $station->displayStatus(),
                            'last_seen_at' => $station->last_seen_at?->diffForHumans(),
                            'regenerate_url' => route('desk-stations.regenerate-token', $station),
                            'destroy_url' => route('desk-stations.destroy', $station),
                        ])) }},
                        statusLabel(status) {
                            return { occupied: '{{ __('Occupied') }}', empty: '{{ __('Empty') }}', offline: '{{ __('Offline') }}', unknown: '{{ __('Never reported') }}' }[status] ?? status;
                        },
                        statusClasses(status) {
                            return {
                                occupied: 'bg-green-100 text-green-700',
                                empty: 'bg-gray-100 text-gray-700',
                                offline: 'bg-red-100 text-red-700',
                                unknown: 'bg-gray-100 text-gray-500',
                            }[status] ?? 'bg-gray-100 text-gray-500';
                        },
                        async refresh() {
                            try {
                                const response = await fetch('{{ route('desk-stations.status') }}', { headers: { Accept: 'application/json' } });
                                const data = await response.json();
                                data.stations.forEach((updated) => {
                                    const station = this.stations.find((s) => s.id === updated.id);
                                    if (station) {
                                        station.display_status = updated.display_status;
                                        station.last_seen_at = updated.last_seen_at;
                                    }
                                });
                            } catch (e) {
                                // A missed poll just means the grid stays as it was until the next one.
                            }
                        },
                    }"
                    x-init="setInterval(() => refresh(), 15000)"
                >
                    @if ($stations->isEmpty())
                        <div class="flex flex-col items-center gap-2 py-10">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-50 text-gray-300">
                                <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cameraIconPath }}" /></svg>
                            </span>
                            <p class="text-sm text-gray-500">{{ __('No desk stations set up yet.') }}</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <template x-for="station in stations" :key="station.id">
                                <div class="rounded-xl ring-1 ring-gray-200 p-5">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-semibold text-gray-900" x-text="station.name"></p>
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold" :class="statusClasses(station.display_status)" x-text="statusLabel(station.display_status)"></span>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400" x-text="station.last_seen_at ? '{{ __('Last reported') }} ' + station.last_seen_at : '{{ __('Never reported') }}'"></p>

                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="flex items-center gap-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $keyIconPath }}" /></svg>
                                            {{ __('Agent Token') }}
                                        </p>
                                        <input type="text" readonly :value="station.token" onclick="this.select()" class="mt-1 block w-full rounded-md border-0 bg-gray-50 px-2 py-1.5 text-xs font-mono text-gray-600 ring-1 ring-gray-200">
                                    </div>

                                    <div class="mt-4 flex items-center justify-between">
                                        <form method="post" :action="station.regenerate_url" onsubmit="return confirm('{{ __('Regenerate this station\'s token? The on-site agent will need the new one.') }}');">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-gold-600 hover:underline">{{ __('Regenerate Token') }}</button>
                                        </form>
                                        <form method="post" :action="station.destroy_url" onsubmit="return confirm('{{ __('Remove this station?') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="text-sm font-semibold text-red-600 hover:underline">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
