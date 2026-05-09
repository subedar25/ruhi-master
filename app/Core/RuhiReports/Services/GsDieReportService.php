<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiItemKstone;
use App\Models\RuhiProduct;
use Illuminate\Support\Collection;

class GsDieReportService
{
    /** Collet + Kundan Full on `r_product.product_type` (`r_item_type.id`). */
    private const PRODUCT_TYPE_IDS = [3, 6];

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Die totals aggregated by k stone across GS orders for products with product_type 3 or 6.
     * Per product GS qty = sum(design_products.quantity × design_qty); then multiply each `r_k_stone` row.
     * Die weight per line = `r_item_k_stone.kstone_dieweight` × line k stone quantity
     * (line k stone qty = product GS qty × `kstone_quantity` on that mapping row).
     *
     * @return array{
     *     gs_name: string,
     *     rows: array<int, array{kstone_name: string, kstone_quantity: int, weight: float, die_weight: float}>,
     *     grand_kstone_quantity: int,
     *     grand_weight: float,
     *     grand_die_weight: float
     * }
     */
    public function buildReport(int $gsId): array
    {
        $gs = RuhiGs::query()->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        $orderRows = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->get(['design_id', 'design_qty']);

        $designQtyByDesign = [];
        foreach ($orderRows as $row) {
            $did = (int) $row->design_id;
            $designQtyByDesign[$did] = ($designQtyByDesign[$did] ?? 0) + (int) $row->design_qty;
        }

        $productQtyForGs = [];

        foreach ($designQtyByDesign as $designId => $designQty) {
            $designProducts = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->get();

            foreach ($designProducts as $dp) {
                $product = RuhiProduct::query()->withTrashed()->find($dp->product_id);
                if (! $product) {
                    continue;
                }
                $ptype = (int) $product->product_type;
                if (! in_array($ptype, self::PRODUCT_TYPE_IDS, true)) {
                    continue;
                }

                $pid = (int) $product->id;
                $lineQty = (int) $dp->quantity * (int) $designQty;
                $productQtyForGs[$pid] = ($productQtyForGs[$pid] ?? 0) + $lineQty;
            }
        }

        $byKstoneId = [];

        foreach ($productQtyForGs as $productId => $totalProductQty) {
            $itemLines = RuhiItemKstone::query()
                ->where('item_id', $productId)
                ->whereHas('kstone', fn ($q) => $q->withTrashed())
                ->with(['kstone' => fn ($q) => $q->withTrashed()])
                ->orderBy('id')
                ->get();

            foreach ($itemLines as $ik) {
                $kid = (int) $ik->kstone_id;
                $ksPerUnit = (int) $ik->kstone_quantity;
                $wtPerUnit = (float) $ik->kstone_weight;
                $diePerUnit = (float) $ik->kstone_dieweight;

                $master = $ik->kstone;
                $name = $master ? $master->displayLabel() : '';

                $qtyTotal = (int) ($totalProductQty * $ksPerUnit);
                $weightTotal = $totalProductQty * $wtPerUnit;
                $dieTotal = $diePerUnit * $qtyTotal;

                if ($weightTotal == 0.0 && $master && $qtyTotal > 0) {
                    $weightTotal = $qtyTotal * (float) $master->stoneweight;
                }

                if (! isset($byKstoneId[$kid])) {
                    $byKstoneId[$kid] = [
                        'kstone_name' => $name,
                        'kstone_quantity' => 0,
                        'weight' => 0.0,
                        'die_weight' => 0.0,
                    ];
                } else {
                    if ($name !== '' && $byKstoneId[$kid]['kstone_name'] === '') {
                        $byKstoneId[$kid]['kstone_name'] = $name;
                    }
                }

                $byKstoneId[$kid]['kstone_quantity'] += $qtyTotal;
                $byKstoneId[$kid]['weight'] += $weightTotal;
                $byKstoneId[$kid]['die_weight'] += $dieTotal;
            }
        }

        $rows = collect(array_values($byKstoneId))
            ->sort(function (array $a, array $b): int {
                $ta = ReportNameSort::hyphenNameTuple((string) $a['kstone_name']);
                $tb = ReportNameSort::hyphenNameTuple((string) $b['kstone_name']);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values()
            ->map(function (array $r): array {
                return [
                    'kstone_name' => $r['kstone_name'] !== '' ? $r['kstone_name'] : '—',
                    'kstone_quantity' => (int) $r['kstone_quantity'],
                    'weight' => round((float) $r['weight'], 3),
                    'die_weight' => round((float) $r['die_weight'], 3),
                ];
            })
            ->all();

        $grandQty = (int) array_sum(array_column($rows, 'kstone_quantity'));
        $grandWt = round((float) array_sum(array_column($rows, 'weight')), 3);
        $grandDie = round((float) array_sum(array_column($rows, 'die_weight')), 3);

        return [
            'gs_name' => $gsName,
            'rows' => $rows,
            'grand_kstone_quantity' => $grandQty,
            'grand_weight' => $grandWt,
            'grand_die_weight' => $grandDie,
        ];
    }
}
