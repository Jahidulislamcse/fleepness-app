<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude ' => 'nullable|numeric',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'formatted_address' => 'nullable|string|max:500',
        ]);

        $address = Address::create([
            'user_id' => $request->user()->id,
            'label' => $request->label,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'street' => $request->street,
            'country' => 'Bangladesh',
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'formatted_address' => $request->formatted_address,
        ]);

        return response()->json($address, 201);
    }

    public function index(Request $request)
    {
        $addresses = Address::where('user_id', Auth::user()->id)->get();

        return response()->json(['addresses' => $addresses]);
    }

    public function update(Request $request, $address_id)
    {
        $address = Address::findOrFail($address_id);
        if (auth()->id() !== $address->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->only([
            'label',
            'street',
            'latitude',
            'longitude',
            'city',
            'postal_code',
            'formatted_address'
        ]);

        $address->update($data);

        return response()->json($address);
    }
}
