<?php

namespace App\Core\RuhiReports\Services;

use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
/**
 * GS Color Collet Report: products with {@see RuhiProduct} product_type = 3 (Collet),
 * quantities from {@see RuhiCollateByColor} scaled by GS order design_qty (same pattern as GS Detail Each Item).
 */
class GsColorColletReportService
{
    /** Master product type “Collet” on {@see RuhiProduct::product_type}. */
    public const PRODUCT_TYPE_COLLET = 3;

    public function listGsForDropdown(): \Illuminate\Support\Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array{
     *     gs_name: string,
     *     rows: array<int, array{collet: string, red: int, green: int, weight: float}>,
     *     grand_red: int,
     *     grand_green: int,
     *     grand_weight: float
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

        if ($designQtyByDesign === []) {
            return [
                'gs_name' => $gsName,
                'rows' => [],
                'grand_red' => 0,
                'grand_green' => 0,
                'grand_weight' => 0.0,
            ];
        }

        $rows = [];

        foreach ($designQtyByDesign as $designId => $scale) {
            $scale = max((int) $scale, 0);

            $dps = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->whereHas('product', fn ($q) => $q->where('product_type', self::PRODUCT_TYPE_COLLET)->withTrashed())
                ->with([
                    'product' => fn ($q) => $q->withTrashed(),
                    'collateByColors',
                ])
                ->get();

            foreach ($dps as $dp) {
                $product = $dp->product;
                if (! $product) {
                    continue;
                }

                $cc = $dp->collateByColors;
                $sumOnlyRed = (int) $cc->sum('only_red_qty');
                $sumRed = (int) $cc->sum('red_qty');
                $sumGreen = (int) $cc->sum('green_qty');
                $sumOnlyGreen = (int) $cc->sum('only_green_qty');

                $redScaled = (int) round(($sumOnlyRed + $sumRed) * $scale);
                $greenScaled = (int) round(($sumGreen + $sumOnlyGreen) * $scale);

                $baseQty = (int) $dp->quantity * $scale;
                $wUnit = (float) $product->weight;
                $lineWeight = round($baseQty * $wUnit, 2);

                $rows[] = [
                    'collet' => (string) $product->product_name,
                    'red' => $redScaled,
                    'green' => $greenScaled,
                    'weight' => $lineWeight,
                ];
            }
        }

        $rows = $this->mergeRowsByColletName($rows);

        usort($rows, function (array $a, array $b): int {
            return $this->compareProductNameMysqlColletOrder($a['collet'], $b['collet']);
        });

        $grandRed = (int) array_sum(array_column($rows, 'red'));
        $grandGreen = (int) array_sum(array_column($rows, 'green'));
        $grandWeight = round((float) array_sum(array_column($rows, 'weight')), 2);

        return [
            'gs_name' => $gsName,
            'rows' => array_values($rows),
            'grand_red' => $grandRed,
            'grand_green' => $grandGreen,
            'grand_weight' => $grandWeight,
        ];
    }

    /**
     * One row per collet (product) name: duplicate names are combined; red, green, weight are summed.
     *
     * @param  array<int, array{collet: string, red: int, green: int, weight: float}>  $rows
     * @return array<int, array{collet: string, red: int, green: int, weight: float}>
     */
    private function mergeRowsByColletName(array $rows): array
    {
        $byKey = [];
        foreach ($rows as $r) {
            $name = trim((string) $r['collet']);
            if ($name === '') {
                $name = (string) $r['collet'];
            }
            if (! isset($byKey[$name])) {
                $byKey[$name] = [
                    'collet' => $name,
                    'red' => 0,
                    'green' => 0,
                    'weight' => 0.0,
                ];
            }
            $byKey[$name]['red'] += (int) $r['red'];
            $byKey[$name]['green'] += (int) $r['green'];
            $byKey[$name]['weight'] += (float) $r['weight'];
        }

        foreach ($byKey as &$r) {
            $r['weight'] = round($r['weight'], 2);
        }
        unset($r);

        return array_values($byKey);
    }

    /**
     * Matches SQL: ORDER BY LEFT(P.product_name, LOCATE('-', P.product_name)),
     * CAST(SUBSTRING(P.product_name, LOCATE('-', P.product_name)+1) AS SIGNED).
     */
    private function compareProductNameMysqlColletOrder(string $a, string $b): int
    {
        [$prefA, $numA] = $this->mysqlColletSortKey($a);
        [$prefB, $numB] = $this->mysqlColletSortKey($b);
        $c = strcmp($prefA, $prefB);
        if ($c !== 0) {
            return $c;
        }

        return $numA <=> $numB;
    }

    /**
     * @return array{0: string, 1: int}
     */
    private function mysqlColletSortKey(string $productName): array
    {
        $pos = strpos($productName, '-');
        if ($pos === false) {
            return ['', $this->mysqlCastAsSigned($productName)];
        }

        $prefix = substr($productName, 0, $pos + 1);
        $afterHyphen = substr($productName, $pos + 1);

        return [$prefix, $this->mysqlCastAsSigned($afterHyphen)];
    }

    /** Leading integer like MySQL CAST(... AS SIGNED). */
    private function mysqlCastAsSigned(string $s): int
    {
        $s = trim($s);
        if ($s === '') {
            return 0;
        }
        if (preg_match('/^([+\-]?\d+)/', $s, $m)) {
            return (int) $m[1];
        }

        return 0;
    }
}
