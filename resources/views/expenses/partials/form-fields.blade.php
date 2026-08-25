@php($expense = $expense ?? null)

<div>
    <x-input-label for="category" :value="__('Category')" />
    <select id="category" name="category" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm" required>
        <option value="">{{ __('Select a category') }}</option>
        @foreach (\App\Models\Expense::CATEGORIES as $value => $label)
            <option value="{{ $value }}" @selected(old('category', $expense?->category) === $value)>{{ __($label) }}</option>
        @endforeach
    </select>
    <x-input-error class="mt-2" :messages="$errors->get('category')" />
</div>

<div>
    <x-input-label for="amount" :value="__('Amount')" />
    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount', $expense?->amount)" required />
    <x-input-error class="mt-2" :messages="$errors->get('amount')" />
</div>

<div>
    <x-input-label for="expense_date" :value="__('Expense Date')" />
    <x-text-input id="expense_date" name="expense_date" type="date" class="mt-1 block w-full" :value="old('expense_date', optional($expense?->expense_date)->format('Y-m-d') ?? now()->toDateString())" required />
    <x-input-error class="mt-2" :messages="$errors->get('expense_date')" />
</div>

<div>
    <x-input-label for="description" :value="__('Description')" />
    <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">{{ old('description', $expense?->description) }}</textarea>
    <x-input-error class="mt-2" :messages="$errors->get('description')" />
</div>

<div
    x-data="{
        webcamOpen: false,
        stream: null,
        previewUrl: null,
        error: '',
        async openWebcam() {
            this.error = '';

            if (! navigator.mediaDevices?.getUserMedia) {
                this.error = '{{ __('Webcam capture is not supported in this browser.') }}';
                return;
            }

            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                this.webcamOpen = true;
                this.$nextTick(() => { this.$refs.video.srcObject = this.stream; });
            } catch (e) {
                this.error = '{{ __('Could not access the camera - check your browser permissions.') }}';
            }
        },
        closeWebcam() {
            this.stream?.getTracks().forEach(track => track.stop());
            this.stream = null;
            this.webcamOpen = false;
        },
        capture() {
            const video = this.$refs.video;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            canvas.toBlob((blob) => {
                if (! blob) return;

                const file = new File([blob], 'receipt-photo.jpg', { type: 'image/jpeg' });
                const transfer = new DataTransfer();
                transfer.items.add(file);
                this.$refs.receiptInput.files = transfer.files;

                if (this.previewUrl) URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = URL.createObjectURL(blob);

                this.closeWebcam();
            }, 'image/jpeg', 0.9);
        },
    }"
    @beforeunload.window="closeWebcam()"
>
    <x-input-label for="receipt_photo" :value="__('Receipt Photo')" />
    @if ($expense?->receipt_photo_path)
        <img src="{{ Storage::url($expense->receipt_photo_path) }}" alt="{{ __('Current receipt') }}" class="mt-2 mb-2 h-24 w-24 object-cover rounded-md border border-gray-200">
    @endif
    <template x-if="previewUrl">
        <img :src="previewUrl" alt="{{ __('Captured receipt preview') }}" class="mt-2 mb-2 h-24 w-24 object-cover rounded-md border border-amber-400">
    </template>

    <div class="mt-1 flex items-center gap-3">
        <input id="receipt_photo" name="receipt_photo" x-ref="receiptInput" type="file" accept="image/*" class="block w-full text-sm border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm">
        <button type="button" @click="openWebcam" class="shrink-0 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
            {{ __('Use Camera') }}
        </button>
    </div>

    <p class="mt-1 text-xs text-gray-500">{{ __('Optional - a photo of the receipt or proof of what the money was spent on.') }}</p>
    <p x-show="error" x-text="error" class="mt-1 text-sm text-red-600"></p>
    <x-input-error class="mt-2" :messages="$errors->get('receipt_photo')" />

    <div x-show="webcamOpen" class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-3">
        <video x-ref="video" autoplay playsinline class="w-full max-w-xs rounded-md bg-black"></video>
        <div class="mt-2 flex gap-2">
            <button type="button" @click="capture" class="rounded-md bg-amber-500 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-600">{{ __('Capture') }}</button>
            <button type="button" @click="closeWebcam" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</button>
        </div>
    </div>
</div>
