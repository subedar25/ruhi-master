<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_design_products')) {
            return;
        }

        Schema::create('r_design_products', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('design_id');
            $table->integer('item_type_id');
            $table->integer('product_id');
            $table->integer('quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_design_products');
    }
};

