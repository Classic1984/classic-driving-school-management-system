<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- PWA: installable on Android/desktop via the manifest, and on
        iOS via the apple-* tags below (iOS doesn't read the manifest). -->
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <meta name="theme-color" content="#000000">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="CDSMS">
        <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|dancing-script:700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
            }
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100" x-data="{ sidebarOpen: false }">
            <div class="print:hidden">
                @include('layouts.navigation')
            </div>

            <div class="lg:pl-64">
                <!-- Mobile Top Bar -->
                <div class="sticky top-0 z-30 flex h-16 items-center gap-4 bg-black px-4 print:hidden lg:hidden">
                    <button @click="sidebarOpen = true" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-amber-400 hover:bg-gray-800 focus:outline-none focus:bg-gray-800 focus:text-amber-400 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-8 w-auto" />
                    </a>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white shadow print:hidden">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>

        @if (auth()->user()?->isDirector())
            <div
                x-data="{
                    show: false,
                    snapshotKey: 'cdsms-expense-snapshot',
                    async check() {
                        try {
                            const response = await fetch('{{ route('expenses.last-updated') }}', {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (! response.ok) return;

                            const data = await response.json();
                            const snapshot = `${data.count}:${data.last_updated_at}`;
                            const previous = localStorage.getItem(this.snapshotKey);

                            // Only alert on a change from a snapshot we've
                            // already seen - the very first check on a
                            // fresh browser just establishes the baseline,
                            // so opening the app doesn't itself look like
                            // an update.
                            if (previous !== null && previous !== snapshot) {
                                this.show = true;
                            }

                            localStorage.setItem(this.snapshotKey, snapshot);
                        } catch (error) {
                            console.warn('Expense update check failed:', error);
                        }
                    },
                    init() {
                        this.check();
                        setInterval(() => this.check(), 5 * 60 * 1000);
                    },
                }"
                x-show="show"
                x-transition
                class="fixed bottom-6 right-6 z-50 max-w-sm rounded-lg bg-black px-5 py-4 text-white shadow-lg print:hidden"
                style="display: none;"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-amber-400">Expenses updated</p>
                        <p class="mt-1 text-sm text-gray-300">Incurred expenses have changed since you last checked.</p>
                        <a href="{{ route('expenses.index') }}" class="mt-2 inline-block text-sm font-medium text-amber-400 hover:text-amber-300">View Expenses &rarr;</a>
                    </div>
                    <button @click="show = false" class="text-gray-400 hover:text-white" aria-label="Dismiss">&times;</button>
                </div>
            </div>
        @endif
    </body>
</html>
