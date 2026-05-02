<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_kstone')) {
            return;
        }

        Schema::create('r_kstone', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name', 100);
            $table->string('color_id', 100);
            $table->integer('quantity')->default(0);
            $table->double('stoneweight', 11, 3)->default(0.000);
            $table->double('dieweight', 11, 3)->default(0.000);
            $table->date('create_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kstone');
    }
};

