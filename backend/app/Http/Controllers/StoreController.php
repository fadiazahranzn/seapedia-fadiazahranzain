<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class StoreController extends Controller
{
    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    public function show(Request $request): JsonResponse
    {
        $store = $request->user()->store()->with('products')->first();

        if (! $store) {
            return response()->json(['message' => 'Toko belum dibuat.'], 404);
        }

        return response()->json(['data' => $store]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'seller') {
            return response()->json(['message' => 'Hanya Seller yang bisa membuat toko.'], 403);
        }

        if ($request->user()->store) {
            return response()->json(['message' => 'Kamu sudah memiliki toko.'], 422);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:stores,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $store = Store::create([
            'user_id'     => $request->user()->id,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['message' => 'Toko berhasil dibuat.', 'data' => $store], 201);
    }

    public function update(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'seller') {
            return response()->json(['message' => 'Hanya Seller yang bisa mengupdate toko.'], 403);
        }

        $store = $request->user()->store;

        if (! $store) {
            return response()->json(['message' => 'Toko tidak ditemukan.'], 404);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:stores,name,' . $store->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $store->update($data);

        return response()->json(['message' => 'Toko berhasil diperbarui.', 'data' => $store]);
    }

    public function publicShow(Store $store): JsonResponse
    {
        return response()->json([
            'data' => $store->load(['products' => fn($q) => $q->latest()->take(8)]),
        ]);
    }
}
