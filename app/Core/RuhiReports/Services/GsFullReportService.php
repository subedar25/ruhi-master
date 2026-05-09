<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiItemKstone;
use App\Models\RuhiProduct;
use Illuminate\Support\Collection;

class GsFullReportService
{
    /** Matches `r_item_type`: "AD Full" — shown as Addfull in the report. */
    private const AD_FULL_ITEM_TYPE_ID = 4;

    private const PULKI_FULL_ITEM_TYPE_ID = 5;

    private const KUNDAN_FULL_ITEM_TYPE_ID = 6;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Three GS-wise full sections: Kundan Full (6), Polki Full (5), AD Full (4) with k stone on AD Full rows.
     *
     * @return array{
     *     gs_name: string,
     *     kundanfull: array{rows: array<int, array{kundanfull: string, total_quantity: int, weight: float}>, grand_total_quantity: int, grand_total_weight: float},
     *     pulkifull: array{rows: array<int, array{pulkifull: string, total_quantity: int, weight: float}>, grand_total_quantity: int, grand_total_weight: float},
     *     addfull: array{rows: array<int, array{addfull: string, kstone_name: string, total_quantity: int, weight: float}>, grand_total_quantity: int, grand_total_weight: float}
     * }
     */
    public function buildReport(int $gsId): array
    {
        $gs = RuhiGs::query()->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        $designQtyByDesign = $this->designQtyByDesign($gsId);

        return [
            'gs_name' => $gsName,
            'kundanfull' => $this->buildSimpleBlock($designQtyByDesign, self::KUNDAN_FULL_ITEM_TYPE_ID, 'kundanfull'),
            'pulkifull' => $this->buildSimpleBlock($designQtyByDesign, self::PULKI_FULL_ITEM_TYPE_ID, 'pulkifull'),
            'addfull' => $this->buildAddfullBlock($designQtyByDesign),
        ];
    }

    /**
     * @return array<int, int>
     */
    private function designQtyByDesign(int $gsId): array
    {
        $orderRows = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->get(['design_id', 'design_qty']);

        $map = [];
        foreach ($orderRows as $row) {
            $did = (int) $row->design_id;
            $map[$did] = ($map[$did] ?? 0) + (int) $row->design_qty;
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $designQtyByDesign
     * @return array{rows: array<int, array<string, mixed>>, grand_total_quantity: int, grand_total_weight: float}
     */
    private function buildSimpleBlock(array $designQtyByDesign, int $itemTypeId, string $labelKey): array
    {
        $byProduct = $this->aggregateByProduct($designQtyByDesign, $itemTypeId);

        $rows = collect(array_values($byProduct))
            ->sort(function (array $a, array $b): int {
                $ta = ReportNameSort::hyphenNameTuple((string) $a['label']);
                $tb = ReportNameSort::hyphenNameTuple((string) $b['label']);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values()
            ->map(function (array $r) use ($labelKey): array {
                return [
                    $labelKey => $r['label'],
                    'total_quantity' => (int) round($r['total_quantity']),
                    'weight' => round((float) $r['weight'], 2),
                ];
            })
            ->all();

        $grandQty = (int) array_sum(array_column($rows, 'total_quantity'));
        $grandWt = round((float) array_sum(array_column($rows, 'weight')), 2);

        return [
            'rows' => $rows,
            'grand_total_quantity' => $grandQty,
            'grand_total_weight' => $grandWt,
        ];
    }

    /**
     * @param  array<int, int>  $designQtyByDesign
     * @return array{rows: array<int, array{addfull: string, kstone_name: string, total_quantity: int, weight: float}>, grand_total_quantity: int, grand_total_weight: float}
     */
    private function buildAddfullBlock(array $designQtyByDesign): array
    {
        $byProduct = $this->aggregateByProduct($designQtyByDesign, self::AD_FULL_ITEM_TYPE_ID);

        $rows = collect(array_values($byProduct))
            ->sort(function (array $a, array $b): int {
                $ta = ReportNameSort::hyphenNameTuple((string) $a['label']);
                $tb = ReportNameSort::hyphenNameTuple((string) $b['label']);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values()
            ->map(function (array $r): array {
                $pid = (int) $r['product_id'];

                return [
                    'addfull' => $r['label'],
                    'kstone_name' => $this->kstoneNameForProduct($pid),
                    'total_quantity' => (int) round($r['total_quantity']),
                    'weight' => round((float) $r['weight'], 2),
                ];
            })
            ->all();

        $grandQty = (int) array_sum(array_column($rows, 'total_quantity'));
        $grandWt = round((float) array_sum(array_column($rows, 'weight')), 2);

        return [
            'rows' => $rows,
            'grand_total_quantity' => $grandQty,
            'grand_total_weight' => $grandWt,
        ];
    }

    private function kstoneNameForProduct(int $productId): string
    {
        $line = RuhiItemKstone::query()
            ->where('item_id', $productId)
            ->whereHas('kstone', fn ($q) => $q->withTrashed())
            ->with(['kstone' => fn ($q) => $q->withTrashed()])
            ->orderBy('id')
            ->first();

        if ($line?->kstone) {
            return $line->kstone->displayLabel();
        }

        return '';
    }

    /**
     * @param  array<int, int>  $designQtyByDesign
     * @return array<int, array{product_id: int, label: string, total_quantity: float, weight: float}>
     */
    private function aggregateByProduct(array $designQtyByDesign, int $itemTypeId): array
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
                        'label' => (string) $product->product_name,
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
}
