<?php

namespace App\Http\Controllers\user;

use App\Models\User;
use App\Models\UserPayment;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    /**
     * Show the logged-in user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $user = Auth::user()->load('payments.paymentMethod');

        // Format payments to show only the payment method name
        $formattedPayments = $user->payments->map(function ($payment) {
            return [
                'payment_method' => $payment->paymentMethod->name, // Show only the name
                'account_number' => $payment->account_number, // Optional, if you want to show account number
            ];
        });

        // Return the user profile data along with the formatted payments
        return response()->json([
            'success' => true,
            'user' => $user->makeHidden(['payments']), // Hide payments from user object
            'payments' => $formattedPayments, // Show only payment method name here
        ]);
    }

    /**
     * Update the logged-in user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSeller(Request $request)
    {
        $user = Auth::user(); // Get the logged-in user

        // Validate the input
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

        // Handle payment methods update
        if (! empty($validatedData['payments'])) {
            $user->payments()->delete(); // Clear previous methods

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

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $bannerImage = $request->file('banner_image');

            $validatedData['banner_image'] = $bannerImage->store('upload/user');
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $validatedData['cover_image'] = $coverImage->store('upload/user');
        }

        // Update the user
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

        // Format payments to show only the payment method name
        $formattedPayments = $user->payments->map(function ($payment) {
            return [
                'payment_method' => $payment->paymentMethod->name, // Show only the name
                'account_number' => $payment->account_number, // Optional, if you want to show account number
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
        // Validate input
        $validated = $request->validate([
            'payments' => 'required|array', // e.g. ['payment_method_id' => 'account_number', ...]
            'payments.*' => 'nullable|string|max:20',
        ]);

        // Get authenticated user
        $user = Auth::user();

        // Delete old payment accounts
        UserPayment::where('user_id', $user->getKey())->delete();

        // Insert updated payment accounts
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
            'payments' => $user->payments()->get(), // Return updated payment accounts
        ]);
    }

    public function updateUser(Request $request)
    {
        $user = Auth::user(); // Get the logged-in user

        // Validate the input
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:15',
            'contact_number' => 'nullable|string|max:15',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => ['nullable', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($user->getKey())],
        ]);

        // Handle banner image upload
        if ($request->hasFile('banner_image')) {
            $bannerImage = $request->file('banner_image');

            $validatedData['banner_image'] = $bannerImage->store('upload/user');
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $coverImage = $request->file('cover_image');
            $validatedData['cover_image'] = $coverImage->store('upload/user');
        }

        // Update the user's profile
        $user->update($validatedData);

        // Return success response
        return response()->json([
            'success' => true,
            'user' => $user,
            'message' => 'Profile updated successfully',
        ]);
    }
}
