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
            ->latest()
            ->paginate(20);

        return view('admin.order.index', compact('orders'));
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
            if ($status !== null && $status !== '') {
                $sellerOrder->status = $status;
            }
        }

        $sellerOrder->save();

        return redirect()->back()->with('success', 'Seller order updated successfully.');
    }

}
