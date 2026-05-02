<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_design_product_item_kstone')) {
            return;
        }

        Schema::create('r_design_product_item_kstone', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('design_id');
            $table->integer('product_id');
            $table->integer('kstone_id');
            $table->integer('kstone_quantity');
            $table->integer('red');
            $table->integer('rg_red')->default(0);
            $table->integer('rg_green')->default(0);
            $table->integer('green');
            $table->integer('white');
            $table->integer('rodo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_design_product_item_kstone');
    }
};

