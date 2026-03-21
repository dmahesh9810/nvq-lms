@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
<div class="row g-4">

    {{-- ── Profile Information ──────────────────────────────────── --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-person-circle text-primary fs-5"></i>
                <h5 class="mb-0 fw-semibold">Profile Information</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    {{-- ── Update Password ──────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header py-3 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock text-warning fs-5"></i>
                <h5 class="mb-0 fw-semibold">Update Password</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    {{-- ── Delete Account ───────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card shadow-sm border-danger">
            <div class="card-header py-3 d-flex align-items-center gap-2 border-danger">
                <i class="bi bi-trash3 text-danger fs-5"></i>
                <h5 class="mb-0 fw-semibold text-danger">Delete Account</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

</div>
@endsection
