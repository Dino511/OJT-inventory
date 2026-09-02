@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Edit User</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">USER DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
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
                            <label class="form-label small fw-semibold">Companies</label>
                            @php $selectedCompanyIds = old('company_ids', $user->companies->pluck('company_id')->all()); @endphp
                            <div class="border rounded p-2" style="max-height: 160px; overflow-y: auto;">
                                @forelse($companies as $company)
                                    <div class="form-check">
                                        <input type="checkbox" name="company_ids[]" value="{{ $company->company_id }}" id="company_{{ $company->company_id }}"
                                               class="form-check-input" {{ in_array($company->company_id, $selectedCompanyIds) ? 'checked' : '' }}>
                                        <label for="company_{{ $company->company_id }}" class="form-check-label small">
                                            {{ $company->name }}
                                            @if($company->company_id == $user->company_id)
                                                <span class="badge bg-success-subtle text-success border ms-1">Active</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-muted small">No companies exist yet.</span>
                                @endforelse
                            </div>
                            <div class="form-text small">Check every company this user should be able to sell for. The badge marks their currently active company in the POS app.</div>
                            @error('company_ids')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-3">
                        <p class="text-muted small mb-2">Leave the password fields blank to keep the current password.</p>

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
                                <i class="bi bi-check-lg me-1"></i> Update User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
