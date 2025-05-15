@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <div class="page-header">
        <h4 class="page-title">Add Payment Method</h4>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.payment-methods.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Payment Method Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Bkash, Nagad" required>
                </div>
                <div class="form-group">
                    <label for="is_active">Status</label>
                    <select name="is_active" class="form-control">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
                <a href="{{ route('admin.payment-methods.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection