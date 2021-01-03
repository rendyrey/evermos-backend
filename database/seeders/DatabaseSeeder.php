<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ProductType;
use App\Models\Product;
use App\Models\Payment;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        User::unguard();
        $this->call(UsersTableSeeder::class);
        User::reguard();

        ProductType::unguard();
        $this->call(ProductTypesTableSeeder::class);
        ProductType::reguard();

        Product::unguard();
        $this->call(ProductsTableSeeder::class);
        Product::reguard();

        Payment::unguard();
        $this->call(PaymentsTableSeeder::class);
        Payment::reguard();
    }
}
