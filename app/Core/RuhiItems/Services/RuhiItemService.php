<?php

namespace App\Core\RuhiItems\Services;

use App\Models\RuhiItemType;
use App\Models\RuhiProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class RuhiItemService
{
    public function paginateForList(string $search, string $itemTypeId, int $perPage, bool $includeDeleted = false): LengthAwarePaginator
    {
        $query = RuhiProduct::query()
            ->with($this->listEagerLoads())
            ->withCount('itemKstones');

        if ($includeDeleted) {
            $query->withTrashed();
        }

        $term = trim((string) $search);
        if ($term !== '') {
            $this->applySearchFilters($query, $term);
        }

        if ($itemTypeId !== '') {
            $query->where('product_type', (int) $itemTypeId);
        }

        return $query
            ->orderByRaw("LEFT(product_name, LOCATE('-', product_name))")
            ->orderByRaw("CAST(SUBSTRING(product_name, LOCATE('-', product_name) + 1) AS SIGNED)")
            ->paginate($perPage)
            ->onEachSide(1);
    }

    /**
     * @return array<string, mixed>
     */
    private function listEagerLoads(): array
    {
        return [
            'itemType:id,item_type',
            'designProducts' => function ($designProductQuery) {
                $designProductQuery->select(['id', 'design_id', 'product_id']);
            },
            'designProducts.design' => function ($designQuery) {
                $designQuery
                    ->withTrashed()
                    ->select(['id', 'design_name', 'deleted_at'])
                    ->orderByRaw("LEFT(design_name, LOCATE('-', design_name))")
                    ->orderByRaw("CAST(SUBSTRING(design_name, LOCATE('-', design_name) + 1) AS SIGNED)")
                    ->orderBy('design_name');
            },
        ];
    }

    private function applySearchFilters(Builder $query, string $term): void
    {
        $productTable = (new RuhiProduct)->getTable();

        $query->where(function (Builder $q) use ($term, $productTable) {
            $q->where("{$productTable}.product_name", 'like', "%{$term}%")
                ->orWhere("{$productTable}.product_desc", 'like', "%{$term}%")
                ->orWhereExists(function ($sub) use ($term, $productTable) {
                    $sub->from('r_item_type as it')
                        ->whereColumn('it.id', "{$productTable}.product_type")
                        ->where('it.item_type', 'like', "%{$term}%");
                })
                ->orWhereExists(function ($sub) use ($term, $productTable) {
                    $sub->from('r_design_products as dp')
                        ->join('r_design as d', 'd.id', '=', 'dp.design_id')
                        ->whereColumn('dp.product_id', "{$productTable}.id")
                        ->where('d.design_name', 'like', "%{$term}%");
                });

            if (ctype_digit($term)) {
                $q->orWhere("{$productTable}.id", (int) $term);
            } else {
                $q->orWhere("{$productTable}.id", 'like', "%{$term}%");
            }
        });
    }

    public function listTypes(): Collection
    {
        return RuhiItemType::query()
            ->orderBy('item_type')
            ->get(['id', 'item_type']);
    }

    public function findById(int $id): RuhiProduct
    {
        return RuhiProduct::query()->findOrFail($id);
    }

    public function create(array $attributes): RuhiProduct
    {
        return RuhiProduct::query()->create($attributes);
    }

    public function update(RuhiProduct $item, array $attributes): bool
    {
        return $item->update($attributes);
    }

    public function softDeleteById(int $id): int
    {
        return RuhiProduct::query()->where('id', $id)->delete();
    }

    public function restoreById(int $id): int
    {
        return RuhiProduct::withTrashed()->where('id', $id)->restore();
    }
}
