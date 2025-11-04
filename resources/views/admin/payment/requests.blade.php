@extends('admin.admin_dashboard')

@section('main')
<div class="container">
    <h2 class="mb-4">Payment Requests</h2>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Store Phone</th>
                <th>Name</th>
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
                <td> {{ $bill->user->phone_number }} </td>
                <td> {{ $bill->user->shop_name }} </td>
                <td>{{ number_format($bill->amount, 2) }} ৳</td>
                <td>{{ $bill->paymentMethod?->name ?? 'N/A' }}</td>
                <td>
                    {{ $bill->status }}
                </td>
                <td>{{ $bill->created_at->format('d M, Y h:i A') }}</td>
                <td>
                    <button class="btn btn-success" onclick="toggleApproveForm({{ $bill->id }})">Approve</button>
                    <button class="btn btn-danger" onclick="rejectRequest({{ $bill->id }})">Reject</button>
                </td>
            </tr>
            <tr id="approve-form-{{ $bill->id }}" style="display:none;">
                <td colspan="8">
                    <form action="{{ route('admin.payment.update', $bill->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <input type="hidden" name="status" value="completed">

                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID</label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" value="{{ $bill->transaction_id }}" required>
                        </div>

                        <button type="submit" class="btn btn-success">Confirm Approve</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleApproveForm({{ $bill->id }})">Cancel</button>
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
    function toggleApproveForm(billId) {
        const form = document.getElementById('approve-form-' + billId);
        form.style.display = (form.style.display === "none" || form.style.display === "") 
            ? "table-row" 
            : "none";
    }

    function rejectRequest(billId) {
        const reason = prompt("Enter reason for rejection:");
        if (reason && reason.trim() !== "") {
            // Create a hidden form dynamically
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/payment/${billId}/reject`;

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'PUT';
            form.appendChild(method);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        } else {
            alert("Rejection cancelled — reason is required.");
        }
    }
</script>


@endsection
