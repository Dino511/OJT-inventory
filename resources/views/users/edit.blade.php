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
                    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4 text-center">
                            @if($user->avatar)
                                <img id="currentAvatar" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border" style="width: 88px; height: 88px; object-fit: cover;">
                            @else
                                <div id="currentAvatar" class="rounded-circle bg-secondary-subtle border d-flex align-items-center justify-content-center mx-auto" style="width: 88px; height: 88px;">
                                    <i class="bi bi-person-fill text-secondary" style="font-size: 2.2rem;"></i>
                                </div>
                            @endif
                            <img id="avatarPreview" src="" alt="Preview" class="rounded-circle border d-none mx-auto mt-2" style="width: 88px; height: 88px; object-fit: cover;">

                            <div class="mt-2">
                                <label for="avatar" class="btn btn-outline-github btn-sm">
                                    <i class="bi bi-camera me-1"></i> Change Photo
                                </label>
                                <input type="file" id="avatar" name="avatar" accept="image/*" class="d-none @error('avatar') is-invalid @enderror">

                                @if($user->avatar)
                                    <div class="form-check d-inline-block ms-2">
                                        <input type="checkbox" class="form-check-input" id="remove_avatar" name="remove_avatar" value="1">
                                        <label class="form-check-label small text-danger" for="remove_avatar">Remove photo</label>
                                    </div>
                                @endif
                            </div>
                            <div class="form-text small">JPG, PNG, or WebP, up to 4MB.</div>
                            @error('avatar')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

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
                            <div class="form-text small">Password reset codes are sent here instead of their primary email, if set.</div>
                            @error('alternative_email')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label small fw-semibold">Role</label>
                            <select id="role" name="role" class="form-select form-select-sm @error('role') is-invalid @enderror" required {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="business_admin" {{ old('role', $user->role) === 'business_admin' ? 'selected' : '' }}>Business Admin</option>
                                <option value="moderator" {{ old('role', $user->role) === 'moderator' ? 'selected' : '' }}>Moderator</option>
                            </select>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <div class="form-text small">You cannot change your own role while signed in.</div>
                            @else
                                <div class="form-text small">Super Admin: full access to both systems. Business Admin: everything except Users management. Moderator: POS only.</div>
                            @endif
                            @error('role')
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

                        <div class="mb-3" id="locationField">
                            <label for="location_id" class="form-label small fw-semibold">POS Location</label>
                            <select id="location_id" name="location_id" class="form-select form-select-sm @error('location_id') is-invalid @enderror">
                                <option value="">No location assigned yet</option>
                                @foreach($companies as $company)
                                    @php $companyLocations = $locations->where('company_id', $company->company_id); @endphp
                                    @if($companyLocations->isNotEmpty())
                                        <optgroup label="{{ $company->name }}">
                                            @foreach($companyLocations as $location)
                                                <option value="{{ $location->id }}" data-company="{{ $location->company_id }}" {{ old('location_id', $user->location_id) == $location->id ? 'selected' : '' }}>
                                                    {{ $location->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-text small">Locks a Moderator to selling from this location in POS. Must be one of the checked companies above.</div>
                            @error('location_id')
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

@push('scripts')
    <script>
        const roleSelect = document.getElementById('role');
        const locationField = document.getElementById('locationField');

        function syncLocationField() {
            locationField.classList.toggle('d-none', roleSelect.value !== 'moderator');
        }

        roleSelect.addEventListener('change', syncLocationField);
        syncLocationField();

        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatarPreview');
        const currentAvatar = document.getElementById('currentAvatar');
        const removeAvatarCheckbox = document.getElementById('remove_avatar');

        avatarInput?.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                avatarPreview.classList.add('d-none');
                avatarPreview.src = '';
                return;
            }
            avatarPreview.src = URL.createObjectURL(file);
            avatarPreview.classList.remove('d-none');
            currentAvatar?.classList.add('d-none');
            // Choosing a new photo supersedes "remove photo".
            if (removeAvatarCheckbox) removeAvatarCheckbox.checked = false;
        });
    </script>
@endpush
