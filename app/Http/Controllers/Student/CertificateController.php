<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * List all certificates earned by the logged-in student.
     */
    public function index()
    {
        $certificates = Certificate::where('user_id', Auth::id())
            ->with('course')
            ->latest('issued_at')
            ->get();

        return view('student.certificates.index', compact('certificates'));
    }

    /**
     * Download a certificate as a PDF.
     * Authorization: only the certificate owner can download it.
     */
    public function download(Certificate $certificate)
    {
        // Ownership check — no policy needed, simple and explicit
        if ($certificate->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to download this certificate.');
        }

        if ($certificate->status === 'revoked') {
            return back()->with('error', 'This certificate has been revoked.');
        }

        $qr = base64_encode(
            (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(120)->generate(
                route('certificate.verify', $certificate->certificate_number)
            )
        );

        $pdf = Pdf::loadView('certificates.pdf', compact('certificate', 'qr'))
            ->setPaper('a4', 'landscape')
            ->setOptions([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        $filename = 'certificate-' . $certificate->certificate_number . '.pdf';

        return $pdf->download($filename);
    }
}
