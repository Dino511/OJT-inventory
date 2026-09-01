@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 600px;">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <h1 class="h5 fw-bold text-dark mb-0">Edit Location</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('locations.update', $location) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Company Name on Top -->
                <div class="mb-3">
                    <label for="company_id" class="form-label fw-semibold small">Company Name</label>
                    <select name="company_id" id="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                        <option value="" disabled>Select a company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->company_id }}" {{ old('company_id', $location->company_id) == $company->company_id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Location Name -->
                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold small">Location Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $location->name) }}" placeholder="e.g. Main Warehouse" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Description / Landmark -->
                <div class="mb-4">
                    <label for="description" class="form-label fw-semibold small">Description / Landmark</label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="e.g. Near the main loading dock, 2nd floor">{{ old('description', $location->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary px-3">Cancel</a>
                    <button type="submit" class="btn btn-github px-4">Update Location</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection