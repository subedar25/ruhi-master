<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_gs_order')) {
            return;
        }

        Schema::create('r_gs_order', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('gs_id');
            $table->integer('slot_id');
            $table->integer('design_id');
            $table->integer('design_qty');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_gs_order');
    }
};

