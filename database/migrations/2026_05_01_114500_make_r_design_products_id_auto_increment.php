<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('r_design_products')) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW KEYS FROM r_design_products WHERE Key_name = 'PRIMARY'");
        if (! $primaryKey) {
            DB::statement('ALTER TABLE r_design_products ADD PRIMARY KEY (id)');
        }

        DB::statement('ALTER TABLE r_design_products MODIFY id INT NOT NULL AUTO_INCREMENT');
    }

    public function down(): void
    {
        if (! Schema::hasTable('r_design_products')) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW KEYS FROM r_design_products WHERE Key_name = 'PRIMARY'");
        if ($primaryKey) {
            DB::statement('ALTER TABLE r_design_products DROP PRIMARY KEY');
        }

        DB::statement('ALTER TABLE r_design_products MODIFY id INT NOT NULL');
    }
};
