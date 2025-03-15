@extends('admin.admin_dashboard')

@section('main')
<div class="container">
    <h2 class="mb-4">Payment History</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Vendor ID & Name</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bills as $key => $bill)
            <tr id="bill-{{ $bill->id }}">
                <td>{{ $key + 1 }}</td>
                <td>{{ $bill->user_id }} <br> {{ $bill->user->name }} </td>
                <td>{{ number_format($bill->amount, 2) }} ৳</td>
                <td>{{ ucfirst($bill->payment_media) }}</td>
                <td>
                    @if($bill->status == 'pending')
                    <span class="badge bg-warning">Pending</span>
                    @elseif($bill->status == 'completed')
                    <span class="badge bg-success">Completed</span>
                    @elseif($bill->status == 'failed')
                    <span class="badge bg-danger">Failed</span>
                    @endif
                </td>
                <td>{{ $bill->created_at->format('d M, Y h:i A') }}</td>
                <td>{{ $bill->updated_at->format('d M, Y h:i A') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No payment history found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>



@endsection
