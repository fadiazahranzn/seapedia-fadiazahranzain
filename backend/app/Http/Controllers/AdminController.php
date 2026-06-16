<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promo;
use App\Models\Store;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    public function dashboard(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $overdueOrders = $this->getOverdueOrders();

        return response()->json([
            'data' => [
                'users'    => [
                    'total'   => User::count(),
                    'buyers'  => User::whereHas('roles', fn($q) => $q->where('role', 'buyer'))->count(),
                    'sellers' => User::whereHas('roles', fn($q) => $q->where('role', 'seller'))->count(),
                    'drivers' => User::whereHas('roles', fn($q) => $q->where('role', 'driver'))->count(),
                ],
                'stores'    => [
                    'total' => Store::count(),
                ],
                'products'  => [
                    'total'        => Product::count(),
                    'out_of_stock' => Product::where('stock', 0)->count(),
                ],
                'orders'    => [
                    'total'           => Order::count(),
                    'sedang_dikemas'  => Order::where('status', 'sedang_dikemas')->count(),
                    'menunggu_pengirim' => Order::where('status', 'menunggu_pengirim')->count(),
                    'sedang_dikirim'  => Order::where('status', 'sedang_dikirim')->count(),
                    'pesanan_selesai' => Order::where('status', 'pesanan_selesai')->count(),
                    'dikembalikan'    => Order::where('status', 'dikembalikan')->count(),
                ],
                'vouchers'  => [
                    'total'   => Voucher::count(),
                    'active'  => Voucher::where('expires_at', '>', now())->where('used_count', '<', \DB::raw('usage_limit'))->count(),
                    'expired' => Voucher::where('expires_at', '<=', now())->count(),
                ],
                'promos'    => [
                    'total'   => Promo::count(),
                    'active'  => Promo::where('expires_at', '>', now())->count(),
                    'expired' => Promo::where('expires_at', '<=', now())->count(),
                ],
                'deliveries' => [
                    'total'     => Delivery::count(),
                    'available' => Delivery::where('status', 'available')->count(),
                    'taken'     => Delivery::where('status', 'taken')->count(),
                    'completed' => Delivery::where('status', 'completed')->count(),
                ],
                'overdue'   => [
                    'total' => count($overdueOrders),
                ],
            ],
        ]);
    }

    public function users(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $users = User::with('roles')->latest()->paginate(20);
        return response()->json($users);
    }

    public function stores(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $stores = Store::with('user:id,name,username')->withCount('products')->latest()->paginate(20);
        return response()->json($stores);
    }

    public function products(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $products = Product::with('store:id,name')->latest()->paginate(20);
        return response()->json($products);
    }

    public function orders(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $orders = Order::with(['user:id,name,username', 'store:id,name', 'items'])
            ->latest()->paginate(20);
        return response()->json($orders);
    }

    public function deliveries(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $deliveries = Delivery::with(['order.store', 'driver:id,name,username'])
            ->latest()->paginate(20);
        return response()->json($deliveries);
    }

    public function overdueOrders(): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        return response()->json(['data' => $this->getOverdueOrders()]);
    }

    private function getOverdueOrders(): array
    {
        $sla = ['instant' => 1, 'next_day' => 2, 'regular' => 5];

        return Order::whereIn('status', ['menunggu_pengirim', 'sedang_dikirim'])
            ->get()
            ->filter(function (Order $order) use ($sla) {
                $days = $sla[$order->delivery_method] ?? 5;
                return $order->created_at->addDays($days)->isPast();
            })
            ->values()
            ->toArray();
    }
}
