<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(\App\Models\Customer::latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $customer = \App\Models\Customer::create($validated);
        return response()->json($customer, 201);
    }

    public function show(\App\Models\Customer $customer)
    {
        return response()->json($customer->load('orders.items.product'));
    }

    public function update(Request $request, \App\Models\Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $customer->update($validated);
        return response()->json($customer);
    }

    public function destroy(\App\Models\Customer $customer)
    {
        $customer->delete();
        return response()->json(null, 204);
    }
}
