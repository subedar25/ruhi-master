<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_product')) {
            return;
        }

        Schema::create('r_product', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_name', 100);
            $table->string('product_desc', 100)->nullable();
            $table->string('photo1', 100);
            $table->string('photo2', 100)->nullable();
            $table->integer('product_type');
            $table->double('weight', 11, 3)->default(0.000);
            $table->date('create_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_product');
    }
};

