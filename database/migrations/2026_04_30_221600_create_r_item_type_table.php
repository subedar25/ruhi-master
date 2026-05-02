<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_item_type')) {
            return;
        }

        Schema::create('r_item_type', function (Blueprint $table) {
            $table->integer('id');
            $table->string('item_type', 255);
            $table->string('abbreviation', 20)->nullable();
            $table->enum('type_by_color', ['Yes', 'No'])->default('No');
            $table->enum('required_kstone', ['Yes', 'No'])->default('No');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_item_type');
    }
};

