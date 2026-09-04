@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <!-- Header -->
    <div class="mb-4">
        <h3 class="fw-bold mb-0">My Profile</h3>
        <p class="text-secondary small mb-0">Update your account details and password.</p>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">ACCOUNT DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Full Name</label>
                            <input type="text" id="name" name="name"
                                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   value="{{ old('name', $user->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">Email address</label>
                            <input type="email" id="email" name="email"
                                   class="form-control form-control-sm @error('email') is-invalid @enderror"
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="alternative_email" class="form-label small fw-semibold">Alternative Email <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="email" id="alternative_email" name="alternative_email"
                                   class="form-control form-control-sm @error('alternative_email') is-invalid @enderror"
                                   value="{{ old('alternative_email', $user->alternative_email) }}">
                            <div class="form-text small">Password reset codes are sent here instead of your primary email, if set.</div>
                            @error('alternative_email')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Role</label>
                            @php
                                $roleLabel = match($user->role) {
                                    'super_admin' => 'Super Admin — full access to both systems',
                                    'business_admin' => 'Business Admin — everything except Users management',
                                    default => 'Moderator — POS only',
                                };
                            @endphp
                            <input type="text" class="form-control form-control-sm" value="{{ $roleLabel }}" disabled>
                            <div class="form-text small">Only a Super Admin can change your role, from the Users page.</div>
                        </div>

                        <hr class="my-3">
                        <p class="text-muted small mb-2">Leave the password fields blank to keep your current password.</p>

                        <div class="mb-3">
                            <label for="current_password" class="form-label small fw-semibold">Current Password</label>
                            <input type="password" id="current_password" name="current_password"
                                   class="form-control form-control-sm @error('current_password') is-invalid @enderror"
                                   placeholder="Required only when setting a new password">
                            @error('current_password')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label small fw-semibold">New Password</label>
                            <input type="password" id="password" name="password"
                                   class="form-control form-control-sm @error('password') is-invalid @enderror">
                            @error('password')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label small fw-semibold">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                   class="form-control form-control-sm">
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
