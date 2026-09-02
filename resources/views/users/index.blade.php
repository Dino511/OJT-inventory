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
                        <th scope="col">COMPANIES</th>
                        <th scope="col" class="text-end" style="width: 140px;">ACTIONS</th>
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
                            <td class="text-secondary small">
                                @forelse($user->companies as $company)
                                    <span class="badge {{ $company->company_id == $user->company_id ? 'bg-success-subtle text-success' : 'bg-light text-secondary' }} border me-1">
                                        {{ $company->name }}
                                    </span>
                                @empty
                                    N/A
                                @endforelse
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
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
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-people fs-2 d-block mb-2 text-secondary"></i>
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
