@extends('layouts.app')

@section('title', 'Assessor Dashboard')
@section('page-title', 'Assessor Dashboard')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12 col-sm-6">
        <div class="stat-card" style="background:#e8f0fe;">
            <div class="stat-icon bg-white text-soft-blue"><i class="bi bi-book-half"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_courses'] }}</div>
            <div class="stat-label text-muted">Published Courses</div>
        </div>
    </div>
    <div class="col-12 col-sm-6">
        <div class="stat-card" style="background:#e6f4ea;">
            <div class="stat-icon bg-white text-soft-green"><i class="bi bi-people-fill"></i></div>
            <div class="stat-value text-dark">{{ $stats['total_students'] }}</div>
            <div class="stat-label text-muted">Total Students</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-clipboard2-check" style="font-size:3rem; color:#1a73e8;"></i>
        <h5 class="mt-3">Assessor Tools</h5>
        <p class="text-muted">Assessment management features will be available in Phase 3.</p>
    </div>
</div>
@endsection
