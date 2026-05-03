<?php

namespace App\Core\RuhiReports\Services;

use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiSlot;
use Illuminate\Support\Collection;

class GsLotWiseItemsReportService
{
    /**
     * @return Collection<int, array{lot: RuhiSlot, total_collate: float|int, designs: Collection<int, array{design_name: string, design_qty: int}>}>
     */
    public function lotBlocksForGs(int $gsId): Collection
    {
        $slots = RuhiSlot::query()
            ->where('gs_id', $gsId)
            ->get()
            ->all();
        usort($slots, function (RuhiSlot $a, RuhiSlot $b): int {
            [$aPrefix, $aHasNum, $aNum, $aName] = $this->slotSortTuple((string) $a->slot_name);
            [$bPrefix, $bHasNum, $bNum, $bName] = $this->slotSortTuple((string) $b->slot_name);

            // Number-first: if both contain a number, compare by numeric value first.
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
        });
        $slots = collect($slots);

        return $slots->map(function (RuhiSlot $slot) use ($gsId) {
            $rows = RuhiGsOrderByColor::query()
                ->where('gs_id', $gsId)
                ->where('lot_id', $slot->id)
                ->with('design')
                ->orderBy('id')
                ->get();

            $totalCollate = 0.0;
            $byDesign = [];

            foreach ($rows as $row) {
                $dubby = (float) ($row->design->dubby_qty ?? 0);
                $qty = (int) $row->design_qty;
                $totalCollate += $qty * $dubby;

                $did = (int) $row->design_id;
                if (! isset($byDesign[$did])) {
                    $byDesign[$did] = [
                        'design_name' => (string) ($row->design->design_name ?? ('#'.$did)),
                        'design_qty' => 0,
                    ];
                }
                $byDesign[$did]['design_qty'] += $qty;
            }

            $designsSorted = collect(array_values($byDesign))
                ->sort(function (array $a, array $b): int {
                    $ta = $this->designNameSortTuple((string) $a['design_name']);
                    $tb = $this->designNameSortTuple((string) $b['design_name']);
                    if ($ta[0] !== $tb[0]) {
                        return $ta[0] <=> $tb[0];
                    }

                    return $ta[1] <=> $tb[1];
                })
                ->values();

            return [
                'lot' => $slot,
                'total_collate' => $totalCollate,
                'designs' => $designsSorted,
            ];
        });
    }

    /**
     * Matches MySQL:
     * ORDER BY LEFT(D.design_name, LOCATE('-', D.design_name)),
     *          CAST(SUBSTRING(D.design_name, LOCATE('-', D.design_name) + 1) AS SIGNED)
     */
    private function designNameSortTuple(string $designName): array
    {
        return $this->naturalNameSortTuple($designName);
    }

    /**
     * Natural sort tuple:
     * - numeric suffix (e.g. Lot-10) sorted by number
     * - non-numeric names sorted alphabetically
     */
    private function naturalNameSortTuple(string $name): array
    {
        $name = trim($name);
        if (preg_match('/^(.*?)(?:-)?(\d+)\s*$/', $name, $m)) {
            return [mb_strtolower(trim($m[1])), 0, (int) $m[2]];
        }

        return [mb_strtolower($name), 1, 0];
    }

    private function slotSortTuple(string $name): array
    {
        $name = trim($name);
        $num = 0;
        $hasNum = 1;
        if (preg_match('/(\d+)\s*$/', $name, $numMatch) === 1) {
            $num = (int) $numMatch[1];
            $hasNum = 0;
        } elseif (preg_match_all('/\d+/', $name, $allNums) === 1 && !empty($allNums[0])) {
            $num = (int) end($allNums[0]);
            $hasNum = 0;
        }
        $prefix = mb_strtolower(trim((string) preg_replace('/\d+/', '', $name)));
        $normalizedName = mb_strtolower($name);

        return [$prefix, $hasNum, $num, $normalizedName];
    }

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
