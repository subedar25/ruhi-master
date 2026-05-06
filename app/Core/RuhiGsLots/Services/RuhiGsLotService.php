<?php

namespace App\Core\RuhiGsLots\Services;

use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiDesign;
use App\Models\RuhiSlot;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RuhiGsLotService
{
    public function findGs(int $gsId): RuhiGs
    {
        return RuhiGs::withTrashed()->findOrFail($gsId);
    }

    public function listDesignsForDropdown(): Collection
    {
        return RuhiDesign::query()
            ->whereNull('deleted_at')
            ->orderBy('design_name')
            ->get(['id', 'design_name']);
    }

    public function listSlotsByGs(int $gsId): Collection
    {
        return RuhiSlot::query()
            ->where('gs_id', $gsId)
            ->get()
            ->sort(function (RuhiSlot $a, RuhiSlot $b): int {
                [$aHasNum, $aNum, $aPrefix, $aName] = $this->lotSortTuple((string) $a->slot_name);
                [$bHasNum, $bNum, $bPrefix, $bName] = $this->lotSortTuple((string) $b->slot_name);

                if ($aHasNum !== $bHasNum) {
                    return $aHasNum <=> $bHasNum;
                }
                if ($aNum !== $bNum) {
                    return $aNum <=> $bNum;
                }
                if ($aPrefix !== $bPrefix) {
                    return $aPrefix <=> $bPrefix;
                }
                if ($aName !== $bName) {
                    return $aName <=> $bName;
                }

                return ((int) $a->id) <=> ((int) $b->id);
            })
            ->values();
    }

    /**
     * @return array{0:int,1:int,2:string,3:string}
     */
    private function lotSortTuple(string $name): array
    {
        $name = trim($name);
        $num = 0;
        $hasNum = 1;

        if (preg_match('/(\d+)\s*$/', $name, $m) === 1) {
            $num = (int) $m[1];
            $hasNum = 0;
        } elseif (preg_match_all('/\d+/', $name, $all) === 1 && ! empty($all[0])) {
            $num = (int) end($all[0]);
            $hasNum = 0;
        }

        $prefix = mb_strtolower(trim((string) preg_replace('/\d+/', '', $name)));
        $normalized = mb_strtolower($name);

        return [$hasNum, $num, $prefix, $normalized];
    }

    public function listLotsWithItemsByGs(int $gsId): Collection
    {
        return RuhiSlot::query()
            ->where('gs_id', $gsId)
            ->with(['lotItems' => function ($query) {
                $query->with('product')->orderByDesc('id');
            }])
            ->get()
            ->sort(function (RuhiSlot $a, RuhiSlot $b): int {
                [$aHasNum, $aNum, $aPrefix, $aName] = $this->lotSortTuple((string) $a->slot_name);
                [$bHasNum, $bNum, $bPrefix, $bName] = $this->lotSortTuple((string) $b->slot_name);

                if ($aHasNum !== $bHasNum) {
                    return $aHasNum <=> $bHasNum;
                }
                if ($aNum !== $bNum) {
                    return $aNum <=> $bNum;
                }
                if ($aPrefix !== $bPrefix) {
                    return $aPrefix <=> $bPrefix;
                }
                if ($aName !== $bName) {
                    return $aName <=> $bName;
                }

                return ((int) $a->id) <=> ((int) $b->id);
            })
            ->values();
    }

    public function paginateLotItemRowsByGs(int $gsId, string $search = '', ?int $lotId = null, int $perPage = 20): LengthAwarePaginator
    {
        return RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->when($lotId, function ($query) use ($lotId) {
                $query->where('lot_id', $lotId);
            })
            ->when(trim($search) !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('product', function ($sq) use ($search) {
                        $sq->where('product_name', 'like', "%{$search}%");
                    })->orWhereHas('design', function ($sq) use ($search) {
                        $sq->where('design_name', 'like', "%{$search}%");
                    })->orWhereHas('lot', function ($sq) use ($search) {
                        $sq->where('slot_name', 'like', "%{$search}%");
                    })->orWhere('id', 'like', "%{$search}%");
                });
            })
            ->with(['design', 'lot'])
            ->orderByDesc('id')
            ->paginate($perPage)
            ->onEachSide(1);
    }

    public function createLotWithItems(int $gsId, string $lotName, array $rows): void
    {
        DB::transaction(function () use ($gsId, $lotName, $rows) {
            $lot = RuhiSlot::query()->create([
                'gs_id' => $gsId,
                'slot_name' => $lotName,
            ]);

            foreach ($rows as $row) {
                $qty = (int) ($row['design_qty'] ?? 0);
                $red = (int) ($row['design_red_qty'] ?? 0);
                $redGreen = (int) ($row['design_red_green_qty'] ?? 0);
                $green = (int) ($row['design_green_qty'] ?? 0);
                $white = max($qty - ($red + $redGreen + $green), 0);

                RuhiGsOrderByColor::query()->create([
                    'gs_id' => $gsId,
                    'lot_id' => (int) $lot->id,
                    'design_id' => (int) $row['design_id'],
                    'design_qty' => $qty,
                    'design_red_qty' => $red,
                    'design_red_green_qty' => $redGreen,
                    'design_green_qty' => $green,
                    'white_qty' => $white,
                ]);
            }
        });
    }

    public function addItemsInLot(int $gsId, int $lotId, array $rows): void
    {
        foreach ($rows as $row) {
            RuhiGsOrderByColor::query()->create([
                'gs_id' => $gsId,
                'lot_id' => $lotId,
                'design_id' => (int) $row['design_id'],
                'design_qty' => (int) $row['design_qty'],
                'design_red_qty' => (int) $row['design_red_qty'],
                'design_red_green_qty' => (int) $row['design_red_green_qty'],
                'design_green_qty' => (int) $row['design_green_qty'],
                'white_qty' => (int) $row['white_qty'],
            ]);
        }
    }

    public function deleteLotItemById(int $id, int $gsId): int
    {
        return RuhiGsOrderByColor::query()
            ->where('id', $id)
            ->where('gs_id', $gsId)
            ->delete();
    }

    public function findLotItemById(int $id, int $gsId): RuhiGsOrderByColor
    {
        return RuhiGsOrderByColor::query()
            ->where('id', $id)
            ->where('gs_id', $gsId)
            ->firstOrFail();
    }

    public function updateLotItem(RuhiGsOrderByColor $row, array $attributes): bool
    {
        return $row->update($attributes);
    }
}
