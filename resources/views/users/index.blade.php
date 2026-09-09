@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <!-- Header & Top Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Users</h3>
            <p class="text-secondary small mb-0">Manage the accounts that can sign in to this system.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-github btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> New User
        </a>
    </div>

    <!-- Main CRUD Card Container -->
    <div class="card shadow-sm">
        <div class="card-header p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary small">ALL USERS</span>
            <span class="badge bg-light text-dark border font-monospace">{{ $users->count() }} Total</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" style="width: 80px;">ID</th>
                        <th scope="col">NAME</th>
                        <th scope="col">EMAIL</th>
                        <th scope="col">ROLE</th>
                        <th scope="col">STATUS</th>
                        <th scope="col">COMPANIES</th>
                        <th scope="col">LOCATION</th>
                        <th scope="col" class="text-end" style="width: 220px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">#{{ $user->id }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span class="badge bg-secondary-subtle text-secondary border ms-1">You</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-secondary small">{{ $user->email }}</td>
                            <td>
                                @php
                                    $roleBadge = match($user->role) {
                                        'super_admin' => ['bg-danger-subtle text-danger', 'Super Admin'],
                                        'business_admin' => ['bg-primary-subtle text-primary', 'Business Admin'],
                                        default => ['bg-secondary-subtle text-secondary', 'Moderator'],
                                    };
                                @endphp
                                <span class="badge {{ $roleBadge[0] }} border">{{ $roleBadge[1] }}</span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success-subtle text-success border">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">Inactive</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                @forelse($user->companies as $company)
                                    <span class="badge {{ $company->company_id == $user->company_id ? 'bg-success-subtle text-success' : 'bg-light text-secondary' }} border me-1">
                                        {{ $company->name }}
                                    </span>
                                @empty
                                    N/A
                                @endforelse
                            </td>
                            <td class="text-secondary small">
                                @if($user->isModerator())
                                    @if($user->location)
                                        <span class="badge bg-success-subtle text-success border">{{ $user->location->name }}</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning border">Not assigned</span>
                                    @endif
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('users.toggle-status', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $user->is_active ? 'Deactivate' : 'Activate' }} this user?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $user->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <i class="bi {{ $user->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-outline-github" title="View Profile"
                                            data-bs-toggle="modal" data-bs-target="#userProfileModal"
                                            data-user-id="{{ $user->id }}"
                                            data-user-name="{{ $user->name }}"
                                            data-user-email="{{ $user->email }}"
                                            data-user-alt-email="{{ $user->alternative_email ?? '' }}"
                                            data-user-role="{{ $roleBadge[1] }}"
                                            data-user-role-badge="{{ $roleBadge[0] }}"
                                            data-user-active="{{ $user->is_active ? '1' : '0' }}"
                                            data-user-avatar="{{ $user->avatar_url ?? '' }}"
                                            data-user-companies="{{ $user->companies->pluck('name')->implode(', ') ?: 'N/A' }}"
                                            data-user-active-company="{{ optional($user->company)->name ?? '' }}"
                                            data-user-location="{{ $user->isModerator() ? (optional($user->location)->name ?? 'Not assigned') : '—' }}"
                                            data-user-created="{{ $user->created_at?->format('M d, Y') }}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2 text-secondary"></i>
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- View Profile Modal (shared, populated on open) -->
    <div class="modal fade" id="userProfileModal" tabindex="-1" aria-labelledby="userProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="userProfileModalLabel">User Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="profileAvatarImg" src="" alt="" class="rounded-circle border d-none" style="width: 72px; height: 72px; object-fit: cover;">
                        <div id="profileAvatarPlaceholder" class="rounded-circle bg-secondary-subtle border d-flex align-items-center justify-content-center mx-auto" style="width: 72px; height: 72px;">
                            <i class="bi bi-person-fill text-secondary" style="font-size: 1.8rem;"></i>
                        </div>
                        <h5 class="fw-bold mt-2 mb-0" id="profileName"></h5>
                        <span class="badge border font-monospace" id="profileId"></span>
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-5 text-secondary">Email</dt>
                        <dd class="col-7" id="profileEmail"></dd>

                        <dt class="col-5 text-secondary">Alternative Email</dt>
                        <dd class="col-7" id="profileAltEmail"></dd>

                        <dt class="col-5 text-secondary">Role</dt>
                        <dd class="col-7"><span class="badge border" id="profileRole"></span></dd>

                        <dt class="col-5 text-secondary">Status</dt>
                        <dd class="col-7" id="profileStatus"></dd>

                        <dt class="col-5 text-secondary">Companies</dt>
                        <dd class="col-7" id="profileCompanies"></dd>

                        <dt class="col-5 text-secondary">Active Company</dt>
                        <dd class="col-7" id="profileActiveCompany"></dd>

                        <dt class="col-5 text-secondary">POS Location</dt>
                        <dd class="col-7" id="profileLocation"></dd>

                        <dt class="col-5 text-secondary">Joined</dt>
                        <dd class="col-7" id="profileCreated"></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const userProfileModal = document.getElementById('userProfileModal');
        userProfileModal.addEventListener('show.bs.modal', function (event) {
            const btn = event.relatedTarget;
            if (!btn) return;

            const avatarUrl = btn.dataset.userAvatar;
            const avatarImg = document.getElementById('profileAvatarImg');
            const avatarPlaceholder = document.getElementById('profileAvatarPlaceholder');
            if (avatarUrl) {
                avatarImg.src = avatarUrl;
                avatarImg.classList.remove('d-none');
                avatarPlaceholder.classList.add('d-none');
            } else {
                avatarImg.classList.add('d-none');
                avatarPlaceholder.classList.remove('d-none');
            }

            document.getElementById('profileName').textContent = btn.dataset.userName;
            document.getElementById('profileId').textContent = '#' + btn.dataset.userId;
            document.getElementById('profileEmail').textContent = btn.dataset.userEmail;
            document.getElementById('profileAltEmail').textContent = btn.dataset.userAltEmail || '—';
            document.getElementById('profileCompanies').textContent = btn.dataset.userCompanies;
            document.getElementById('profileActiveCompany').textContent = btn.dataset.userActiveCompany || '—';
            document.getElementById('profileLocation').textContent = btn.dataset.userLocation;
            document.getElementById('profileCreated').textContent = btn.dataset.userCreated;

            const roleBadge = document.getElementById('profileRole');
            roleBadge.textContent = btn.dataset.userRole;
            roleBadge.className = 'badge border ' + btn.dataset.userRoleBadge;

            const statusEl = document.getElementById('profileStatus');
            if (btn.dataset.userActive === '1') {
                statusEl.innerHTML = '<span class="badge bg-success-subtle text-success border">Active</span>';
            } else {
                statusEl.innerHTML = '<span class="badge bg-danger-subtle text-danger border">Inactive</span>';
            }
        });
    </script>
@endpush
