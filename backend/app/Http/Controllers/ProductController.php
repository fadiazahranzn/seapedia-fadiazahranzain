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
            'sort'     => 'nullable|in:terbaru,harga_asc,harga_desc',
        ]);

        $sort = $request->sort ?? 'terbaru';

        $query = Product::with('store:id,name,description,slug')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category, fn($q) => $q->where('category', $request->category))
            ->when($request->store_id, fn($q) => $q->where('store_id', $request->store_id))
            ->when($sort === 'harga_asc',  fn($q) => $q->orderBy('price', 'asc'))
            ->when($sort === 'harga_desc', fn($q) => $q->orderBy('price', 'desc'))
            ->when($sort === 'terbaru',    fn($q) => $q->latest());

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
