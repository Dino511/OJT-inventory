@extends('layouts.app')

@section('title', 'Unit Conversions')

@section('content')
    <!-- Header & Top Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('base-units.index') }}" class="text-decoration-none">Base Units</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Conversions</li>
                </ol>
            </nav>
            <h3 class="fw-bold mb-1">Unit Conversions</h3>
            <p class="text-secondary small mb-0">Define how base units relate to each other, e.g. 1 Box = 12 Piece.</p>
        </div>
        <a href="{{ route('unit-conversions.create') }}" class="btn btn-github btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> New Conversion
        </a>
    </div>

    <!-- Main CRUD Card Container -->
    <div class="card shadow-sm">
        <div class="card-header p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary small">ALL CONVERSIONS</span>
            <span class="badge bg-light text-dark border font-monospace">{{ $conversions->count() }} Total</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col">CONVERSION</th>
                        <th scope="col" class="text-end" style="width: 140px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($conversions as $conversion)
                        <tr>
                            <td>
                                <span class="fw-semibold text-dark">1 {{ $conversion->fromUnit->name ?? 'N/A' }}</span>
                                <span class="text-secondary mx-1">=</span>
                                <span class="fw-semibold text-dark">{{ (float) $conversion->factor }} {{ $conversion->toUnit->name ?? 'N/A' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('unit-conversions.edit', $conversion->id) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('unit-conversions.destroy', $conversion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this conversion?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-left-right fs-2 d-block mb-2 text-secondary"></i>
                                No conversions defined yet. Click <strong>New Conversion</strong> to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
