@extends('layouts.app')

@section('title', 'New Purchase Order')

@push('styles')
    <style>
        .po-items-table th,
        .po-items-table td {
            padding-top: 0.65rem;
            padding-bottom: 0.65rem;
        }

        .po-items-table .remove-row {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('purchase-orders.index') }}" class="text-decoration-none">Purchase Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">New</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">New Purchase Order</h3>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white p-3 border-bottom">
            <span class="fw-semibold text-secondary small text-uppercase">Order Details</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('purchase-orders.store') }}" method="POST" id="poForm">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="supplier_id" class="form-label small fw-semibold">Supplier</label>
                        <select id="supplier_id" name="supplier_id" class="form-select form-select-sm @error('supplier_id') is-invalid @enderror" required>
                            <option value="">Select a supplier...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @if($suppliers->isEmpty())
                            <div class="form-text small">No suppliers yet — <a href="{{ route('suppliers.create') }}">add one first</a>.</div>
                        @else
                            <div class="form-text small">Only products linked to this supplier will be selectable below.</div>
                        @endif
                        @error('supplier_id')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="company_id" class="form-label small fw-semibold">Company</label>
                        <select id="company_id" name="company_id" class="form-select form-select-sm @error('company_id') is-invalid @enderror" required>
                            <option value="">Select a company...</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->company_id }}" {{ old('company_id') == $company->company_id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="location_id" class="form-label small fw-semibold">Deliver To (Warehouse)</label>
                        <select id="location_id" name="location_id" class="form-select form-select-sm @error('location_id') is-invalid @enderror" required disabled>
                            <option value="">Select a company first...</option>
                        </select>
                        <div class="form-text small">Only warehouse locations can receive purchase orders.</div>
                        @error('location_id')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="order_date" class="form-label small fw-semibold">Order Date</label>
                        <input type="date" id="order_date" name="order_date"
                               class="form-control form-control-sm @error('order_date') is-invalid @enderror"
                               value="{{ old('order_date', now()->toDateString()) }}" required>
                        @error('order_date')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="expected_delivery_date" class="form-label small fw-semibold">Expected Delivery</label>
                        <input type="date" id="expected_delivery_date" name="expected_delivery_date"
                               class="form-control form-control-sm @error('expected_delivery_date') is-invalid @enderror"
                               value="{{ old('expected_delivery_date') }}" required>
                        @error('expected_delivery_date')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="notes" class="form-label small fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea id="notes" name="notes" rows="1" class="form-control form-control-sm @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback d-block small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <label class="form-label small fw-semibold">Products</label>
                <div class="table-responsive border rounded mb-2">
                    <table class="table table-sm align-middle mb-0 po-items-table">
                        <colgroup>
                            <col style="width: 56%;">
                            <col style="width: 18%;">
                            <col style="width: 22%;">
                            <col style="width: 48px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="small text-secondary text-uppercase ps-3">Product</th>
                                <th class="small text-secondary text-uppercase">Quantity</th>
                                <th class="small text-secondary text-uppercase">Unit Cost</th>
                                <th class="pe-3"></th>
                            </tr>
                        </thead>
                        <tbody id="poRows"></tbody>
                    </table>
                </div>

                <button type="button" id="addProductRow" class="btn btn-outline-github btn-sm mt-2 mb-4" disabled>
                    <i class="bi bi-plus-lg me-1"></i> Add Product
                </button>

                <!-- Form Action Buttons -->
                <div class="d-flex align-items-center gap-2 mt-2 pt-3 border-top">
                    <button type="submit" class="btn btn-github btn-sm px-3">
                        <i class="bi bi-cart-plus me-1"></i> Create Purchase Order
                    </button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-outline-github btn-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Every location and product, tagged with the company they belong
        // to — lets picking a Company filter both the "Deliver To" location
        // and every product row's choices without a round-trip.
        const allLocations = @json($locationOptions);
        const allProducts = @json($productOptions);

        // Product/location names come straight from user-entered data and
        // are inserted via innerHTML below — escape them so a name like
        // "<img src=x onerror=...>" renders as text instead of executing.
        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[char]));
        }

        const companySelect = document.getElementById('company_id');
        const supplierSelect = document.getElementById('supplier_id');
        const locationSelect = document.getElementById('location_id');
        const rowsContainer = document.getElementById('poRows');
        const addRowBtn = document.getElementById('addProductRow');

        // A product must belong to the selected Company and be linked to
        // the selected Supplier to show up — a product with no suppliers
        // linked at all simply won't appear once a supplier is chosen.
        function productsForCompany(companyId) {
            const supplierId = supplierSelect.value;

            return allProducts.filter(p => {
                if (String(p.company_id) !== String(companyId)) return false;
                if (supplierId && !p.supplier_ids.map(String).includes(String(supplierId))) return false;
                return true;
            });
        }

        function syncLocationOptions() {
            const companyId = companySelect.value;
            const locations = allLocations.filter(l => String(l.company_id) === String(companyId));

            if (!companyId || locations.length === 0) {
                locationSelect.innerHTML = '<option value="">' + (companyId ? 'No warehouse locations for this company' : 'Select a company first...') + '</option>';
                locationSelect.disabled = true;
            } else {
                locationSelect.disabled = false;
                locationSelect.innerHTML = '<option value="">Select a location...</option>' +
                    locations.map(l => `<option value="${l.id}">${escapeHtml(l.name)}</option>`).join('');
            }
        }

        // When a supplier is selected but has no linked products at all
        // (either globally, or none for the chosen company), say so
        // explicitly instead of leaving the dropdown looking broken with
        // just the placeholder — this is the most common cause of an
        // "empty" product list, since supplier links are opt-in per
        // product (see the Suppliers picker on the product edit form).
        function emptyOptionLabel(companyId) {
            if (!companyId) return 'Select a company first...';
            if (supplierSelect.value) return 'No products linked to this supplier for this company';
            return 'No products for this company';
        }

        function syncRowProductOptions() {
            const companyId = companySelect.value;
            const products = productsForCompany(companyId);
            const placeholder = products.length ? 'Select a product...' : emptyOptionLabel(companyId);

            document.querySelectorAll('.product-select').forEach(sel => {
                const current = sel.value;
                sel.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>` +
                    products.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('');
                if (products.some(p => String(p.id) === current)) {
                    sel.value = current;
                }
            });
        }

        function addProductRow() {
            const companyId = companySelect.value;
            const products = productsForCompany(companyId);
            const placeholder = products.length ? 'Select a product...' : emptyOptionLabel(companyId);

            const row = document.createElement('tr');
            row.className = 'po-row';
            row.innerHTML = `
                <td class="ps-3">
                    <select name="product_id[]" class="form-select form-select-sm product-select" style="max-width: 420px;" required>
                        <option value="">${escapeHtml(placeholder)}</option>
                        ${products.map(p => `<option value="${p.id}">${escapeHtml(p.name)}</option>`).join('')}
                    </select>
                </td>
                <td>
                    <input type="number" name="quantity[]" min="1" class="form-control form-control-sm" required>
                </td>
                <td>
                    <input type="number" name="unit_cost[]" min="0" step="0.01" class="form-control form-control-sm unit-cost-input" placeholder="e.g. 50.00">
                </td>
                <td class="pe-3 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Remove this product">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </td>
            `;

            rowsContainer.appendChild(row);

            row.querySelector('.remove-row').addEventListener('click', () => {
                row.remove();
            });
        }

        // Suggest the product's known cost as the Unit Cost whenever a
        // product is (re)selected in any row — the field stays editable so
        // the user can override it for this specific order.
        rowsContainer.addEventListener('change', (e) => {
            if (!e.target.classList.contains('product-select')) return;

            const row = e.target.closest('tr');
            const unitCostInput = row ? row.querySelector('.unit-cost-input') : null;
            const product = allProducts.find(p => String(p.id) === e.target.value);

            if (unitCostInput) {
                unitCostInput.value = product && product.cost !== null ? product.cost : '';
            }
        });

        companySelect.addEventListener('change', () => {
            syncLocationOptions();
            syncRowProductOptions();
            addRowBtn.disabled = !companySelect.value;
        });

        supplierSelect.addEventListener('change', () => {
            syncRowProductOptions();
        });

        addRowBtn.addEventListener('click', addProductRow);

        // Expected delivery can't be before the order date.
        const orderDateInput = document.getElementById('order_date');
        const expectedDeliveryInput = document.getElementById('expected_delivery_date');
        orderDateInput.addEventListener('change', () => {
            expectedDeliveryInput.min = orderDateInput.value;
            if (expectedDeliveryInput.value && expectedDeliveryInput.value < orderDateInput.value) {
                expectedDeliveryInput.value = orderDateInput.value;
            }
        });
        expectedDeliveryInput.min = orderDateInput.value;

        syncLocationOptions();
        addRowBtn.disabled = !companySelect.value;

        // Always start with one product row.
        if (rowsContainer.children.length === 0) {
            addProductRow();
        }
    </script>
@endpush
