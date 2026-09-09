@extends('layouts.app')

@section('title', 'Add New Supplier')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none">Suppliers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Create Supplier</h3>
    </div>

    <div class="card shadow-sm">
        <div class="card-header p-3">
            <span class="fw-semibold text-secondary small">NEW SUPPLIER DETAILS</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf

                <div class="row">
                    <!-- Left column: identity -->
                    <div class="col-lg-6">
                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Supplier Name</label>
                            <input type="text" id="name" name="name"
                                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="e.g. Acme Distributors" required autofocus>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        @include('suppliers._name-field', [
                            'fieldPrefix' => 'contact_person',
                            'label' => 'Contact Person',
                            'required' => true,
                            'namePrefixes' => $namePrefixes,
                            'selectedPrefix' => '',
                            'nameValue' => '',
                        ])

                        @include('suppliers._name-field', [
                            'fieldPrefix' => 'secondary_contact_person',
                            'label' => 'Additional Contact Person',
                            'required' => false,
                            'namePrefixes' => $namePrefixes,
                            'selectedPrefix' => '',
                            'nameValue' => '',
                        ])
                    </div>

                    <!-- Right column: contact details -->
                    <div class="col-lg-6 mt-4 mt-lg-0">
                        @include('suppliers._phone-field', [
                            'fieldPrefix' => 'phone',
                            'label' => 'Phone',
                            'required' => true,
                            'countryCodes' => $countryCodes,
                            'selectedCode' => '+63',
                            'numberValue' => '',
                        ])

                        @include('suppliers._phone-field', [
                            'fieldPrefix' => 'secondary_phone',
                            'label' => 'Secondary Phone',
                            'required' => false,
                            'countryCodes' => $countryCodes,
                            'selectedCode' => '+63',
                            'numberValue' => '',
                        ])

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">Email <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="email" id="email" name="email"
                                   class="form-control form-control-sm @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}">
                            @error('email')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label small fw-semibold">Address</label>
                            <textarea id="address" name="address" rows="2" class="form-control form-control-sm @error('address') is-invalid @enderror" required>{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Action Buttons -->
                <div class="d-flex align-items-center gap-2 mt-2 pt-3 border-top">
                    <button type="submit" class="btn btn-github btn-sm px-3">
                        <i class="bi bi-plus-lg me-1"></i> Save Supplier
                    </button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-github btn-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
