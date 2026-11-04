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
        <meta name="theme-color" content="#0B1E3D">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="CDSMS">
        <link rel="apple-touch-icon" href="{{ asset('images/pwa/icon-192.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|figtree:400,500,600|dancing-script:700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
            }
        </script>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-paper" x-data="{ sidebarOpen: false }">
            <div class="print:hidden">
                @include('layouts.navigation')
            </div>

            <div class="lg:pl-64">
                <!-- Mobile Top Bar -->
                <div class="sticky top-0 z-30 flex h-16 items-center gap-4 bg-navy px-4 print:hidden lg:hidden">
                    <button @click="sidebarOpen = true" class="inline-flex items-center justify-center p-2 rounded-md text-navy-300 hover:text-gold-300 hover:bg-white/5 focus:outline-none focus:bg-white/5 focus:text-gold-300 transition duration-150 ease-in-out">
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
                    <header class="bg-white border-b border-line print:hidden">
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
                class="fixed bottom-6 right-6 z-50 max-w-sm rounded-xl bg-navy px-5 py-4 text-white shadow-lg print:hidden"
                style="display: none;"
            >
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gold-300">Expenses updated</p>
                        <p class="mt-1 text-sm text-navy-200">Incurred expenses have changed since you last checked.</p>
                        <a href="{{ route('expenses.index') }}" class="mt-2 inline-block text-sm font-medium text-gold-300 hover:text-gold-200">View Expenses &rarr;</a>
                    </div>
                    <button @click="show = false" class="text-navy-300 hover:text-white" aria-label="Dismiss">&times;</button>
                </div>
            </div>
        @endif

        @auth
            {{-- Idle screensaver: a branded full-screen overlay that
            appears after $idleMinutes of no mouse/keyboard/touch
            activity anywhere on the page, and clears on the next one. --}}
            <div
                x-data="{
                    idleMinutes: 10,
                    show: false,
                    time: '',
                    date: '',
                    idleTimer: null,
                    clockTimer: null,
                    updateClock() {
                        const now = new Date();
                        this.time = now.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true });
                        this.date = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
                    },
                    resetIdleTimer() {
                        this.show = false;
                        clearTimeout(this.idleTimer);
                        this.idleTimer = setTimeout(() => { this.show = true; }, this.idleMinutes * 60 * 1000);
                    },
                    init() {
                        this.updateClock();
                        this.clockTimer = setInterval(() => this.updateClock(), 1000);
                        this.resetIdleTimer();
                        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach((eventName) => {
                            window.addEventListener(eventName, () => this.resetIdleTimer(), { passive: true });
                        });
                    },
                }"
                x-show="show"
                x-cloak
                x-transition.opacity.duration.500ms
                class="fixed inset-0 z-[9999] flex items-center justify-center cursor-pointer print:hidden"
                style="display: none; background: radial-gradient(circle at 50% 42%, #10264d 0%, #0B1E3D 45%, #071530 100%);"
            >
                <svg class="pointer-events-none absolute top-1/2 left-1/2 h-[640px] w-[640px] -mt-80 -ml-80 text-gold-400/[0.07] animate-[spin_90s_linear_infinite]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.4">
                    <circle cx="12" cy="12" r="9.5" />
                    <circle cx="12" cy="12" r="6.5" />
                    <path stroke-linecap="round" d="M12 2.5v4M12 17.5v4M2.5 12h4M17.5 12h4M4.9 4.9l2.8 2.8M16.3 16.3l2.8 2.8M19.1 4.9l-2.8 2.8M7.7 16.3l-2.8 2.8" />
                </svg>

                <div class="relative flex flex-col items-center gap-5 px-6 text-center">
                    <div class="flex flex-col items-center gap-2.5">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gold-500/15 ring-1 ring-gold-400/30">
                            <svg class="h-6 w-6 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" /></svg>
                        </span>
                        <p class="text-[13px] font-bold tracking-[0.18em] uppercase text-gold-400">{{ __('Classic Driving School') }}</p>
                    </div>

                    <div>
                        <p class="m-0 text-6xl sm:text-8xl font-extrabold leading-none tracking-tight text-white tabular-nums" x-text="time"></p>
                        <p class="mt-3.5 text-base text-navy-200" x-text="date"></p>
                    </div>

                    <p class="mt-1.5 max-w-sm text-sm italic text-navy-300">{{ __('"Safe Drivers. Better Roads. A Brighter Tomorrow."') }}</p>

                    <p class="mt-8 text-xs font-semibold tracking-[0.08em] uppercase text-gold-400 animate-pulse">{{ __('Tap anywhere or press any key to continue') }}</p>
                </div>
            </div>
        @endauth
    </body>
</html>
