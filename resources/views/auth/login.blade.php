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
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js'));
            }
        </script>
    </head>
    <body class="font-sans text-navy-800 antialiased">
        <div class="min-h-screen flex flex-wrap">

            {{-- Brand panel - hidden on small screens so the login card
            alone fills the viewport, same as it did before this redesign. --}}
            <div
                x-data="{ ready: false }"
                x-init="setTimeout(() => ready = true, 1800)"
                class="hidden lg:flex lg:flex-1 relative overflow-hidden flex-col justify-between p-14 text-white"
                style="background: radial-gradient(circle at 30% 20%, #15356b 0%, #0B1E3D 55%, #071530 100%);"
            >
                <div class="pointer-events-none absolute -right-24 -bottom-40 h-[520px] w-[520px] rounded-full" style="background: radial-gradient(circle, rgba(201,166,107,0.18) 0%, rgba(201,166,107,0) 70%);"></div>
                <svg class="pointer-events-none absolute -right-10 -top-10 h-72 w-72 text-gold-500/[0.06]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.4">
                    <circle cx="12" cy="12" r="9.5" /><circle cx="12" cy="12" r="6.5" />
                    <path stroke-linecap="round" d="M12 2.5v4M12 17.5v4M2.5 12h4M17.5 12h4" />
                </svg>

                <a href="/" class="relative flex items-center gap-3.5">
                    <x-application-logo class="h-12 w-12 shrink-0" />
                    <div>
                        <p class="text-lg font-extrabold tracking-wide">{{ __('CLASSIC') }} <span class="text-gold-400">{{ __('DRIVING SCHOOL') }}</span></p>
                        <p class="mt-0.5 text-[11px] font-bold tracking-[0.22em] text-navy-200">{{ __('MANAGEMENT SYSTEM') }}</p>
                    </div>
                </a>

                <div class="relative mt-10">
                    <p class="mb-2.5 text-sm italic text-gold-400">{{ __('"Safe Drivers. Better Roads. A Brighter Tomorrow."') }}</p>
                    <h1 class="text-4xl font-extrabold leading-tight">{{ __('Welcome to') }} <span class="text-gold-400">{{ __('CDSMS') }}</span></h1>
                    <p class="mt-3 max-w-md text-[15px] text-navy-200">{{ __('Your complete driving school management solution - students, training, payments, and reports in one place.') }}</p>
                </div>

                <div class="relative flex items-center gap-3">
                    <template x-if="!ready">
                        <span class="inline-block h-4 w-4 rounded-full border-2 border-gold-400/25 border-t-gold-400 animate-spin"></span>
                    </template>
                    <template x-if="ready">
                        <span class="flex h-[18px] w-[18px] items-center justify-center rounded-full bg-green-500/20 text-green-400">
                            <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </span>
                    </template>
                    <span class="text-[13px] font-semibold text-navy-200" x-text="ready ? '{{ __('Workspace ready') }}' : '{{ __('Preparing your workspace…') }}'"></span>
                </div>
            </div>

            {{-- Login card --}}
            <div class="flex flex-1 items-center justify-center p-6 sm:p-10 bg-paper min-w-[320px]">
                <div class="w-full max-w-md bg-white border border-line shadow-sm rounded-2xl p-8 sm:p-9">
                    <a href="/" class="mb-5 inline-flex rounded-2xl bg-navy p-3 shadow-sm">
                        <x-application-logo class="h-10 w-10" />
                    </a>

                    <h2 class="text-xl font-extrabold text-gray-900">{{ __('Welcome Back') }}</h2>
                    <p class="mt-1 mb-6 text-sm text-gray-500">{{ __('Sign in to access your dashboard') }}</p>

                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Address -->
                        <div>
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password')" />

                            <x-text-input id="password" class="block mt-1 w-full"
                                            type="password"
                                            name="password"
                                            required autocomplete="current-password" />

                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div class="block mt-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-gold-600 shadow-sm focus:ring-gold-500" name="remember">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-between mt-6">
                            @if (Route::has('password.request'))
                                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gold-500" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif

                            <x-primary-button class="ms-3 w-full sm:w-auto justify-center">
                                {{ __('Log in') }}
                            </x-primary-button>
                        </div>
                    </form>

                    <p class="mt-7 text-center text-xs text-gray-400">{{ __('Secure sign-in · Classic Driving School & Son Nigeria Limited') }}</p>
                </div>
            </div>
        </div>

        @include('partials.push-notifications')
    </body>
</html>
