<?php

namespace App\Core\RuhiItemKstones\Services;

use App\Models\RuhiItemKstone;
use App\Models\RuhiKstone;
use App\Models\RuhiProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RuhiItemKstoneService
{
    public function findProductForColletPage(int $productId): RuhiProduct
    {
        return RuhiProduct::withTrashed()->with('itemType')->findOrFail($productId);
    }

    public function listKstonesForDropdown(): Collection
    {
        return RuhiKstone::query()->orderBy('name')->get(['id', 'name']);
    }

    public function paginateForItem(int $itemId, string $search, int $perPage): LengthAwarePaginator
    {
        $query = RuhiItemKstone::query()
            ->where('item_id', $itemId)
            ->with(['product', 'kstone']);

        if (trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('kstone', function ($kq) use ($search) {
                        $kq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('id')->paginate($perPage)->onEachSide(1);
    }

    public function findById(int $id): RuhiItemKstone
    {
        return RuhiItemKstone::query()->findOrFail($id);
    }

    public function create(array $attributes): RuhiItemKstone
    {
        return RuhiItemKstone::query()->create($attributes);
    }

    public function update(RuhiItemKstone $row, array $attributes): bool
    {
        return $row->update($attributes);
    }

    public function deleteById(int $id): void
    {
        RuhiItemKstone::query()->whereKey($id)->delete();
    }
}
