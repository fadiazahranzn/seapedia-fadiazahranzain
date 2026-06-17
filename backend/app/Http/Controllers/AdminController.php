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
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminController extends Controller
{
    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    public function dashboard(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'admin') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Parse period filter — metrics that are time-sensitive use this range,
        // totals (users, stores, products) always reflect all-time counts.
        $from = $request->filled('from') ? Carbon::parse($request->from)->startOfDay() : null;
        $to   = $request->filled('to')   ? Carbon::parse($request->to)->endOfDay()     : null;

        $filterOrder = function ($query) use ($from, $to) {
            if ($from) $query->where('created_at', '>=', $from);
            if ($to)   $query->where('created_at', '<=', $to);
        };

        $overdueOrders = $this->getOverdueOrders();

        return response()->json([
            'data' => [
                // All-time counts — not filtered by period
                'users'    => [
                    'total'   => User::count(),
                    'buyers'  => User::whereHas('roles', fn($q) => $q->where('role', 'buyer'))->count(),
                    'sellers' => User::whereHas('roles', fn($q) => $q->where('role', 'seller'))->count(),
                    'drivers' => User::whereHas('roles', fn($q) => $q->where('role', 'driver'))->count(),
                ],
                'stores'   => ['total' => Store::count()],
                'products' => [
                    'total'        => Product::count(),
                    'out_of_stock' => Product::where('stock', 0)->count(),
                ],

                // Period-filtered order stats
                'orders'   => [
                    'total'             => Order::where($filterOrder)->count(),
                    'sedang_dikemas'    => Order::where('status', 'sedang_dikemas')->where($filterOrder)->count(),
                    'menunggu_pengirim' => Order::where('status', 'menunggu_pengirim')->where($filterOrder)->count(),
                    'sedang_dikirim'    => Order::where('status', 'sedang_dikirim')->where($filterOrder)->count(),
                    'pesanan_selesai'   => Order::where('status', 'pesanan_selesai')->where($filterOrder)->count(),
                    'dikembalikan'      => Order::where('status', 'dikembalikan')->where($filterOrder)->count(),
                ],

                // Voucher & promo — filtered by created_at for new ones in period
                'vouchers' => [
                    'total'   => Voucher::where($filterOrder)->count(),
                    'active'  => Voucher::where($filterOrder)->where('expires_at', '>', now())->where('used_count', '<', \DB::raw('usage_limit'))->count(),
                    'expired' => Voucher::where($filterOrder)->where('expires_at', '<=', now())->count(),
                    'list'    => Voucher::where($filterOrder)->orderByDesc('used_count')->limit(5)->get(),
                ],
                'promos'   => [
                    'total'   => Promo::where($filterOrder)->count(),
                    'active'  => Promo::where($filterOrder)->where('expires_at', '>', now())->count(),
                    'expired' => Promo::where($filterOrder)->where('expires_at', '<=', now())->count(),
                ],

                'deliveries' => [
                    'total'     => Delivery::where($filterOrder)->count(),
                    'available' => Delivery::where('status', 'available')->where($filterOrder)->count(),
                    'taken'     => Delivery::where('status', 'taken')->where($filterOrder)->count(),
                    'completed' => Delivery::where('status', 'completed')->where($filterOrder)->count(),
                ],
                'overdue'  => [
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
