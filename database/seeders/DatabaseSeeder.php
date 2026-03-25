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

        $deptIt = Department::query()->where('code', 'IT')->firstOrFail();
        $deptHr = Department::query()->where('code', 'HR')->firstOrFail();

        // Matches dummy SQL + Postman: password for all is "password"
        $users = [
            ['email' => 'requester@test.local', 'name' => 'Budi Santoso', 'role' => 'requester', 'department_id' => $deptIt->id],
            ['email' => 'purchasing@test.local', 'name' => 'Siti Aminah', 'role' => 'purchasing', 'department_id' => $deptIt->id],
            ['email' => 'approver@test.local', 'name' => 'Dedi Kurniawan', 'role' => 'approver', 'department_id' => $deptIt->id],
            ['email' => 'requester2@test.local', 'name' => 'Rina Wijaya', 'role' => 'requester', 'department_id' => $deptHr->id],
            ['email' => 'warehouse@test.local', 'name' => 'Eko Prasetyo', 'role' => 'warehouse', 'department_id' => $deptIt->id],
        ];

        foreach ($users as $row) {
            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => Hash::make('password'),
                    'department_id' => $row['department_id'],
                    'role' => $row['role'],
                ]
            );
        }
    }
}
