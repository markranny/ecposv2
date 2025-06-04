<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StoreConnection;

class StoreConnectionSeeder extends Seeder
{
    public function run()
    {
        // Seed HQ2 database
        StoreConnection::create([
            'store_id' => 'HQ2',
            'database_name' => env('DB_DATABASE', 'ecpostarlac'),
            'host' => env('DB_HOST'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);

        // Example of seeding another store
        StoreConnection::create([
            'store_id' => 'STORE1',
            'database_name' => 'ecposstore1',
            'host' => env('DB_HOST'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ]);
    }
}
