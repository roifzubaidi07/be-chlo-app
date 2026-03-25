<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stocks')) {
            return;
        }

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items');
            $table->integer('quantity_on_hand')->default(0);
            $table->string('location', 100);
            $table->timestampTz('updated_at')->useCurrent();
            $table->softDeletesTz();
            $table->unique(['item_id', 'location']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_stocks_item_id ON stocks (item_id) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_stocks_item_id');
        }
        Schema::dropIfExists('stocks');
    }
};
