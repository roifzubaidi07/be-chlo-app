<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Laptop 14 inch', 'code' => 'IT-LAP-14', 'price' => 12500000],
            ['name' => 'Kertas A4 80gsm (rim)', 'code' => 'OFF-PPR-A4', 'price' => 45000],
            ['name' => 'Tinta Printer HP', 'code' => 'IT-INK-HP', 'price' => 320000],
        ];

        foreach ($items as $row) {
            Item::query()->firstOrCreate(
                ['code' => $row['code']],
                ['name' => $row['name'], 'price' => $row['price']]
            );
        }
    }
}
