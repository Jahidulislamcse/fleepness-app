<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserProfileController extends Controller
{
    /**
     * Show the logged-in user's profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $user = Auth::user(); // Get the logged-in user data

        // Return the user profile data as JSON
        return response()->json([
            'success' => true,
            'user' => $user
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

        $user = Auth::user(); // Get the logged-in user

        // Validate the input
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'shop_name' => 'nullable|string|max:255',
            'shop_category' => 'nullable|integer',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:15',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Validate image
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Validate image
            'pickup_location' => 'nullable|string',
            'address' => 'nullable|string',
            'contact_number' => 'nullable|string|max:15',
            'description' => 'nullable|string',
            'payment_bkash' => 'nullable|boolean',
            'payment_nagad' => 'nullable|boolean',
            'payment_number' => 'nullable|string|max:20',
        ]);

        // Handle file uploads if any
        if ($request->hasFile('banner_image')) {
            $banner_image = $request->file('banner_image');
            $name_gen = hexdec(uniqid()) . '.' . $banner_image->getClientOriginalExtension();
            $path = public_path('upload/user');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Move the file to the server
            $banner_image->move($path, $name_gen);

            // Update the user's banner_image field
            $validatedData['banner_image'] = 'upload/user/' . $name_gen;
        }

        if ($request->hasFile('cover_image')) {
            $cover_image = $request->file('cover_image');
            $name_gen = hexdec(uniqid()) . '.' . $cover_image->getClientOriginalExtension();
            $path = public_path('upload/user');

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Move the file to the server
            $cover_image->move($path, $name_gen);

            // Update the user's cover_image field
            $validatedData['cover_image'] = 'upload/user/' . $name_gen;
        }

        // Update the user's profile
        $user->update($validatedData);

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
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
            'address' => 'nullable|string',
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
