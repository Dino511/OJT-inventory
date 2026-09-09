@extends('layouts.app')

@section('title', 'Inventory List')

@section('content')
    <!-- Page Header & Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Inventory Items</h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inventory</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.history.index') }}" class="btn btn-outline-github btn-sm px-3">
                <i class="bi bi-clock-history me-1"></i> Activity Log
            </a>
            <a href="{{ route('inventory.create') }}" class="btn btn-github btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> Add Inventory
            </a>
        </div>
    </div>

    <!-- Main Card & Data Table -->
    <div class="card shadow-sm">
        <div class="card-header p-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-secondary small">ALL STOCK RECORDS</span>
            <span class="badge bg-light text-dark border font-monospace">{{ $inventories->count() }} Total</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        @php
                            $sortLink = fn (string $column) => route('inventory.index', [
                                'sort' => $column,
                                'direction' => ($sort === $column && $direction === 'asc') ? 'desc' : 'asc',
                            ]);
                            $alphaIcon = fn (string $column) => $sort !== $column
                                ? 'bi-arrow-down-up text-muted'
                                : ($direction === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up');
                            $numIcon = fn (string $column) => $sort !== $column
                                ? 'bi-arrow-down-up text-muted'
                                : ($direction === 'asc' ? 'bi-sort-numeric-down' : 'bi-sort-numeric-up');
                        @endphp
                        <th>
                            <a href="{{ $sortLink('product') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                PRODUCT <i class="bi {{ $alphaIcon('product') }} small"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('location') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                LOCATION <i class="bi {{ $alphaIcon('location') }} small"></i>
                            </a>
                        </th>
                        <th>
                            <a href="{{ $sortLink('quantity') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                QUANTITY <i class="bi {{ $numIcon('quantity') }} small"></i>
                            </a>
                        </th>
                        <th>STATUS</th>
                        <th class="text-end" style="width: 270px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inventories as $item)
                        <tr>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">#{{ $item->id }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $item->product->name ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <span class="text-secondary">
                                    {{ $item->location->name ?? 'N/A' }}
                                    @if($item->location)
                                        <span class="badge bg-light text-secondary border ms-1">{{ $item->location->isWarehouse() ? 'Warehouse' : 'Store' }}</span>
                                    @endif
                                </span>
                            </td>
                            @php [$statusClass, $statusLabel] = $item->stock_status_badge; @endphp
                            <td>
                                <span class="badge {{ $statusClass }} border px-2 py-1">
                                    {{ $item->quantity ?? $item->stock ?? 0 }} units
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $statusClass }} border px-2 py-1">{{ $statusLabel }}</span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('inventory.history', $item->id) }}" class="btn btn-outline-github" title="History">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(($item->quantity ?? 0) > 0)
                                        <a href="{{ route('inventory.transfer', $item->id) }}" class="btn btn-outline-github" title="Transfer stock to another location">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin())
                                        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this stock record? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                No inventory items found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection