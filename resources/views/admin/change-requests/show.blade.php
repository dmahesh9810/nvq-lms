@extends('layouts.app')

@section('title', 'Review Change Request')
@section('page-title', 'Review Change Request')

@section('content')

@php
    $statusColors = ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger'];
    $actionColors = ['update' => 'primary', 'delete' => 'danger'];
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-{{ $statusColors[$changeRequest->status] ?? 'secondary' }} fs-6 me-2">
            {{ ucfirst($changeRequest->status) }}
        </span>
        <span class="text-muted small">Submitted {{ $changeRequest->created_at->diffForHumans() }} by <strong>{{ $changeRequest->requester->name }}</strong></span>
    </div>
    <a href="{{ route('admin.change-requests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to All Requests
    </a>
</div>

<div class="row g-4">
    {{-- Left: Request Summary --}}
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2 text-primary"></i>Request Summary</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th class="text-muted w-40">Type</th>
                        <td><span class="badge bg-secondary">{{ $changeRequest->typeLabel() }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Action</th>
                        <td><span class="badge bg-{{ $actionColors[$changeRequest->action] ?? 'secondary' }} bg-opacity-75">{{ $changeRequest->actionLabel() }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Target</th>
                        <td class="fw-semibold">{{ $changeRequest->target_title }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Target ID</th>
                        <td class="text-muted small">#{{ $changeRequest->target_id }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Instructor</th>
                        <td>{{ $changeRequest->requester->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Submitted</th>
                        <td class="small text-muted">{{ $changeRequest->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                    @if($changeRequest->reviewer)
                    <tr>
                        <th class="text-muted">Reviewed By</th>
                        <td>{{ $changeRequest->reviewer->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Reviewed At</th>
                        <td class="small text-muted">{{ $changeRequest->reviewed_at?->format('d M Y, H:i') }}</td>
                    </tr>
                    @endif
                    @if($changeRequest->admin_note)
                    <tr>
                        <th class="text-muted">Admin Note</th>
                        <td class="text-danger small">{{ $changeRequest->admin_note }}</td>
                    </tr>
                    @endif
                </table>

                @if(! $liveTarget)
                <div class="alert alert-warning mt-3 py-2 small">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    The target resource no longer exists in the database.
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Payload Diff --}}
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header py-3 px-4">
                <h6 class="mb-0 fw-semibold">
                    @if($changeRequest->action === 'delete')
                        <i class="bi bi-trash me-2 text-danger"></i>Deletion Request — Current Values
                    @else
                        <i class="bi bi-pencil me-2 text-primary"></i>Proposed Changes vs Current Values
                    @endif
                </h6>
            </div>
            <div class="card-body">
                @if($changeRequest->action === 'delete')
                    @if($liveTarget)
                    <div class="alert alert-danger py-2 small mb-3">
                        <strong>Warning:</strong> Approving this will permanently delete <strong>{{ $changeRequest->target_title }}</strong>
                        and all its child records.
                    </div>
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr><th>Field</th><th>Current Value</th></tr>
                        </thead>
                        <tbody>
                            @foreach(['title', 'description', 'is_active', 'status'] as $field)
                                @if(isset($liveTarget->$field))
                                <tr>
                                    <td class="text-muted small fw-semibold">{{ ucfirst($field) }}</td>
                                    <td class="small">{{ is_bool($liveTarget->$field) ? ($liveTarget->$field ? 'Yes' : 'No') : ($liveTarget->$field ?? '—') }}</td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="text-muted">Target no longer exists — nothing to delete.</p>
                    @endif

                @elseif($changeRequest->action === 'update')
                    @if($changeRequest->payload)
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Field</th>
                                <th class="text-danger">Current Value</th>
                                <th class="text-success">Proposed Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($changeRequest->payload as $field => $newValue)
                            @php $currentValue = $liveTarget?->$field ?? '—'; @endphp
                            <tr>
                                <td class="text-muted small fw-semibold">{{ ucfirst(str_replace('_', ' ', $field)) }}</td>
                                <td class="small text-danger">
                                    {{ is_bool($currentValue) ? ($currentValue ? 'Yes' : 'No') : (is_string($currentValue) ? \Illuminate\Support\Str::limit($currentValue, 60) : '—') }}
                                </td>
                                <td class="small text-success fw-semibold">
                                    {{ is_bool($newValue) ? ($newValue ? 'Yes' : 'No') : \Illuminate\Support\Str::limit((string)$newValue, 60) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                        <p class="text-muted small">No payload fields provided.</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Approve / Reject Actions --}}
@if($changeRequest->isPending())
<div class="row g-4 mt-2">
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="fw-semibold text-success mb-3"><i class="bi bi-check-circle me-2"></i>Approve Request</h6>
                @if(! $liveTarget)
                    <div class="alert alert-warning small py-2">Cannot approve — target resource not found.</div>
                @else
                <p class="small text-muted mb-3">
                    Approving will immediately apply the
                    @if($changeRequest->action === 'delete') <strong class="text-danger">deletion</strong>
                    @else <strong class="text-primary">update</strong> @endif
                    to <strong>{{ $changeRequest->target_title }}</strong>.
                </p>
                <form action="{{ route('admin.change-requests.approve', $changeRequest) }}" method="POST"
                      onsubmit="return confirm('Approve and apply this change request?')">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-2"></i>Approve & Apply
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-body">
                <h6 class="fw-semibold text-danger mb-3"><i class="bi bi-x-circle me-2"></i>Reject Request</h6>
                <form action="{{ route('admin.change-requests.reject', $changeRequest) }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Rejection Reason (optional)</label>
                        <textarea name="admin_note" class="form-control form-control-sm" rows="3"
                                  placeholder="Explain why this request is being rejected…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-100"
                            onclick="return confirm('Reject this change request?')">
                        <i class="bi bi-x-circle me-2"></i>Reject Request
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-secondary mt-4 py-2">
    <i class="bi bi-lock me-2"></i>
    This request has already been <strong>{{ $changeRequest->status }}</strong>
    @if($changeRequest->reviewer) by {{ $changeRequest->reviewer->name }} @endif
    @if($changeRequest->reviewed_at) on {{ $changeRequest->reviewed_at->format('d M Y') }} @endif.
</div>
@endif

@endsection
