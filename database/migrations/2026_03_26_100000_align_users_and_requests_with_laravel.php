<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alignUsersTable();
        $this->alignRequestsTable();
    }

    public function down(): void
    {
        if (Schema::hasTable('requests') && Schema::hasColumn('requests', 'lock_version')) {
            Schema::table('requests', function (Blueprint $table) {
                $table->dropColumn('lock_version');
            });
        }
    }

    private function alignUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        $addedAuthColumns = false;

        Schema::table('users', function (Blueprint $table) use (&$addedAuthColumns) {
            if (! Schema::hasColumn('users', 'email')) {
                $table->string('email')->nullable()->unique();
                $addedAuthColumns = true;
            }
            if (! Schema::hasColumn('users', 'email_verified_at')) {
                $table->timestampTz('email_verified_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'password')) {
                $table->string('password')->nullable();
            }
            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }
            if (! Schema::hasColumn('users', 'created_at')) {
                $table->timestampTz('created_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'updated_at')) {
                $table->timestampTz('updated_at')->nullable();
            }
        });

        if (! $addedAuthColumns) {
            return;
        }

        $password = Hash::make('password');
        $now = now();

        $emailsById = [
            1 => 'requester@test.local',
            2 => 'purchasing@test.local',
            3 => 'approver@test.local',
            4 => 'requester2@test.local',
            5 => 'warehouse@test.local',
            6 => 'inactive@test.local',
        ];

        foreach ($emailsById as $id => $email) {
            DB::table('users')->where('id', $id)->update([
                'email' => $email,
                'password' => $password,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN email SET NOT NULL');
            DB::statement('ALTER TABLE users ALTER COLUMN password SET NOT NULL');
        }
    }

    private function alignRequestsTable(): void
    {
        if (! Schema::hasTable('requests')) {
            return;
        }

        if (Schema::hasColumn('requests', 'lock_version')) {
            return;
        }

        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedInteger('lock_version')->default(0);
        });
    }
};
