<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\SellerTransaction;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Promo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    private function makeAddressSnapshot(User $user): array
    {
        $addr = $user->addresses()->where('is_default', true)->first()
            ?? $user->addresses()->first();

        return $addr ? [
            'label'          => $addr->label,
            'recipient_name' => $addr->recipient_name,
            'phone'          => $addr->phone,
            'full_address'   => $addr->full_address,
            'province'       => $addr->province,
            'city'           => $addr->city,
            'district'       => $addr->district,
            'postal_code'    => $addr->postal_code,
        ] : [];
    }

    private function deliveryFee(string $method): float
    {
        return match ($method) {
            'instant'  => 25000,
            'next_day' => 15000,
            'regular'  => 9000,
        };
    }

    private function createOrder(
        User   $buyer,
        int    $storeId,
        array  $items,         // [['product' => Product, 'qty' => int], ...]
        string $status,
        string $deliveryMethod,
        Carbon $createdAt,
        ?Voucher $voucher = null,
        ?Promo   $promo   = null,
    ): Order {
        $subtotal = collect($items)->sum(fn($i) => $i['product']->price * $i['qty']);
        $discount = 0;

        if ($voucher) {
            $discount = $voucher->discount_type === 'percentage'
                ? min($subtotal * $voucher->discount_value / 100, $voucher->max_discount ?? PHP_INT_MAX)
                : $voucher->discount_value;
        } elseif ($promo) {
            $discount = $promo->discount_type === 'percentage'
                ? min($subtotal * $promo->discount_value / 100, $promo->max_discount ?? PHP_INT_MAX)
                : $promo->discount_value;
        }

        $fee   = $this->deliveryFee($deliveryMethod);
        $ppn   = ($subtotal - $discount) * 0.11;
        $total = $subtotal - $discount + $fee + $ppn;

        $order = Order::create([
            'user_id'         => $buyer->id,
            'store_id'        => $storeId,
            'address_snapshot'=> $this->makeAddressSnapshot($buyer),
            'voucher_id'      => $voucher?->id,
            'promo_id'        => $promo?->id,
            'delivery_method' => $deliveryMethod,
            'subtotal'        => $subtotal,
            'discount_amount' => $discount,
            'delivery_fee'    => $fee,
            'ppn_amount'      => round($ppn, 2),
            'total'           => round($total, 2),
            'status'          => $status,
            'created_at'      => $createdAt,
            'updated_at'      => $createdAt,
        ]);

        foreach ($items as $item) {
            OrderItem::create([
                'order_id'      => $order->id,
                'product_id'    => $item['product']->id,
                'product_name'  => $item['product']->name,
                'product_price' => $item['product']->price,
                'quantity'      => $item['qty'],
                'subtotal'      => $item['product']->price * $item['qty'],
                'created_at'    => $createdAt,
            ]);
        }

        // Status history
        $statusFlow = ['sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim', 'pesanan_selesai'];
        $dikembalikan = $status === 'dikembalikan';

        $steps = $dikembalikan
            ? ['sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim', 'dikembalikan']
            : array_slice($statusFlow, 0, array_search($status, $statusFlow) + 1);

        foreach ($steps as $i => $s) {
            OrderStatusHistory::create([
                'order_id'   => $order->id,
                'status'     => $s,
                'note'       => null,
                'created_at' => $createdAt->copy()->addHours($i * 4),
            ]);
        }

        // Delivery record
        $needsDelivery = in_array($status, ['menunggu_pengirim', 'sedang_dikirim', 'pesanan_selesai', 'dikembalikan']);
        if ($needsDelivery) {
            $driver    = User::whereHas('roles', fn($q) => $q->where('role', 'driver'))->inRandomOrder()->first();
            $driverFee = round($fee * 0.7, 2);

            $deliveryStatus = match ($status) {
                'menunggu_pengirim' => 'available',
                'sedang_dikirim'    => 'taken',
                default             => 'completed',
            };

            Delivery::create([
                'order_id'      => $order->id,
                'driver_user_id'=> in_array($deliveryStatus, ['taken', 'completed']) ? $driver?->id : null,
                'status'        => $deliveryStatus,
                'picked_up_at'  => $deliveryStatus !== 'available' ? $createdAt->copy()->addHours(8) : null,
                'delivered_at'  => $deliveryStatus === 'completed' ? $createdAt->copy()->addHours(12) : null,
                'earning'       => $deliveryStatus === 'completed' ? $driverFee : null,
                'created_at'    => $createdAt->copy()->addHours(4),
                'updated_at'    => $createdAt->copy()->addHours(8),
            ]);
        }

        // Seller transaction for completed orders
        if ($status === 'pesanan_selesai') {
            SellerTransaction::create([
                'store_id'    => $storeId,
                'order_id'    => $order->id,
                'type'        => 'income',
                'amount'      => round($subtotal - $discount, 2),
                'description' => 'Pendapatan dari order #' . $order->id,
                'created_at'  => $createdAt->copy()->addHours(13),
            ]);
        }

        if ($status === 'dikembalikan') {
            SellerTransaction::create([
                'store_id'    => $storeId,
                'order_id'    => $order->id,
                'type'        => 'reversal',
                'amount'      => round($subtotal - $discount, 2),
                'description' => 'Reversal dari order #' . $order->id,
                'created_at'  => $createdAt->copy()->addHours(24),
            ]);
        }

        return $order;
    }

    public function run(): void
    {
        $buyer1 = User::where('username', 'buyer1')->first();
        $buyer2 = User::where('username', 'buyer2')->first();

        // Ambil produk per store secara dinamis
        $store1 = \App\Models\Store::whereHas('user', fn($q) => $q->where('username', 'seller1'))->first();
        $store2 = \App\Models\Store::whereHas('user', fn($q) => $q->where('username', 'seller2'))->first();
        $store3 = \App\Models\Store::whereHas('user', fn($q) => $q->where('username', 'multiuser'))->first();

        $store1Id = $store1->id;
        $store2Id = $store2->id;
        $store3Id = $store3->id;

        // Produk per store (ambil semua, nanti pilih acak)
        $p1 = Product::where('store_id', $store1Id)->get(); // Elektronik
        $p2 = Product::where('store_id', $store2Id)->get(); // Fashion
        $p3 = Product::where('store_id', $store3Id)->get(); // Serba Ada

        $voucher = Voucher::where('code', 'SEAPEDIA10')->first();
        $promo   = Promo::where('code', 'FLASHSALE')->first();

        $now = Carbon::now();

        // Helper: ambil satu produk acak dari koleksi
        $pick = fn($col) => $col->random();

        // Rotasi kondisi untuk 30 hari — semua 5 status terwakili
        // Pola per 5 hari: dikemas, menunggu, dikirim, selesai, dikembalikan
        $statuses = [
            'sedang_dikemas', 'menunggu_pengirim', 'sedang_dikirim', 'pesanan_selesai', 'dikembalikan',
            'pesanan_selesai', 'pesanan_selesai', 'sedang_dikirim', 'pesanan_selesai', 'sedang_dikemas',
            'dikembalikan', 'pesanan_selesai', 'menunggu_pengirim', 'pesanan_selesai', 'sedang_dikirim',
            'pesanan_selesai', 'dikembalikan', 'sedang_dikemas', 'pesanan_selesai', 'menunggu_pengirim',
            'pesanan_selesai', 'sedang_dikirim', 'pesanan_selesai', 'dikembalikan', 'sedang_dikemas',
            'pesanan_selesai', 'menunggu_pengirim', 'sedang_dikirim', 'pesanan_selesai', 'sedang_dikemas',
        ];

        $methods  = ['regular', 'next_day', 'instant'];
        $discounts = [null, $voucher, null, $promo, null, $voucher, null, null, $promo, null];

        // =====================================================================
        // BUYER 1 — 30 orders (10 dari store1, 10 dari store2, 10 dari store3)
        // =====================================================================
        for ($i = 0; $i < 30; $i++) {
            $day    = 29 - $i;
            $date   = $now->copy()->subDays($day);
            $status = $statuses[$i];
            $method = $methods[$i % 3];
            $disc   = $discounts[$i % 10];

            if ($i < 10) {
                $storeId = $store1Id;
                $prod1   = $pick($p1);
                $items   = $i % 3 === 0
                    ? [['product' => $pick($p1), 'qty' => 1], ['product' => $pick($p1), 'qty' => rand(1, 2)]]
                    : [['product' => $pick($p1), 'qty' => rand(1, 2)]];
            } elseif ($i < 20) {
                $storeId = $store2Id;
                $items   = $i % 4 === 0
                    ? [['product' => $pick($p2), 'qty' => rand(1, 3)], ['product' => $pick($p2), 'qty' => rand(1, 2)]]
                    : [['product' => $pick($p2), 'qty' => rand(1, 4)]];
            } else {
                $storeId = $store3Id;
                $items   = $i % 5 === 0
                    ? [['product' => $pick($p3), 'qty' => rand(1, 2)], ['product' => $pick($p3), 'qty' => 1]]
                    : [['product' => $pick($p3), 'qty' => rand(1, 3)]];
            }

            $useVoucher = ($disc instanceof \App\Models\Voucher) ? $disc : null;
            $usePromo   = ($disc instanceof \App\Models\Promo)   ? $disc : null;

            $this->createOrder($buyer1, $storeId, $items, $status, $method, $date, $useVoucher, $usePromo);
        }

        // =====================================================================
        // BUYER 2 — 30 orders (rotasi store berbeda dari buyer1)
        // =====================================================================
        $statuses2 = [
            'pesanan_selesai', 'sedang_dikemas', 'dikembalikan', 'menunggu_pengirim', 'sedang_dikirim',
            'pesanan_selesai', 'pesanan_selesai', 'dikembalikan', 'sedang_dikirim', 'pesanan_selesai',
            'sedang_dikemas', 'menunggu_pengirim', 'pesanan_selesai', 'dikembalikan', 'sedang_dikirim',
            'pesanan_selesai', 'sedang_dikemas', 'pesanan_selesai', 'menunggu_pengirim', 'dikembalikan',
            'pesanan_selesai', 'sedang_dikirim', 'pesanan_selesai', 'sedang_dikemas', 'sedang_dikirim',
            'pesanan_selesai', 'dikembalikan', 'menunggu_pengirim', 'sedang_dikirim', 'sedang_dikemas',
        ];

        for ($i = 0; $i < 30; $i++) {
            $day    = 29 - $i;
            $date   = $now->copy()->subDays($day);
            $status = $statuses2[$i];
            $method = $methods[(($i + 1) % 3)];
            $disc   = $discounts[($i + 3) % 10];

            if ($i < 10) {
                $storeId = $store2Id;
                $items   = $i % 3 === 0
                    ? [['product' => $pick($p2), 'qty' => rand(1, 3)], ['product' => $pick($p2), 'qty' => rand(1, 2)]]
                    : [['product' => $pick($p2), 'qty' => rand(1, 4)]];
            } elseif ($i < 20) {
                $storeId = $store1Id;
                $items   = $i % 4 === 0
                    ? [['product' => $pick($p1), 'qty' => 1], ['product' => $pick($p1), 'qty' => rand(1, 2)]]
                    : [['product' => $pick($p1), 'qty' => rand(1, 2)]];
            } else {
                $storeId = $store3Id;
                $items   = $i % 5 === 0
                    ? [['product' => $pick($p3), 'qty' => rand(1, 2)], ['product' => $pick($p3), 'qty' => 1]]
                    : [['product' => $pick($p3), 'qty' => rand(1, 3)]];
            }

            $useVoucher = ($disc instanceof \App\Models\Voucher) ? $disc : null;
            $usePromo   = ($disc instanceof \App\Models\Promo)   ? $disc : null;

            $this->createOrder($buyer2, $storeId, $items, $status, $method, $date, $useVoucher, $usePromo);
        }
    }
}
