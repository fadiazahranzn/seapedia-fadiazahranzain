<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class SellerProductController extends Controller
{
    private function sellerStore(Request $request)
    {
        return $request->user()->store;
    }

    private function ensureSeller(Request $request): ?JsonResponse
    {
        if (JWTAuth::parseToken()->getPayload()->get('active_role') !== 'seller') {
            return response()->json(['message' => 'Hanya Seller yang bisa mengakses ini.'], 403);
        }
        if (! $this->sellerStore($request)) {
            return response()->json(['message' => 'Buat toko terlebih dahulu.'], 422);
        }
        return null;
    }

    public function index(Request $request): JsonResponse
    {
        if ($err = $this->ensureSeller($request)) return $err;

        $products = $this->sellerStore($request)->products()->latest()->paginate(20);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        if ($err = $this->ensureSeller($request)) return $err;

        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'category'       => 'nullable|string|max:100',
            'brand'          => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:5000',
            'price'          => 'required|integer|min:1',
            'stock'          => 'required|integer|min:0',
            'weight'         => 'nullable|numeric|min:0',
            'image_url'      => 'nullable|url|max:500',
            'specifications' => 'nullable|array',
        ]);

        $product = $this->sellerStore($request)->products()->create($data);

        return response()->json(['message' => 'Produk berhasil ditambahkan.', 'data' => $product], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        if ($err = $this->ensureSeller($request)) return $err;

        if ($product->store_id !== $this->sellerStore($request)->id) {
            return response()->json(['message' => 'Produk bukan milik toko kamu.'], 403);
        }

        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'category'       => 'nullable|string|max:100',
            'brand'          => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:5000',
            'price'          => 'required|integer|min:1',
            'stock'          => 'required|integer|min:0',
            'weight'         => 'nullable|numeric|min:0',
            'image_url'      => 'nullable|url|max:500',
            'specifications' => 'nullable|array',
        ]);

        $product->update($data);

        return response()->json(['message' => 'Produk berhasil diperbarui.', 'data' => $product]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        if ($err = $this->ensureSeller($request)) return $err;

        if ($product->store_id !== $this->sellerStore($request)->id) {
            return response()->json(['message' => 'Produk bukan milik toko kamu.'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus.']);
    }
}
