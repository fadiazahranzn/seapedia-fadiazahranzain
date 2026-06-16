<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class WalletController extends Controller
{
    private function ensureBuyer(): ?JsonResponse
    {
        if (JWTAuth::parseToken()->getPayload()->get('active_role') !== 'buyer') {
            return response()->json(['message' => 'Hanya Buyer yang bisa mengakses dompet.'], 403);
        }
        return null;
    }

    public function show(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $wallet = $request->user()->wallet;
        if (! $wallet) return response()->json(['message' => 'Wallet tidak ditemukan.'], 404);

        return response()->json(['data' => $wallet]);
    }

    public function topUp(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $data = $request->validate([
            'amount' => 'required|integer|min:10000|max:10000000',
        ]);

        $wallet = $request->user()->wallet;

        DB::transaction(function () use ($wallet, $data) {
            $wallet->increment('balance', $data['amount']);
            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'type'        => 'topup',
                'amount'      => $data['amount'],
                'description' => 'Top-up saldo',
                'created_at'  => now(),
            ]);
        });

        $wallet->refresh();

        return response()->json([
            'message' => 'Top-up berhasil.',
            'data'    => ['balance' => $wallet->balance],
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        if ($err = $this->ensureBuyer()) return $err;

        $wallet = $request->user()->wallet;
        $transactions = $wallet->transactions()->orderByDesc('created_at')->paginate(20);

        return response()->json($transactions);
    }
}
