<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class VoucherController extends Controller
{
    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    // Public: validate a voucher code
    public function validate(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string']);

        $voucher = Voucher::where('code', strtoupper($data['code']))->first();

        if (! $voucher) {
            return response()->json(['message' => 'Voucher tidak ditemukan.'], 404);
        }

        if (! $voucher->isValid()) {
            $reason = $voucher->expires_at->isPast() ? 'Voucher sudah kadaluarsa.' : 'Voucher sudah habis digunakan.';
            return response()->json(['message' => $reason, 'valid' => false], 422);
        }

        return response()->json(['valid' => true, 'data' => $voucher]);
    }

    // Admin: list all vouchers
    public function index(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json(['data' => Voucher::latest()->get()]);
    }

    // Admin: show single voucher
    public function show(Voucher $voucher): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json(['data' => $voucher]);
    }

    // Admin: create voucher
    public function store(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $data = $request->validate([
            'code'           => 'required|string|max:50|unique:vouchers,code',
            'description'    => 'nullable|string|max:255',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_purchase'   => 'nullable|numeric|min:0',
            'max_discount'   => 'nullable|numeric|min:0',
            'usage_limit'    => 'required|integer|min:1',
            'expires_at'     => 'required|date|after:now',
        ]);

        $data['code'] = strtoupper($data['code']);
        $voucher = Voucher::create($data);

        return response()->json(['message' => 'Voucher berhasil dibuat.', 'data' => $voucher], 201);
    }

    // Admin: delete voucher
    public function destroy(Voucher $voucher): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $voucher->delete();
        return response()->json(['message' => 'Voucher dihapus.']);
    }
}
