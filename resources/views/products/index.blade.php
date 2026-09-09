@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <!-- Header & Top Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-bold mb-1">Products</h3>
            <p class="text-secondary small mb-0">Manage product inventory and catalog details.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('products.history.index') }}" class="btn btn-outline-github btn-sm px-3">
                <i class="bi bi-clock-history me-1"></i> Activity Log
            </a>
            <a href="{{ route('products.create') }}" class="btn btn-github btn-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> New Product
            </a>
        </div>
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
        <div class="table-responsive" x-data="productsSorter()">
            <table class="table table-hover align-middle mb-0" id="productsTable" x-ref="table">
                <thead>
                    <tr>
                        <th scope="col" style="width: 60px;"></th>
                        <th scope="col" style="width: 120px;" class="sortable-th" @click="sort('sku', 'text')">
                            SKU <i :class="icon('sku')"></i>
                        </th>
                        <th scope="col" class="sortable-th" @click="sort('name', 'text')">
                            NAME <i :class="icon('name')"></i>
                        </th>
                        <th scope="col">DESCRIPTION</th>
                        <th scope="col" class="sortable-th" @click="sort('company', 'text')">
                            COMPANY <i :class="icon('company')"></i>
                        </th>
                        <th scope="col" class="sortable-th" @click="sort('supplier', 'text')">
                            SUPPLIER <i :class="icon('supplier')"></i>
                        </th>
                        <th scope="col" class="sortable-th" @click="sort('category', 'text')">
                            CATEGORY <i :class="icon('category')"></i>
                        </th>
                        <th scope="col" class="sortable-th" @click="sort('baseUnit', 'text')">
                            BASE UNIT <i :class="icon('baseUnit')"></i>
                        </th>
                        <th scope="col" class="sortable-th" @click="sort('reorder', 'number')">
                            REORDER POINT <i :class="icon('reorder')"></i>
                        </th>
                        <th scope="col" class="sortable-th" @click="sort('status', 'text')">
                            STATUS <i :class="icon('status')"></i>
                        </th>
                        <th scope="col" class="text-end" style="width: 200px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody x-ref="tbody">
                    @forelse($products as $product)
                        <tr
                            data-sku="{{ $product->code ?? '' }}"
                            data-name="{{ $product->name }}"
                            data-company="{{ $product->company->name ?? '' }}"
                            data-supplier="{{ $product->suppliers->first()->name ?? '' }}"
                            data-category="{{ $product->category->name ?? '' }}"
                            data-base-unit="{{ $product->baseUnit->name ?? 'Piece' }}"
                            data-reorder="{{ $product->reorder_point ?? 0 }}"
                            data-status="{{ $product->is_active ? 'Active' : 'Inactive' }}"
                        >
                            <td>
                                <a href="{{ route('products.show', $product->id) }}">
                                    @if($product->photo)
                                        <img src="{{ $product->photo_url }}" alt="{{ $product->name }}" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center rounded border bg-light text-muted" style="width: 40px; height: 40px;">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    @endif
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace">
                                    {{ $product->code ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('products.show', $product->id) }}" class="fw-semibold text-dark text-decoration-none">{{ $product->name }}</a>
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
                            <td class="text-secondary small">
                                @if($product->suppliers->isNotEmpty())
                                    <span title="{{ $product->suppliers->pluck('name')->implode(', ') }}">
                                        {{ $product->suppliers->first()->name }}
                                        @if($product->suppliers->count() > 1)
                                            <span class="badge bg-light text-secondary border ms-1">+{{ $product->suppliers->count() - 1 }}</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($product->category)
                                    <span class="badge bg-secondary-subtle text-secondary border px-2">
                                        {{ $product->category->name }}
                                    </span>
                                    @if($product->category->parent)
                                        <div class="text-muted" style="font-size: 0.75rem;">in {{ $product->category->parent->name }}</div>
                                    @endif
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                <span class="fw-semibold text-dark">{{ $product->unit_value ?? 1 }}</span>
                                {{ $product->baseUnit->name ?? 'Piece' }}
                                @if($conversion = $product->unitConversion)
                                    <div class="text-muted" style="font-size: 0.75rem;">
                                        = {{ (float) $conversion->factor * ($product->unit_value ?? 1) }} {{ $conversion->toUnit->name ?? '' }}
                                    </div>
                                @endif
                            </td>
                            <td class="text-secondary small">
                                {{ number_format($product->reorder_point ?? 0, 2) }}
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge bg-success-subtle text-success border">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <form action="{{ route('products.toggle-status', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ $product->is_active ? 'Deactivate' : 'Activate' }} this product?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn {{ $product->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="bi {{ $product->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    <a href="{{ route('products.history', $product->id) }}" class="btn btn-outline-github" title="History">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-github" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if(auth()->user()->isSuperAdmin())
                                        @php
                                            $hasDependencies = $product->pending_purchase_order_items_count > 0
                                                || $product->pending_stock_transfer_items_count > 0
                                                || $product->stocked_inventory_count > 0;
                                        @endphp
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline product-delete-form" data-has-dependencies="{{ $hasDependencies ? '1' : '0' }}">
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
                            <td colspan="11" class="text-center py-5 text-muted">
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
        .sortable-th {
            cursor: pointer;
            user-select: none;
            white-space: nowrap;
        }
        .sortable-th:hover {
            color: var(--bs-body-color);
        }
        .sortable-th .bi-sort-up,
        .sortable-th .bi-sort-down {
            color: var(--bs-primary, #1f883d);
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Alpine.js component powering the Products table's click-to-sort
        // headers. The table isn't paginated (every product is already in
        // the DOM), so sorting is done client-side against each row's
        // data-* attributes — no reload, no server round-trip. Row markup
        // itself is untouched Blade output; this only reorders <tr> nodes.
        function productsSorter() {
            return {
                sortKey: null,
                sortDir: 'asc',

                icon(key) {
                    if (this.sortKey !== key) return 'bi bi-arrow-down-up text-muted small';
                    return this.sortDir === 'asc' ? 'bi bi-sort-up small' : 'bi bi-sort-down small';
                },

                sort(key, type) {
                    this.sortDir = (this.sortKey === key && this.sortDir === 'asc') ? 'desc' : 'asc';
                    this.sortKey = key;

                    const tbody = this.$refs.tbody;
                    const totalColumns = this.$refs.table.querySelectorAll('thead th').length;
                    const rows = Array.from(tbody.querySelectorAll('tr')).filter((row) => row.cells.length === totalColumns);

                    rows.sort((rowA, rowB) => {
                        let valueA = rowA.dataset[key] ?? '';
                        let valueB = rowB.dataset[key] ?? '';

                        if (type === 'number') {
                            valueA = parseFloat(valueA) || 0;
                            valueB = parseFloat(valueB) || 0;
                            return this.sortDir === 'asc' ? valueA - valueB : valueB - valueA;
                        }

                        valueA = valueA.toLowerCase();
                        valueB = valueB.toLowerCase();
                        if (valueA < valueB) return this.sortDir === 'asc' ? -1 : 1;
                        if (valueA > valueB) return this.sortDir === 'asc' ? 1 : -1;
                        return 0;
                    });

                    rows.forEach((row) => tbody.appendChild(row));
                },
            };
        }

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

        // Delete confirmation: warn more strongly when the product is tied
        // to pending purchase orders/transfers or still has stock on hand.
        document.querySelectorAll('.product-delete-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const hasDependencies = form.dataset.hasDependencies === '1';
                const message = hasDependencies
                    ? 'Warning: This product is linked to active purchase orders or inventory records. Are you sure you want to delete this product?'
                    : 'Are you sure you want to delete this product?';

                if (confirm(message)) {
                    form.submit();
                }
            });
        });
    </script>
@endpush