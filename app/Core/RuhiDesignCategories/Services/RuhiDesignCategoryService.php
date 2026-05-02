<?php

namespace App\Core\RuhiDesignCategories\Services;

use App\Models\RuhiDesignCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RuhiDesignCategoryService
{
    public function paginateForList(string $search, int $perPage, bool $includeDeleted = false): LengthAwarePaginator
    {
        $query = RuhiDesignCategory::query();
        if ($includeDeleted) {
            $query->withTrashed();
        }

        if (trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('category_name', 'like', "%{$search}%")
                    ->orWhere('abbreviation', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('category_name')->paginate($perPage)->onEachSide(1);
    }

    public function findById(int $id): RuhiDesignCategory
    {
        return RuhiDesignCategory::withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): RuhiDesignCategory
    {
        return RuhiDesignCategory::query()->create($attributes);
    }

    public function update(RuhiDesignCategory $category, array $attributes): bool
    {
        return $category->update($attributes);
    }

    public function softDeleteById(int $id): int
    {
        return RuhiDesignCategory::query()->where('id', $id)->delete();
    }

    public function restoreById(int $id): int
    {
        return RuhiDesignCategory::withTrashed()->where('id', $id)->restore();
    }
}
