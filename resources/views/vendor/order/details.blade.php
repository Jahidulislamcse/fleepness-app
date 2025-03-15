@extends('vendor.vendor_dashboard')
@section('main')
<div class="page-inner">
    <div class="page-header">
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('vendor.dashboard') }}">
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
    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 row-cols-xl-2">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h4>Shipping Details</h4>
                </div>
                <hr>
                <div class="card-body">
                    <table class="table" style="background:#F4F6FA;font-weight: 600;">
                        <tr>
                            <th>Shipping Name:</th>
                            <th></th>
                        </tr>

                        <tr>
                            <th>Shipping Phone:</th>
                            <th></th>
                        </tr>

                        <tr>
                            <th>Shipping Email:</th>
                            <th></th>
                        </tr>

                        <tr>
                            <th>Shipping Address:</th>
                            <th>{{ $orderInfo->customerInfo->address }}</th>
                        </tr>

                        {{-- <tr>
                            <th>Post Code :</th>
                            <th>{{ $orderInfo->customerInfo->post_code }}</th>
                        </tr> --}}

                        <tr>
                            <th>Order Date :</th>
                            <th>{{ $orderInfo->created_at->format('Y/ m/ d') }}</th>
                        </tr>

                    </table>

                </div>

            </div>
        </div>


        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h4>Order Details
                        <span class="text-danger">Invoice : {{ $orderInfo->invoice_no }} </span>
                    </h4>
                </div>
                <hr>
                <div class="card-body">
                    <table class="table" style="background:#F4F6FA;font-weight: 600;">
                        <tr>
                            <th> Name :</th>
                            <th>{{ $orderInfo->customerInfo->name }}</th>
                        </tr>

                        <tr>
                            <th>Phone :</th>
                            <th>{{ $orderInfo->customerInfo->phone_number }}</th>
                        </tr>

                        <tr>
                            <th>Payment Type:</th>
                            <th>{{ $orderInfo->payment_method }}</th>
                        </tr>

                        <tr>
                            <th>Invoice:</th>
                            <th class="text-danger">{{ $orderInfo->invoice_no }}</th>
                        </tr>

                        <tr>
                            <th>Order Amonut:</th>
                            <th>{{ $orderInfo->amount + $orderInfo->shipping_cost }} &nbsp;{{ env('currency') }}</th>
                        </tr>

                        <tr>
                            <th>Order Status:</th>
                            <th><span class="badge bg-danger" style="font-size: 15px;">{{ $orderInfo->status }}</span></th>
                        </tr>
                        <tr>
                            <th>Payment Status:</th>
                            <th><span class="badge bg-danger" style="font-size: 15px;">{{ $orderInfo->payment_status }}</span></th>
                        </tr>
                        <tr>
                            <th> </th>
                            <th>
                                @if ($orderInfo->status == 'pending')
                                <a href="{{ route('vendor.order.pending-confirm', $orderInfo->id) }}"
                                    class="btn btn-block btn-success" id="confirm"
                                    onclick="return confirm('Are you sure you want to Confirm this order?');">Confirm
                                    Order</a>
                                <a href="{{ route('vendor.order.pending-cancel', $orderInfo->id) }}"
                                    class="btn btn-block btn-success" id="cancel"
                                    onclick="return confirm('Are you sure you want to Cancel this order?');">Cancel
                                    Order</a>
                                @elseif($orderInfo->status == 'confirm')
                                <a href="{{ route('vendor.order.confirm-processing', $orderInfo->id) }}"
                                    class="btn btn-block btn-success" id="processing">Processing Order</a>
                                @elseif($orderInfo->status == 'processing')
                                <a href="{{ route('vendor.order.processing-ready-ship', $orderInfo->id) }}"
                                    class="btn btn-block btn-success" id="ship">Ready to ship</a>
                                @endif
                            </th>
                        </tr>

                    </table>

                </div>

            </div>
        </div>
    </div>






    <div class="row row-cols-1 row-cols-md-1 row-cols-lg-2 row-cols-xl-1">
        <div class="col">
            <div class="card">


                <div class="table-responsive">
                    <table class="table" style="font-weight: 600;">
                        <tbody>
                            <tr>
                                <td class="col-md-1">
                                    <label>Image </label>
                                </td>
                                <td class="col-md-2">
                                    <label>Product Name </label>
                                </td>

                                <td class="col-md-1">
                                    <label>Stock </label>
                                </td>
                                <td class="col-md-1">
                                    <label>Quantity </label>
                                </td>

                                <td class="col-md-2">
                                    <label>Price </label>
                                </td>

                                <td class="col-md-2">
                                    <label>Sub Total </label>
                                </td>

                            </tr>

                            @php
                            $grandTotal = 0;
                            @endphp
                            @foreach ($orderInfo->orderDetails as $item)
                            @php
                            $totalPrice = $item->price * $item->qty;
                            $grandTotal += $totalPrice;

                            @endphp

                            <tr>
                                <td class="col-md-1">
                                    @if ($item->productInfo && $item->productInfo->imagesProduct)
                                    <label>
                                        <img src="{{ asset($item->productInfo->imagesProduct->path) }}" style="width:50px; height:50px;">
                                    </label>
                                    @endif
                                </td>
                                <td class="col-md-2">
                                    <div>
                                        <h5>{{ $item->productInfo->name }}</h5>
                                        <br />
                                        <h5>Size:{{ $item->stockInfo->size }}</h5>
                                    </div>

                                </td>



                                <td class="col-md-1">
                                    <label>{{ $item->productInfo->quantity }}</label>
                                </td>
                                <td class="col-md-1">
                                    <label>{{ $item->qty }} </label>
                                </td>

                                <td class="col-md-2">
                                    <label>{{ $item->price }} &nbsp;{{ env('currency') }}
                                    </label>
                                </td>

                                <td class="col-md-2">
                                    <label>{{ $item->price * $item->qty }}
                                        &nbsp;{{ env('currency') }}
                                    </label>
                                </td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="5" class="text-end"><strong>Item Total:</strong></td>
                                <td>{{ $grandTotal  }} &nbsp;{{ env('currency') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Shipping Charge:</strong></td>
                                <td>{{ $orderInfo->shipping_cost }} &nbsp;{{ env('currency') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                                <td>{{ $grandTotal + $orderInfo->shipping_cost }} &nbsp;{{ env('currency') }}</td>
                            </tr>

                        </tbody>
                    </table>

                </div>



            </div>
        </div>

    </div>

</div>


@endsection
