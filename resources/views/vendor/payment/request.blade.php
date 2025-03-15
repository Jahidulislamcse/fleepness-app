@extends('vendor.vendor_dashboard')

@section('main')

<div class="container">
    <h3 class="mb-3">Withdraw Funds</h3>

    <form action="{{ route('vendor.money.withdraw') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="balance" class="form-label">Current Balance</label>
            <input type="text" class="form-control" value="{{ auth()->user()->balance }}" disabled>
        </div>

        <div class="mb-3">
            <label for="amount" class="form-label">Enter Withdrawal Amount</label>
            <input type="number" class="form-control" id="amount" name="amount"
                min="1" max="{{ $max_withdrawable }}" required>
            <small class="form-text text-muted">
                You can withdraw up to {{ $max_withdrawable }} after deducting your pending requested amount .
            </small>
        </div>

        <button type="submit" class="btn btn-primary">Send Request</button>
    </form>

    @if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
</div>

@endsection
