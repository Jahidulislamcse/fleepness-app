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
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Orders</h4>

                    </div>
                </div>
                <div class="card-body">
                    <!-- Modal -->
                    <div class="modal fade" id="addRowModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">Add New</h1>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="basic-datatables" class="display table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SN</th>
                                    <th>Customer & Phone</th>
                                    <th>Vendor ID & store</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Order date</th>
                                    <th>Payment Type</th>
                                    <th>Payment Status</th>
                                    <th>Updated Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orderList as $key => $orderInfo)
                                <tr>
                                    <td>{{ $key + 1 }}</td>


                                    <td>{{ $orderInfo->customerInfo->name }} <br />
                                        {{ $orderInfo->customerInfo->phone_number }}
                                    </td>
                                    <td> {{ $orderInfo->vendor->id }} <br />
                                        {{ $orderInfo->vendor->name }}
                                    </td>
                                    <td><span class="badge bg-danger" style="font-size: 15px;">{{ $orderInfo->status }}</span>
                                    </td>
                                    <td>{{ $orderInfo->amount + $orderInfo->shipping_cost }}</td>
                                    <td>{{ $orderInfo->created_at->format('d/m/Y')}}</td>
                                    <td>{{ $orderInfo->payment_type }}</td>
                                    <td>{{ $orderInfo->payment_status }}</td>
                                    <td>{{ $orderInfo->updated_at->format('d/m/Y')}}</td>
                                    <td>
                                        <a href="{{ route('admin.order.details', $orderInfo->id) }}"
                                            class="btn btn-warning btn-sm">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


@endsection
