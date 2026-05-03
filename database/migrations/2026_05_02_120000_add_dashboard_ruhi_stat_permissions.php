<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $module = Module::query()->where('slug', 'dashboard')->first();
        if (! $module) {
            return;
        }

        $guardName = 'web';
        $rows = [
            ['name' => 'dashboard-manage-gs', 'display_name' => 'Dashboard Manage GS', 'slug' => 'dashboard-manage-gs'],
            ['name' => 'dashboard-manage-design', 'display_name' => 'Dashboard Manage Design', 'slug' => 'dashboard-manage-design'],
            ['name' => 'dashboard-manage-items', 'display_name' => 'Dashboard Manage Items', 'slug' => 'dashboard-manage-items'],
            ['name' => 'dashboard-manage-kstone', 'display_name' => 'Dashboard Manage Kstone', 'slug' => 'dashboard-manage-kstone'],
            ['name' => 'dashboard-manage-design-category', 'display_name' => 'Dashboard Manage Design Category', 'slug' => 'dashboard-manage-design-category'],
            ['name' => 'dashboard-manage-item-category', 'display_name' => 'Dashboard Manage Item Category', 'slug' => 'dashboard-manage-item-category'],
        ];

        foreach ($rows as $p) {
            Permission::updateOrCreate(
                ['name' => $p['name'], 'guard_name' => $guardName],
                [
                    'display_name' => $p['display_name'],
                    'module_id' => $module->id,
                    'slug' => $p['slug'],
                    'is_active' => true,
                    'type' => $module->type ?? 'public',
                ]
            );
        }
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'dashboard-manage-gs',
                'dashboard-manage-design',
                'dashboard-manage-items',
                'dashboard-manage-kstone',
                'dashboard-manage-design-category',
                'dashboard-manage-item-category',
            ])
            ->delete();
    }
};
