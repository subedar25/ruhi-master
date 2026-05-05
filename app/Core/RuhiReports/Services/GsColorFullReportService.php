<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiCollateByColor;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiItemKstone;
use App\Models\RuhiKstone;
use Illuminate\Support\Collection;

/**
 * GS Color Full Report: legacy CI {@code gsColorFullReport} / {@code getFullReportByDesignAndType}
 * for product types Kundan (6), Pulki (5), Add Full (4). Collet Kstone Color uses type 3 with the same math.
 *
 * Flow: each {@see RuhiGsOrderByColor} row × design products × each {@see RuhiCollateByColor} row,
 * {@code calculateDuplicateColor}, {@code sort_array} (natural case), {@code calculateKStonesByProductId}-style
 * weights from {@see RuhiItemKstone} × catalog {@see RuhiKstone} sums by name + color_id, then optional {@code sfilter}.
 */
class GsColorFullReportService
{
    public const PRODUCT_TYPE_KUNDAN_FULL = 6;

    public const PRODUCT_TYPE_PULKI_FULL = 5;

    public const PRODUCT_TYPE_ADD_FULL = 4;

    public const PRODUCT_TYPE_COLLET = 3;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @param  ?int  $sfilter  null/0 = all; 1 = exclude product names containing "(S)"; 2 = only those rows
     * @return array{
     *     gs_name: string,
     *     kundanfull: array<int, array<string, mixed>>,
     *     pulkifull: array<int, array<string, mixed>>,
     *     addfull: array<int, array<string, mixed>>,
     *     totals_kundanfull: array<string, int|float>,
     *     totals_pulkifull: array<string, int|float>,
     *     totals_addfull: array<string, int|float>
     * }
     */
    public function buildReport(int $gsId, ?int $sfilter = null): array
    {
        $gs = RuhiGs::query()->whereNull('deleted_at')->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        $k = $this->buildEnrichedBlock($gsId, self::PRODUCT_TYPE_KUNDAN_FULL, $sfilter);
        $p = $this->buildEnrichedBlock($gsId, self::PRODUCT_TYPE_PULKI_FULL, $sfilter);
        $a = $this->buildEnrichedBlock($gsId, self::PRODUCT_TYPE_ADD_FULL, $sfilter);

        return [
            'gs_name' => $gsName,
            'kundanfull' => $k,
            'pulkifull' => $p,
            'addfull' => $a,
            'totals_kundanfull' => $this->sumKundanDetailBlock($k),
            'totals_pulkifull' => $this->sumSimpleColorFullBlock($p),
            'totals_addfull' => $this->sumSimpleColorFullBlock($a),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{
     *     total_color_qty: int,
     *     red_qty: int,
     *     red_kstone_wt: float,
     *     red_die_wt: float,
     *     green_qty: int,
     *     green_kstone_wt: float,
     *     green_die_wt: float,
     *     white_qty: int,
     *     white_kstone_wt: float,
     *     white_die_wt: float,
     *     total_wt: float
     * }
     */
    private function sumKundanDetailBlock(array $rows): array
    {
        $t = [
            'total_color_qty' => 0,
            'red_qty' => 0,
            'red_kstone_wt' => 0.0,
            'red_die_wt' => 0.0,
            'green_qty' => 0,
            'green_kstone_wt' => 0.0,
            'green_die_wt' => 0.0,
            'white_qty' => 0,
            'white_kstone_wt' => 0.0,
            'white_die_wt' => 0.0,
            'total_wt' => 0.0,
        ];

        foreach ($rows as $r) {
            $t['total_color_qty'] += (int) $r['total_color_qty'];
            $t['red_qty'] += (int) $r['red_qty'];
            $t['red_kstone_wt'] += (float) $r['red_kstone_wt'];
            $t['red_die_wt'] += (float) $r['red_die_wt'];
            $t['green_qty'] += (int) $r['green_qty'];
            $t['green_kstone_wt'] += (float) $r['green_kstone_wt'];
            $t['green_die_wt'] += (float) $r['green_die_wt'];
            $t['white_qty'] += (int) $r['white_qty'];
            $t['white_kstone_wt'] += (float) $r['white_kstone_wt'];
            $t['white_die_wt'] += (float) $r['white_die_wt'];
            $t['total_wt'] += (float) ($r['total_wt'] ?? 0);
        }

        $t['red_kstone_wt'] = round($t['red_kstone_wt'], 2);
        $t['red_die_wt'] = round($t['red_die_wt'], 2);
        $t['green_kstone_wt'] = round($t['green_kstone_wt'], 2);
        $t['green_die_wt'] = round($t['green_die_wt'], 2);
        $t['white_kstone_wt'] = round($t['white_kstone_wt'], 2);
        $t['white_die_wt'] = round($t['white_die_wt'], 2);
        $t['total_wt'] = round($t['total_wt'], 2);

        return $t;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{total_color_qty: int, red_qty: int, green_qty: int, white_qty: int, total_wt: float}
     */
    private function sumSimpleColorFullBlock(array $rows): array
    {
        $t = [
            'total_color_qty' => 0,
            'red_qty' => 0,
            'green_qty' => 0,
            'white_qty' => 0,
            'total_wt' => 0.0,
        ];

        foreach ($rows as $r) {
            $t['total_color_qty'] += (int) $r['total_color_qty'];
            $t['red_qty'] += (int) $r['red_qty'];
            $t['green_qty'] += (int) $r['green_qty'];
            $t['white_qty'] += (int) $r['white_qty'];
            $t['total_wt'] += (float) ($r['total_wt'] ?? 0);
        }

        $t['total_wt'] = round($t['total_wt'], 2);

        return $t;
    }

    /**
     * GS Wise Collet Kstone Color Report — mirrors legacy CI {@code gsColletKstoneReport}:
     * each {@code gs_order_by_color} row × {@code getFullReportByDesignAndType($details, 3)},
     * {@code calculateDuplicateColor}, {@code sort_array} (natural case name),
     * {@code calculateKStonesByProductId}.
     *
     * Uses {@see self::PRODUCT_TYPE_COLLET} ({@code product_type} = 3) with {@see rawSliceFullReport()}
     * (full-report collate math: {@code only_green_qty} green paths).
     *
     * @return array{
     *     gs_name: string,
     *     rows: array<int, array<string, mixed>>,
     *     totals: array{
     *         total_color_qty: int,
     *         red_qty: int,
     *         red_kstone_wt: float,
     *         red_die_wt: float,
     *         green_qty: int,
     *         green_kstone_wt: float,
     *         green_die_wt: float,
     *         white_qty: int,
     *         white_kstone_wt: float,
     *         white_die_wt: float
     *     }
     * }
     */
    public function buildColletKstoneColorReport(int $gsId): array
    {
        $gs = RuhiGs::query()->whereNull('deleted_at')->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        $merged = $this->collectMergedProducts($gsId, self::PRODUCT_TYPE_COLLET);
        $rows = [];
        foreach ($merged as $m) {
            $rows[] = $this->buildDisplayRowFromMerged($m, self::PRODUCT_TYPE_COLLET);
        }

        return [
            'gs_name' => $gsName,
            'rows' => $rows,
            'totals' => $this->sumColletKstoneColorReportRows($rows),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{
     *     total_color_qty: int,
     *     red_qty: int,
     *     red_kstone_wt: float,
     *     red_die_wt: float,
     *     green_qty: int,
     *     green_kstone_wt: float,
     *     green_die_wt: float,
     *     white_qty: int,
     *     white_kstone_wt: float,
     *     white_die_wt: float
     * }
     */
    private function sumColletKstoneColorReportRows(array $rows): array
    {
        $t = [
            'total_color_qty' => 0,
            'red_qty' => 0,
            'red_kstone_wt' => 0.0,
            'red_die_wt' => 0.0,
            'green_qty' => 0,
            'green_kstone_wt' => 0.0,
            'green_die_wt' => 0.0,
            'white_qty' => 0,
            'white_kstone_wt' => 0.0,
            'white_die_wt' => 0.0,
        ];

        foreach ($rows as $r) {
            $t['total_color_qty'] += (int) $r['total_color_qty'];
            $t['red_qty'] += (int) $r['red_qty'];
            $t['red_kstone_wt'] += (float) $r['red_kstone_wt'];
            $t['red_die_wt'] += (float) $r['red_die_wt'];
            $t['green_qty'] += (int) $r['green_qty'];
            $t['green_kstone_wt'] += (float) $r['green_kstone_wt'];
            $t['green_die_wt'] += (float) $r['green_die_wt'];
            $t['white_qty'] += (int) $r['white_qty'];
            $t['white_kstone_wt'] += (float) $r['white_kstone_wt'];
            $t['white_die_wt'] += (float) $r['white_die_wt'];
        }

        $t['red_kstone_wt'] = round($t['red_kstone_wt'], 2);
        $t['red_die_wt'] = round($t['red_die_wt'], 2);
        $t['green_kstone_wt'] = round($t['green_kstone_wt'], 2);
        $t['green_die_wt'] = round($t['green_die_wt'], 2);
        $t['white_kstone_wt'] = round($t['white_kstone_wt'], 2);
        $t['white_die_wt'] = round($t['white_die_wt'], 2);

        return $t;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildEnrichedBlock(int $gsId, int $productTypeId, ?int $sfilter): array
    {
        $merged = $this->collectMergedProducts($gsId, $productTypeId);
        $rows = [];
        foreach ($merged as $m) {
            $rows[] = $this->buildDisplayRowFromMerged($m, $productTypeId);
        }

        return $this->applySfilter($rows, $sfilter);
    }

    /**
     * @return Collection<int, RuhiGsOrderByColor>
     */
    private function sortedOrderRows(int $gsId): Collection
    {
        return RuhiGsOrderByColor::query()
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
    }

    /**
     * @return array<int, array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int, weight: float}>
     */
    private function collectMergedProducts(int $gsId, int $productTypeId): array
    {
        $orderRows = $this->sortedOrderRows($gsId);
        if ($orderRows->isEmpty()) {
            return [];
        }

        $raw = [];
        foreach ($orderRows as $order) {
            $dps = RuhiDesignProduct::query()
                ->where('design_id', (int) $order->design_id)
                ->whereHas('product', fn ($q) => $q->where('product_type', $productTypeId)->withTrashed())
                ->with([
                    'product' => fn ($q) => $q->withTrashed(),
                    'collateByColors',
                ])
                ->get();

            foreach ($dps as $dp) {
                if (! $dp->product) {
                    continue;
                }

                $collates = $dp->collateByColors;
                if ($collates->isEmpty()) {
                    $raw[] = $this->rawSliceFullReport($dp, $order, null);
                } else {
                    foreach ($collates as $cc) {
                        $raw[] = $this->rawSliceFullReport($dp, $order, $cc);
                    }
                }
            }
        }

        $merged = $this->mergeDuplicateColor($raw);
        usort($merged, static function (array $a, array $b): int {
            return strnatcasecmp((string) $a['product_name'], (string) $b['product_name']);
        });

        return $merged;
    }

    /**
     * Legacy {@code getFullReportByDesignAndType} colour math (uses {@code only_green_qty} for green paths).
     *
     * @return array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int, weight: float}
     */
    private function rawSliceFullReport(RuhiDesignProduct $dp, RuhiGsOrderByColor $order, ?RuhiCollateByColor $cc): array
    {
        $product = $dp->product;
        $qty = (int) $dp->quantity;
        $designQty = (int) $order->design_qty;
        $designRedQty = (int) $order->design_red_qty;
        $designRedGreenQty = (int) $order->design_red_green_qty;
        $designGreenQty = (int) $order->design_green_qty;

        $onlyRed = $cc !== null ? (int) $cc->only_red_qty : 0;
        $redQty = $cc !== null ? (int) $cc->red_qty : 0;
        $onlyGreen = $cc !== null ? (int) $cc->only_green_qty : 0;
        $greenQty = $cc !== null ? (int) $cc->green_qty : 0;

        $totalColorQty = $qty * $designQty;

        if (! empty($onlyRed) && ! empty($redQty)) {
            $finalRed = $onlyRed * $designRedQty + $redQty * $designRedGreenQty;
        } elseif (! empty($onlyRed) && empty($redQty)) {
            $finalRed = $onlyRed * $designRedQty;
        } elseif (empty($onlyRed) && ! empty($redQty)) {
            $finalRed = $redQty * $designRedGreenQty;
        } else {
            $finalRed = 0;
        }

        $finalGreen = 0;
        if (! empty($onlyGreen) && ! empty($greenQty)) {
            $finalGreen = $onlyGreen * $designGreenQty + $greenQty * $designRedGreenQty;
        } elseif (! empty($onlyGreen) && empty($greenQty)) {
            $finalGreen = $onlyGreen * $designGreenQty;
        } elseif (empty($onlyGreen) && ! empty($greenQty)) {
            $finalGreen = $greenQty * $designGreenQty;
        }

        $finalRedGreen = 0;
        $finalTotalGreen = $finalGreen + $finalRedGreen;

        $designWhiteQty = $totalColorQty - ($finalRed + $finalGreen);

        return [
            'product_id' => (int) $product->id,
            'product_name' => (string) $product->product_name,
            'total_color_qty' => $totalColorQty,
            'total_red_qty' => $finalRed,
            'total_green_qty' => $finalTotalGreen,
            'total_white_qty' => $designWhiteQty,
            'weight' => (float) $product->weight,
        ];
    }

    /**
     * Legacy {@code calculateDuplicateColor}.
     *
     * @param  array<int, array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int, weight: float}>  $arrProducts
     * @return array<int, array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int, weight: float}>
     */
    private function mergeDuplicateColor(array $arrProducts): array
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

    /**
     * Legacy {@code calculateKStonesByProductId}: catalog stone/die sums by kstone name + color_id, scaled by
     * item-kstone flags × merged colour qtys (same structure as CI).
     *
     * @param  array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int, weight: float}  $m
     * @return array<string, mixed>
     */
    private function buildDisplayRowFromMerged(array $m, int $productTypeId): array
    {
        $pid = (int) $m['product_id'];
        $rq = (int) $m['total_red_qty'];
        $gq = (int) $m['total_green_qty'];
        $wq = (int) $m['total_white_qty'];
        $baseTotal = (int) $m['total_color_qty'];

        $lines = RuhiItemKstone::query()
            ->where('item_id', $pid)
            ->with(['kstone' => fn ($q) => $q->withTrashed()])
            ->get()
            ->sort(function (RuhiItemKstone $a, RuhiItemKstone $b): int {
                $na = (string) ($a->kstone?->name ?? '');
                $nb = (string) ($b->kstone?->name ?? '');

                return ReportNameSort::compareTuples(
                    ReportNameSort::hyphenNameTuple($na),
                    ReportNameSort::hyphenNameTuple($nb)
                );
            })
            ->values();

        if ($lines->isEmpty()) {
            return $this->emptyDisplayRow($m, $productTypeId);
        }

        $sumRedKw = 0.0;
        $sumRedDw = 0.0;
        $sumGreenKw = 0.0;
        $sumGreenDw = 0.0;
        $sumWhiteKw = 0.0;
        $sumWhiteDw = 0.0;

        foreach ($lines as $ik) {
            $ks = $ik->kstone;
            if (! $ks) {
                continue;
            }

            $name = (string) $ks->name;
            $ktcq = (int) $ik->kstone_quantity * $baseTotal;
            $ktr = (int) $ik->red * $rq;
            $ktg = (int) $ik->green * $gq;
            $ktw = max(0, $ktcq - $ktr - $ktg);

            $cr = $this->catalogStoneDieSumByNameAndColor($name, 1);
            $cg = $this->catalogStoneDieSumByNameAndColor($name, 2);
            $cw = $this->catalogStoneDieSumByNameAndColor($name, 3);

            $sumRedKw += $ktr * $cr['stoneweight'];
            $sumRedDw += $ktr * $cr['dieweight'];
            $sumGreenKw += $ktg * $cg['stoneweight'];
            $sumGreenDw += $ktg * $cg['dieweight'];
            $sumWhiteKw += $ktw * $cw['stoneweight'];
            $sumWhiteDw += $ktw * $cw['dieweight'];
        }

        $first = $lines->first();
        $firstStoneWt = $first && $first->kstone ? (float) $first->kstone->stoneweight : 0.0;
        $firstName = $first && $first->kstone ? trim((string) $first->kstone->name) : '';
        $firstQty = $first ? (int) $first->kstone_quantity : 0;

        $redKw = round($sumRedKw, 2);
        $redDw = round($sumRedDw, 2);
        $greenKw = round($sumGreenKw, 2);
        $greenDw = round($sumGreenDw, 2);
        $whiteKw = round($sumWhiteKw, 2);
        $whiteDw = round($sumWhiteDw, 2);

        $sumChannelWt = round($redKw + $redDw + $greenKw + $greenDw + $whiteKw + $whiteDw, 2);

        if ($productTypeId === self::PRODUCT_TYPE_PULKI_FULL || $productTypeId === self::PRODUCT_TYPE_ADD_FULL) {
            $totalWt = round($firstStoneWt * $baseTotal, 2);
        } else {
            $totalWt = $sumChannelWt;
        }

        $kstoneLabel = $this->formatKstoneForBlock($productTypeId, $firstName, $firstQty, $firstStoneWt);

        return [
            'product_name' => (string) $m['product_name'],
            'kstone' => $kstoneLabel,
            'total_color_qty' => $baseTotal,
            'red_qty' => $rq,
            'red_kstone_wt' => $redKw,
            'red_die_wt' => $redDw,
            'green_qty' => $gq,
            'green_kstone_wt' => $greenKw,
            'green_die_wt' => $greenDw,
            'white_qty' => $wq,
            'white_kstone_wt' => $whiteKw,
            'white_die_wt' => $whiteDw,
            'total_wt' => $totalWt,
        ];
    }

    /**
     * @param  array{product_id: int, product_name: string, total_color_qty: int, total_red_qty: int, total_green_qty: int, total_white_qty: int, weight: float}  $m
     * @return array<string, mixed>
     */
    private function emptyDisplayRow(array $m, int $productTypeId): array
    {
        return [
            'product_name' => (string) $m['product_name'],
            'kstone' => '',
            'total_color_qty' => (int) $m['total_color_qty'],
            'red_qty' => (int) $m['total_red_qty'],
            'red_kstone_wt' => 0.0,
            'red_die_wt' => 0.0,
            'green_qty' => (int) $m['total_green_qty'],
            'green_kstone_wt' => 0.0,
            'green_die_wt' => 0.0,
            'white_qty' => (int) $m['total_white_qty'],
            'white_kstone_wt' => 0.0,
            'white_die_wt' => 0.0,
            'total_wt' => 0.0,
        ];
    }

    /**
     * Legacy {@code getKstoneDieStoneweightByname}: SUM(stoneweight), SUM(dieweight) on {@see RuhiKstone} by name + color_id.
     *
     * @return array{stoneweight: float, dieweight: float}
     */
    private function catalogStoneDieSumByNameAndColor(string $name, int $colorId): array
    {
        if ($name === '') {
            return ['stoneweight' => 0.0, 'dieweight' => 0.0];
        }

        $agg = RuhiKstone::query()
            ->withTrashed()
            ->where('name', $name)
            ->where('color_id', $colorId)
            ->selectRaw('COALESCE(SUM(stoneweight), 0) as sw, COALESCE(SUM(dieweight), 0) as dw')
            ->first();

        if ($agg === null) {
            return ['stoneweight' => 0.0, 'dieweight' => 0.0];
        }

        return [
            'stoneweight' => (float) ($agg->sw ?? 0),
            'dieweight' => (float) ($agg->dw ?? 0),
        ];
    }

    private function formatKstoneForBlock(int $productTypeId, string $kstoneName, int $kstoneQty, float $stoneWeight): string
    {
        if ($productTypeId === self::PRODUCT_TYPE_KUNDAN_FULL || $productTypeId === self::PRODUCT_TYPE_COLLET) {
            return $this->formatKstoneKundanfull($kstoneName, $kstoneQty);
        }

        return $this->formatKstonePulkiAddFull($kstoneName, $stoneWeight);
    }

    /** Kundanfull / collet detail: `<kstone name>-(<kstone qty>)`. */
    private function formatKstoneKundanfull(string $kstoneName, int $kstoneQty): string
    {
        $kstoneName = trim($kstoneName);

        if ($kstoneName === '' && $kstoneQty === 0) {
            return '';
        }

        if ($kstoneName === '') {
            return '-('.$kstoneQty.')';
        }

        return $kstoneName.'-('.$kstoneQty.')';
    }

    /** Pulkifull & AddFull: `kstone name (wt <weight>)` using {@see RuhiKstone::stoneweight}. */
    private function formatKstonePulkiAddFull(string $kstoneName, float $stoneWeight): string
    {
        $kstoneName = trim($kstoneName);
        $wtLabel = rtrim(rtrim(number_format($stoneWeight, 4, '.', ''), '0'), '.');

        if ($wtLabel === '') {
            $wtLabel = '0';
        }

        if ($kstoneName === '' && $stoneWeight == 0.0) {
            return '';
        }

        if ($kstoneName === '') {
            return '(wt '.$wtLabel.')';
        }

        return $kstoneName.' (wt '.$wtLabel.')';
    }

    /**
     * Legacy {@code removeItemByWord} / {@code searchMatchWord} on {@code product_name} after k-stone enrichment.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function applySfilter(array $rows, ?int $sfilter): array
    {
        if ($sfilter === 1) {
            return array_values(array_filter(
                $rows,
                static fn (array $r): bool => stripos((string) $r['product_name'], '(S)') === false
            ));
        }

        if ($sfilter === 2) {
            return array_values(array_filter(
                $rows,
                static fn (array $r): bool => stripos((string) $r['product_name'], '(S)') !== false
            ));
        }

        return $rows;
    }
}
