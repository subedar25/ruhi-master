<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('r_collate_by_color')) {
            return;
        }

        Schema::table('r_collate_by_color', function (Blueprint $table) {
            $table->integer('color_id')->nullable()->default(0)->change();
            $table->integer('only_red_qty')->nullable()->default(0)->change();
            $table->integer('red_qty')->nullable()->default(0)->change();
            $table->integer('green_qty')->nullable()->default(0)->change();
            $table->integer('only_green_qty')->nullable()->default(0)->change();
            $table->integer('white_qty')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('r_collate_by_color')) {
            return;
        }

        Schema::table('r_collate_by_color', function (Blueprint $table) {
            $table->integer('color_id')->default(0)->nullable(false)->change();
            $table->integer('only_red_qty')->default(0)->nullable(false)->change();
            $table->integer('red_qty')->default(0)->nullable(false)->change();
            $table->integer('green_qty')->default(0)->nullable(false)->change();
            $table->integer('only_green_qty')->default(0)->nullable(false)->change();
            $table->integer('white_qty')->default(0)->nullable(false)->change();
        });
    }
};
