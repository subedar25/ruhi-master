<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_design_category')) {
            return;
        }

        Schema::create('r_design_category', function (Blueprint $table) {
            $table->integer('id');
            $table->string('category_name', 255);
            $table->string('abbreviation', 20)->nullable();
            $table->dateTime('created_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_design_category');
    }
};

