<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->string('email', 200)->nullable();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
