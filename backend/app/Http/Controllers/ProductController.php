<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search'   => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'store_id' => 'nullable|integer|exists:stores,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Product::with('store:id,name,description,slug')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
            ->latest();

        $perPage = (int) ($request->per_page ?? 12);
        $products = $query->paginate($perPage);

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => $product->load('store:id,name,description,slug'),
        ]);
    }
}
