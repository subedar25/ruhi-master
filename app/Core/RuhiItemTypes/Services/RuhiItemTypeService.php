<?php

namespace App\Core\RuhiItemTypes\Services;

use App\Models\RuhiItemType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RuhiItemTypeService
{
    public function paginateForList(string $search, int $perPage, bool $includeDeleted = false): LengthAwarePaginator
    {
        $query = RuhiItemType::query();
        if ($includeDeleted) {
            $query->withTrashed();
        }

        if (trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('item_type', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('item_type')->paginate($perPage)->onEachSide(1);
    }

    public function findById(int $id): RuhiItemType
    {
        return RuhiItemType::withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): RuhiItemType
    {
        return RuhiItemType::query()->create($attributes);
    }

    public function update(RuhiItemType $type, array $attributes): bool
    {
        return $type->update($attributes);
    }

    public function softDeleteById(int $id): int
    {
        return RuhiItemType::query()->where('id', $id)->delete();
    }

    public function restoreById(int $id): int
    {
        return RuhiItemType::withTrashed()->where('id', $id)->restore();
    }
}
