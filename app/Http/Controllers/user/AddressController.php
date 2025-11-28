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
            'longitude' => 'nullable|numeric',
            'address_text' => 'required|string|max:500',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'formatted_address' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        $exists = Address::where('user_id', auth()->id())->exists();

        if ($request->is_default || !$exists) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
            $defaultValue = true;
        } else {
            $defaultValue = false;
        }

        $address = Address::create([
            'user_id'            => auth()->id(),
            'label'              => $request->label,
            'latitude'           => $request->latitude,
            'longitude'          => $request->longitude,
            'address_text'       => $request->address_text,
            'address_line_1'     => $request->address_line_1,
            'address_line_2'     => $request->address_line_2,
            'area'               => $request->area,
            'city'               => $request->city,
            'postal_code'        => $request->postal_code,
            'formatted_address'  => $request->formatted_address,
            'is_default'         => $defaultValue,
        ]);

        return response()->json($address, 201);
    }


    public function index(Request $request)
    {
        $addresses = Address::where('user_id', Auth::user()->id)->get();
        return response()->json(['addresses' => $addresses]);
    }

    public function setDefault($id)
    {
        $address = Address::findOrFail($id);

        if (auth()->id() !== $address->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Address::where('user_id', auth()->id())
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'message' => 'Default address updated successfully.',
            'address' => $address
        ]);
    }

    public function getDefault()
    {
        $address = Address::where('user_id', auth()->id())
            ->where('is_default', true)
            ->first();

        return response()->json([
            'default_address' => $address
        ]);
    }


    public function update(Request $request, $address_id)
    {
        $address = Address::findOrFail($address_id);

        if (auth()->id() !== $address->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'label' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'address_text' => 'nullable|string|max:500',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'formatted_address' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->is_default) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json($address);
    }
}
