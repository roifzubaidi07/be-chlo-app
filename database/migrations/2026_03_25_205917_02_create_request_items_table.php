<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50);
            $table->foreignId('request_id')->constrained('requests');
            $table->foreignId('item_id')->constrained('items');
            $table->decimal('qty', 14, 3);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax', 14, 2)->default(0);
            $table->softDeletesTz();
            $table->unique(['request_id', 'code']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_request_items_request_id ON request_items (request_id) WHERE deleted_at IS NULL');
            DB::statement('CREATE INDEX idx_request_items_item_id ON request_items (item_id) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_request_items_request_id');
            DB::statement('DROP INDEX IF EXISTS idx_request_items_item_id');
        }
        Schema::dropIfExists('request_items');
    }
};
