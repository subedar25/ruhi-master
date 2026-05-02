<?php

namespace App\Core\RuhiDesignProductItemKstones\Services;

use App\Models\RuhiDesign;
use App\Models\RuhiDesignProductItemKstone;
use App\Models\RuhiItemKstone;
use App\Models\RuhiProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RuhiDesignProductItemKstoneService
{
    public function findDesign(int $designId): RuhiDesign
    {
        return RuhiDesign::withTrashed()->findOrFail($designId);
    }

    public function findProduct(int $productId): RuhiProduct
    {
        return RuhiProduct::withTrashed()->findOrFail($productId);
    }

    public function paginateForDesignAndProduct(int $designId, int $productId, string $search, int $perPage): LengthAwarePaginator
    {
        $query = RuhiDesignProductItemKstone::query()
            ->where('design_id', $designId)
            ->where('product_id', $productId)
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

    public function listAllForDesignAndProduct(int $designId, int $productId): Collection
    {
        return RuhiDesignProductItemKstone::query()
            ->where('design_id', $designId)
            ->where('product_id', $productId)
            ->with(['product', 'kstone'])
            ->orderByDesc('id')
            ->get();
    }

    public function listKstonesByProductFromItemKstone(int $productId): Collection
    {
        return RuhiItemKstone::query()
            ->where('item_id', $productId)
            ->with('kstone')
            ->orderBy('id')
            ->get()
            ->values();
    }

    public function create(array $attributes): RuhiDesignProductItemKstone
    {
        return RuhiDesignProductItemKstone::query()->create($attributes);
    }

    public function existingByDesignAndProduct(int $designId, int $productId): Collection
    {
        return RuhiDesignProductItemKstone::query()
            ->where('design_id', $designId)
            ->where('product_id', $productId)
            ->get()
            ->keyBy('kstone_id');
    }

    public function updateOrCreateByKeys(array $keys, array $attributes): RuhiDesignProductItemKstone
    {
        return RuhiDesignProductItemKstone::query()->updateOrCreate($keys, $attributes);
    }
}
