<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    /**
     * List all issued certificates with optional filtering by course or student name.
     */
    public function index(Request $request)
    {
        $query = Certificate::with(['user', 'course'])
            ->latest('issued_at');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $certificates = $query->paginate(20)->withQueryString();
        $courses = Course::orderBy('title')->get(['id', 'title']);

        return view('admin.certificates.index', compact('certificates', 'courses'));
    }

    /**
     * Revoke a certificate (sets status to revoked).
     */
    public function revoke(Certificate $certificate)
    {
        $certificate->update(['status' => 'revoked']);

        return redirect()->route('admin.certificates.index')
            ->with('success', "Certificate {$certificate->certificate_number} has been revoked.");
    }

    /**
     * Reinstate a previously revoked certificate.
     */
    public function reinstate(Certificate $certificate)
    {
        $certificate->update(['status' => 'active']);

        return redirect()->route('admin.certificates.index')
            ->with('success', "Certificate {$certificate->certificate_number} reinstated.");
    }
}
