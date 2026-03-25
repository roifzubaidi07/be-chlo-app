<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users');
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->timestampTz('created_at')->useCurrent();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_status_histories_request_created ON status_histories (request_id, created_at)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_status_histories_request_created');
        }
        Schema::dropIfExists('status_histories');
    }
};
