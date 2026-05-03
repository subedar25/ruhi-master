<?php

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Module "Ruhi Masters" with CRUD-style permissions per master area.
     */
    public function up(): void
    {
        $guardName = 'web';

        $module = Module::updateOrCreate(
            ['slug' => 'ruhi-masters'],
            [
                'name' => 'Ruhi Masters',
                'type' => 'public',
            ]
        );

        $rows = [
            ['name' => 'list-ruhi-items', 'display_name' => 'View Ruhi Items', 'slug' => 'list-ruhi-items'],
            ['name' => 'create-ruhi-item', 'display_name' => 'Create Ruhi Item', 'slug' => 'create-ruhi-item'],
            ['name' => 'edit-ruhi-item', 'display_name' => 'Edit Ruhi Item', 'slug' => 'edit-ruhi-item'],
            ['name' => 'delete-ruhi-item', 'display_name' => 'Delete Ruhi Item', 'slug' => 'delete-ruhi-item'],

            ['name' => 'list-ruhi-collet-kstones', 'display_name' => 'View Collet K-Stones', 'slug' => 'list-ruhi-collet-kstones'],
            ['name' => 'create-ruhi-collet-kstone', 'display_name' => 'Create Collet K-Stone', 'slug' => 'create-ruhi-collet-kstone'],
            ['name' => 'edit-ruhi-collet-kstone', 'display_name' => 'Edit Collet K-Stone', 'slug' => 'edit-ruhi-collet-kstone'],
            ['name' => 'delete-ruhi-collet-kstone', 'display_name' => 'Delete Collet K-Stone', 'slug' => 'delete-ruhi-collet-kstone'],

            ['name' => 'list-ruhi-designs', 'display_name' => 'View Ruhi Designs', 'slug' => 'list-ruhi-designs'],
            ['name' => 'create-ruhi-design', 'display_name' => 'Create Ruhi Design', 'slug' => 'create-ruhi-design'],
            ['name' => 'edit-ruhi-design', 'display_name' => 'Edit Ruhi Design', 'slug' => 'edit-ruhi-design'],
            ['name' => 'delete-ruhi-design', 'display_name' => 'Delete Ruhi Design', 'slug' => 'delete-ruhi-design'],

            ['name' => 'list-ruhi-design-products', 'display_name' => 'View Design Products', 'slug' => 'list-ruhi-design-products'],
            ['name' => 'create-ruhi-design-product', 'display_name' => 'Create Design Product', 'slug' => 'create-ruhi-design-product'],
            ['name' => 'edit-ruhi-design-product', 'display_name' => 'Edit Design Product', 'slug' => 'edit-ruhi-design-product'],
            ['name' => 'delete-ruhi-design-product', 'display_name' => 'Delete Design Product', 'slug' => 'delete-ruhi-design-product'],

            ['name' => 'list-ruhi-gs', 'display_name' => 'View Ruhi GS', 'slug' => 'list-ruhi-gs'],
            ['name' => 'create-ruhi-gs', 'display_name' => 'Create Ruhi GS', 'slug' => 'create-ruhi-gs'],
            ['name' => 'edit-ruhi-gs', 'display_name' => 'Edit Ruhi GS', 'slug' => 'edit-ruhi-gs'],
            ['name' => 'delete-ruhi-gs', 'display_name' => 'Delete Ruhi GS', 'slug' => 'delete-ruhi-gs'],

            ['name' => 'list-ruhi-gs-lots', 'display_name' => 'View GS Lots', 'slug' => 'list-ruhi-gs-lots'],
            ['name' => 'create-ruhi-gs-lot', 'display_name' => 'Create GS Lot', 'slug' => 'create-ruhi-gs-lot'],
            ['name' => 'edit-ruhi-gs-lot', 'display_name' => 'Edit GS Lot', 'slug' => 'edit-ruhi-gs-lot'],
            ['name' => 'delete-ruhi-gs-lot', 'display_name' => 'Delete GS Lot', 'slug' => 'delete-ruhi-gs-lot'],

            ['name' => 'list-ruhi-kstones', 'display_name' => 'View K Stones', 'slug' => 'list-ruhi-kstones'],
            ['name' => 'create-ruhi-kstone', 'display_name' => 'Create K Stone', 'slug' => 'create-ruhi-kstone'],
            ['name' => 'edit-ruhi-kstone', 'display_name' => 'Edit K Stone', 'slug' => 'edit-ruhi-kstone'],
            ['name' => 'delete-ruhi-kstone', 'display_name' => 'Delete K Stone', 'slug' => 'delete-ruhi-kstone'],

            ['name' => 'list-ruhi-design-categories', 'display_name' => 'View Design Categories', 'slug' => 'list-ruhi-design-categories'],
            ['name' => 'create-ruhi-design-category', 'display_name' => 'Create Design Category', 'slug' => 'create-ruhi-design-category'],
            ['name' => 'edit-ruhi-design-category', 'display_name' => 'Edit Design Category', 'slug' => 'edit-ruhi-design-category'],
            ['name' => 'delete-ruhi-design-category', 'display_name' => 'Delete Design Category', 'slug' => 'delete-ruhi-design-category'],

            ['name' => 'list-ruhi-item-types', 'display_name' => 'View Item Categories', 'slug' => 'list-ruhi-item-types'],
            ['name' => 'create-ruhi-item-type', 'display_name' => 'Create Item Category', 'slug' => 'create-ruhi-item-type'],
            ['name' => 'edit-ruhi-item-type', 'display_name' => 'Edit Item Category', 'slug' => 'edit-ruhi-item-type'],
            ['name' => 'delete-ruhi-item-type', 'display_name' => 'Delete Item Category', 'slug' => 'delete-ruhi-item-type'],
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
            'list-ruhi-items', 'create-ruhi-item', 'edit-ruhi-item', 'delete-ruhi-item',
            'list-ruhi-collet-kstones', 'create-ruhi-collet-kstone', 'edit-ruhi-collet-kstone', 'delete-ruhi-collet-kstone',
            'list-ruhi-designs', 'create-ruhi-design', 'edit-ruhi-design', 'delete-ruhi-design',
            'list-ruhi-design-products', 'create-ruhi-design-product', 'edit-ruhi-design-product', 'delete-ruhi-design-product',
            'list-ruhi-gs', 'create-ruhi-gs', 'edit-ruhi-gs', 'delete-ruhi-gs',
            'list-ruhi-gs-lots', 'create-ruhi-gs-lot', 'edit-ruhi-gs-lot', 'delete-ruhi-gs-lot',
            'list-ruhi-kstones', 'create-ruhi-kstone', 'edit-ruhi-kstone', 'delete-ruhi-kstone',
            'list-ruhi-design-categories', 'create-ruhi-design-category', 'edit-ruhi-design-category', 'delete-ruhi-design-category',
            'list-ruhi-item-types', 'create-ruhi-item-type', 'edit-ruhi-item-type', 'delete-ruhi-item-type',
        ];

        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $names)
            ->delete();

        Module::query()->where('slug', 'ruhi-masters')->delete();
    }
};
