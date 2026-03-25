<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DepartmentSeeder::class);
        $this->call(ItemSeeder::class);
        $this->call(VendorSeeder::class);

        $dept = Department::query()->where('code', 'IT')->firstOrFail();

        $users = [
            ['email' => 'requester@test.local', 'name' => 'Budi Requester', 'role' => 'requester'],
            ['email' => 'approver@test.local', 'name' => 'Siti Approver', 'role' => 'approver'],
            ['email' => 'purchasing@test.local', 'name' => 'Dedi Purchasing', 'role' => 'purchasing'],
            ['email' => 'warehouse@test.local', 'name' => 'Rina Warehouse', 'role' => 'warehouse'],
        ];

        foreach ($users as $row) {
            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'department_id' => $dept->id,
                    'role' => $row['role'],
                ]
            );
        }
    }
}
