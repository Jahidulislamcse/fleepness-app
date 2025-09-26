<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $fee = Fee::first(); 
        return view('admin.fees.form', compact('fee'));
    }

    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'vat' => 'required|numeric',
            'platform_fee' => 'required|numeric',
            'commission' => 'required|numeric',
        ]);

        $fee = Fee::first();
        if ($fee) {
            $fee->update($validated);
        } else {
            $fee = Fee::create($validated);
        }

        return redirect()->back()->with('success', 'Fees saved successfully!');
    }
}
