<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Promo;
use App\Models\SellerTransaction;
use App\Models\Voucher;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class OrderController extends Controller
{
    const DELIVERY_FEES = [
        'instant'  => 25000,
        'next_day' => 15000,
        'regular'  => 10000,
    ];

    private function activeRole(): string
    {
        return JWTAuth::parseToken()->getPayload()->get('active_role');
    }

    private function calculateDiscount(float $subtotal, ?Voucher $voucher, ?Promo $promo): array
    {
        $voucherDiscount = 0;
        $promoDiscount   = 0;

        if ($voucher) {
            if ($voucher->min_purchase && $subtotal < $voucher->min_purchase) {
                return ['error' => "Minimum pembelian untuk voucher ini adalah Rp " . number_format($voucher->min_purchase, 0, ',', '.')];
            }
            $voucherDiscount = $voucher->discount_type === 'percentage'
                ? $subtotal * ($voucher->discount_value / 100)
                : $voucher->discount_value;

            if ($voucher->max_discount) {
                $voucherDiscount = min($voucherDiscount, $voucher->max_discount);
            }
        }

        if ($promo) {
            if ($promo->min_purchase && $subtotal < $promo->min_purchase) {
                return ['error' => "Minimum pembelian untuk promo ini adalah Rp " . number_format($promo->min_purchase, 0, ',', '.')];
            }
            $promoDiscount = $promo->discount_type === 'percentage'
                ? $subtotal * ($promo->discount_value / 100)
                : $promo->discount_value;

            if ($promo->max_discount) {
                $promoDiscount = min($promoDiscount, $promo->max_discount);
            }
        }

        return [
            'voucher_discount' => round($voucherDiscount),
            'promo_discount'   => round($promoDiscount),
            'total_discount'   => round($voucherDiscount + $promoDiscount),
        ];
    }

    public function previewCheckout(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $data = $request->validate([
            'delivery_method' => 'required|in:instant,next_day,regular',
            'voucher_code'    => 'nullable|string',
            'promo_code'      => 'nullable|string',
        ]);

        $cart = $request->user()->cart()->with('items.product')->first();
        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong.'], 422);
        }

        $subtotal    = $cart->items->sum(fn($i) => $i->product->price * $i->quantity);
        $deliveryFee = self::DELIVERY_FEES[$data['delivery_method']];

        $voucher = null;
        $promo   = null;

        if (! empty($data['voucher_code'])) {
            $voucher = Voucher::where('code', strtoupper($data['voucher_code']))->first();
            if (! $voucher || ! $voucher->isValid()) {
                return response()->json(['message' => 'Voucher tidak valid atau sudah kadaluarsa.'], 422);
            }
        }

        if (! empty($data['promo_code'])) {
            $promo = Promo::where('code', strtoupper($data['promo_code']))->first();
            if (! $promo || ! $promo->isValid()) {
                return response()->json(['message' => 'Promo tidak valid atau sudah kadaluarsa.'], 422);
            }
        }

        $discountResult = $this->calculateDiscount($subtotal, $voucher, $promo);
        if (isset($discountResult['error'])) {
            return response()->json(['message' => $discountResult['error']], 422);
        }

        $discountAmount = $discountResult['total_discount'];
        $taxBase        = $subtotal + $deliveryFee - $discountAmount;
        $ppnAmount      = round($taxBase * 0.12);
        $total          = $taxBase + $ppnAmount;

        return response()->json([
            'data' => [
                'subtotal'         => $subtotal,
                'voucher_discount' => $discountResult['voucher_discount'],
                'promo_discount'   => $discountResult['promo_discount'],
                'total_discount'   => $discountAmount,
                'delivery_fee'     => $deliveryFee,
                'tax_base'         => $taxBase,
                'ppn_amount'       => $ppnAmount,
                'ppn_rate'         => '12%',
                'total'            => $total,
                'items'            => $cart->items->map(fn($i) => [
                    'product_name' => $i->product->name,
                    'quantity'     => $i->quantity,
                    'price'        => $i->product->price,
                    'subtotal'     => $i->product->price * $i->quantity,
                ]),
            ],
        ]);
    }

    public function checkout(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Hanya Buyer yang bisa checkout.'], 403);
        }

        $data = $request->validate([
            'address_id'      => 'required|exists:addresses,id',
            'delivery_method' => 'required|in:instant,next_day,regular',
            'voucher_code'    => 'nullable|string',
            'promo_code'      => 'nullable|string',
        ]);

        $user    = $request->user();
        $cart    = $user->cart()->with('items.product')->first();
        $wallet  = $user->wallet;
        $address = $user->addresses()->findOrFail($data['address_id']);

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong.'], 422);
        }

        if ($address->user_id !== $user->id) {
            return response()->json(['message' => 'Alamat tidak valid.'], 403);
        }

        // Validate voucher
        $voucher = null;
        if (! empty($data['voucher_code'])) {
            $voucher = Voucher::where('code', strtoupper($data['voucher_code']))->lockForUpdate()->first();
            if (! $voucher || ! $voucher->isValid()) {
                return response()->json(['message' => 'Voucher tidak valid atau sudah kadaluarsa.'], 422);
            }
        }

        // Validate promo
        $promo = null;
        if (! empty($data['promo_code'])) {
            $promo = Promo::where('code', strtoupper($data['promo_code']))->first();
            if (! $promo || ! $promo->isValid()) {
                return response()->json(['message' => 'Promo tidak valid atau sudah kadaluarsa.'], 422);
            }
        }

        $subtotal    = $cart->items->sum(fn($i) => $i->product->price * $i->quantity);
        $deliveryFee = self::DELIVERY_FEES[$data['delivery_method']];

        $discountResult = $this->calculateDiscount($subtotal, $voucher, $promo);
        if (isset($discountResult['error'])) {
            return response()->json(['message' => $discountResult['error']], 422);
        }

        $discountAmount = $discountResult['total_discount'];
        $taxBase        = $subtotal + $deliveryFee - $discountAmount;
        $ppnAmount      = round($taxBase * 0.12);
        $total          = $taxBase + $ppnAmount;

        if ($wallet->balance < $total) {
            return response()->json([
                'message'   => 'Saldo tidak mencukupi.',
                'balance'   => $wallet->balance,
                'total'     => $total,
                'shortfall' => $total - $wallet->balance,
            ], 422);
        }

        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Stok produk \"{$item->product->name}\" tidak mencukupi.",
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($user, $cart, $wallet, $address, $data, $voucher, $promo, $subtotal, $deliveryFee, $discountAmount, $ppnAmount, $total) {
            foreach ($cart->items as $item) {
                $item->product->decrement('stock', $item->quantity);
            }

            $wallet->decrement('balance', $total);
            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'type'        => 'payment',
                'amount'      => $total,
                'description' => 'Pembayaran pesanan',
                'created_at'  => now(),
            ]);

            if ($voucher) {
                $voucher->increment('used_count');
            }

            $order = Order::create([
                'user_id'          => $user->id,
                'store_id'         => $cart->store_id,
                'address_snapshot' => [
                    'label'          => $address->label,
                    'recipient_name' => $address->recipient_name,
                    'phone'          => $address->phone,
                    'full_address'   => $address->full_address,
                    'province'       => $address->province,
                    'city'           => $address->city,
                    'district'       => $address->district,
                    'postal_code'    => $address->postal_code,
                ],
                'voucher_id'      => $voucher?->id,
                'promo_id'        => $promo?->id,
                'delivery_method' => $data['delivery_method'],
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_fee'    => $deliveryFee,
                'ppn_amount'      => $ppnAmount,
                'total'           => $total,
                'status'          => 'sedang_dikemas',
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'    => $item->product_id,
                    'product_name'  => $item->product->name,
                    'product_price' => $item->product->price,
                    'quantity'      => $item->quantity,
                    'subtotal'      => $item->product->price * $item->quantity,
                ]);
            }

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'sedang_dikemas',
                'note'       => 'Pesanan dibuat.',
                'created_at' => now(),
            ]);

            $cart->items()->delete();
            $cart->update(['store_id' => null]);

            return $order;
        });

        return response()->json([
            'message' => 'Checkout berhasil!',
            'data'    => $order->load(['items', 'store', 'statusHistories', 'voucher', 'promo']),
        ], 201);
    }

    // Buyer: order list
    public function index(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $orders = $request->user()->orders()
            ->with(['store:id,name', 'items', 'voucher:id,code', 'promo:id,code'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    // Buyer: order detail
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => $order->load(['store', 'items.product', 'statusHistories', 'voucher', 'promo']),
        ]);
    }

    // Buyer: spending report
    public function buyerReport(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $user   = $request->user();
        $orders = $user->orders()->with(['store:id,name', 'items', 'statusHistories'])->latest()->get();

        $totalSpent     = $orders->where('status', 'pesanan_selesai')->sum('total');
        $totalOrders    = $orders->count();
        $completedCount = $orders->where('status', 'pesanan_selesai')->count();
        $pendingCount   = $orders->whereNotIn('status', ['pesanan_selesai', 'dikembalikan'])->count();

        return response()->json([
            'data' => [
                'summary' => [
                    'total_orders'    => $totalOrders,
                    'completed_orders'=> $completedCount,
                    'pending_orders'  => $pendingCount,
                    'total_spent'     => $totalSpent,
                ],
                'orders' => $orders,
            ],
        ]);
    }

    // Seller: incoming orders
    public function sellerOrders(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'seller') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $store = $request->user()->store;
        if (! $store) {
            return response()->json(['message' => 'Toko tidak ditemukan.'], 404);
        }

        $orders = $store->orders()
            ->with(['user:id,name,username', 'items', 'statusHistories', 'voucher:id,code', 'promo:id,code'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    // Seller: process order (sedang_dikemas -> menunggu_pengirim)
    public function processOrder(Request $request, Order $order): JsonResponse
    {
        if ($this->activeRole() !== 'seller') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $store = $request->user()->store;
        if (! $store || $order->store_id !== $store->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->status !== 'sedang_dikemas') {
            return response()->json(['message' => 'Pesanan hanya bisa diproses saat status Sedang Dikemas.'], 422);
        }

        DB::transaction(function () use ($order, $store) {
            $order->update(['status' => 'menunggu_pengirim']);

            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'menunggu_pengirim',
                'note'       => 'Pesanan diproses oleh seller.',
                'created_at' => now(),
            ]);

            // Record seller income
            SellerTransaction::firstOrCreate(
                ['order_id' => $order->id, 'type' => 'income'],
                [
                    'store_id'    => $store->id,
                    'amount'      => $order->subtotal - $order->discount_amount,
                    'description' => "Pendapatan dari pesanan #{$order->id}",
                ]
            );
        });

        return response()->json([
            'message' => 'Pesanan berhasil diproses.',
            'data'    => $order->fresh(['statusHistories']),
        ]);
    }

    // Seller: income report
    public function sellerReport(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'seller') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $store = $request->user()->store;
        if (! $store) {
            return response()->json(['message' => 'Toko tidak ditemukan.'], 404);
        }

        $orders = $store->orders()
            ->with(['user:id,name,username', 'items', 'statusHistories', 'voucher:id,code', 'promo:id,code'])
            ->latest()
            ->get();

        $transactions  = $store->sellerTransactions()->latest()->get();
        $totalIncome   = $transactions->where('type', 'income')->sum('amount');
        $totalReversal = $transactions->where('type', 'reversal')->sum('amount');
        $netIncome     = $totalIncome - $totalReversal;

        $processedCount  = $orders->whereNotIn('status', ['sedang_dikemas'])->count();
        $completedCount  = $orders->where('status', 'pesanan_selesai')->count();

        return response()->json([
            'data' => [
                'summary' => [
                    'total_orders'     => $orders->count(),
                    'processed_orders' => $processedCount,
                    'completed_orders' => $completedCount,
                    'total_income'     => $totalIncome,
                    'total_reversal'   => $totalReversal,
                    'net_income'       => $netIncome,
                ],
                'orders'       => $orders,
                'transactions' => $transactions,
            ],
        ]);
    }
}
