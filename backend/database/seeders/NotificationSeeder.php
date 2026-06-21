<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $buyer1  = User::where('email', 'buyer1@seapedia.com')->first();
        $buyer2  = User::where('email', 'buyer2@seapedia.com')->first();
        $seller1 = User::where('email', 'seller1@seapedia.com')->first();
        $seller2 = User::where('email', 'seller2@seapedia.com')->first();
        $driver1 = User::where('email', 'driver1@seapedia.com')->first();

        $data = [
            // Buyer 1
            ['user' => $buyer1, 'type' => 'order_created',   'title' => 'Pesanan Berhasil Dibuat',       'body' => 'Pesanan #1 sedang dikemas oleh penjual.',                      'link' => '/buyer/orders/1', 'menit' => 5],
            ['user' => $buyer1, 'type' => 'order_processed',  'title' => 'Pesanan Menunggu Kurir',         'body' => 'Pesanan #1 sudah selesai dikemas dan menunggu kurir.',          'link' => '/buyer/orders/1', 'menit' => 30],
            ['user' => $buyer1, 'type' => 'order_picked',     'title' => 'Pesanan Sedang Dikirim',         'body' => 'Pesanan #1 sudah diambil kurir dan dalam perjalanan ke kamu.',  'link' => '/buyer/orders/1', 'menit' => 60],
            ['user' => $buyer1, 'type' => 'order_completed',  'title' => 'Pesanan Telah Tiba!',            'body' => 'Pesanan #2 sudah diterima. Terima kasih sudah belanja!',         'link' => '/buyer/orders/2', 'menit' => 120],

            // Buyer 2
            ['user' => $buyer2, 'type' => 'order_created',   'title' => 'Pesanan Berhasil Dibuat',       'body' => 'Pesanan #3 sedang dikemas oleh penjual.',                      'link' => '/buyer/orders/3', 'menit' => 10],
            ['user' => $buyer2, 'type' => 'order_picked',    'title' => 'Pesanan Sedang Dikirim',        'body' => 'Pesanan #3 sudah diambil kurir dan dalam perjalanan ke kamu.',  'link' => '/buyer/orders/3', 'menit' => 45],

            // Seller 1
            ['user' => $seller1, 'type' => 'new_order',       'title' => 'Pesanan Baru Masuk!',           'body' => 'Ada pesanan baru #1 dari pembeli. Segera proses.',              'link' => '/seller/orders', 'menit' => 5],
            ['user' => $seller1, 'type' => 'new_order',       'title' => 'Pesanan Baru Masuk!',           'body' => 'Ada pesanan baru #2 dari pembeli. Segera proses.',              'link' => '/seller/orders', 'menit' => 20],
            ['user' => $seller1, 'type' => 'order_completed', 'title' => 'Pesanan Selesai',               'body' => 'Pesanan #2 telah berhasil diterima pembeli.',                   'link' => '/seller/orders', 'menit' => 120],

            // Seller 2
            ['user' => $seller2, 'type' => 'new_order',       'title' => 'Pesanan Baru Masuk!',           'body' => 'Ada pesanan baru #3 dari pembeli. Segera proses.',              'link' => '/seller/orders', 'menit' => 10],

            // Driver 1 — (tidak ada tipe khusus untuk driver, pakai info job)
            ['user' => $driver1, 'type' => 'new_order',       'title' => 'Job Baru Tersedia',             'body' => 'Ada pengiriman baru di area kamu. Cek sekarang!',               'link' => '/driver/jobs',   'menit' => 15],
        ];

        foreach ($data as $d) {
            if (!$d['user']) continue;
            Notification::create([
                'user_id'    => $d['user']->id,
                'type'       => $d['type'],
                'title'      => $d['title'],
                'body'       => $d['body'],
                'link'       => $d['link'],
                'is_read'    => false,
                'created_at' => now()->subMinutes($d['menit']),
                'updated_at' => now()->subMinutes($d['menit']),
            ]);
        }
    }
}
