<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class VerifyCertificateController extends Controller
{
    /**
     * Display the public certificate verification form.
     */
    public function showForm(Request $request)
    {
        return view('certificates.verify');
    }

    public function verifyByUrl($certificate_number)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('certificate_number', $certificate_number)
            ->first();

        return view('certificates.result', compact('certificate'));
    }

    /**
     * Process the verification request.
     */
    public function verify(Request $request)
    {
        // Prevent enumeration by strictly validating the expected format: IQB-YYYY-XXXXXX
        $validated = $request->validate([
            'certificate_number' => [
                'required',
                'string',
                'regex:/^IQB-\d{4}-[A-Z0-9]{6}$/'
            ]
        ], [
            'certificate_number.regex' => 'The certificate number format is invalid. It should look like IQB-YYYY-XXXXXX.'
        ]);

        $certificate = Certificate::with(['user', 'course'])
            ->where('certificate_number', $validated['certificate_number'])
            ->first();

        // Pass the certificate (or null) to the result view
        return view('certificates.result', compact('certificate'));
    }
}
