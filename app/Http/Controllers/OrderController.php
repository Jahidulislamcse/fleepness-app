<?php

namespace App\Http\Controllers;

use App\Enums\SellerOrderStatus;
use App\Models\CartItem;
use App\Models\DeliveryModel;
use App\Models\Fee;
use App\Models\Order;
use App\Models\SellerOrder;
use App\Models\SellerOrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
   public function store(Request $request)
    {
        $fee = Fee::first();

        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $cartItems = CartItem::with(['product', 'size'])
            ->where('user_id', $userId)
            ->where('selected', 1)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'No items selected in cart.'], 422);
        }

        $deliveryModel = DeliveryModel::find($request->delivery_model_id);

        if (!$deliveryModel) {
            return response()->json(['message' => 'Invalid delivery model.'], 422);
        }

        DB::beginTransaction();

        try {
            $uniqueSellerCount = $cartItems->pluck('product.user_id')->unique()->count();
            $isMultiSeller = $uniqueSellerCount > 1;

            $order = new Order;
            $order->user_id = $userId;
            $order->order_code = '#ORD-' . random_int(10000, 99999);
            $order->is_multi_seller = $isMultiSeller;
            $order->total_sellers = $uniqueSellerCount;
            $order->delivery_model = $deliveryModel->id;

            $order->delivery_fee = $deliveryModel->fee * $uniqueSellerCount;

            $order->product_cost = 0;
            $order->commission = 0;
            $order->platform_fee = 0;
            $order->vat = 0;
            $order->grand_total = 0;
            $order->save();

            $orderProductCost = 0;

            $grouped = $cartItems->groupBy('product.user_id');

            foreach ($grouped as $sellerId => $sellerItems) {
                $sellerOrder = new SellerOrder;
                $sellerOrder->order_id = $order->id;
                $sellerOrder->seller_id = $sellerId;
                $sellerOrder->status = SellerOrderStatus::Pending;
                $sellerOrder->product_cost = 0;
                $sellerOrder->commission = 0;
                $sellerOrder->vat = 0;
                $sellerOrder->delivery_fee = 0;
                $sellerOrder->balance = 0;
                $sellerOrder->rider_assigned = false;
                $sellerOrder->save();

                $sellerTotal = 0;

                foreach ($sellerItems as $cartItem) {
                    $product = $cartItem->product;
                    $qty = $cartItem->quantity;

                    $price = ($product->discount_price && $product->discount_price > 0)
                        ? $product->discount_price
                        : $product->selling_price;

                    $totalCost = $price * $qty;

                    $sItem = new SellerOrderItem;
                    $sItem->seller_order_id = $sellerOrder->id;
                    $sItem->product_id = $product->id;
                    $sItem->size = $cartItem->size_id ? $cartItem->size->size_name : null;
                    $sItem->quantity = $qty;
                    $sItem->total_cost = $totalCost;
                    $sItem->save();

                    $sellerTotal += $totalCost;

                    $product->decrement('quantity', $qty);
                }

                $sellerOrder->product_cost = $sellerTotal;
                $sellerOrder->commission = $sellerTotal * ($fee->commission / 100);
                $sellerOrder->vat = $sellerTotal * ($fee->vat / 100);

                $sellerOrder->delivery_fee = $order->delivery_fee / $uniqueSellerCount;

                $sellerOrder->save();

                $sellerOrder->notifySellerAboutNewOrderFromBuyer();

                $orderProductCost += $sellerTotal;
            }

            $order->product_cost = $orderProductCost;
            $order->commission = $orderProductCost * ($fee->commission / 100);
            $order->platform_fee = $fee->platform_fee;
            $order->vat = $orderProductCost * ($fee->vat / 100);

            $order->grand_total = $orderProductCost
                + (float) $order->delivery_fee
                + (float) $order->platform_fee
                + (float) $order->vat;
            $order->save();

            CartItem::where('user_id', $userId)
                ->where('selected', 1)
                ->delete();

            DB::commit();

            $order->load([
                'sellerOrders.items.product',
                'sellerOrders.seller',
            ]);

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function sellerOrders(Request $request)
    {
        $sellerId = auth()->id();

        if (! $sellerId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $orders = \App\Models\SellerOrder::with([
            'items',
        ])
            ->where('seller_id', $sellerId)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Seller orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    public function sellerOrderDetail(Request $request, $id)
    {
        $sellerId = auth()->id();

        if (! $sellerId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $sellerOrder = SellerOrder::with([
            'items' => function ($query) {
                $query->with([
                    'product:id,name,discount_price,selling_price,quantity',
                    'product.images',
                ]);
            },
        ])
            ->where('id', $id)
            ->where('seller_id', $sellerId)
            ->first();

        if (! $sellerOrder) {
            return response()->json(['message' => 'Seller order not found.'], 404);
        }

        return response()->json([
            'message' => 'Seller order retrieved successfully',
            'data' => $sellerOrder,
        ]);
    }

    // Accept Seller Order
    public function acceptSellerOrder(Request $request, $id)
    {
        $sellerId = auth()->id();

        $sellerOrder = SellerOrder::with('order')
            ->where('id', $id)
            ->where('seller_id', $sellerId)
            ->first();

        if (! $sellerOrder) {
            return response()->json(['message' => 'Seller order not found.'], 404);
        }

        $sellerOrder->status = 'packaging';
        $sellerOrder->status_message = $request->input('message', 'The order is in packaging');

        $sellerOrder->delivery_start_time = Carbon::now();

        $mainOrder = Order::where('id', $sellerOrder->order_id)
            ->first();

        $delivery_model = $mainOrder->delivery_model;

        $deliveryModel = DeliveryModel::find($delivery_model);
        if ($deliveryModel) {
            $sellerOrder->delivery_end_time = Carbon::now()->addMinutes($deliveryModel->minutes);
        }

        $sellerOrder->save();

        return response()->json([
            'message' => 'Seller order accepted successfully',
            'data' => $sellerOrder,
        ]);
    }

    // Reject Seller Order
    public function rejectSellerOrder(Request $request, $id)
    {
        $sellerId = auth()->id();

        $order = SellerOrder::where('id', $id)
            ->where('seller_id', $sellerId)
            ->first();

        if (! $order) {
            return response()->json(['message' => 'Seller order not found.'], 404);
        }

        $order->status = 'rejected';
        $order->status_message = $request->input('message', 'The order is rejected by the seller');
        $order->save();

        return response()->json([
            'message' => 'You rejected the order successfully',
            'data' => $order,
        ]);
    }
}
