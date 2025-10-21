<?php

namespace App\Http\Controllers\user;

use App\Models\User;
use App\Models\UserPayment;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\Follower;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{

    public function show()
    {
        $user = Auth::user()->load('payments.paymentMethod');
        $productCount = Product::where('user_id', $user->id)->count();
        $followerCount = Follower::where('vendor_id', $user->id)->count();

        $formattedPayments = $user->payments->map(function ($payment) {
            return [
                'payment_method' => $payment->paymentMethod->name, 
                'account_number' => $payment->account_number, 
            ];
        });

        return response()->json([
            'success' => true,
            'user' => $user->makeHidden(['payments']), 
            'product_count' => $productCount,
            'follower_count' => $followerCount ,
            'payments' => $formattedPayments, 
        ]);
    }


    public function updateSeller(Request $request)
    {
        $user = Auth::user(); 

        $validatedData = $request->validate([
            'address' => 'nullable|string',
            'payments' => 'nullable|array',
            'description' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'shop_category' => 'nullable|integer',
            'pickup_location' => 'nullable|string',
            'payments.*' => 'nullable|string|max:20',
            'shop_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255|unique:users,email,'.$user->id,
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if (! empty($validatedData['payments'])) {
            $user->payments()->delete(); 

            foreach ($validatedData['payments'] as $method => $number) {
                if ($number) {
                    $user->payments()->create([
                        'user_id' => $user->getKey(),
                        'account_number' => $number,
                        'payment_method_id' => ucfirst($method),
                    ]);
                }
            }
        }

        unset($validatedData['payments']);

        if ($request->hasFile('banner_image')) {
            $bannerImage = $request->file('banner_image');

            $validatedData['banner_image'] = $bannerImage->store('upload/user');
        }

        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $validatedData['cover_image'] = $coverImage->store('upload/user');
        }

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user->load('payments'),
        ], 200);
    }

    public function getPaymentAccounts()
    {
        $user = Auth::user()->load('payments.paymentMethod');

        $formattedPayments = $user->payments->map(function ($payment) {
            return [
                'payment_method' => $payment->paymentMethod->name, 
                'account_number' => $payment->account_number, 
            ];
        });

        return response()->json([
            'payments' => $formattedPayments,
        ]);
    }

    public function getPaymenMethods()
    {
        $methods = PaymentMethod::all();

        return response()->json([
            'methods' => $methods,
        ]);
    }

    public function updatePaymentAccounts(Request $request)
    {
        $validated = $request->validate([
            'payments' => 'required|array', 
            'payments.*' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        UserPayment::where('user_id', $user->getKey())->delete();

        foreach ($validated['payments'] as $payment_method_id => $account_number) {
            if ($account_number) {
                UserPayment::create([
                    'user_id' => $user->getKey(),
                    'account_number' => $account_number,
                    'payment_method_id' => $payment_method_id,
                ]);
            }
        }

        return response()->json([
            'message' => 'Payment accounts updated successfully',
            'payments' => $user->payments()->get(), 
        ]);
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user(); 

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'contact_number' => 'nullable|string|max:15',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user->getKey())],
        ]);

        if ($request->hasFile('banner_image')) {
            $bannerImage = $request->file('banner_image');

            $validatedData['banner_image'] = $bannerImage->store('upload/user');
        }

        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $validatedData['cover_image'] = $coverImage->store('upload/user');
        }

        $user->update($validatedData);

        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Profile updated successfully',
        ]);
    }
}
