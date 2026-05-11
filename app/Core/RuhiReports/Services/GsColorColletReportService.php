<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiCollateByColor;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use Illuminate\Support\Collection;

/**
 * GS Color Collet Report: matches legacy CI {@code gsColorColletReport} / {@code getColorColleteByDesign}.
 *
 * For the selected GS, each {@see RuhiGsOrderByColor} row (per lot + design) supplies
 * {@code design_qty}, {@code design_red_qty}, {@code design_red_green_qty}, {@code design_green_qty}.
 * For each row, load design products with {@see RuhiProduct::product_type} = 3 (Collet) and apply
 * collate-by-color rules per {@see RuhiCollateByColor} row. Results merge by {@code product_id},
 * then rows with both red and green zero are dropped (legacy {@code removeRedGeenQtyZero}).
 */
class GsColorColletReportService
{
    /** Master product type “Collet” on {@see RuhiProduct::product_type}. */
    public const PRODUCT_TYPE_COLLET = 3;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
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
        $gs = RuhiGs::query()->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        $orderRows = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->with(['design'])
            ->get()
            ->sort(function (RuhiGsOrderByColor $a, RuhiGsOrderByColor $b): int {
                $lot = ((int) $a->lot_id) <=> ((int) $b->lot_id);
                if ($lot !== 0) {
                    return $lot;
                }
                $na = (string) ($a->design?->design_name ?? '');
                $nb = (string) ($b->design?->design_name ?? '');

                return ReportNameSort::compareTuples(
                    ReportNameSort::hyphenNameTuple($na),
                    ReportNameSort::hyphenNameTuple($nb)
                );
            })
            ->values();

        if ($orderRows->isEmpty()) {
            return $this->emptyReport($gsName);
        }

        $raw = [];

        foreach ($orderRows as $order) {
            $designQty = (int) $order->design_qty;
            $designRedQty = (int) $order->design_red_qty;
            $designRedGreenQty = (int) $order->design_red_green_qty;
            $designGreenQty = (int) $order->design_green_qty;

            $dps = RuhiDesignProduct::query()
                ->where('design_id', (int) $order->design_id)
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

                $collates = $dp->collateByColors;
                if ($collates->isEmpty()) {
                    $raw = array_merge($raw, $this->pushLegacySlices(
                        $dp,
                        $product->id,
                        (string) $product->product_name,
                        (float) $product->weight,
                        $designQty,
                        $designRedQty,
                        $designRedGreenQty,
                        $designGreenQty,
                        null
                    ));
                } else {
                    foreach ($collates as $cc) {
                        $raw = array_merge($raw, $this->pushLegacySlices(
                            $dp,
                            $product->id,
                            (string) $product->product_name,
                            (float) $product->weight,
                            $designQty,
                            $designRedQty,
                            $designRedGreenQty,
                            $designGreenQty,
                            $cc
                        ));
                    }
                }
            }
        }

        $merged = $this->mergeDuplicateProductsById($raw);
        $filtered = array_values(array_filter(
            $merged,
            static fn (array $r): bool => ((int) $r['total_red_qty']) !== 0 || ((int) $r['total_green_qty']) !== 0
        ));

        usort($filtered, static function (array $a, array $b): int {
            return strnatcasecmp((string) $a['product_name'], (string) $b['product_name']);
        });

        $rows = [];
        foreach ($filtered as $r) {
            $unitWt = (float) $r['weight'];
            $rows[] = [
                'collet' => (string) $r['product_name'],
                'red' => (int) $r['total_red_qty'],
                'green' => (int) $r['total_green_qty'],
                'weight' => round($unitWt, 2),
            ];
        }

        $grandRed = (int) array_sum(array_column($rows, 'red'));
        $grandGreen = (int) array_sum(array_column($rows, 'green'));
        $grandWeight = round((float) array_sum(array_column($rows, 'weight')), 2);

        return [
            'gs_name' => $gsName,
            'rows' => $rows,
            'grand_red' => $grandRed,
            'grand_green' => $grandGreen,
            'grand_weight' => $grandWeight,
        ];
    }

    /**
     * @return array<int, array{product_id: int, product_name: string, weight: float, total_color_qty: int, total_red_qty: int, total_green_qty: int}>
     */
    private function pushLegacySlices(
        RuhiDesignProduct $dp,
        int $productId,
        string $productName,
        float $unitWeight,
        int $designQty,
        int $designRedQty,
        int $designRedGreenQty,
        int $designGreenQty,
        ?RuhiCollateByColor $cc
    ): array {
        $qty = [
            'total_color_qty' => 0,
            'total_red_qty' => 0,
            'total_green_qty' => 0,
        ];
        $this->applyLegacyCollateMath(
            $dp,
            $cc,
            $designQty,
            $designRedQty,
            $designRedGreenQty,
            $designGreenQty,
            $qty
        );

        return [[
            'product_id' => $productId,
            'product_name' => $productName,
            'weight' => $unitWeight,
            'total_color_qty' => (int) $qty['total_color_qty'],
            'total_red_qty' => (int) $qty['total_red_qty'],
            'total_green_qty' => (int) $qty['total_green_qty'],
        ]];
    }

    /**
     * Legacy CI arithmetic from {@code gsColorColletReport} inner loop.
     *
     * @param  array{total_color_qty: int, total_red_qty: int, total_green_qty: int}  $out
     */
    private function applyLegacyCollateMath(
        RuhiDesignProduct $dp,
        ?RuhiCollateByColor $cc,
        int $designQty,
        int $designRedQty,
        int $designRedGreenQty,
        int $designGreenQty,
        array &$out
    ): void {
        $quantity = (int) $dp->quantity;
        $onlyRed = $cc !== null ? (int) $cc->only_red_qty : 0;
        $redQty = $cc !== null ? (int) $cc->red_qty : 0;
        $greenQty = $cc !== null ? (int) $cc->green_qty : 0;

        $totalColorQty = $quantity * $designQty;

        if (! empty($onlyRed) && empty($redQty)) {
            $finalRed = $onlyRed * $designRedQty;
        } elseif (empty($onlyRed) && ! empty($redQty)) {
            $finalRed = $redQty * $designRedGreenQty;
        } elseif (! empty($onlyRed) && ! empty($redQty)) {
            $finalRed = $onlyRed * $designRedQty + $redQty * $designRedGreenQty;
        } else {
            $finalRed = 0;
        }

        $finalGreen = 0;
        if (! empty($designGreenQty)) {
            if (! empty($onlyRed) && empty($greenQty)) {
                $finalGreen = $onlyRed * $designGreenQty;
            } elseif (! empty($onlyRed) && ! empty($greenQty)) {
                $finalGreen = $onlyRed * $designGreenQty + $greenQty * $designRedGreenQty;
            }
        }

        if (! empty($greenQty) && ! empty($redQty)) {
            $finalRedGreen = $greenQty * $designRedGreenQty;
        } elseif (! empty($greenQty) && empty($redQty)) {
            $finalRedGreen = $greenQty * $designRedGreenQty;
        } else {
            $finalRedGreen = 0;
        }

        $finalTotalGreen = $finalGreen + $finalRedGreen;

        $out['total_color_qty'] += $totalColorQty;
        $out['total_red_qty'] += $finalRed;
        $out['total_green_qty'] += $finalTotalGreen;
    }

    /**
     * Legacy {@code calculateDuplicateColor}: sum qty columns by {@code product_id}; keep first row’s unit {@code weight}.
     *
     * @param  array<int, array{product_id: int, product_name: string, weight: float, total_color_qty: int, total_red_qty: int, total_green_qty: int}>  $arrProducts
     * @return array<int, array{product_id: int, product_name: string, weight: float, total_color_qty: int, total_red_qty: int, total_green_qty: int}>
     */
    private function mergeDuplicateProductsById(array $arrProducts): array
    {
        $arrResults = [];

        foreach ($arrProducts as $details) {
            $productId = $details['product_id'];

            if (isset($arrResults[$productId])) {
                $arrResults[$productId]['total_color_qty'] += $details['total_color_qty'];
                $arrResults[$productId]['total_red_qty'] += $details['total_red_qty'];
                $arrResults[$productId]['total_green_qty'] += $details['total_green_qty'];
            } else {
                $arrResults[$productId] = $details;
            }
        }

        return array_values($arrResults);
    }

    /**
     * @return array{gs_name: string, rows: array<int, mixed>, grand_red: int, grand_green: int, grand_weight: float}
     */
    private function emptyReport(string $gsName): array
    {
        return [
            'gs_name' => $gsName,
            'rows' => [],
            'grand_red' => 0,
            'grand_green' => 0,
            'grand_weight' => 0.0,
        ];
    }
}
