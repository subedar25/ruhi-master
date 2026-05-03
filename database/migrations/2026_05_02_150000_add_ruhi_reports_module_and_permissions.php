<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $guardName = 'web';

        $module = Module::updateOrCreate(
            ['slug' => 'ruhi-reports'],
            [
                'name' => 'Ruhi Reports',
                'type' => 'public',
            ]
        );

        $rows = [
            ['name' => 'view-ruhi-report-gs-wise-casting', 'display_name' => 'GS Wise Casting Report', 'slug' => 'view-ruhi-report-gs-wise-casting'],
            ['name' => 'view-ruhi-report-gs-wise-casting-detail', 'display_name' => 'GS Wise Casting Detail Report', 'slug' => 'view-ruhi-report-gs-wise-casting-detail'],
            ['name' => 'view-ruhi-report-gs-wise-dubby', 'display_name' => 'GS Wise Dubby Report', 'slug' => 'view-ruhi-report-gs-wise-dubby'],
            ['name' => 'view-ruhi-report-gs-wise-collet', 'display_name' => 'GS Wise Collet Report', 'slug' => 'view-ruhi-report-gs-wise-collet'],
            ['name' => 'view-ruhi-report-gs-full', 'display_name' => 'GS Full Report', 'slug' => 'view-ruhi-report-gs-full'],
            ['name' => 'view-ruhi-report-gs-die', 'display_name' => 'GS Die Report', 'slug' => 'view-ruhi-report-gs-die'],
            ['name' => 'view-ruhi-report-gs-detail-each-item', 'display_name' => 'GS Wise Detail Report of Each Item', 'slug' => 'view-ruhi-report-gs-detail-each-item'],
            ['name' => 'view-ruhi-report-gs-color-collet', 'display_name' => 'GS Color Collet Report', 'slug' => 'view-ruhi-report-gs-color-collet'],
            ['name' => 'view-ruhi-report-gs-wise-drop', 'display_name' => 'GS Wise Drop Report', 'slug' => 'view-ruhi-report-gs-wise-drop'],
            ['name' => 'view-ruhi-report-gs-color-full', 'display_name' => 'GS Color Full Report', 'slug' => 'view-ruhi-report-gs-color-full'],
            ['name' => 'view-ruhi-report-gs-collet-kstone-color', 'display_name' => 'GS Wise Collet Kstone Color Report', 'slug' => 'view-ruhi-report-gs-collet-kstone-color'],
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
        $names = [
            'view-ruhi-report-gs-wise-casting',
            'view-ruhi-report-gs-wise-casting-detail',
            'view-ruhi-report-gs-wise-dubby',
            'view-ruhi-report-gs-wise-collet',
            'view-ruhi-report-gs-full',
            'view-ruhi-report-gs-die',
            'view-ruhi-report-gs-detail-each-item',
            'view-ruhi-report-gs-color-collet',
            'view-ruhi-report-gs-wise-drop',
            'view-ruhi-report-gs-color-full',
            'view-ruhi-report-gs-collet-kstone-color',
        ];

        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $names)
            ->delete();

        Module::query()->where('slug', 'ruhi-reports')->delete();
    }
};
