<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('r_item_type', function (Blueprint $table) {
            if (! Schema::hasColumn('r_item_type', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('r_item_type', function (Blueprint $table) {
            if (Schema::hasColumn('r_item_type', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
