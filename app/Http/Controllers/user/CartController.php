<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Validation\Rule;
use App\Models\DeliveryModel;
use App\Models\ProductSize;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CartController extends Controller
{
    use AuthorizesRequests;

    public function addOrUpdate(Request $request)
    {
        try {
            $productId = $request->input('product_id');
            $hasSizes  = $productId ? ProductSize::where('product_id', $productId)->exists() : false;

            $sizeRules = [
                Rule::exists('product_sizes', 'id')->where(fn ($q) =>
                    $q->where('product_id', $productId)
                ),
            ];
            array_unshift($sizeRules, $hasSizes ? 'required' : 'nullable');

            $validated = $request->validate([
                'product_id' => ['required', 'exists:products,id'],
                'quantity'   => ['required', 'integer', 'min:1'],
                'size_id'    => $sizeRules,
            ], [
                'size_id.required' => 'Please select a size for this product.',
            ]);

            $user = Auth::user();
            $product = Product::findOrFail($request->product_id);

            // Custom quantity checks
            if ($request->quantity <= 0) {
                return response()->json(['message' => 'Quantity must be greater than zero.'], 400);
            }

            if ($request->quantity > $product->quantity) {
                return response()->json(['message' => 'Quantity exceeds available stock.'], 400);
            }

            // Create or update cart item
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

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $cartItems = CartItem::with(['product.firstImage', 'size'])->where('user_id', $user->id)->get();

        return response()->json([
            'cart_items' => $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->discount_price ?? $item->product->selling_price,
                        'image_url' => $item->product->image_url,
                        'description' => $item->product->short_description,
                    ],
                    'size' => $item->size_id ? [
                        'id' => $item->size->id,
                        'name' => $item->size->size_name,
                        'value' => $item->size->size_value,
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
