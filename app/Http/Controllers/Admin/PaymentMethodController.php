<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::all();
        return view('admin.payment_methods.index', compact('methods'));
    }

    // Show the form to create a new payment method
    public function create()
    {
        return view('admin.payment_methods.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'is_active' => 'required|boolean',  // Add validation for status
        ]);

        $path = null;
        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $filename = uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('upload/payment_icons'), $filename);
            $path = 'upload/payment_icons/' . $filename;
        }

        PaymentMethod::create([
            'name' => $validated['name'],
            'icon' => $path,
            'is_active' => $validated['is_active'], // Set from validated input
        ]);

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method added.');
    }


    public function edit($id)
    {
        $method = PaymentMethod::findOrFail($id);
        return view('admin.payment_methods.edit', compact('method'));
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name,' . $method->id,
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
            'is_active' => 'required|boolean',  // Add validation for status
        ]);

        $method->name = $validated['name'];

        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $filename = uniqid() . '.' . $icon->getClientOriginalExtension();
            $icon->move(public_path('upload/payment_icons'), $filename);
            $method->icon = 'upload/payment_icons/' . $filename;
        }

        $method->is_active = $validated['is_active'];  // Update status from input

        $method->save();

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method updated.');
    }


    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete();

        return redirect()->back()->with('success', 'Payment method deleted.');
    }

    public function toggleStatus($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->is_active = !$method->is_active;
        $method->save();

        return redirect()->back()->with('success', 'Payment method status updated.');
    }
}
