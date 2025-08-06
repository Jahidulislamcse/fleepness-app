<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\UserPayment;
use Illuminate\Validation\ValidationException;

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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateSeller(Request $request)
    {
        try {
            $user = Auth::user(); // Get the logged-in user

            // Validate the input
            $validatedData = $request->validate([
                'name' => 'nullable|string|max:255',
                'shop_name' => 'nullable|string|max:255',
                'shop_category' => 'nullable|integer',
                'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
                'phone_number' => 'nullable|string|max:15',
                'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'pickup_location' => 'nullable|string',
                'address' => 'nullable|string',
                'description' => 'nullable|string',
                'payments' => 'nullable|array',
                'payments.*' => 'nullable|string|max:20',
            ]);

            // Handle payment methods update
            if (!empty($validatedData['payments'])) {
                $user->payments()->delete(); // Clear previous methods

                foreach ($validatedData['payments'] as $method => $number) {
                    if ($number) {
                        $user->payments()->create([
                            'user_id' => $user->id,
                            'payment_method_id' => ucfirst($method),
                            'account_number' => $number,
                        ]);
                    }
                }
            }

            unset($validatedData['payments']);

            // Handle banner image upload
            if ($request->hasFile('banner_image')) {
                $banner_image = $request->file('banner_image');
                $name_gen = hexdec(uniqid()) . '.' . $banner_image->getClientOriginalExtension();
                $path = public_path('upload/user');

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $banner_image->move($path, $name_gen);
                $validatedData['banner_image'] = 'upload/user/' . $name_gen;
            }

            // Handle cover image upload
            if ($request->hasFile('cover_image')) {
                $cover_image = $request->file('cover_image');
                $name_gen = hexdec(uniqid()) . '.' . $cover_image->getClientOriginalExtension();
                $path = public_path('upload/user');

                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $cover_image->move($path, $name_gen);
                $validatedData['cover_image'] = 'upload/user/' . $name_gen;
            }

            // Update the user
            $user->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->load('payments'),
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Log the error for debugging (optional)
            Log::error('Seller update error', ['error' => $e->getMessage()]);

            // Other unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

    public function getPaymentAccounts()
    {
        $user = auth()->user()->load('payments.paymentMethod');

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
        $user = auth()->user();

        // Delete old payment accounts
        UserPayment::where('user_id', $user->id)->delete();

        // Insert updated payment accounts
        foreach ($validated['payments'] as $payment_method_id => $account_number) {
            if ($account_number) {
                UserPayment::create([
                    'user_id' => $user->id,
                    'payment_method_id' => $payment_method_id,
                    'account_number' => $account_number,
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
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:15',
            'contact_number' => 'nullable|string|max:15',
        ]);

        // Update the user's profile
        $user->update($validatedData);

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
