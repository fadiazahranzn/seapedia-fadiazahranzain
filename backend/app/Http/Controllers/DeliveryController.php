<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DeliveryController extends Controller
{
    // Earning = 80% dari delivery fee
    const EARNING_RATE = 0.8;

    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    // Driver: lihat semua job tersedia (status menunggu_pengirim)
    public function availableJobs(): JsonResponse
    {
        if ($this->activeRole() !== 'driver') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $jobs = Delivery::where('status', 'available')
            ->with([
                'order' => fn($q) => $q->with(['store:id,name', 'items']),
            ])
            ->latest()
            ->get();

        return response()->json(['data' => $jobs]);
    }

    // Driver: detail job
    public function showJob(Delivery $delivery): JsonResponse
    {
        if ($this->activeRole() !== 'driver') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'data' => $delivery->load(['order.store', 'order.items', 'order.statusHistories']),
        ]);
    }

    // Driver: ambil job
    public function takeJob(Request $request, Delivery $delivery): JsonResponse
    {
        if ($this->activeRole() !== 'driver') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($delivery->status !== 'available') {
            return response()->json(['message' => 'Job ini sudah diambil driver lain.'], 422);
        }

        $user = $request->user();

        DB::transaction(function () use ($delivery, $user) {
            $delivery->update([
                'driver_user_id' => $user->id,
                'status'         => 'taken',
                'picked_up_at'   => now(),
            ]);

            $delivery->order->update(['status' => 'sedang_dikirim']);

            OrderStatusHistory::create([
                'order_id'   => $delivery->order_id,
                'status'     => 'sedang_dikirim',
                'note'       => 'Pesanan diambil oleh driver.',
                'created_at' => now(),
            ]);
        });

        // Notif ke buyer
        NotificationService::send(
            $delivery->order->user_id,
            'order_picked',
            'Pesanan Sedang Dikirim',
            "Pesanan #{$delivery->order_id} sudah diambil kurir dan dalam perjalanan ke kamu.",
            "/buyer/orders/{$delivery->order_id}"
        );

        return response()->json([
            'message' => 'Job berhasil diambil!',
            'data'    => $delivery->fresh(['order.store', 'order.statusHistories']),
        ]);
    }

    // Driver: konfirmasi selesai
    public function completeJob(Request $request, Delivery $delivery): JsonResponse
    {
        if ($this->activeRole() !== 'driver') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($delivery->driver_user_id !== $request->user()->id) {
            return response()->json(['message' => 'Bukan job Anda.'], 403);
        }

        if ($delivery->status !== 'taken') {
            return response()->json(['message' => 'Job tidak dalam status pengiriman.'], 422);
        }

        $earning = round($delivery->order->delivery_fee * self::EARNING_RATE);

        DB::transaction(function () use ($delivery, $earning) {
            $delivery->update([
                'status'       => 'completed',
                'delivered_at' => now(),
                'earning'      => $earning,
            ]);

            $delivery->order->update(['status' => 'pesanan_selesai']);

            OrderStatusHistory::create([
                'order_id'   => $delivery->order_id,
                'status'     => 'pesanan_selesai',
                'note'       => 'Pesanan telah diterima oleh pembeli.',
                'created_at' => now(),
            ]);
        });

        // Notif ke buyer
        NotificationService::send(
            $delivery->order->user_id,
            'order_completed',
            'Pesanan Telah Tiba!',
            "Pesanan #{$delivery->order_id} sudah diterima. Terima kasih sudah belanja di SEAPEDIA!",
            "/buyer/orders/{$delivery->order_id}"
        );

        // Notif ke seller
        $delivery->order->load('store');
        if ($delivery->order->store?->user_id) {
            NotificationService::send(
                $delivery->order->store->user_id,
                'order_completed',
                'Pesanan Selesai',
                "Pesanan #{$delivery->order_id} telah berhasil diterima pembeli.",
                "/seller/orders"
            );
        }

        return response()->json([
            'message' => 'Pengiriman selesai!',
            'data'    => $delivery->fresh(['order.store', 'order.statusHistories']),
        ]);
    }

    // Driver: job aktif saya
    public function myActiveJob(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'driver') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $job = Delivery::where('driver_user_id', $request->user()->id)
            ->where('status', 'taken')
            ->with(['order.store', 'order.items', 'order.statusHistories'])
            ->first();

        return response()->json(['data' => $job]);
    }

    // Driver: riwayat job & earning
    public function myJobs(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'driver') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $jobs = Delivery::where('driver_user_id', $request->user()->id)
            ->with(['order.store', 'order.items'])
            ->latest()
            ->get();

        $totalEarning    = $jobs->where('status', 'completed')->sum('earning');
        $completedCount  = $jobs->where('status', 'completed')->count();
        $activeJob       = $jobs->firstWhere('status', 'taken');

        return response()->json([
            'data' => [
                'summary' => [
                    'total_jobs'      => $jobs->count(),
                    'completed_jobs'  => $completedCount,
                    'total_earning'   => $totalEarning,
                    'earning_rate'    => (self::EARNING_RATE * 100) . '% dari delivery fee',
                ],
                'active_job' => $activeJob,
                'jobs'       => $jobs,
            ],
        ]);
    }

    // Buyer/Seller: tracking pengiriman
    public function trackOrder(Request $request, Order $order): JsonResponse
    {
        $role = $this->activeRole();

        if (!in_array($role, ['buyer', 'seller'])) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Buyer hanya bisa track order miliknya
        if ($role === 'buyer' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        // Seller hanya bisa track order tokonya
        if ($role === 'seller') {
            $store = $request->user()->store;
            if (!$store || $order->store_id !== $store->id) {
                return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
            }
        }

        $delivery = $order->delivery()->with('driver:id,name')->first();

        return response()->json([
            'data' => [
                'order_status'    => $order->status,
                'status_histories' => $order->statusHistories()->orderBy('created_at')->get(),
                'delivery'        => $delivery,
            ],
        ]);
    }
}
