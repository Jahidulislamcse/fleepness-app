<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\DeliveryModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CartController extends Controller
{
    use AuthorizesRequests;

    public function addOrUpdate(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer',
            'size_id' => 'nullable|exists:size_template_items,id', 
        ]);

        $user = Auth::user();
        $product = Product::findOrFail($request->product_id);

        if ($request->quantity <= 0) {
            return response()->json(['message' => 'Quantity must be greater than zero.'], 400);
        }

        if ($request->quantity > $product->quantity) {
            return response()->json(['message' => 'Quantity exceeds available stock.'], 400);
        }

        // Add size_id to the unique constraint for updateOrCreate to handle different sizes separately
        $cartItem = CartItem::updateOrCreate(
            [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'size_id' => $request->size_id,
            ],
            [
                'quantity' => $request->quantity,
                'selected' => true,
            ]
        );

        return response()->json([
            'message' => 'Cart item created or updated',
            'cart_item' => [
                'id' => $cartItem->id,
                'product_id' => $cartItem->product_id,
                'size_id' => $cartItem->size_id,
                'quantity' => $cartItem->quantity,
            ]
        ]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // Load size relationship
        $cartItems = CartItem::with(['product.firstImage', 'size'])->where('user_id', $user->id)->get();

        return response()->json([
            'cart_items' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'image_url' => $item->product->image_url,
                        'description' => $item->product->short_description,
                    ],
                    'size' => $item->size ? [
                        'id' => $item->size->id,
                        // add other size fields as needed
                        'name' => $item->size->name ?? null,
                    ] : null,
                    'quantity' => $item->quantity,
                ];
            }),
        ]);
    }

    public function destroy($id)
    {
        $authUserId = Auth::id();
        $item = CartItem::findOrFail($id);
        if ($authUserId !== $item->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $item->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    public function summary(Request $request)
    {
        $user = Auth::user();
        $deliveryModelId = $request->query('delivery_model_id', 1); // default to Express id = 1
        $deliveryModel = DeliveryModel::find($deliveryModelId) ?? DeliveryModel::find(1);

        $selectedItems = CartItem::with('product')
            ->where('user_id', $user->id)
            ->where('selected', true)
            ->get();

        $itemTotal = $selectedItems->sum(function ($item) {
            $price = $item->product->discount_price ?? $item->product->selling_price;
            return $price * $item->quantity;
        });

        $platformFee = 30;  // example fixed charge
        $vatFee = 15;       // example fixed VAT charge
        $deliveryFee = $deliveryModel->fee ?? 0;

        $grandTotal = $itemTotal + $platformFee + $vatFee + $deliveryFee;

        return response()->json([
            'item_total' => $itemTotal,
            'delivery_fee' => $deliveryFee,
            'platform_fee' => $platformFee,
            'vat_fee' => $vatFee,
            'grand_total' => $grandTotal,
        ]);
    }
}
