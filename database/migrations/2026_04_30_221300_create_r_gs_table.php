<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_gs')) {
            return;
        }

        Schema::create('r_gs', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name', 255);
            $table->date('created_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('r_gs');
    }
};

