@extends('layouts.app')

@section('title', 'My Change Requests')
@section('page-title', 'My Change Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0 small">Requests you have submitted for admin review.</p>
    </div>
    <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Courses
    </a>
</div>

@php
    $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
    $actionColors = ['update' => 'primary', 'delete' => 'danger'];
@endphp

<div class="card">
    <div class="card-body p-0">
        @if($requests->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:2.5rem;"></i>
                <p class="mt-3 mb-0">You have not submitted any change requests yet.</p>
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Type</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="pe-4">Admin Note</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td class="ps-4">
                            <span class="badge bg-secondary">{{ $req->typeLabel() }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $actionColors[$req->action] ?? 'secondary' }} bg-opacity-75">
                                {{ $req->actionLabel() }}
                            </span>
                        </td>
                        <td class="fw-medium">{{ $req->target_title }}</td>
                        <td class="text-muted small">{{ $req->created_at->diffForHumans() }}</td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$req->status] ?? 'secondary' }} {{ $req->status === 'pending' ? 'text-dark' : '' }}">
                                {{ ucfirst($req->status) }}
                            </span>
                        </td>
                        <td class="pe-4 text-muted small">
                            {{ $req->admin_note ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
