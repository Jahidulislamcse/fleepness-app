<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\SellerOrderItem;
use App\Models\Product;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminOrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'sellerOrders.items.product'])
            ->whereColumn('total_sellers', '!=', 'completed_order')
            ->latest()
            ->paginate(20);

        return view('admin.order.index', compact('orders'));
    }

    public function completed()
    {
        $orders = Order::with(['user', 'sellerOrders.items.product'])
            ->whereColumn('total_sellers', '=', 'completed_order')
            ->latest()
            ->paginate(20);

        return view('admin.order.completed', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'sellerOrders.items.product'])->findOrFail($id);
        return view('admin.order.show', compact('order'));
    }

    public function updateSellerOrder(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|in:on_the_way,delivered',
        ]);

        $sellerOrder = SellerOrder::findOrFail($id);

        $riderAssigned = $request->has('rider_assigned') ? 1 : 0;
        $sellerOrder->rider_assigned = $riderAssigned;

        if ($riderAssigned) {
            $sellerOrder->status = 'on_the_way';
        } else {
            $status = $request->input('status');
            if ($status) {
                $sellerOrder->status = \App\Enums\SellerOrderStatus::from($status);
            }
        }

        $sellerOrder->save();

        if ($sellerOrder->status->value === 'delivered') {

            $sellerOrder->balance = $sellerOrder->product_cost - $sellerOrder->commission;
            $sellerOrder->save();

            $order = $sellerOrder->order;

            $orderBalance = $order->balance ?? 0;

            $orderBalance += $sellerOrder->commission;     
            $orderBalance += $sellerOrder->delivery_fee;   
            $orderBalance += $sellerOrder->vat;            

            if (!$order->platform_fee_added) {
                $orderBalance += $order->platform_fee;
                $order->platform_fee_added = true;
            }
            $order->completed_order = ($order->completed_order ?? 0) + 1;

            $order->balance = $orderBalance;
            $order->save();

            $user = User::find($sellerOrder->seller_id);
            if ($user) {
                $user->total_sales = ($user->total_sales ?? 0) + $sellerOrder->product_cost;
                $user->balance = ($user->balance ?? 0) + $sellerOrder->balance;
                $user->save();
            }
        }

        return redirect()->back()->with('success', 'Seller order updated successfully.');
    }



}
