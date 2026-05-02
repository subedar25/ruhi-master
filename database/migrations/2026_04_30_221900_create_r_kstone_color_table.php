<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_kstone_color')) {
            return;
        }

        Schema::create('r_kstone_color', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_kstone_color');
    }
};

