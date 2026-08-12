@props(['data'])

<div {{ $attributes }}>
    {!! app(\App\Services\QrCodeGenerator::class)->svg($data) !!}
</div>
