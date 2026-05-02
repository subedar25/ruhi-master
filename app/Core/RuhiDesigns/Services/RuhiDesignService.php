<?php

namespace App\Core\RuhiDesigns\Services;

use App\Models\RuhiDesign;
use App\Models\RuhiDesignCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RuhiDesignService
{
    public function paginateForList(string $search, string $categoryId, int $perPage, bool $includeDeleted = false): LengthAwarePaginator
    {
        $query = RuhiDesign::query()->with('category');
        if ($includeDeleted) {
            $query->withTrashed();
        }

        if (trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('design_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('category_name', 'like', "%{$search}%")
                            ->orWhere('abbreviation', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryId !== '') {
            $query->where('category_id', (int) $categoryId);
        }

        return $query->orderByDesc('id')->paginate($perPage)->onEachSide(1);
    }

    public function listCategories(): Collection
    {
        return RuhiDesignCategory::query()
            ->orderBy('category_name')
            ->get(['id', 'category_name', 'abbreviation']);
    }

    public function findCategoryById(int $id): RuhiDesignCategory
    {
        return RuhiDesignCategory::query()->findOrFail($id);
    }

    public function findById(int $id): RuhiDesign
    {
        return RuhiDesign::query()->findOrFail($id);
    }

    public function create(array $attributes): RuhiDesign
    {
        return RuhiDesign::query()->create($attributes);
    }

    public function update(RuhiDesign $design, array $attributes): bool
    {
        return $design->update($attributes);
    }

    public function softDeleteById(int $id): int
    {
        return RuhiDesign::query()->where('id', $id)->delete();
    }

    public function restoreById(int $id): int
    {
        return RuhiDesign::withTrashed()->where('id', $id)->restore();
    }
}

