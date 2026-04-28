<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(\App\Models\Product::with('category')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
        ]);

        $product = \App\Models\Product::create($validated);
        return response()->json($product, 201);
    }

    public function show(\App\Models\Product $product)
    {
        return response()->json($product->load('category'));
    }

    public function update(Request $request, \App\Models\Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'price' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    public function destroy(\App\Models\Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
