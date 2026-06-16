<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'username' => 'admin',
            'email'    => 'admin@seapedia.com',
            'password' => Hash::make('admin123'),
            'name'     => 'Administrator',
        ]);
        UserRole::create(['user_id' => $admin->id, 'role' => 'admin']);

        // Seller 1
        $seller1 = User::create([
            'username' => 'seller1',
            'email'    => 'seller1@seapedia.com',
            'password' => Hash::make('seller123'),
            'name'     => 'Seller Satu',
            'phone'    => '081111111111',
        ]);
        UserRole::create(['user_id' => $seller1->id, 'role' => 'seller']);
        $store1 = Store::create([
            'user_id'     => $seller1->id,
            'name'        => 'Toko Elektronik Makmur',
            'description' => 'Jual berbagai produk elektronik berkualitas',
        ]);
        Product::create([
            'store_id' => $store1->id,
            'name'     => 'Laptop Gaming XYZ',
            'category' => 'Electronics',
            'brand'    => 'XYZ Brand',
            'price'    => 12000000,
            'stock'    => 10,
            'weight'   => 2000,
            'specifications' => ['RAM' => '16GB', 'Storage' => '512GB', 'Color' => 'Black'],
        ]);
        Product::create([
            'store_id' => $store1->id,
            'name'     => 'Smartphone Pro Max',
            'category' => 'Electronics',
            'brand'    => 'ProMax',
            'price'    => 5500000,
            'stock'    => 25,
            'weight'   => 200,
            'specifications' => ['RAM' => '8GB', 'Storage' => '256GB', 'Color' => 'Silver'],
        ]);

        // Seller 2
        $seller2 = User::create([
            'username' => 'seller2',
            'email'    => 'seller2@seapedia.com',
            'password' => Hash::make('seller123'),
            'name'     => 'Seller Dua',
            'phone'    => '082222222222',
        ]);
        UserRole::create(['user_id' => $seller2->id, 'role' => 'seller']);
        $store2 = Store::create([
            'user_id'     => $seller2->id,
            'name'        => 'Fashion Keren Store',
            'description' => 'Pakaian trendy dan berkualitas',
        ]);
        Product::create([
            'store_id' => $store2->id,
            'name'     => 'Kaos Polos Premium',
            'category' => 'Fashion',
            'brand'    => 'BasicWear',
            'price'    => 150000,
            'stock'    => 100,
            'weight'   => 200,
            'specifications' => ['Material' => 'Cotton', 'Size' => 'M', 'Color' => 'Navy'],
        ]);
        Product::create([
            'store_id' => $store2->id,
            'name'     => 'Celana Chino Slim Fit',
            'category' => 'Fashion',
            'brand'    => 'SlimFit',
            'price'    => 275000,
            'stock'    => 50,
            'weight'   => 400,
            'specifications' => ['Material' => 'Cotton Blend', 'Size' => '32', 'Color' => 'Khaki'],
        ]);

        // Buyer 1
        $buyer1 = User::create([
            'username' => 'buyer1',
            'email'    => 'buyer1@seapedia.com',
            'password' => Hash::make('buyer123'),
            'name'     => 'Buyer Satu',
            'phone'    => '083333333333',
        ]);
        UserRole::create(['user_id' => $buyer1->id, 'role' => 'buyer']);
        Wallet::create(['user_id' => $buyer1->id, 'balance' => 500000]);
        Cart::create(['user_id' => $buyer1->id]);
        Address::create([
            'user_id'        => $buyer1->id,
            'label'          => 'Rumah',
            'recipient_name' => 'Buyer Satu',
            'phone'          => '083333333333',
            'full_address'   => 'Jl. Contoh No. 1 RT 01/RW 01',
            'province'       => 'DKI Jakarta',
            'city'           => 'Jakarta Selatan',
            'district'       => 'Kebayoran Baru',
            'postal_code'    => '12160',
            'is_default'     => true,
        ]);

        // Driver 1
        $driver1 = User::create([
            'username' => 'driver1',
            'email'    => 'driver1@seapedia.com',
            'password' => Hash::make('driver123'),
            'name'     => 'Driver Satu',
            'phone'    => '084444444444',
        ]);
        UserRole::create(['user_id' => $driver1->id, 'role' => 'driver']);

        // Multi-role user (Buyer + Seller + Driver)
        $multi = User::create([
            'username' => 'multiuser',
            'email'    => 'multi@seapedia.com',
            'password' => Hash::make('multi123'),
            'name'     => 'Multi Role User',
            'phone'    => '085555555555',
        ]);
        UserRole::create(['user_id' => $multi->id, 'role' => 'buyer']);
        UserRole::create(['user_id' => $multi->id, 'role' => 'seller']);
        UserRole::create(['user_id' => $multi->id, 'role' => 'driver']);
        Wallet::create(['user_id' => $multi->id, 'balance' => 1000000]);
        Cart::create(['user_id' => $multi->id]);
        Store::create([
            'user_id'     => $multi->id,
            'name'        => 'Toko Serba Ada',
            'description' => 'Toko milik multi-role user',
        ]);
        Address::create([
            'user_id'        => $multi->id,
            'label'          => 'Rumah',
            'recipient_name' => 'Multi Role User',
            'phone'          => '085555555555',
            'full_address'   => 'Jl. Multi No. 5 RT 02/RW 03',
            'province'       => 'Jawa Barat',
            'city'           => 'Bandung',
            'district'       => 'Coblong',
            'postal_code'    => '40132',
            'is_default'     => true,
        ]);
    }
}
