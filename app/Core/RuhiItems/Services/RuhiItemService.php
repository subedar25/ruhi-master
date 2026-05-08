<?php

namespace App\Core\RuhiItems\Services;

use App\Models\RuhiItemType;
use App\Models\RuhiProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RuhiItemService
{
    public function paginateForList(string $search, string $itemTypeId, int $perPage, bool $includeDeleted = false): LengthAwarePaginator
    {
        $query = RuhiProduct::query()
            ->with('itemType')
            ->withCount('itemKstones');
        if ($includeDeleted) {
            $query->withTrashed();
        }

        $term = trim((string) $search);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('product_name', 'like', "%{$term}%")
                    ->orWhere('product_desc', 'like', "%{$term}%")
                    ->orWhereHas('itemType', function ($itemTypeQuery) use ($term) {
                        $itemTypeQuery->where('item_type', 'like', "%{$term}%");
                    });

                if (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                } else {
                    $q->orWhere('id', 'like', "%{$term}%");
                }
            });
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

