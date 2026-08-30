@php
    $shieldCheckIconPath = 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z';
@endphp

<section x-data="{ open: {{ $user->hasEnabledTwoFactorAuthentication() || $qrCodeSvg ? 'true' : 'false' }} }">
    <button type="button" x-on:click="open = !open" class="w-full flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-green-50 text-green-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
            </span>
            <div class="text-left">
                <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                    {{ __('Two Factor Authentication') }}
                    @if ($user->hasEnabledTwoFactorAuthentication())
                        <x-badge color="green">{{ __('Enabled') }}</x-badge>
                    @else
                        <x-badge color="green">{{ __('Recommended') }}</x-badge>
                    @endif
                </h2>
                <p class="text-sm text-gray-500">{{ __('Add an extra layer of security to your account.') }}</p>
            </div>
        </div>
        <svg class="h-5 w-5 shrink-0 text-gray-400 transition-transform" x-bind:class="open ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
    </button>

    <div x-show="open" x-cloak x-transition class="mt-6 space-y-6 pt-4 border-t border-gray-100">
        <p class="text-sm text-gray-600">
            {{ __('Add an extra layer of security to your account by requiring a code from an authenticator app (like Google Authenticator) when you log in.') }}
        </p>

        @if ($user->hasEnabledTwoFactorAuthentication())
            <p class="text-sm font-medium text-green-600">
                {{ __('Two factor authentication is enabled.') }}
            </p>

            @if (session('recoveryCodes'))
                <div class="rounded-md bg-amber-50 border border-amber-200 p-4">
                    <p class="text-sm font-medium text-gray-900">
                        {{ __('Save these recovery codes somewhere safe. Each one can be used once to log in if you lose access to your authenticator app - they will not be shown again.') }}
                    </p>
                    <div class="mt-3 grid grid-cols-2 gap-1 font-mono text-sm text-gray-700">
                        @foreach (session('recoveryCodes') as $recoveryCode)
                            <div>{{ $recoveryCode }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-4">
                <form method="post" action="{{ route('two-factor.recovery-codes') }}">
                    @csrf
                    <x-secondary-button type="submit">{{ __('Regenerate Recovery Codes') }}</x-secondary-button>
                </form>

                <form method="post" action="{{ route('two-factor.disable') }}" onsubmit="return confirm('{{ __('Are you sure you want to disable two factor authentication?') }}');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Disable') }}</button>
                </form>
            </div>
        @elseif ($qrCodeSvg)
            <p class="text-sm text-gray-600">
                {{ __('Scan the QR code below with your authenticator app, then enter the 6-digit code it generates to finish setup.') }}
            </p>

            <div class="inline-block bg-white p-4 border border-gray-200 rounded-md">
                {!! $qrCodeSvg !!}
            </div>

            <form method="post" action="{{ route('two-factor.confirm') }}" class="max-w-xs space-y-4">
                @csrf

                <div>
                    <x-input-label for="two_factor_code" :value="__('Code')" />
                    <x-text-input id="two_factor_code" name="code" type="text" inputmode="numeric" class="mt-1 block w-full" autocomplete="one-time-code" autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('code')" />
                </div>

                <x-primary-button type="submit">{{ __('Confirm') }}</x-primary-button>
            </form>
        @else
            <form method="post" action="{{ route('two-factor.enable') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-black hover:bg-gray-900 px-5 py-3 text-sm font-bold text-amber-400 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $shieldCheckIconPath }}" /></svg>
                    {{ __('Enable Two Factor Authentication') }}
                </button>
            </form>
        @endif
    </div>
</section>
