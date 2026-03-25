<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->decimal('price', 14, 2)->default(0);
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
