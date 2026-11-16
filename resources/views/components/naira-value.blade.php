@props(['value'])

{{--
    At large/bold sizes (text-3xl font-extrabold and up), the ₦ glyph's
    horizontal bars render close enough to the next character that they
    visually bleed into it - e.g. "₦80,000.00" reads as if the balance
    were struck through. A small gap after the symbol fixes that without
    needing a visible space character that could wrap onto its own line.
--}}
@if (str_starts_with($value, '₦'))
    <span class="mr-1">₦</span>{{ substr($value, strlen('₦')) }}
@else
    {{ $value }}
@endif
