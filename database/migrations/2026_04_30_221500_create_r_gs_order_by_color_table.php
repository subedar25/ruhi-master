<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_gs_order_by_color')) {
            return;
        }

        Schema::create('r_gs_order_by_color', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('gs_id');
            $table->integer('lot_id');
            $table->integer('design_id');
            $table->integer('design_qty');
            $table->integer('design_red_qty');
            $table->integer('design_red_green_qty');
            $table->integer('design_green_qty');
            $table->integer('white_qty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_gs_order_by_color');
    }
};

