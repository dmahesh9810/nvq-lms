<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChangeRequestController extends Controller
{
    /**
     * List all pending change requests for admin review.
     */
    public function index()
    {
        $pendingRequests = ChangeRequest::with('requester')
            ->where('status', 'pending')
            ->latest()
            ->paginate(20);

        $allRequests = ChangeRequest::with(['requester', 'reviewer'])
            ->latest()
            ->paginate(30, ['*'], 'all_page');

        return view('admin.change-requests.index', compact('pendingRequests', 'allRequests'));
    }

    /**
     * Show the detail of a single change request.
     */
    public function show(ChangeRequest $changeRequest)
    {
        $changeRequest->load(['requester', 'reviewer']);
        $liveTarget = $changeRequest->resolveTarget();

        return view('admin.change-requests.show', compact('changeRequest', 'liveTarget'));
    }

    /**
     * Approve a change request and apply the changes.
     */
    public function approve(ChangeRequest $changeRequest)
    {
        if (! $changeRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        DB::transaction(function () use ($changeRequest) {
            $target = $changeRequest->resolveTarget();

            // Guard: target may have been deleted by admin in the meantime
            if (! $target) {
                $changeRequest->update([
                    'status'      => 'rejected',
                    'admin_note'  => 'Target resource no longer exists — auto-rejected.',
                    'reviewed_by' => Auth::id(),
                    'reviewed_at' => now(),
                ]);
                return;
            }

            if ($changeRequest->action === 'delete') {
                // Handle file cleanup for courses (thumbnail) and lessons (pdf)
                if ($changeRequest->type === 'course' && $target->thumbnail) {
                    Storage::disk('public')->delete($target->thumbnail);
                }
                if ($changeRequest->type === 'lesson' && $target->pdf_path) {
                    Storage::disk('public')->delete($target->pdf_path);
                }
                $target->delete();

            } elseif ($changeRequest->action === 'update') {
                $payload = $changeRequest->payload ?? [];
                if (! empty($payload)) {
                    $target->update($payload);
                }
            }

            $changeRequest->update([
                'status'      => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
        });

        return back()->with('success', 'Change request approved and applied successfully.');
    }

    /**
     * Reject a change request with an optional note.
     */
    public function reject(Request $request, ChangeRequest $changeRequest)
    {
        if (! $changeRequest->isPending()) {
            return back()->with('error', 'This request has already been reviewed.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $changeRequest->update([
            'status'      => 'rejected',
            'admin_note'  => $request->input('admin_note'),
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Change request rejected and instructor has been notified.');
    }
}
