<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiProduct;
use Illuminate\Support\Collection;

class GsWiseDubbyReportService
{
    /** Dubby lines use `r_design_products.item_type_id` (= `r_item_type.id`). */
    private const DUBBY_ITEM_TYPE_ID = 1;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Aggregates dubby design products for all lots under this GS.
     * Total quantity = design_products.quantity × sum(design_qty from r_gs_order_by_color for this gs & design).
     * Weight = total quantity × r_product.weight.
     *
     * @return array{
     *     gs_name: string,
     *     rows: array<int, array{dubby: string, total_quantity: int, weight: float}>
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

        $byProduct = [];

        foreach ($designQtyByDesign as $designId => $designQty) {
            $designProducts = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->where('item_type_id', self::DUBBY_ITEM_TYPE_ID)
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
                        'dubby' => (string) $product->product_name,
                        'total_quantity' => 0.0,
                        'weight' => 0.0,
                    ];
                }

                $byProduct[$pid]['total_quantity'] += $lineQty;
                $byProduct[$pid]['weight'] += $lineWeight;
            }
        }

        $rows = collect(array_values($byProduct))
            ->sort(function (array $a, array $b): int {
                $ta = ReportNameSort::hyphenNameTuple((string) $a['dubby']);
                $tb = ReportNameSort::hyphenNameTuple((string) $b['dubby']);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values()
            ->map(function (array $r): array {
                return [
                    'dubby' => $r['dubby'],
                    'total_quantity' => (int) round($r['total_quantity']),
                    'weight' => round($r['weight'], 2),
                ];
            })
            ->all();

        return [
            'gs_name' => $gsName,
            'rows' => $rows,
        ];
    }
}
