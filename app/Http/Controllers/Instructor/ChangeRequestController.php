<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\ChangeRequest;
use App\Models\Course;
use App\Models\Module;
use App\Models\Unit;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChangeRequestController extends Controller
{
    /**
     * List the authenticated instructor's own change requests.
     */
    public function index()
    {
        $requests = ChangeRequest::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('instructor.change-requests.index', compact('requests'));
    }

    /**
     * Submit a new change request.
     *
     * Expected POST body:
     *   type   : course | module | unit | lesson
     *   action : update | delete
     *   target_id : int
     *   payload : array of proposed field changes (for update)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'      => ['required', 'in:course,module,unit,lesson'],
            'action'    => ['required', 'in:update,delete'],
            'target_id' => ['required', 'integer', 'min:1'],
            'payload'   => ['nullable', 'array'],
        ]);

        $type     = $validated['type'];
        $action   = $validated['action'];
        $targetId = $validated['target_id'];

        // ── 1. Resolve target & verify it exists ──────────────────────────────
        $target = $this->resolveTarget($type, $targetId);
        if (! $target) {
            return back()->with('error', 'The target resource was not found.');
        }

        // ── 2. Authorization: user must be assigned to the resource ───────────
        if (! $this->canRequestChangeFor($type, $target)) {
            abort(403, 'You are not authorised to request changes for this resource.');
        }

        // ── 3. Sanitise payload (only allow known editable fields) ────────────
        $cleanPayload = null;
        if ($action === 'update' && ! empty($validated['payload'])) {
            $cleanPayload = $this->sanitisePayload($type, $validated['payload']);
            if (empty($cleanPayload)) {
                return back()->with('error', 'No valid fields were included in the request.');
            }
        }

        // ── 4. Prevent duplicate pending requests ─────────────────────────────
        $existing = ChangeRequest::where([
            'user_id'   => Auth::id(),
            'type'      => $type,
            'action'    => $action,
            'target_id' => $targetId,
            'status'    => 'pending',
        ])->first();

        if ($existing) {
            return back()->with('error', 'You already have a pending request for this item. Please wait for admin review.');
        }

        // ── 5. Create the request ─────────────────────────────────────────────
        ChangeRequest::create([
            'user_id'      => Auth::id(),
            'type'         => $type,
            'action'       => $action,
            'target_id'    => $targetId,
            'target_title' => $this->extractTitle($target),
            'payload'      => $cleanPayload,
            'status'       => 'pending',
        ]);

        $actionLabel = $action === 'delete' ? 'deletion' : 'edit';
        return back()->with('success', "Your {$actionLabel} request has been submitted and is awaiting admin review.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveTarget(string $type, int $id): ?object
    {
        return match ($type) {
            'course'  => Course::find($id),
            'module'  => Module::find($id),
            'unit'    => Unit::find($id),
            'lesson'  => Lesson::find($id),
            default   => null,
        };
    }

    /**
     * Verify the authenticated user has at least view-level access to the target.
     */
    private function canRequestChangeFor(string $type, object $target): bool
    {
        $userId  = Auth::id();
        $isAdmin = Auth::user()->isAdmin();

        if ($isAdmin) return true;

        return match ($type) {
            'course' => $target->instructor_id === $userId
                || $target->assignedInstructors()->where('users.id', $userId)->exists(),

            'module' => $target->course->instructor_id === $userId
                || $target->course->assignedInstructors()->where('users.id', $userId)->exists()
                || $target->assignedInstructors()->where('users.id', $userId)->exists(),

            'unit' => $target->module->course->instructor_id === $userId
                || $target->module->course->assignedInstructors()->where('users.id', $userId)->exists()
                || $target->module->assignedInstructors()->where('users.id', $userId)->exists(),

            'lesson' => $target->unit->module->course->instructor_id === $userId
                || $target->unit->module->course->assignedInstructors()->where('users.id', $userId)->exists()
                || $target->unit->module->assignedInstructors()->where('users.id', $userId)->exists(),

            default => false,
        };
    }

    /**
     * Whitelist only safe, editable fields per resource type.
     */
    private function sanitisePayload(string $type, array $raw): array
    {
        $allowed = match ($type) {
            'course' => ['title', 'description', 'category', 'level', 'price'],
            'module' => ['title', 'description', 'order', 'is_active'],
            'unit'   => ['title', 'description', 'order', 'is_active'],
            'lesson' => ['title', 'content', 'type', 'order', 'is_active', 'duration'],
            default  => [],
        };

        return array_filter(
            array_intersect_key($raw, array_flip($allowed)),
            fn($v) => $v !== null && $v !== ''
        );
    }

    private function extractTitle(object $target): string
    {
        return $target->title ?? $target->name ?? "#{$target->id}";
    }
}
