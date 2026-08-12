<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Renders an arbitrary piece of text as an inline QR code SVG - no
 * external image service involved, everything generated locally. Used to
 * put scannable student/program details directly on certificates and
 * payment receipts.
 */
class QrCodeGenerator
{
    /**
     * Render the given text as an inline SVG string, ready to be echoed
     * directly into a Blade view.
     */
    public function svg(string $text, int $size = 160): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size),
            new SvgImageBackEnd,
        );

        $svg = (new Writer($renderer))->writeString($text);

        // Strip the leading XML declaration line so this can be embedded
        // directly inline in an HTML document.
        return trim(substr($svg, strpos($svg, "\n") + 1));
    }
}
