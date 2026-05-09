<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('r_gs')) {
            return;
        }

        if (! Schema::hasColumn('r_gs', 'deleted_at')) {
            return;
        }

        DB::table('r_gs')->whereNotNull('deleted_at')->delete();

        Schema::table('r_gs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('r_gs')) {
            return;
        }

        if (Schema::hasColumn('r_gs', 'deleted_at')) {
            return;
        }

        Schema::table('r_gs', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
