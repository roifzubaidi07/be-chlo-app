<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        Vendor::query()->firstOrCreate(
            ['code' => 'VND-MITRA'],
            [
                'name' => 'PT Mitra Supplies',
                'email' => 'sales@mitrasupplies.test',
            ]
        );

        Vendor::query()->firstOrCreate(
            ['code' => 'VND-ELEK'],
            [
                'name' => 'CV Elektronik Jaya',
                'email' => 'order@elekjaya.test',
            ]
        );
    }
}
