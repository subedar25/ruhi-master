<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_kstone_add_history')) {
            return;
        }

        Schema::create('r_kstone_add_history', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('kstone_id');
            $table->integer('color_id');
            $table->date('updated_date')->nullable();
            $table->date('created_date');
            $table->integer('prev_total')->default(0);
            $table->integer('added');
            $table->integer('updated_total');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kstone_add_history');
    }
};

