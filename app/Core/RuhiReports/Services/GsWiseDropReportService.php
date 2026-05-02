<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;

/**
 * GS Wise Drop Report: lines where {@see RuhiDesignProduct::item_type_id} is Drop (legacy id 8),
 * quantities from collate-by-color scaled by GS design_qty (same as GS Detail Each Item drop rows).
 */
class GsWiseDropReportService
{
    /** Drop lines use `r_design_products.item_type_id` (= `r_item_type.id`). */
    private const DROP_ITEM_TYPE_ID = 8;

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
     *     rows: array<int, array{drop: string, red: int, green: int, white: int}>
     * }
     */
    public function buildReport(int $gsId): array
    {
        $gs = RuhiGs::query()->whereNull('deleted_at')->find($gsId);
        if (! $gs) {
            return [
                'gs_name' => '',
                'rows' => [],
            ];
        }

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
            ];
        }

        $rows = [];

        foreach ($designQtyByDesign as $designId => $scale) {
            $scale = max((int) $scale, 0);

            $dps = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->where('item_type_id', self::DROP_ITEM_TYPE_ID)
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
                $sumWhite = (int) $cc->sum('white_qty');

                $redScaled = (int) round(($sumOnlyRed + $sumRed) * $scale);
                $greenScaled = (int) round(($sumGreen + $sumOnlyGreen) * $scale);
                $whiteScaled = (int) round($sumWhite * $scale);

                $rows[] = [
                    'drop' => (string) $product->product_name,
                    'red' => $redScaled,
                    'green' => $greenScaled,
                    'white' => $whiteScaled,
                ];
            }
        }

        $rows = $this->mergeRowsByDropName($rows);

        usort($rows, function (array $a, array $b): int {
            return ReportNameSort::compareTuples(
                ReportNameSort::hyphenNameTuple($a['drop']),
                ReportNameSort::hyphenNameTuple($b['drop'])
            );
        });

        return [
            'gs_name' => $gsName,
            'rows' => array_values($rows),
        ];
    }

    /**
     * @param  array<int, array{drop: string, red: int, green: int, white: int}>  $rows
     * @return array<int, array{drop: string, red: int, green: int, white: int}>
     */
    private function mergeRowsByDropName(array $rows): array
    {
        $byKey = [];
        foreach ($rows as $r) {
            $name = trim((string) $r['drop']);
            if ($name === '') {
                $name = (string) $r['drop'];
            }
            if (! isset($byKey[$name])) {
                $byKey[$name] = [
                    'drop' => $name,
                    'red' => 0,
                    'green' => 0,
                    'white' => 0,
                ];
            }
            $byKey[$name]['red'] += (int) $r['red'];
            $byKey[$name]['green'] += (int) $r['green'];
            $byKey[$name]['white'] += (int) $r['white'];
        }

        return array_values($byKey);
    }
}
