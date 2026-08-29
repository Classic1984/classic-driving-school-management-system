@auth
    @if (config('services.webpush.vapid_public_key'))
        <div
            x-data="{
                vapidPublicKey: '{{ config('services.webpush.vapid_public_key') }}',
                supported: false,
                subscribed: false,
                busy: false,
                message: '',
                urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
                    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
                },
                async init() {
                    this.supported = 'serviceWorker' in navigator && 'PushManager' in window;
                    if (! this.supported) return;

                    const registration = await navigator.serviceWorker.ready;
                    const existing = await registration.pushManager.getSubscription();
                    this.subscribed = existing !== null;
                },
                async subscribe() {
                    this.busy = true;
                    this.message = '';
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.subscribe({
                            userVisibleOnly: true,
                            applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey),
                        });

                        await fetch('{{ route('push-subscriptions.store') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(subscription.toJSON()),
                        });

                        this.subscribed = true;
                    } catch (error) {
                        this.message = 'Could not enable notifications on this device.';
                        console.warn('Push subscribe failed:', error);
                    } finally {
                        this.busy = false;
                    }
                },
                async unsubscribe() {
                    this.busy = true;
                    this.message = '';
                    try {
                        const registration = await navigator.serviceWorker.ready;
                        const subscription = await registration.pushManager.getSubscription();

                        if (subscription) {
                            await fetch('{{ route('push-subscriptions.destroy') }}', {
                                method: 'DELETE',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ endpoint: subscription.endpoint }),
                            });
                            await subscription.unsubscribe();
                        }

                        this.subscribed = false;
                    } catch (error) {
                        this.message = 'Could not turn off notifications.';
                        console.warn('Push unsubscribe failed:', error);
                    } finally {
                        this.busy = false;
                    }
                },
                async sendTest() {
                    this.busy = true;
                    this.message = '';
                    try {
                        await fetch('{{ route('push-subscriptions.test') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        });
                        this.message = 'Test notification sent.';
                    } catch (error) {
                        this.message = 'Could not send a test notification.';
                    } finally {
                        this.busy = false;
                    }
                },
            }"
            x-show="supported"
            x-cloak
            class="fixed top-6 right-6 z-50 print:hidden"
            style="display: none;"
        >
            <div class="rounded-lg bg-black px-4 py-3 text-white shadow-lg text-xs">
                <template x-if="!subscribed">
                    <button type="button" @click="subscribe()" :disabled="busy" class="font-medium text-amber-400 hover:text-amber-300 disabled:opacity-50">
                        <span x-show="!busy">🔔 Enable Notifications</span>
                        <span x-show="busy" x-cloak>Enabling…</span>
                    </button>
                </template>
                <template x-if="subscribed">
                    <div class="flex items-center gap-3">
                        <span class="text-gray-300">🔔 Notifications on</span>
                        <button type="button" @click="sendTest()" :disabled="busy" class="text-amber-400 hover:text-amber-300 font-medium disabled:opacity-50">{{ __('Test') }}</button>
                        <button type="button" @click="unsubscribe()" :disabled="busy" class="text-gray-400 hover:text-white disabled:opacity-50">{{ __('Turn off') }}</button>
                    </div>
                </template>
                <p x-show="message" x-text="message" x-cloak class="mt-1 text-gray-400"></p>
            </div>
        </div>
    @endif
@endauth
