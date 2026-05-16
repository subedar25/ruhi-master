<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('r_design_products')) {
            Schema::table('r_design_products', function (Blueprint $table) {
                $table->index('product_id', 'r_design_products_product_id_index');
                $table->index('design_id', 'r_design_products_design_id_index');
            });
        }

        if (Schema::hasTable('r_design')) {
            Schema::table('r_design', function (Blueprint $table) {
                $table->index('design_name', 'r_design_design_name_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('r_design_products')) {
            Schema::table('r_design_products', function (Blueprint $table) {
                $table->dropIndex('r_design_products_product_id_index');
                $table->dropIndex('r_design_products_design_id_index');
            });
        }

        if (Schema::hasTable('r_design')) {
            Schema::table('r_design', function (Blueprint $table) {
                $table->dropIndex('r_design_design_name_index');
            });
        }
    }
};
