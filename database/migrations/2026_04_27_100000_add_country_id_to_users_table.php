<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'country_id')) {
                $table->unsignedInteger('country_id')->nullable()->after('state');
            }
        });

        if (Schema::hasTable('countries')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('country_id')
                    ->references('id')
                    ->on('countries')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'country_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasTable('countries')) {
                $table->dropForeign(['country_id']);
            }
            $table->dropColumn('country_id');
        });
    }
};

