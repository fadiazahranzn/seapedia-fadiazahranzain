<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
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

    public function checkout(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Hanya Buyer yang bisa checkout.'], 403);
        }

        $data = $request->validate([
            'address_id'      => 'required|exists:addresses,id',
            'delivery_method' => 'required|in:instant,next_day,regular',
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

        // Calculate subtotal
        $subtotal = $cart->items->sum(fn($item) => $item->product->price * $item->quantity);
        $deliveryFee = self::DELIVERY_FEES[$data['delivery_method']];
        $discountAmount = 0;
        $ppnAmount = round(($subtotal + $deliveryFee - $discountAmount) * 0.12);
        $total = $subtotal + $deliveryFee - $discountAmount + $ppnAmount;

        if ($wallet->balance < $total) {
            return response()->json([
                'message'   => 'Saldo tidak mencukupi.',
                'balance'   => $wallet->balance,
                'total'     => $total,
                'shortfall' => $total - $wallet->balance,
            ], 422);
        }

        // Check stock
        foreach ($cart->items as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'message' => "Stok produk \"{$item->product->name}\" tidak mencukupi.",
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($user, $cart, $wallet, $address, $data, $subtotal, $deliveryFee, $discountAmount, $ppnAmount, $total) {
            // Reduce stock
            foreach ($cart->items as $item) {
                $item->product->decrement('stock', $item->quantity);
            }

            // Deduct wallet
            $wallet->decrement('balance', $total);
            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'type'        => 'payment',
                'amount'      => $total,
                'description' => 'Pembayaran pesanan',
                'created_at'  => now(),
            ]);

            // Create order
            $order = Order::create([
                'user_id'         => $user->id,
                'store_id'        => $cart->store_id,
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
                'delivery_method' => $data['delivery_method'],
                'subtotal'        => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_fee'    => $deliveryFee,
                'ppn_amount'      => $ppnAmount,
                'total'           => $total,
                'status'          => 'sedang_dikemas',
            ]);

            // Order items
            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id'    => $item->product_id,
                    'product_name'  => $item->product->name,
                    'product_price' => $item->product->price,
                    'quantity'      => $item->quantity,
                    'subtotal'      => $item->product->price * $item->quantity,
                ]);
            }

            // Status history
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => 'sedang_dikemas',
                'note'       => 'Pesanan dibuat.',
                'created_at' => now(),
            ]);

            // Clear cart
            $cart->items()->delete();
            $cart->update(['store_id' => null]);

            return $order;
        });

        return response()->json([
            'message' => 'Checkout berhasil!',
            'data'    => $order->load(['items', 'store', 'statusHistories']),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $orders = $request->user()->orders()
            ->with(['store:id,name', 'items'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        return response()->json([
            'data' => $order->load(['store', 'items.product', 'statusHistories']),
        ]);
    }

    // Preview checkout summary (before confirming)
    public function previewCheckout(Request $request): JsonResponse
    {
        if ($this->activeRole() !== 'buyer') {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        $data = $request->validate([
            'delivery_method' => 'required|in:instant,next_day,regular',
        ]);

        $cart = $request->user()->cart()->with('items.product')->first();

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Keranjang kosong.'], 422);
        }

        $subtotal    = $cart->items->sum(fn($item) => $item->product->price * $item->quantity);
        $deliveryFee = self::DELIVERY_FEES[$data['delivery_method']];
        $ppnAmount   = round(($subtotal + $deliveryFee) * 0.12);
        $total       = $subtotal + $deliveryFee + $ppnAmount;

        return response()->json([
            'data' => [
                'subtotal'     => $subtotal,
                'delivery_fee' => $deliveryFee,
                'ppn_amount'   => $ppnAmount,
                'ppn_rate'     => '12%',
                'total'        => $total,
                'items'        => $cart->items->map(fn($i) => [
                    'product_name' => $i->product->name,
                    'quantity'     => $i->quantity,
                    'subtotal'     => $i->product->price * $i->quantity,
                ]),
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
            ->with(['user:id,name,username', 'items'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }
}
