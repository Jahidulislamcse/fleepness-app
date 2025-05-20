<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryModel;

class DeliveryModelController extends Controller
{
    public function index()
    {
        $models = DeliveryModel::all();
        return view('admin.delivery_models.index', compact('models'));
    }

    // User: List delivery models
    public function userIndex()
    {
        $models = DeliveryModel::all();

        return response()->json(['models' => $models]);
    }

    public function create()
    {
        return view('admin.delivery_models.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'minutes' => 'required|integer|min:1',
            'fee' => 'required|numeric|min:0',
        ]);

        DeliveryModel::create($request->only('name', 'minutes', 'fee'));

        return redirect()->route('admin.delivery.models.index')->with('success', 'Delivery model created.');
    }

    public function edit(DeliveryModel $model)
    {
        return view('admin.delivery_models.edit', compact('model'));
    }

    public function update(Request $request, DeliveryModel $model)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'minutes' => 'required|integer|min:1',
            'fee' => 'required|numeric|min:0',
        ]);

        $model->update($request->only('name', 'minutes', 'fee'));

        return redirect()->route('admin.delivery.models.index')->with('success', 'Delivery model updated.');
    }

    public function destroy(DeliveryModel $model)
    {
        $model->delete();
        return redirect()->route('admin.delivery.models.index')->with('success', 'Delivery model deleted.');
    }
}
