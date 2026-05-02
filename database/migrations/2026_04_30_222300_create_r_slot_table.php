<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_slot')) {
            return;
        }

        Schema::create('r_slot', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('gs_id');
            $table->string('slot_name', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_slot');
    }
};

