<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_design')) {
            return;
        }

        Schema::create('r_design', function (Blueprint $table) {
            $table->integer('id');
            $table->string('design_name', 100);
            $table->string('design_desc', 100)->nullable();
            $table->integer('category_id');
            $table->string('photo1', 100)->nullable();
            $table->string('photo2', 100)->nullable();
            $table->string('dubby_qty', 100)->default('0');
            $table->string('zumka_qty', 255)->nullable();
            $table->string('uf', 500)->nullable();
            $table->text('note')->nullable();
            $table->date('create_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_design');
    }
};

