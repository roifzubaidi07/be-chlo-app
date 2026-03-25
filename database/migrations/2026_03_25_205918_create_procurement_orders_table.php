<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests');
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->string('po_number', 50)->unique();
            $table->string('status', 50);
            $table->softDeletesTz();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_procurement_orders_request ON procurement_orders (request_id) WHERE deleted_at IS NULL');
            DB::statement('CREATE INDEX idx_procurement_orders_vendor ON procurement_orders (vendor_id) WHERE deleted_at IS NULL');
            DB::statement('CREATE INDEX idx_procurement_orders_status ON procurement_orders (status) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_procurement_orders_request');
            DB::statement('DROP INDEX IF EXISTS idx_procurement_orders_vendor');
            DB::statement('DROP INDEX IF EXISTS idx_procurement_orders_status');
        }
        Schema::dropIfExists('procurement_orders');
    }
};
