<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_collate_by_color')) {
            return;
        }

        Schema::create('r_collate_by_color', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('design_product_id');
            $table->integer('color_id')->default(0);
            $table->integer('only_red_qty')->default(0);
            $table->integer('red_qty')->default(0);
            $table->integer('green_qty')->default(0);
            $table->integer('only_green_qty')->default(0);
            $table->integer('white_qty')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_collate_by_color');
    }
};

