@extends('vendor.vendor_dashboard')

@section('main')
<div class="container">
    <h2 class="mb-4">Payment History</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Transaction ID</th>
                <th>Status</th>
                <th>Request Date</th>
                <th>Receiving Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bills as $key => $bill)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ number_format($bill->amount, 2) }} ৳</td>
                <td>{{ ucfirst($bill->payment_media) }}</td>
                <td>{{ ucfirst($bill->transaction_id) }}</td>
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
                @if($bill->status == 'completed')
                <td>{{ $bill->updated_at->format('d M, Y h:i A') }}</td>
                @else
                <td>-</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">No payment history found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
