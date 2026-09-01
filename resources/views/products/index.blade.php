@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <!-- Header & Top Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Products</h3>
            <p class="text-secondary small mb-0">Manage product inventory and catalog details.</p>
        </div>
        <a href="{{ route('products.create') }}" class="btn btn-github btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i> New Product
        </a>
    </div>

    <!-- Main CRUD Card Container -->
    <div class="card shadow-sm">
        <!-- Filter & Info Header -->
        <div class="card-header p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="tableSearch" class="form-control border-start-0" placeholder="Filter products...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <span class="badge bg-light text-dark border">
                        <i class="bi bi-box-seam me-1"></i> {{ $products->count() }} Total
                    </span>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="productsTable">
                <thead>
                    <tr>
                        <th scope="col" style="width: 120px;">SKU</th>
                        @php
                            $sortLink = fn (string $column) => route('products.index', [
                                'sort' => $column,
                                'direction' => ($sort === $column && $direction === 'asc') ? 'desc' : 'asc',
                            ]);
                            $sortIcon = fn (string $column) => $sort !== $column
                                ? 'bi-arrow-down-up text-muted'
                                : ($direction === 'asc' ? 'bi-sort-alpha-down' : 'bi-sort-alpha-up');
                        @endphp
                        <th scope="col">
                            <a href="{{ $sortLink('name') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                NAME <i class="bi {{ $sortIcon('name') }} small"></i>
                            </a>
                        </th>
                        <th scope="col">DESCRIPTION</th>
                        <th scope="col">
                            <a href="{{ $sortLink('company') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                COMPANY <i class="bi {{ $sortIcon('company') }} small"></i>
                            </a>
                        </th>
                        <th scope="col">
                            <a href="{{ $sortLink('category') }}" class="text-decoration-none text-dark d-inline-flex align-items-center gap-1">
                                CATEGORY <i class="bi {{ $sortIcon('category') }} small"></i>
                            </a>
                        </th>
                        <th scope="col">BASE UNIT</th>
                        <th scope="col">REORDER POINT</th>
                        <th scope="col" class="text-end" style="width: 120px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">
                                    {{ $product->code ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $product->name }}</div>
                            </td>
                            <td class="text-secondary small">
                                @if($product->description)
                                    @if(Str::length($product->description) > 50)
                                        <span tabindex="0" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover focus" title="{{ $product->description }}" style="cursor: help; border-bottom: 1px dotted #999;">
                                            {{ Str::limit($product->description, 50) }}
                                        </span>
                                    @else
                                        {{ $product->description }}
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td class="text-secondary small">
                                {{ $product->company->name ?? 'N/A' }}
                            </td>
                            <td>
                                @if($product->category)
                                    <span class="badge bg-secondary-subtle text-secondary border px-2">
                                        {{ $product->category->name }}
                                    </span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                <span class="fw-semibold text-dark">{{ $product->unit_value ?? 1 }}</span>
                                {{ $product->baseUnit->name ?? 'Piece' }}
                                @if($conversion = $conversions->get($product->base_unit_id))
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        = {{ (float) $conversion->factor * ($product->unit_value ?? 1) }} {{ $conversion->toUnit->name ?? '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                {{ number_format($product->reorder_point ?? 0, 2) }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
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
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                                No products found. Click <strong>New Product</strong> to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .tooltip .tooltip-inner {
            max-width: 320px;
            text-align: left;
            white-space: normal;
            word-wrap: break-word;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.getElementById('tableSearch')?.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#productsTable tbody tr');
            rows.forEach(row => {
                let text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    </script>
@endpush