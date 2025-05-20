@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <h4 class="page-title">Add Delivery Model</h4>

    <div class="page-header">
        <a href="{{ route('admin.delivery.models.index') }}" class="btn btn-secondary mb-3">Back to List</a>
    </div>

    <form action="{{ route('admin.delivery.models.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Model Name</label>
            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="minutes" class="form-label">Delivery Time (minutes)</label>
            <input type="number" id="minutes" name="minutes" class="form-control @error('minutes') is-invalid @enderror" value="{{ old('minutes') }}" min="1" required>
            @error('minutes') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="fee" class="form-label">Fee</label>
            <input type="number" id="fee" name="fee" class="form-control @error('fee') is-invalid @enderror" value="{{ old('fee') }}" min="0" step="0.01" required>
            @error('fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Create Model</button>
    </form>
</div>
@endsection
