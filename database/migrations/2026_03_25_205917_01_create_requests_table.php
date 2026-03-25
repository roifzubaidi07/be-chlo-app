<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('requests')) {
            return;
        }

        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->foreignId('user_request_id')->constrained('users');
            $table->string('status', 50);
            $table->unsignedInteger('lock_version')->default(0);
            $table->timestampTz('created_at')->nullable();
            $table->softDeletesTz();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_requests_user ON requests (user_request_id) WHERE deleted_at IS NULL');
            DB::statement('CREATE INDEX idx_requests_status ON requests (status) WHERE deleted_at IS NULL');
            DB::statement('CREATE INDEX idx_requests_created_at ON requests (created_at) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_requests_user');
            DB::statement('DROP INDEX IF EXISTS idx_requests_status');
            DB::statement('DROP INDEX IF EXISTS idx_requests_created_at');
        }
        Schema::dropIfExists('requests');
    }
};
