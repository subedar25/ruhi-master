<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_k_stone')) {
            return;
        }

        Schema::create('r_k_stone', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('item_id');
            $table->integer('kstone_id');
            $table->integer('kstone_quantity')->default(0);
            $table->double('kstone_weight', 11, 3)->default(0.000);
            $table->double('kstone_dieweight', 11, 3)->default(0.000);
            $table->integer('red')->default(0);
            $table->integer('rg_red')->default(0);
            $table->integer('rg_green')->default(0);
            $table->integer('green')->default(0);
            $table->integer('white')->default(0);
            $table->integer('rodo')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_k_stone');
    }
};

