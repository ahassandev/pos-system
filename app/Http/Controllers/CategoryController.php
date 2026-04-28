<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(\App\Models\Category::withCount('products')->latest()->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        $category = \App\Models\Category::create($validated);
        return response()->json($category, 201);
    }

    public function show(\App\Models\Category $category)
    {
        return response()->json($category->load('products'));
    }

    public function update(Request $request, \App\Models\Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ]);

        $category->update($validated);
        return response()->json($category);
    }

    public function destroy(\App\Models\Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
