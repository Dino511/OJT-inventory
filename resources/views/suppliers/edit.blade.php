@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
    <!-- Breadcrumb & Page Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none">Suppliers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit #{{ $supplier->id }}</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-0">Edit Supplier</h3>
    </div>

    <div class="row">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header p-3">
                    <span class="fw-semibold text-secondary small">EDIT SUPPLIER DETAILS</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold">Supplier Name</label>
                            <input type="text" id="name" name="name"
                                   class="form-control form-control-sm @error('name') is-invalid @enderror"
                                   value="{{ old('name', $supplier->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="contact_person" class="form-label small fw-semibold">Contact Person</label>
                                <input type="text" id="contact_person" name="contact_person"
                                       class="form-control form-control-sm @error('contact_person') is-invalid @enderror"
                                       value="{{ old('contact_person', $supplier->contact_person) }}" required>
                                @error('contact_person')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="secondary_contact_person" class="form-label small fw-semibold">Additional Contact Person <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" id="secondary_contact_person" name="secondary_contact_person"
                                       class="form-control form-control-sm @error('secondary_contact_person') is-invalid @enderror"
                                       value="{{ old('secondary_contact_person', $supplier->secondary_contact_person) }}">
                                @error('secondary_contact_person')
                                    <div class="invalid-feedback d-block small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                @include('suppliers._phone-field', [
                                    'fieldPrefix' => 'phone',
                                    'label' => 'Phone',
                                    'required' => true,
                                    'countryCodes' => $countryCodes,
                                    'selectedCode' => $phone['code'],
                                    'numberValue' => $phone['number'],
                                ])
                            </div>
                            <div class="col-md-6">
                                @include('suppliers._phone-field', [
                                    'fieldPrefix' => 'secondary_phone',
                                    'label' => 'Secondary Phone',
                                    'required' => false,
                                    'countryCodes' => $countryCodes,
                                    'selectedCode' => $secondaryPhone['code'],
                                    'numberValue' => $secondaryPhone['number'],
                                ])
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold">Email <span class="text-muted fw-normal">(optional)</span></label>
                            <input type="email" id="email" name="email"
                                   class="form-control form-control-sm @error('email') is-invalid @enderror"
                                   value="{{ old('email', $supplier->email) }}">
                            @error('email')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label small fw-semibold">Address</label>
                            <textarea id="address" name="address" rows="2" class="form-control form-control-sm @error('address') is-invalid @enderror" required>{{ old('address', $supplier->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback d-block small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Action Buttons -->
                        <div class="d-flex align-items-center gap-2 mt-4 pt-2 border-top">
                            <button type="submit" class="btn btn-github btn-sm px-3">
                                <i class="bi bi-check-lg me-1"></i> Update Supplier
                            </button>
                            <a href="{{ route('suppliers.index') }}" class="btn btn-outline-github btn-sm">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
