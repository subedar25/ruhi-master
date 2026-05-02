<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('r_design_category')) {
            return;
        }

        DB::statement('SET @OLD_SQL_MODE = @@SESSION.sql_mode');
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''))");

        try {
            $primaryKey = DB::selectOne("SHOW KEYS FROM r_design_category WHERE Key_name = 'PRIMARY'");
            if (! $primaryKey) {
                DB::statement('ALTER TABLE r_design_category ADD PRIMARY KEY (id)');
            }
            DB::statement('ALTER TABLE r_design_category MODIFY id INT NOT NULL AUTO_INCREMENT');
        } finally {
            DB::statement('SET SESSION sql_mode = @OLD_SQL_MODE');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('r_design_category')) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW KEYS FROM r_design_category WHERE Key_name = 'PRIMARY'");
        if ($primaryKey) {
            DB::statement('ALTER TABLE r_design_category DROP PRIMARY KEY');
        }
        DB::statement('ALTER TABLE r_design_category MODIFY id INT NOT NULL');
    }
};
