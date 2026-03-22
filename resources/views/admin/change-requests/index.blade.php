@extends('layouts.app')

@section('title', 'Change Requests')
@section('page-title', 'Change Requests')

@section('content')

@php
    $statusColors = ['pending' => 'warning text-dark', 'approved' => 'success', 'rejected' => 'danger'];
    $actionColors = ['update' => 'primary', 'delete' => 'danger'];
    $typeIcons    = ['course' => 'bi-book', 'module' => 'bi-collection', 'unit' => 'bi-folder2-open', 'lesson' => 'bi-file-text'];
@endphp

{{-- Pending Requests --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between py-3 px-4">
        <h6 class="mb-0 fw-semibold">
            <i class="bi bi-hourglass-split text-warning me-2"></i>
            Pending Change Requests
            @if($pendingRequests->total() > 0)
                <span class="badge bg-warning text-dark ms-1">{{ $pendingRequests->total() }}</span>
            @endif
        </h6>
    </div>
    <div class="card-body p-0">
        @if($pendingRequests->isEmpty())
            <div class="p-4 text-center text-muted">No pending change requests.</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Type</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Instructor</th>
                        <th>Submitted</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingRequests as $req)
                    <tr>
                        <td class="ps-4">
                            <i class="bi {{ $typeIcons[$req->type] ?? 'bi-question' }} me-1 text-muted"></i>
                            <span class="badge bg-secondary">{{ $req->typeLabel() }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $actionColors[$req->action] ?? 'secondary' }} bg-opacity-75">
                                {{ $req->actionLabel() }}
                            </span>
                        </td>
                        <td class="fw-medium">{{ $req->target_title }}</td>
                        <td class="text-muted small">{{ $req->requester->name }}</td>
                        <td class="text-muted small">{{ $req->created_at->diffForHumans() }}</td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.change-requests.show', $req) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Review
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $pendingRequests->links() }}</div>
        @endif
    </div>
</div>

{{-- All Requests (history) --}}
<div class="card">
    <div class="card-header py-3 px-4">
        <h6 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-muted"></i>All Change Requests (History)</h6>
    </div>
    <div class="card-body p-0">
        @if($allRequests->isEmpty())
            <div class="p-4 text-center text-muted">No change requests yet.</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sm">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Type</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Instructor</th>
                        <th>Status</th>
                        <th>Reviewed By</th>
                        <th class="pe-4 text-end">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allRequests as $req)
                    <tr class="{{ $req->status === 'rejected' ? 'table-danger bg-opacity-25' : ($req->status === 'approved' ? 'table-success bg-opacity-10' : '') }}">
                        <td class="ps-4">
                            <span class="badge bg-secondary">{{ $req->typeLabel() }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $actionColors[$req->action] ?? 'secondary' }} bg-opacity-75">
                                {{ $req->actionLabel() }}
                            </span>
                        </td>
                        <td class="small">{{ $req->target_title }}</td>
                        <td class="small text-muted">{{ $req->requester->name }}</td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$req->status] ?? 'secondary' }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $req->reviewer?->name ?? '—' }}</td>
                        <td class="pe-4 text-end">
                            <a href="{{ route('admin.change-requests.show', $req) }}" class="btn btn-xs btn-outline-secondary" style="font-size:0.75rem; padding:2px 8px;">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $allRequests->appends(['page' => request('page')])->links() }}</div>
        @endif
    </div>
</div>

@endsection
