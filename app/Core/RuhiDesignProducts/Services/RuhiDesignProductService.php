<?php

namespace App\Core\RuhiDesignProducts\Services;

use App\Models\RuhiDesign;
use App\Models\RuhiCollateByColor;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiItemType;
use App\Models\RuhiProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RuhiDesignProductService
{
    public function findDesignForProductsPage(int $designId): RuhiDesign
    {
        return RuhiDesign::withTrashed()->with('category')->findOrFail($designId);
    }

    public function updateDesignSummary(RuhiDesign $design, array $attributes): bool
    {
        return $design->update($attributes);
    }

    public function listItemTypes(): Collection
    {
        return RuhiItemType::query()
            ->orderByRaw('CASE WHEN show_order IS NULL THEN 1 ELSE 0 END')
            ->orderBy('show_order')
            ->orderBy('item_type')
            ->get(['id', 'item_type', 'show_order', 'required_kstone', 'type_by_color']);
    }

    public function paginateByDesignAndType(int $designId, int $itemTypeId, int $perPage = 5): LengthAwarePaginator
    {
        return RuhiDesignProduct::query()
            ->where('design_id', $designId)
            ->where('item_type_id', $itemTypeId)
            ->with(['product', 'itemType', 'collateByColors'])
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], $this->pageName($itemTypeId))
            ->onEachSide(1);
    }

    public function countByDesignAndType(int $designId, int $itemTypeId): int
    {
        return RuhiDesignProduct::query()
            ->where('design_id', $designId)
            ->where('item_type_id', $itemTypeId)
            ->count();
    }

    public function listProductsForDropdown(int $itemTypeId): Collection
    {
        return RuhiProduct::query()
            ->withTrashed()
            ->where('product_type', $itemTypeId)
            ->orderByRaw("LEFT(product_name, LOCATE('-', product_name))")
            ->orderByRaw("CAST(SUBSTRING(product_name, LOCATE('-', product_name) + 1) AS SIGNED)")
            ->get(['id', 'product_name']);
    }

    public function create(array $attributes): RuhiDesignProduct
    {
        return RuhiDesignProduct::query()->create($attributes);
    }

    public function findById(int $id): RuhiDesignProduct
    {
        return RuhiDesignProduct::query()->findOrFail($id);
    }

    public function update(RuhiDesignProduct $row, array $attributes): bool
    {
        return $row->update($attributes);
    }

    public function deleteById(int $id): void
    {
        RuhiDesignProduct::query()->whereKey($id)->delete();
    }

    public function findItemTypeById(int $id): RuhiItemType
    {
        return RuhiItemType::query()->findOrFail($id);
    }

    public function colorValuesForDesignProduct(int $designProductId): array
    {
        return [
            'only_red_qty' => (int) RuhiCollateByColor::query()->where('design_product_id', $designProductId)->sum('only_red_qty'),
            'red_qty' => (int) RuhiCollateByColor::query()->where('design_product_id', $designProductId)->sum('red_qty'),
            'green_qty' => (int) RuhiCollateByColor::query()->where('design_product_id', $designProductId)->sum('green_qty'),
            'only_green_qty' => (int) RuhiCollateByColor::query()->where('design_product_id', $designProductId)->sum('only_green_qty'),
            'white_qty' => (int) RuhiCollateByColor::query()->where('design_product_id', $designProductId)->sum('white_qty'),
        ];
    }

    public function upsertColorValuesForDesignProduct(int $designProductId, array $values): void
    {
        $row = RuhiCollateByColor::query()->firstOrNew([
            'design_product_id' => $designProductId,
            'color_id' => 0,
        ]);

        $row->only_red_qty = (int) ($values['only_red_qty'] ?? 0);
        $row->red_qty = (int) ($values['red_qty'] ?? 0);
        $row->green_qty = (int) ($values['green_qty'] ?? 0);
        $row->only_green_qty = (int) ($values['only_green_qty'] ?? 0);
        $row->white_qty = (int) ($values['white_qty'] ?? 0);
        $row->save();
    }

    public function pageName(int $itemTypeId): string
    {
        return 'type_'.$itemTypeId.'_page';
    }

    public function listPrintBlocks(int $designId): array
    {
        $types = $this->listItemTypes();

        $blocks = $types->map(function ($type) use ($designId) {
            $rows = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->where('item_type_id', (int) $type->id)
                ->with(['product', 'collateByColors'])
                ->get()
                ->sortBy(function ($row) {
                    $name = (string) ($row->product->product_name ?? '');
                    $parts = explode('-', $name, 2);
                    $prefix = $parts[0] ?? $name;
                    $number = isset($parts[1]) ? (int) preg_replace('/\D+/', '', $parts[1]) : 0;

                    return sprintf('%s-%010d', $prefix, $number);
                })
                ->values();

            return [
                'type' => $type,
                'total' => $rows->count(),
                'rows' => $rows,
                'is_color' => strtolower((string) ($type->type_by_color ?? 'no')) === 'yes',
            ];
        })->filter(fn ($block) => $block['total'] > 0)->values();

        return [
            'colorBlocks' => $blocks->filter(fn ($block) => $block['is_color'])->values(),
            'nonColorBlocks' => $blocks->filter(fn ($block) => ! $block['is_color'])->values(),
        ];
    }
}
