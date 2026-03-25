<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::query()->firstOrCreate(
            ['code' => 'IT'],
            ['name' => 'Information Technology']
        );

        Department::query()->firstOrCreate(
            ['code' => 'HR'],
            ['name' => 'Human Resources']
        );
    }
}
