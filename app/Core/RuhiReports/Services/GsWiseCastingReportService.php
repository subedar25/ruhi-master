<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiDesign;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiProduct;
use App\Models\RuhiSlot;
use Illuminate\Support\Collection;

class GsWiseCastingReportService
{
    /** Casting lines use `r_design_products.item_type_id` (= `r_item_type.id`). */
    private const CASTING_ITEM_TYPE_ID = 2;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Lots (slots) belonging to the given GS.
     *
     * @return Collection<int, RuhiSlot>
     */
    public function listLotsForGs(int $gsId): Collection
    {
        return RuhiSlot::query()
            ->where('gs_id', $gsId)
            ->orderBy('slot_name')
            ->orderBy('id')
            ->get();
    }

    /**
     * @return array{
     *     gs_name: string,
     *     lot_name: string,
     *     design_names_csv: string,
     *     rows: array<int, array{casting: string, total_quantity: int, weight: float}>,
     *     grand_total_quantity: int,
     *     grand_total_weight: float
     * }
     */
    public function buildReport(int $gsId, int $lotId): array
    {
        $gs = RuhiGs::query()->whereNull('deleted_at')->find($gsId);
        $slot = RuhiSlot::query()->where('gs_id', $gsId)->find($lotId);

        $gsName = (string) ($gs->name ?? '');
        $lotName = (string) ($slot->slot_name ?? '');

        $orderRows = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->where('lot_id', $lotId)
            ->get(['design_id', 'design_qty']);

        $designQtyByDesign = [];
        foreach ($orderRows as $row) {
            $did = (int) $row->design_id;
            $designQtyByDesign[$did] = ($designQtyByDesign[$did] ?? 0) + (int) $row->design_qty;
        }

        $designNamesCsv = $this->designNamesCommaSeparated(array_keys($designQtyByDesign));

        $byProduct = [];

        foreach ($designQtyByDesign as $designId => $designQty) {
            $designProducts = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->where('item_type_id', self::CASTING_ITEM_TYPE_ID)
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
                        'casting' => (string) $product->product_name,
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
                $ta = ReportNameSort::hyphenNameTuple((string) $a['casting']);
                $tb = ReportNameSort::hyphenNameTuple((string) $b['casting']);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values()
            ->map(function (array $r): array {
                return [
                    'casting' => $r['casting'],
                    'total_quantity' => (int) round($r['total_quantity']),
                    'weight' => round($r['weight'], 2),
                ];
            })
            ->all();

        $grandTotalQty = (int) array_sum(array_column($rows, 'total_quantity'));
        $grandTotalWt = round((float) array_sum(array_column($rows, 'weight')), 2);

        return [
            'gs_name' => $gsName,
            'lot_name' => $lotName,
            'design_names_csv' => $designNamesCsv,
            'rows' => $rows,
            'grand_total_quantity' => $grandTotalQty,
            'grand_total_weight' => $grandTotalWt,
        ];
    }

    /**
     * @param  array<int>  $designIds
     */
    private function designNamesCommaSeparated(array $designIds): string
    {
        if ($designIds === []) {
            return '';
        }

        $designs = RuhiDesign::query()
            ->withTrashed()
            ->whereIn('id', $designIds)
            ->get(['id', 'design_name']);

        $sorted = $designs->sort(function (RuhiDesign $a, RuhiDesign $b): int {
            $ta = ReportNameSort::hyphenNameTuple((string) $a->design_name);
            $tb = ReportNameSort::hyphenNameTuple((string) $b->design_name);

            return ReportNameSort::compareTuples($ta, $tb);
        });

        return $sorted->pluck('design_name')->implode(', ');
    }

    /**
     * Per design × casting line: quantity = design_products.quantity × GS order design_qty.
     * Uses casting item type on `r_design_products.item_type_id`.
     * Rows are grouped per design for merged Design Name cells (rowspan) in the UI.
     *
     * @return array{
     *     gs_name: string,
     *     lot_name: string,
     *     design_groups: array<int, array{design_name: string, lines: array<int, array{casting: string, total_quantity: int}>}>,
     *     grand_total_quantity: int
     * }
     */
    public function buildDetailReport(int $gsId, int $lotId): array
    {
        $gs = RuhiGs::query()->whereNull('deleted_at')->find($gsId);
        $slot = RuhiSlot::query()->where('gs_id', $gsId)->find($lotId);

        $gsName = (string) ($gs->name ?? '');
        $lotName = (string) ($slot->slot_name ?? '');

        $designQtyByDesign = [];
        $orderRows = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->where('lot_id', $lotId)
            ->get(['design_id', 'design_qty']);

        foreach ($orderRows as $row) {
            $did = (int) $row->design_id;
            $designQtyByDesign[$did] = ($designQtyByDesign[$did] ?? 0) + (int) $row->design_qty;
        }

        if ($designQtyByDesign === []) {
            return [
                'gs_name' => $gsName,
                'lot_name' => $lotName,
                'design_groups' => [],
                'grand_total_quantity' => 0,
            ];
        }

        $designs = RuhiDesign::query()
            ->withTrashed()
            ->whereIn('id', array_keys($designQtyByDesign))
            ->get(['id', 'design_name']);

        $sortedDesigns = $designs->sort(function (RuhiDesign $a, RuhiDesign $b): int {
            $ta = ReportNameSort::hyphenNameTuple((string) $a->design_name);
            $tb = ReportNameSort::hyphenNameTuple((string) $b->design_name);

            return ReportNameSort::compareTuples($ta, $tb);
        });

        $designGroups = [];

        foreach ($sortedDesigns as $design) {
            $designId = (int) $design->id;
            $designQty = (int) ($designQtyByDesign[$designId] ?? 0);
            $designName = (string) $design->design_name;

            $designProducts = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->where('item_type_id', self::CASTING_ITEM_TYPE_ID)
                ->with([
                    'product' => fn ($q) => $q->withTrashed(),
                ])
                ->get();

            $designProducts = $designProducts->sort(function (RuhiDesignProduct $a, RuhiDesignProduct $b): int {
                $na = (string) ($a->product?->product_name ?? '');
                $nb = (string) ($b->product?->product_name ?? '');
                $ta = ReportNameSort::hyphenNameTuple($na);
                $tb = ReportNameSort::hyphenNameTuple($nb);

                return ReportNameSort::compareTuples($ta, $tb);
            })->values();

            $lines = [];

            foreach ($designProducts as $dp) {
                $product = $dp->product;
                if (! $product) {
                    continue;
                }

                $lineQty = (int) $dp->quantity * $designQty;

                $lines[] = [
                    'casting' => (string) $product->product_name,
                    'total_quantity' => (int) $lineQty,
                ];
            }

            if ($lines !== []) {
                $designGroups[] = [
                    'design_name' => $designName,
                    'lines' => $lines,
                ];
            }
        }

        $grandTotalQty = 0;
        foreach ($designGroups as $group) {
            foreach ($group['lines'] as $line) {
                $grandTotalQty += (int) $line['total_quantity'];
            }
        }

        return [
            'gs_name' => $gsName,
            'lot_name' => $lotName,
            'design_groups' => $designGroups,
            'grand_total_quantity' => $grandTotalQty,
        ];
    }
}
