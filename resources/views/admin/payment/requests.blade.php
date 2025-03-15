@extends('admin.admin_dashboard')

@section('main')
<div class="container">
    <h2 class="mb-4">Payment Requests</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Vendor ID & Name</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
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
                <td>
                    <button class="btn btn-primary" onclick="toggleUpdateForm({{$bill->id}})">Update</button>
                </td>
            </tr>
            <tr id="update-form-{{ $bill->id }}" style="display:none;">
                <td colspan="6">
                    <form action="{{ route('admin.payment.update', $bill->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="status" class="form-label">Update Status</label>
                            <select class="form-control" name="status" required>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="{{ $bill->transaction_id }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="payment_media" class="form-label">Payment Media</label>
                            <input type="text" class="form-control" id="payment_media" name="payment_media" value="{{ $bill->payment_media }}" required>
                        </div>

                        <button type="submit" class="btn btn-success">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleUpdateForm({{ $bill->id }})">Cancel</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No payment history found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    function toggleUpdateForm(billId) {
        var form = document.getElementById('update-form-' + billId);
        // Toggle only the form row visibility, leaving the bill row intact
        if (form.style.display === "none" || form.style.display === "") {
            form.style.display = "table-row";
        } else {
            form.style.display = "none";
        }
    }
</script>

@endsection
