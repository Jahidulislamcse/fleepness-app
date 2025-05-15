@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Edit Payment Method</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.payment-methods.update', $method->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="name">Payment Method Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $method->name }}" required>
                </div>
                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" {{ $method->is_active ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$method->is_active ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection