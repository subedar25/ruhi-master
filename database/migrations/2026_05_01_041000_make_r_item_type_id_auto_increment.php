<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('r_item_type')) {
            return;
        }

        DB::statement('SET @OLD_SQL_MODE = @@SESSION.sql_mode');
        DB::statement("SET SESSION sql_mode=(SELECT REPLACE(REPLACE(@@sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''))");

        try {
            $primaryKey = DB::selectOne("SHOW KEYS FROM r_item_type WHERE Key_name = 'PRIMARY'");
            if (! $primaryKey) {
                DB::statement('ALTER TABLE r_item_type ADD PRIMARY KEY (id)');
            }
            DB::statement('ALTER TABLE r_item_type MODIFY id INT NOT NULL AUTO_INCREMENT');
        } finally {
            DB::statement('SET SESSION sql_mode = @OLD_SQL_MODE');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('r_item_type')) {
            return;
        }

        $primaryKey = DB::selectOne("SHOW KEYS FROM r_item_type WHERE Key_name = 'PRIMARY'");
        if ($primaryKey) {
            DB::statement('ALTER TABLE r_item_type DROP PRIMARY KEY');
        }
        DB::statement('ALTER TABLE r_item_type MODIFY id INT NOT NULL');
    }
};
