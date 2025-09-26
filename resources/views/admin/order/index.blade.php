@extends('admin.admin_dashboard')
@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('admin.dashboard') }}">
                    <i class="icon-home"></i>
                    Dashbard
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0)">Orders</a>
            </li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">All Orders</h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="basic-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Order Code</th>
                                    <th>Customer</th>
                                    <th>Total Sellers</th>
                                    <th>Product Cost</th>
                                    <th>Delivery Fee</th>
                                    <th>Grand Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $key => $order)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $order->order_code }}</td>
                                        <td>{{ $order->user->name ?? 'N/A' }}</td>
                                        <td>{{ $order->total_sellers }}</td>
                                        <td>{{ number_format($order->product_cost, 2) }}</td>
                                        <td>{{ number_format($order->delivery_fee, 2) }}</td>
                                        <td>{{ number_format($order->grand_total, 2) }}</td>
                                        <td>
                                            @if ($order->is_multi_seller)
                                                <span class="badge bg-info">Multi Seller</span>
                                            @else
                                                <span class="badge bg-success">Single Seller</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->created_at->format('d M Y h:i A') }}</td>
                                        <td>
                                            <a href="{{ route('admin.order.view', $order->id) }}" 
                                               class="btn btn-primary btn-sm">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="mt-3">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>  
</div>


@endsection
