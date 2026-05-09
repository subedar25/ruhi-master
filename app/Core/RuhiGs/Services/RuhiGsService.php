<?php

namespace App\Core\RuhiGs\Services;

use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiSlot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RuhiGsService
{
    public function paginateForList(string $search, int $perPage): LengthAwarePaginator
    {
        $query = RuhiGs::query();

        $term = trim($search);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");

                if (ctype_digit($term)) {
                    $q->orWhere('id', (int) $term);
                } else {
                    $q->orWhere('id', 'like', "%{$term}%");
                }
            });
        }

        return $query->orderBy('id')->paginate($perPage)->onEachSide(1);
    }

    public function findById(int $id): RuhiGs
    {
        return RuhiGs::query()->findOrFail($id);
    }

    public function create(array $attributes): RuhiGs
    {
        return RuhiGs::query()->create($attributes);
    }

    public function update(RuhiGs $gs, array $attributes): bool
    {
        return $gs->update($attributes);
    }

    /**
     * Permanently remove GS and dependent lots / order rows. Deletions are audited per model.
     */
    public function permanentlyDeleteById(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $gs = RuhiGs::query()->findOrFail($id);

            RuhiGsOrderByColor::query()
                ->where('gs_id', $gs->id)
                ->orderBy('id')
                ->get()
                ->each->delete();

            RuhiSlot::query()
                ->where('gs_id', $gs->id)
                ->orderBy('id')
                ->get()
                ->each->delete();

            if (Schema::hasTable('r_gs_order')) {
                DB::table('r_gs_order')->where('gs_id', $gs->id)->delete();
            }

            $gs->delete();
        });
    }
}
