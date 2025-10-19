@extends('admin.admin_dashboard')

@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="icon-home"></i> Dashboard
                </a>
            </li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('admin.order.all') }}">Orders</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item">Order Detail</li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">

            {{-- Order Summary --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title">Order {{ $order->order_code }}</h4>
                </div>
                <div class="card-body">
                    <p><strong>Customer:</strong> {{ $order->user->name ?? 'N/A' }}</p>
                    <p><strong>Phone:</strong> {{ $order->user->phone_number ?? 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}</p>
                    <p><strong>Placed On:</strong> {{ $order->created_at->format('d M Y h:i A') }}</p>
                    <p><strong>Time Spent:</strong> 
                        {{ $order->created_at->diffForHumans(now(), [
                            'parts' => 3, 
                            'short' => true, 
                            'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                        ]) }}
                    </p>
                    <p><strong>Seller:</strong>
                        @if ($order->is_multi_seller)
                        <span class="badge bg-info">Multi Seller</span>
                        @else
                        <span class="badge bg-success">Single Seller</span>
                        @endif
                    </p>
                    <hr>
                    <p><strong>Product Cost:</strong> ${{ number_format($order->product_cost, 2) }}</p>
                    <p><strong>Delivery Fee:</strong> ${{ number_format($order->delivery_fee, 2) }}</p>
                    <p><strong>Grand Total:</strong> ${{ number_format($order->grand_total, 2) }}</p>
                </div>
            </div>

            {{-- Seller Orders --}}
            @foreach ($order->sellerOrders as $sellerOrder)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        Seller: {{ $sellerOrder->seller->name ?? 'Unknown Seller' }}  ({{ $sellerOrder->seller->phone_number ?? 'Unknown' }})
                    </h5>
                    <span class="badge bg-primary">Status: {{ $sellerOrder->status->value }}</span>
                </div>
                <div class="card-body">

                    {{-- Items --}}
                    <table class="table table-bordered table-hover mb-4">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Variant</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sellerOrder->items as $item)
                            <tr>
                                <td>{{ $item->product->name ?? 'N/A' }}</td>
                                <td>{{ $item->size ?? '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->total_cost / $item->quantity, 2) }}</td>
                                <td>${{ number_format($item->total_cost, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Update Seller Order Form --}}
                    @if(!in_array($sellerOrder->status->value, ['rejected', 'pending', 'delivered']))
                        <form action="{{ route('admin.order.updateSellerOrder', $sellerOrder->id) }}" method="POST" class="row g-3">
                            @csrf
                            @method('PUT')

                            @if(!in_array($sellerOrder->status->value, ['packaging']))
                                <div class="col-md-4">
                                    <label for="status" class="form-label">Update Status</label>
                                    <select name="status" id="status_{{ $sellerOrder->id }}" class="form-control">
                                        <option value="" selected disabled>Change Status</option>
                                        <option value="delivered" {{ $sellerOrder->status->value == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    </select>
                                </div>
                            @endif

                            @if(!in_array($sellerOrder->status->value, ['rejected', 'pending', 'on_the_way']))
                                <div class="col-md-4 d-flex align-items-center">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" name="rider_assigned" value="1"
                                            id="rider_assigned_{{ $sellerOrder->id }}"
                                            {{ $sellerOrder->rider_assigned ? 'checked' : '' }}
                                            {{ $sellerOrder->status->value != 'packaging' ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="rider_assigned_{{ $sellerOrder->id }}">
                                            Assign Rider
                                        </label>
                                    </div>
                                </div>
                            @endif

                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            @endforeach

        </div>
    </div>
</div>
@endsection