<?php

namespace Database\Seeders;

use App\Models\Promo;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        Voucher::firstOrCreate(['code' => 'SEAPEDIA10'], [
            'description'    => 'Diskon 10% untuk semua pembelian',
            'discount_type'  => 'percentage',
            'discount_value' => 10,
            'min_purchase'   => 50000,
            'max_discount'   => 50000,
            'usage_limit'    => 100,
            'used_count'     => 0,
            'expires_at'     => now()->addYear(),
        ]);

        Voucher::firstOrCreate(['code' => 'HEMAT20K'], [
            'description'    => 'Potongan Rp 20.000',
            'discount_type'  => 'fixed',
            'discount_value' => 20000,
            'min_purchase'   => 100000,
            'max_discount'   => null,
            'usage_limit'    => 50,
            'used_count'     => 0,
            'expires_at'     => now()->addMonths(6),
        ]);

        Promo::firstOrCreate(['code' => 'FLASHSALE'], [
            'description'    => 'Flash sale diskon 15%',
            'discount_type'  => 'percentage',
            'discount_value' => 15,
            'min_purchase'   => null,
            'max_discount'   => 75000,
            'expires_at'     => now()->addMonths(3),
        ]);

        Promo::firstOrCreate(['code' => 'GRATIS5K'], [
            'description'    => 'Potongan Rp 5.000 tanpa minimum',
            'discount_type'  => 'fixed',
            'discount_value' => 5000,
            'min_purchase'   => null,
            'max_discount'   => null,
            'expires_at'     => now()->addMonths(2),
        ]);
    }
}
