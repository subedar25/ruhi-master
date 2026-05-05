<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiItemKstone;
use App\Models\RuhiProduct;
use Illuminate\Support\Collection;

class GsWiseColletReportService
{
    /** Collet lines use `r_design_products.item_type_id` (= `r_item_type.id`). */
    private const COLLET_ITEM_TYPE_ID = 3;

    /** Casting totals (same product id) feed `casting_kstone_totalqty` like legacy. */
    private const CASTING_ITEM_TYPE_ID = 2;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Collet quantities aggregated per GS (all lots), matching legacy
     * `SUM(OT.quantity * CC.design_qty)` over `r_gs_order_by_color` for the GS.
     * K stone: first row from `r_k_stone` for the product (`item_id`), same as legacy getKstoneByProductId(…, 1).
     *
     * @return array{
     *     gs_name: string,
     *     rows: array<int, array{
     *         collet: string,
     *         total_quantity: int,
     *         weight: float,
     *         total_casting_quantity: int,
     *         kstone_id: int|string,
     *         kstone_quantity: int,
     *         kstone_name: string,
     *         kstone_stoneweight: float,
     *         kstone_dieweight: float,
     *         kstone_totalqty: int,
     *         casting_kstone_totalqty: int
     *     }>,
     *     grand_total_quantity: int,
     *     grand_total_weight: float,
     *     grand_total_kstone_qty: int,
     *     grand_total_casting_kstone_qty: int
     * }
     */
    public function buildReport(int $gsId): array
    {
        $gs = RuhiGs::query()->whereNull('deleted_at')->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        $orderRows = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->get(['design_id', 'design_qty']);

        $designQtyByDesign = [];
        foreach ($orderRows as $row) {
            $did = (int) $row->design_id;
            $designQtyByDesign[$did] = ($designQtyByDesign[$did] ?? 0) + (int) $row->design_qty;
        }

        $castingQtyByProduct = $this->aggregateQuantityByProduct($designQtyByDesign, self::CASTING_ITEM_TYPE_ID);
        $byProduct = $this->aggregateQuantityByProduct($designQtyByDesign, self::COLLET_ITEM_TYPE_ID);

        $rows = collect(array_values($byProduct))
            ->sort(function (array $a, array $b): int {
                $ta = ReportNameSort::hyphenNameTuple((string) $a['collet']);
                $tb = ReportNameSort::hyphenNameTuple((string) $b['collet']);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values()
            ->map(function (array $r) use ($castingQtyByProduct): array {
                $pid = (int) $r['product_id'];
                $colletQty = (int) round($r['total_quantity']);
                $castingQty = (int) round($castingQtyByProduct[$pid]['total_quantity'] ?? 0);

                $k = $this->kstoneMetricsForProduct($pid, $colletQty, $castingQty);

                return [
                    'collet' => $r['collet'],
                    'total_quantity' => $colletQty,
                    'weight' => round((float) $r['weight'], 2),
                    'total_casting_quantity' => $castingQty,
                    'kstone_id' => $k['kstone_id'],
                    'kstone_quantity' => $k['kstone_quantity'],
                    'kstone_name' => $k['kstone_name'],
                    'kstone_stoneweight' => $k['kstone_stoneweight'],
                    'kstone_dieweight' => $k['kstone_dieweight'],
                    'kstone_totalqty' => $k['kstone_totalqty'],
                    'casting_kstone_totalqty' => $k['casting_kstone_totalqty'],
                ];
            })
            ->all();

        $grandTotalQty = (int) array_sum(array_column($rows, 'total_quantity'));
        $grandTotalWt = round((float) array_sum(array_column($rows, 'weight')), 2);
        $grandKstone = (int) array_sum(array_column($rows, 'kstone_totalqty'));
        $grandCastingKstone = (int) array_sum(array_column($rows, 'casting_kstone_totalqty'));

        return [
            'gs_name' => $gsName,
            'rows' => $rows,
            'grand_total_quantity' => $grandTotalQty,
            'grand_total_weight' => $grandTotalWt,
            'grand_total_kstone_qty' => $grandKstone,
            'grand_total_casting_kstone_qty' => $grandCastingKstone,
        ];
    }

    /**
     * @param  array<int, int>  $designQtyByDesign
     * @return array<int, array{product_id: int, collet: string, total_quantity: float, weight: float}>
     */
    private function aggregateQuantityByProduct(array $designQtyByDesign, int $itemTypeId): array
    {
        $byProduct = [];

        foreach ($designQtyByDesign as $designId => $designQty) {
            $designProducts = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->where('item_type_id', $itemTypeId)
                ->get();

            foreach ($designProducts as $dp) {
                $product = RuhiProduct::query()->withTrashed()->find($dp->product_id);
                if (! $product) {
                    continue;
                }

                $pid = (int) $product->id;
                $dpQty = (int) $dp->quantity;
                $weightUnit = (float) $product->weight;
                $lineQty = $dpQty * (int) $designQty;
                $lineWeight = $lineQty * $weightUnit;

                if (! isset($byProduct[$pid])) {
                    $byProduct[$pid] = [
                        'product_id' => $pid,
                        'collet' => (string) $product->product_name,
                        'total_quantity' => 0.0,
                        'weight' => 0.0,
                    ];
                }

                $byProduct[$pid]['total_quantity'] += $lineQty;
                $byProduct[$pid]['weight'] += $lineWeight;
            }
        }

        return $byProduct;
    }

    /**
     * Mirrors legacy calculateKstone(): first `r_k_stone` row per product, multiples by collet and casting totals.
     *
     * @return array{
     *     kstone_id: int|string,
     *     kstone_quantity: int,
     *     kstone_name: string,
     *     kstone_stoneweight: float,
     *     kstone_dieweight: float,
     *     kstone_totalqty: int,
     *     casting_kstone_totalqty: int
     * }
     */
    private function kstoneMetricsForProduct(int $productId, int $colletQty, int $castingQty): array
    {
        $line = RuhiItemKstone::query()
            ->where('item_id', $productId)
            ->whereHas('kstone', fn ($q) => $q->withTrashed())
            ->with(['kstone' => fn ($q) => $q->withTrashed()])
            ->orderBy('id')
            ->first();

        if (! $line) {
            return [
                'kstone_id' => '',
                'kstone_quantity' => 0,
                'kstone_name' => '',
                'kstone_stoneweight' => 0.0,
                'kstone_dieweight' => 0.0,
                'kstone_totalqty' => 0,
                'casting_kstone_totalqty' => 0,
            ];
        }

        $ksQty = (int) $line->kstone_quantity;
        $master = $line->kstone;

        return [
            'kstone_id' => (int) $line->kstone_id,
            'kstone_quantity' => $ksQty,
            'kstone_name' => $master ? $master->displayLabel() : '',
            'kstone_stoneweight' => $master ? (float) $master->stoneweight : (float) $line->kstone_weight,
            'kstone_dieweight' => $master ? (float) $master->dieweight : (float) $line->kstone_dieweight,
            'kstone_totalqty' => (int) ($colletQty * $ksQty),
            'casting_kstone_totalqty' => (int) ($castingQty * $ksQty),
        ];
    }
}
