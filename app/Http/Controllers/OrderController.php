<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\CartItem;
use App\Models\SellerOrder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\SellerOrderItem;
use App\Enums\SellerOrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Attributes\CurrentUser;

class OrderController extends Controller
{
    public function store(Request $request, #[CurrentUser()] User $user)
    {
        $fee = \App\Models\Fee::query()->first();

        $cartItems = CartItem::with(['product', 'size'])
            ->where('user_id', $user->getKey())
            ->where('selected', 1)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'No items selected in cart.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $deliveryModel = \App\Models\DeliveryModel::query()->find($request->delivery_model_id);

        if (! $deliveryModel) {
            return response()->json(['message' => 'Invalid delivery model.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        DB::beginTransaction();

        $userId = $user->getKey();

        try {
            $uniqueSellerCount = $cartItems->pluck('product.user_id')->unique()->count();
            $isMultiSeller = 1 < $uniqueSellerCount;

            $order = new Order;
            $order->user_id = $userId;
            $order->order_code = Str::orderId();
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
                $sellerOrder->customer_id = $userId;
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

                    $price = ($product->discount_price && 0 < $product->discount_price)
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

            \App\Models\CartItem::query()->where('user_id', $userId)
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

    public function sellerOrders(#[CurrentUser()] User $seller)
    {
        $orders = SellerOrder::with([
            'items',
        ])
            ->latest()
            ->where('seller_id', $seller->getKey())
            ->paginate(10);

        return response()->json([
            'message' => 'Seller orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    public function MyOrders(Request $request, #[CurrentUser()] User $user)
    {
        $status = $request->enum('status', SellerOrderStatus::class);

        $orders = Order::with([
            'sellerOrders',
        ])
            ->where('user_id', $user->getKey())
            ->latest()
            ->when($status?->isDelivered())
            ->whereHas('sellerOrders', function (\Illuminate\Contracts\Database\Query\Builder $q): void {
                $q->where('status', SellerOrderStatus::Delivered);
            })
            ->when($status?->isActive())
            ->whereHas('sellerOrders', function (\Illuminate\Contracts\Database\Query\Builder $q): void {
                $q->whereNotIn('status', [SellerOrderStatus::Delivered, SellerOrderStatus::Rejected]);
            })
            ->when($status?->isActive())->whereHas('sellerOrders', function (\Illuminate\Contracts\Database\Query\Builder $q): void {
                $q->where('status', SellerOrderStatus::Rejected);
            })
            ->paginate(10);

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    public function MyStoreOrders(Request $request, #[CurrentUser()] User $seller)
    {
        $search = $request->query('search');
        $status = $request->enum('status', SellerOrderStatus::class);

        $orders = \App\Models\SellerOrder::query()
            ->where('seller_id', $seller->getKey())
            ->latest()
            ->when(filled($status))
            ->where('status', $status)
            ->when(filled($search))
            ->whereHas('customer', function (\Illuminate\Contracts\Database\Query\Builder $query) use ($search): void {
                $query->whereLike('name', "%{$search}%")
                    ->orWhereLike('phone_number', "%{$search}%");
            })
            ->paginate(10);

        return response()->json([
            'message' => 'Store orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    public function searchOrderById(Request $request, #[CurrentUser()] User $user)
    {
        $search = $request->query('order_code');

        abort_if(
            blank($search),
            response(['message' => 'Order ID is required.'], Response::HTTP_BAD_REQUEST)
        );

        $numericPart = preg_replace('/[^0-9]/', '', $search);

        $order = Order::with(['sellerOrders'])
            ->where('user_id', $user->getKey())
            ->where(function (\Illuminate\Contracts\Database\Query\Builder $query) use ($search, $numericPart): void {
                $query->whereLike('order_code', "%$search%")
                    ->orWhereLike('order_code', "%$numericPart%");
            })
            ->firstOrFail();

        return response()->json([
            'message' => 'Order retrieved successfully',
            'data' => $order,
        ]);
    }

    public function sellerOrderDetail(SellerOrder $order, #[CurrentUser()] User $seller)
    {
        abort_unless($order->seller()->is($seller), Response::HTTP_NOT_FOUND, 'Seller order not found.');

        $order->load([
            'items' => function ($query): void {
                $query->with([
                    'product:id,name,discount_price,selling_price,quantity',
                    'product.images',
                ]);
            },
        ]);

        return response()->json([
            'message' => 'Seller order retrieved successfully',
            'data' => $order,
        ]);
    }

    // Accept Seller Order
    public function acceptSellerOrder(Request $request, SellerOrder $order, #[CurrentUser()] User $seller)
    {
        abort_unless($order->seller()->is($seller), Response::HTTP_NOT_FOUND, 'Seller order not found.');

        $order->status = SellerOrderStatus::Packaging;
        $order->status_message = $request->input('message', 'The order is in packaging');

        $order->delivery_start_time = now();

        $mainOrder = $order->order;

        $delivery_model = $mainOrder->delivery_model;

        $deliveryModel = \App\Models\DeliveryModel::query()->find($delivery_model);
        if ($deliveryModel) {
            $order->delivery_end_time = now()->addMinutes($deliveryModel->minutes);
        }

        $order->save();

        $order->notifyBuyerAboutOrderStatus();

        return response()->json([
            'message' => 'Seller order accepted successfully',
            'data' => $order,
        ]);
    }

    // Reject Seller Order
    public function rejectSellerOrder(Request $request, SellerOrder $order, #[CurrentUser()] User $seller)
    {
        abort_unless($order->seller()->is($seller), Response::HTTP_NOT_FOUND, 'Seller order not found.');

        $order->status = SellerOrderStatus::Rejected;
        $order->status_message = $request->input('message', 'The order is rejected by the seller');
        $order->save();

        $order->notifyBuyerAboutOrderStatus();

        return response()->json([
            'message' => 'You rejected the order successfully',
            'data' => $order,
        ]);
    }
}
