<?php

namespace App\Core\RuhiGs\Services;

use App\Models\RuhiGs;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RuhiGsService
{
    public function paginateForList(string $search, int $perPage, bool $includeDeleted = false): LengthAwarePaginator
    {
        $query = RuhiGs::query();
        if ($includeDeleted) {
            $query->withTrashed();
        }

        if (trim($search) !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('id')->paginate($perPage)->onEachSide(1);
    }

    public function findById(int $id): RuhiGs
    {
        return RuhiGs::withTrashed()->findOrFail($id);
    }

    public function create(array $attributes): RuhiGs
    {
        return RuhiGs::query()->create($attributes);
    }

    public function update(RuhiGs $gs, array $attributes): bool
    {
        return $gs->update($attributes);
    }

    public function softDeleteById(int $id): int
    {
        return RuhiGs::query()->where('id', $id)->delete();
    }

    public function restoreById(int $id): int
    {
        return RuhiGs::withTrashed()->where('id', $id)->restore();
    }
}

