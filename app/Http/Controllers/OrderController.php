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
use App\Models\DeliveryModel;
use App\Models\Fee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Container\Attributes\CurrentUser;

class OrderController extends Controller
{
    public function store(Request $request, #[CurrentUser()] User $user)
    {
        if (! $user->defaultAddress()->exists()) {
            return response()->json([
                'message' => 'Please set a default delivery address before placing an order.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fee = Fee::query()->first();

        $cartItems = CartItem::with(['product', 'size'])
            ->where('user_id', $user->getKey())
            ->where('selected', 1)
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'No items selected in cart.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $deliveryModel = DeliveryModel::query()->find($request->delivery_model_id);

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
            $sellerIndex = 1;

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
                $sellerOrder->seller_order_code = $order->order_code.$sellerIndex++;
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

            CartItem::query()->where('user_id', $userId)
                ->where('selected', 1)
                ->delete();

            DB::commit();

            $order->load([
                'sellerOrders.items.product',
                'sellerOrders.seller',
            ]);

            $user->load('defaultAddress');

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order,
                'default_address' => $user->defaultAddress,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sellerOrders(#[CurrentUser()] User $seller, Request $request)
    {

        $query = SellerOrder::with([
            'items' => function ($q) {
                $q->with(['product:id,name,selling_price,discount_price', 'product.images:id,product_id,path']);
            },
            'customer:id,name,phone_number,email',
            'customer.defaultAddress'
        ])
            ->where('seller_id', $seller->getKey());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $numericPart = preg_replace('/[^0-9]/', '', $search);

            $query->where(function ($q) use ($search, $numericPart) {
                $q->where('seller_order_code', 'like', "%{$search}%")
                    ->orWhere('seller_order_code', 'like', "%{$numericPart}%")
                    ->orWhereHas('items.product', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('customer', function ($q3) use ($search) {
                        $q3->where('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->latest()->paginate(10);

        return response()->json([
            'message' => 'Seller orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    public function myOrders(Request $request, #[CurrentUser()] User $user)
    {
        $status     = $request->enum('status', SellerOrderStatus::class);
        $search     = $request->query('order_code');
        $numericPart = $search ? preg_replace('/[^0-9]/', '', $search) : null;

        $query = Order::with([
            'sellerOrders' => function ($q) {
                $q->with(['seller:id,shop_name,shop_category,banner_image,cover_image']);
            },
        ])
            ->where('user_id', $user->getKey())
            ->latest();

        if (!blank($search)) {
            $query->where(function ($q) use ($search, $numericPart) {
                $q->whereLike('order_code', "%{$search}%")
                ->orWhereLike('order_code', "%{$numericPart}%");
            });

            $order = $query->firstOrFail();

            return response()->json([
                'message' => 'Order retrieved successfully',
                'data'    => $order,
            ]);
        }

        $query
            ->when($status?->isDelivered(), function ($q) {
                $q->whereHas('sellerOrders', function ($q) {
                    $q->where('status', SellerOrderStatus::Delivered);
                });
            })
            ->when($status?->isActive(), function ($q) {
                $q->whereHas('sellerOrders', function ($q) {
                    $q->whereNotIn('status', [
                        SellerOrderStatus::Delivered,
                        SellerOrderStatus::Rejected,
                    ]);
                });
            })
            ->when($status?->isRejected(), function ($q) {
                $q->whereHas('sellerOrders', function ($q) {
                    $q->where('status', SellerOrderStatus::Rejected);
                });
            });

        $orders = $query->paginate(10);

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data'    => $orders,
        ]);
    }


    public function MyStoreOrders(Request $request, #[CurrentUser()] User $seller)
    {
        $search = $request->query('search');
        $status = $request->enum('status', SellerOrderStatus::class);

        $orders = SellerOrder::query()
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

    public function topSellingProductsLast7Days($seller)
    {
        $fromDate = Carbon::now()->subDays(7);

        $items = SellerOrderItem::query()
            ->whereHas('sellerOrder', function ($q) use ($seller, $fromDate) {
                $q->where('seller_id', $seller)
                ->where('created_at', '>=', $fromDate);
            })
            ->selectRaw('product_id, SUM(quantity) as total_sold, SUM(total_cost) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(6)
            ->with([
                'product' => function ($q) {
                    $q->select('id', 'name', 'selling_price', 'discount_price')
                    ->with('firstImage');
                }
            ])
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No sales found for this seller in the last 7 days',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Top selling products for the last 7 days retrieved successfully',
            'data' => $items->map(function ($item) {
                return [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'image' => $item->product->image, 
                    'selling_price' => $item->product->selling_price,
                    'discount_price' => $item->product->discount_price,
                    'total_sold' => (int) $item->total_sold,
                    'total_revenue' => (float) $item->total_revenue,
                    'sizes' => $item->product->sizes->map(fn($size) => [
                        'id' => $size->id,
                        'size_name' => $size->size_name,
                        'size_value' => $size->size_value,
                    ])
                ];
            })
        ], 200);
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
            'customer:id,name,phone_number,email',
            'customer.defaultAddress',
        ]);

        return response()->json([
            'message' => 'Seller order retrieved successfully',
            'data' => $order,
        ]);
    }

    public function myOrderDetail($id)
    {
        $userId = auth()->id();
        $customer = auth()->user();

        if (! $userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $order = Order::with([
            'sellerOrders.seller:id,name,shop_name,cover_image',
            'sellerOrders.items.product' => function ($query) {
                $query->select('id', 'name', 'discount_price', 'selling_price', 'quantity')
                    ->with(['images:id,product_id,path']);
            },
        ])
            ->where('user_id', $userId)
            ->find($id);

        if (! $order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        return response()->json([
            'message' => 'Order detail fetched successfully.',
            'order' => [
                'id' => $order->id,
                'order_code' => $order->order_code,
                'created_at' => $order->created_at->toDateTimeString(),
                'is_multi_seller' => $order->is_multi_seller,
                'delivery_model' => $order->deliveryModel->name ?? null,
                'delivery_fee' => $order->delivery_fee,
                'product_cost' => $order->product_cost,
                'vat' => $order->vat,
                'platform_fee' => $order->platform_fee,
                'grand_total' => $order->grand_total,
                'address' => $customer->defaultAddress,

                'sellers' => $order->sellerOrders->map(function ($sellerOrder) {
                    return [
                        'seller_id' => $sellerOrder->seller->id,
                        'seller_name' => $sellerOrder->seller->shop_name ?? $sellerOrder->seller->name,
                        'status' => $sellerOrder->status,
                        'delivery_fee' => $sellerOrder->delivery_fee,
                        'product_cost' => $sellerOrder->product_cost,
                        'vat' => $sellerOrder->vat,
                        'items' => $sellerOrder->items->map(function ($item) {
                            $product = $item->product;

                            return [
                                'product_id' => $product->id,
                                'product_name' => $product->name ?? '',
                                'quantity' => $item->quantity,
                                'size' => $item->size,
                                'total_cost' => $item->total_cost,
                                'unit_price' => $product->discount_price ?? $product->selling_price,
                                'images' => $product->images->map(function ($img) {
                                    return asset('upload/'.$img->path);
                                }),
                            ];
                        }),
                    ];
                }),
            ],
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

        $deliveryModel = DeliveryModel::query()->find($delivery_model);
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
