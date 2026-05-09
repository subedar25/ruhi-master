<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiCollateByColor;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use Illuminate\Support\Collection;

/**
 * GS Wise Drop Report: matches legacy CI {@code gsDropReport} / {@code getColorColleteByDesign}
 * with {@code product_type => 8}.
 *
 * Each {@see RuhiGsOrderByColor} row is processed separately (not aggregated by design).
 * White is {@code total_color_qty - (final_red + final_green)} using the same partial green as CI
 * (not {@code final_total_green} when red–green split exists — CI omits that block for drop).
 */
class GsWiseDropReportService
{
    /** Drop / color product type on {@see RuhiProduct::product_type} (legacy CI filter). */
    public const PRODUCT_TYPE_DROP = 8;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array{
     *     gs_name: string,
     *     rows: array<int, array{drop: string, red: int, green: int, white: int}>,
     *     grand_red: int,
     *     grand_green: int,
     *     grand_white: int
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
            return [
                'gs_name' => $gsName,
                'rows' => [],
                'grand_red' => 0,
                'grand_green' => 0,
                'grand_white' => 0,
            ];
        }

        $raw = [];

        foreach ($orderRows as $order) {
            $designQty = (int) $order->design_qty;
            $designRedQty = (int) $order->design_red_qty;
            $designRedGreenQty = (int) $order->design_red_green_qty;
            $designGreenQty = (int) $order->design_green_qty;

            $dps = RuhiDesignProduct::query()
                ->where('design_id', (int) $order->design_id)
                ->whereHas('product', fn ($q) => $q->where('product_type', self::PRODUCT_TYPE_DROP)->withTrashed())
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
                    $raw[] = $this->buildRawSlice(
                        $dp,
                        (int) $product->id,
                        (string) $product->product_name,
                        $designQty,
                        $designRedQty,
                        $designRedGreenQty,
                        $designGreenQty,
                        null
                    );
                } else {
                    foreach ($collates as $cc) {
                        $raw[] = $this->buildRawSlice(
                            $dp,
                            (int) $product->id,
                            (string) $product->product_name,
                            $designQty,
                            $designRedQty,
                            $designRedGreenQty,
                            $designGreenQty,
                            $cc
                        );
                    }
                }
            }
        }

        $merged = $this->mergeDuplicateProductsById($raw);

        usort($merged, static function (array $a, array $b): int {
            return strnatcasecmp((string) $a['product_name'], (string) $b['product_name']);
        });

        $rows = [];
        foreach ($merged as $r) {
            $rows[] = [
                'drop' => (string) $r['product_name'],
                'red' => (int) $r['total_red_qty'],
                'green' => (int) $r['total_green_qty'],
                'white' => (int) $r['total_white_qty'],
            ];
        }

        return [
            'gs_name' => $gsName,
            'rows' => $rows,
            'grand_red' => (int) array_sum(array_column($rows, 'red')),
            'grand_green' => (int) array_sum(array_column($rows, 'green')),
            'grand_white' => (int) array_sum(array_column($rows, 'white')),
        ];
    }

    /**
     * @return array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int}
     */
    private function buildRawSlice(
        RuhiDesignProduct $dp,
        int $productId,
        string $productName,
        int $designQty,
        int $designRedQty,
        int $designRedGreenQty,
        int $designGreenQty,
        ?RuhiCollateByColor $cc
    ): array {
        $qty = (int) $dp->quantity;
        $onlyRed = $cc !== null ? (int) $cc->only_red_qty : 0;
        $redQty = $cc !== null ? (int) $cc->red_qty : 0;
        $greenQty = $cc !== null ? (int) $cc->green_qty : 0;

        $totalColorQty = $qty * $designQty;

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
        if (! empty($onlyRed) && empty($greenQty)) {
            $finalGreen = $onlyRed * $designGreenQty;
        } elseif (! empty($onlyRed) && ! empty($greenQty)) {
            $finalGreen = $onlyRed * $designGreenQty + $greenQty * $designRedGreenQty;
        }

        $finalRedGreen = 0;

        $finalTotalGreen = $finalGreen + $finalRedGreen;

        $designWhiteQty = $totalColorQty - ($finalRed + $finalGreen);

        return [
            'product_id' => $productId,
            'product_name' => $productName,
            'total_color_qty' => $totalColorQty,
            'total_red_qty' => $finalRed,
            'total_green_qty' => $finalTotalGreen,
            'total_white_qty' => $designWhiteQty,
        ];
    }

    /**
     * Legacy {@code calculateDuplicateColor}: sum qty columns by {@code product_id}; keep first {@code product_name}.
     *
     * @param  array<int, array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int}>  $arrProducts
     * @return array<int, array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int}>
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
                $arrResults[$productId]['total_white_qty'] += $details['total_white_qty'];
            } else {
                $arrResults[$productId] = $details;
            }
        }

        return array_values($arrResults);
    }
}
