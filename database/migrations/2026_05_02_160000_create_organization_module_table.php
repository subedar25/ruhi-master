<?php

use App\Models\Module;
use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_module', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('module_id');
            $table->primary(['organization_id', 'module_id']);
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->cascadeOnDelete();
        });

        $orgIds = Organization::withTrashed()->pluck('id');
        $moduleIds = Module::query()->pluck('id');
        if ($orgIds->isEmpty() || $moduleIds->isEmpty()) {
            return;
        }

        $rows = [];
        foreach ($orgIds as $oid) {
            foreach ($moduleIds as $mid) {
                $rows[] = [
                    'organization_id' => $oid,
                    'module_id' => $mid,
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('organization_module')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_module');
    }
};
