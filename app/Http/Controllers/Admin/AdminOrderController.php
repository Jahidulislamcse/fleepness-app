<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Order;
use App\Models\SellerOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class AdminOrderController extends Controller
{
    public function index(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $orders = Order::with(['user', 'sellerOrders.items.product'])
            ->whereColumn('total_sellers', '!=', 'completed_order')
            ->latest()
            ->paginate(20);

        return view('admin.order.index', ['orders' => $orders]);
    }

    public function completed(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $orders = Order::with(['user', 'sellerOrders.items.product'])
            ->whereColumn('total_sellers', '=', 'completed_order')
            ->latest()
            ->paginate(20);

        return view('admin.order.completed', ['orders' => $orders]);
    }

    public function show($id): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
    {
        $order = Order::with(['user', 'sellerOrders.items.product'])->findOrFail($id);

        return view('admin.order.show', ['order' => $order]);
    }

    public function updateSellerOrder(Request $request, $id)
    {
        $request->validate([
            'status' => ['nullable', Rule::enum(\App\Enums\SellerOrderStatus::class)->only([
                \App\Enums\SellerOrderStatus::On_The_Way,
                \App\Enums\SellerOrderStatus::Delivered,
            ])],
        ]);

        /** @var SellerOrder */
        $sellerOrder = \App\Models\SellerOrder::query()->findOrFail($id);

        $riderAssigned = $request->boolean('rider_assigned');
        $sellerOrder->rider_assigned = $riderAssigned;

        if ($riderAssigned) {
            $sellerOrder->status = \App\Enums\SellerOrderStatus::On_The_Way;
        } else {
            $status = $request->enum('status', \App\Enums\SellerOrderStatus::class);
            if ($status) {
                $sellerOrder->status = $status;
            }
        }

        $sellerOrder->save();

        if ($sellerOrder->status->isDelivered()) {
            $sellerOrder->balance = $sellerOrder->product_cost - $sellerOrder->commission;
            $sellerOrder->save();

            $order = $sellerOrder->order;

            $orderBalance = $order->balance ?? 0;

            $orderBalance += $sellerOrder->commission;
            $orderBalance += $sellerOrder->delivery_fee;
            $orderBalance += $sellerOrder->vat;

            if (! $order->platform_fee_added) {
                $orderBalance += $order->platform_fee;
                $order->platform_fee_added = true;
            }
            $order->completed_order = ($order->completed_order ?? 0) + 1;
            $order->balance = $orderBalance;
            $order->save();

            $user = \App\Models\User::query()->find($sellerOrder->seller_id);
            if ($user) {
                $user->total_sales = ($user->total_sales ?? 0) + $sellerOrder->product_cost;
                $user->balance = ($user->balance ?? 0) + $sellerOrder->balance;
                $user->save();
            }
        }

        $sellerOrder->notifyBuyerAboutOrderStatus();

        return back()->with('success', 'Seller order updated successfully.');
    }
}
