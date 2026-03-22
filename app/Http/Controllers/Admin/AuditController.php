<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    /**
     * Display a listing of the verification logs for TVEC officials.
     */
    public function index()
    {
        $logs = VerificationLog::with([
            'assessor', 
            'instructor', 
            'submission.assignment.unit.module.course', 
            'submission.student'
        ])
        ->latest()
        ->paginate(30);

        return view('admin.audits.index', compact('logs'));
    }
}
