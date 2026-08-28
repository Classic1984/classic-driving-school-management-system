<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\View\View;

class CertificateVerificationController extends Controller
{
    /**
     * Public "scan the QR code" verification page. Looked up by the
     * printed certificate number rather than route-model-bound, so an
     * unrecognized or tampered-with number renders a clear "not valid"
     * result instead of a bare 404.
     */
    public function show(string $certificateNumber): View
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->with(['student', 'course', 'instructor'])
            ->first();

        return view('certificates.verify', compact('certificate'));
    }
}
