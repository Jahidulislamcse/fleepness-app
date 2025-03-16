<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VendorReview;
use Illuminate\Http\Request;

class UserVendorReviewController extends Controller
{
    // Store a new review
    public function store(Request $request, $vendor_id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $vendor = User::findOrFail($vendor_id);

        $review = VendorReview::create([
            'vendor_id' => $vendor->id,
            'user_id' => auth()->id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review,
        ], 201);
    }

    // Fetch reviews for a vendor
    public function index($vendor_id)
    {
        $vendor = User::findOrFail($vendor_id);
        $reviews = VendorReview::where('vendor_id', $vendor->id)->get();

        return response()->json([
            'vendor' => $vendor,
            'reviews' => $reviews,
        ]);
    }
}
