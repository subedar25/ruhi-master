<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_kstone_history')) {
            return;
        }

        Schema::create('r_kstone_history', function (Blueprint $table) {
            $table->integer('id');
            $table->string('description', 50);
            $table->string('kstone', 50);
            $table->date('date');
            $table->string('red', 50);
            $table->string('green', 50)->nullable();
            $table->string('white', 50)->nullable();
            $table->string('rodo', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_kstone_history');
    }
};

