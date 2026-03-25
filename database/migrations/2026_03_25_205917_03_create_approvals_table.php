<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->foreignId('request_id')->constrained('requests');
            $table->foreignId('user_approval_id')->constrained('users');
            $table->string('status', 30);
            $table->unique(['request_id', 'code']);
            $table->unique(['request_id', 'user_approval_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_approvals_request_id ON approvals (request_id)');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_approvals_request_id');
        }
        Schema::dropIfExists('approvals');
    }
};
