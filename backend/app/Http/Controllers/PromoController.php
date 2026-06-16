<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class PromoController extends Controller
{
    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    // Public: validate a promo code
    public function validate(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string']);

        $promo = Promo::where('code', strtoupper($data['code']))->first();

        if (! $promo) {
            return response()->json(['message' => 'Promo tidak ditemukan.'], 404);
        }

        if (! $promo->isValid()) {
            return response()->json(['message' => 'Promo sudah kadaluarsa.', 'valid' => false], 422);
        }

        return response()->json(['valid' => true, 'data' => $promo]);
    }

    // Admin: list all promos
    public function index(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json(['data' => Promo::latest()->get()]);
    }

    // Admin: show single promo
    public function show(Promo $promo): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json(['data' => $promo]);
    }

    // Admin: create promo
    public function store(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:promos,code',
            'description'    => 'nullable|string|max:255',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase'   => 'nullable|numeric|min:0',
            'max_discount'   => 'nullable|numeric|min:0',
            'expires_at'     => 'required|date|after:now',
        ]);

        $data['code'] = strtoupper($data['code']);
        $promo = Promo::create($data);

        return response()->json(['message' => 'Promo berhasil dibuat.', 'data' => $promo], 201);
    }

    // Admin: delete promo
    public function destroy(Promo $promo): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $promo->delete();
        return response()->json(['message' => 'Promo dihapus.']);
    }
}
