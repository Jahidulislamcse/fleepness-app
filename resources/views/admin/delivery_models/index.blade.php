@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <h4 class="page-title">Delivery Models</h4>

    <div class="page-header">
        <a href="{{ route('admin.delivery.models.create') }}" class="btn btn-primary float-end mb-3">Add New Model</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Minutes</th>
                <th>Fee</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($models as $model)
            <tr>
                <td>{{ $model->id }}</td>
                <td>{{ $model->name }}</td>
                <td>{{ $model->minutes }}</td>
                <td>{{ $model->fee }}</td>
                <td>
                    <a href="{{ route('admin.delivery.models.edit', $model->id) }}" class="btn btn-sm btn-warning">Edit</a>

                    <form action="{{ route('admin.delivery.models.destroy', $model->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Are you sure you want to delete this delivery model?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach

            @if($models->isEmpty())
            <tr>
                <td colspan="5" class="text-center">No delivery models found.</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection
