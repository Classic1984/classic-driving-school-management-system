<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two Factor Authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add an extra layer of security to your account by requiring a code from an authenticator app (like Google Authenticator) when you log in.') }}
        </p>
    </header>

    @if (session('status') === 'two-factor-authentication-required')
        <p class="mt-4 text-sm font-medium text-red-600">
            {{ __('Your role requires two factor authentication. Please finish setting it up below before you can access the rest of the system.') }}
        </p>
    @endif

    <div class="mt-6 space-y-6">
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
                <x-primary-button type="submit">{{ __('Enable Two Factor Authentication') }}</x-primary-button>
            </form>
        @endif
    </div>
</section>
