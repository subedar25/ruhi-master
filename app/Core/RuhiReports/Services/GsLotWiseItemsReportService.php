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
            ->orderBy('slot_name')
            ->orderBy('id')
            ->get();

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
        $pos = strpos($designName, '-');
        if ($pos === false) {
            $leading = '';
            $afterHyphen = $designName;
        } else {
            $mysqlLocate = $pos + 1;
            $leading = substr($designName, 0, $mysqlLocate);
            $afterHyphen = substr($designName, $mysqlLocate);
        }

        if (preg_match('/^\s*(-?\d+)/', $afterHyphen, $m)) {
            $num = (int) $m[1];
        } else {
            $num = 0;
        }

        return [$leading, $num];
    }

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
