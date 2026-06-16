<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\SellerTransaction;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class OverdueController extends Controller
{
    // SLA dalam hari per metode pengiriman
    const SLA_DAYS = [
        'instant'  => 1,
        'next_day' => 2,
        'regular'  => 5,
    ];

    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    // Admin: proses semua order overdue
    public function processOverdue(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $overdueOrders = Order::whereIn('status', ['menunggu_pengirim', 'sedang_dikirim'])
            ->where('status', '!=', 'dikembalikan')
            ->get()
            ->filter(function (Order $order) {
                $days = self::SLA_DAYS[$order->delivery_method] ?? 5;
                return $order->created_at->addDays($days)->isPast();
            });

        $processed = [];
        $skipped   = [];

        foreach ($overdueOrders as $order) {
            DB::transaction(function () use ($order, &$processed) {
                // Update order status
                $order->update(['status' => 'dikembalikan']);

                // Status history
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'status'     => 'dikembalikan',
                    'note'       => 'Pesanan dikembalikan otomatis karena melewati batas waktu pengiriman.',
                    'created_at' => now(),
                ]);

                // Refund ke wallet buyer
                $wallet = $order->user->wallet;
                if ($wallet) {
                    $wallet->increment('balance', $order->total);
                    WalletTransaction::create([
                        'wallet_id'   => $wallet->id,
                        'type'        => 'refund',
                        'amount'      => $order->total,
                        'description' => "Refund pesanan #{$order->id} (overdue)",
                        'created_at'  => now(),
                    ]);
                }

                // Reversal income seller (cegah double)
                $existingIncome = SellerTransaction::where('order_id', $order->id)
                    ->where('type', 'income')
                    ->first();

                if ($existingIncome) {
                    $existingReversal = SellerTransaction::where('order_id', $order->id)
                        ->where('type', 'reversal')
                        ->first();

                    if (!$existingReversal) {
                        SellerTransaction::create([
                            'store_id'    => $order->store_id,
                            'order_id'    => $order->id,
                            'type'        => 'reversal',
                            'amount'      => $existingIncome->amount,
                            'description' => "Reversal pendapatan pesanan #{$order->id} (overdue)",
                            'created_at'  => now(),
                        ]);
                    }
                }

                // Restore stok produk
                foreach ($order->items as $item) {
                    $item->product()->increment('stock', $item->quantity);
                }

                // Update delivery jika ada
                $delivery = $order->delivery;
                if ($delivery && $delivery->status !== 'completed') {
                    $delivery->update(['status' => 'completed', 'delivered_at' => now()]);
                }

                $processed[] = [
                    'order_id'        => $order->id,
                    'delivery_method' => $order->delivery_method,
                    'refunded'        => $order->total,
                ];
            });
        }

        return response()->json([
            'message'   => count($processed) . ' pesanan overdue berhasil diproses.',
            'processed' => $processed,
            'skipped'   => $skipped,
        ]);
    }

    // Admin: simulasi hari berikutnya (mundurkan created_at semua order aktif 1 hari)
    public function simulateNextDay(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $affected = Order::whereIn('status', ['menunggu_pengirim', 'sedang_dikirim'])
            ->update(['created_at' => DB::raw('created_at - INTERVAL 1 DAY')]);

        return response()->json([
            'message'  => "Simulasi hari berikutnya berhasil. {$affected} pesanan aktif dimundurkan 1 hari.",
            'affected' => $affected,
        ]);
    }
}
