@extends('admin.admin_dashboard')

@section('main')
<div class="container">
    <h2 class="mb-4">Payment History</h2>

    <div class="mb-3">
        <a href="{{ route('admin.payment.history') }}" class="btn btn-secondary btn-sm {{ $status ?? '' == '' ? 'active' : '' }}">All</a>
        <a href="{{ route('admin.payment.history', ['status' => 'approved']) }}" class="btn btn-success btn-sm {{ $status == 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('admin.payment.history', ['status' => 'rejected']) }}" class="btn btn-warning btn-sm {{ $status == 'rejected' ? 'active' : '' }}">Rejected</a>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                  <th>Name</th>
                <th>Amount</th>
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
                <td> {{ $bill->user->phone_number }} </td>
                <td> {{ $bill->user->shop_name }} </td>
                <td>{{ number_format($bill->amount, 2) }} ৳</td>
                <td>{{ $bill->paymentMethod?->name ?? 'N/A' }}</td>
                <td>
                    @if ($bill->status === 'approved')
                        <span class="badge bg-success px-2 py-1">Approved</span>
                    @elseif ($bill->status === 'rejected')
                        <button class="badge bg-warning text-dark px-2 py-1 border-0" 
                                data-bs-toggle="modal" 
                                data-bs-target="#rejectReasonModal-{{ $bill->id }}">
                            Rejected
                        </button>

                        <div class="modal fade" id="rejectReasonModal-{{ $bill->id }}" tabindex="-1" aria-labelledby="rejectReasonModalLabel-{{ $bill->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered px-4">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectReasonModalLabel-{{ $bill->id }}">Rejection Reason</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                {{ $bill->note ?? 'No reason provided' }}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            </div>
                            </div>
                        </div>
                        </div>
                    @else
                        <span class="badge bg-secondary px-2 py-1">{{ ucfirst($bill->status) }}</span>
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
