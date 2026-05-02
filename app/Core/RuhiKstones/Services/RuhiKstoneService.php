<?php

namespace App\Core\RuhiKstones\Services;

use App\Models\RuhiKstone;
use App\Models\RuhiKstoneColor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RuhiKstoneService
{
    public function listColors(): Collection
    {
        return RuhiKstoneColor::query()->orderBy('name')->get();
    }

    public function paginateForList(string $search, string $colorId, int $perPage, bool $includeDeleted = false): LengthAwarePaginator
    {
        $query = RuhiKstone::query()->with('color');
        if ($includeDeleted) {
            $query->withTrashed();
        }

        if (trim($colorId) !== '') {
            $query->where('color_id', (string) (int) $colorId);
        }

        if (trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('color', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return $query->orderByDesc('id')->paginate($perPage)->onEachSide(1);
    }

    public function findById(int $id): RuhiKstone
    {
        return RuhiKstone::withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): RuhiKstone
    {
        return RuhiKstone::query()->create($attributes);
    }

    public function update(RuhiKstone $kstone, array $attributes): bool
    {
        return $kstone->update($attributes);
    }

    public function softDeleteById(int $id): int
    {
        return RuhiKstone::query()->where('id', $id)->delete();
    }

    public function restoreById(int $id): int
    {
        return RuhiKstone::withTrashed()->where('id', $id)->restore();
    }
}
