<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiItemKstone;
use Illuminate\Support\Collection;

/**
 * GS Color Full Report: three blocks by {@see \App\Models\RuhiProduct::product_type}
 * (Kundan 6, Pulki 5, Add Full 4, Collet 3), collate-by-color math per legacy {@code getFullReportByDesignAndType}
 * using {@see RuhiGsOrderByColor} multipliers and {@see \App\Models\RuhiCollateByColor} sums.
 *
 * Kundanfull only: merged **Kundan Total Qty** = Σ (`design_product.quantity` × `design_qty`).
 * **Total quantity** for white = Kundan Total Qty × `r_k_stone.kstone_quantity`;
 * **white qty** = total quantity − (red qty + green qty); Pulki/AddFull keep legacy white per line.
 * Kundanfull **Kstone Wt** (red/green/white) = channel qty × `r_kstone.stoneweight`.
 * Pulkifull & AddFull **Wt** (simple block) = `r_kstone.stoneweight` × Total Qty (merged {@code total_color_qty}).
 */
class GsColorFullReportService
{
    public const PRODUCT_TYPE_KUNDAN_FULL = 6;

    public const PRODUCT_TYPE_PULKI_FULL = 5;

    public const PRODUCT_TYPE_ADD_FULL = 4;

    /** Master collet type on {@see \App\Models\RuhiProduct::product_type} (same as {@see GsColorColletReportService::PRODUCT_TYPE_COLLET}). */
    public const PRODUCT_TYPE_COLLET = 3;

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array{
     *     gs_name: string,
     *     kundanfull: array<int, array<string, mixed>>,
     *     pulkifull: array<int, array<string, mixed>>,
     *     addfull: array<int, array<string, mixed>>
     * }
     */
    public function buildReport(int $gsId): array
    {
        $gs = RuhiGs::query()->whereNull('deleted_at')->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        return [
            'gs_name' => $gsName,
            'kundanfull' => $this->buildBlock($gsId, self::PRODUCT_TYPE_KUNDAN_FULL),
            'pulkifull' => $this->buildBlock($gsId, self::PRODUCT_TYPE_PULKI_FULL),
            'addfull' => $this->buildBlock($gsId, self::PRODUCT_TYPE_ADD_FULL),
        ];
    }

    /**
     * GS Wise Collet Kstone Color Report: collet master products only (`product_type` = 3), same row math as other color-full blocks (non-Kundan).
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

        $rows = $this->buildBlock($gsId, self::PRODUCT_TYPE_COLLET);

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
     * @return array<int, array{
     *     product_name: string,
     *     kstone: string,
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
     * }>
     */
    private function buildBlock(int $gsId, int $productTypeId): array
    {
        $orders = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->get();

        if ($orders->isEmpty()) {
            return [];
        }

        /** @var array<int, array<string, mixed>> $byPid */
        $byPid = [];

        foreach ($orders as $order) {
            $designId = (int) $order->design_id;

            $dps = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->whereHas('product', fn ($q) => $q->where('product_type', $productTypeId)->withTrashed())
                ->with([
                    'product' => fn ($q) => $q->withTrashed(),
                    'itemType',
                    'collateByColors',
                ])
                ->get();

            foreach ($dps as $dp) {
                $line = $this->computeLine($dp, $order, $productTypeId);
                if ($line === null) {
                    continue;
                }

                $pid = (int) $line['product_id'];
                if (! isset($byPid[$pid])) {
                    $byPid[$pid] = [
                        'product_name' => (string) $line['product_name'],
                        'kstone_name' => (string) $line['kstone_name'],
                        'kstone_qty' => (int) $line['kstone_qty'],
                        'kstone_stone_wt' => (float) $line['kstone_stone_wt'],
                        'total_color_qty' => 0,
                        'red_qty' => 0,
                        'green_qty' => 0,
                        'white_qty' => 0,
                        'unit_kstone_wt' => (float) $line['unit_kstone_wt'],
                        'unit_die_wt' => (float) $line['unit_die_wt'],
                    ];
                }

                $byPid[$pid]['total_color_qty'] += (int) $line['total_color_qty'];
                $byPid[$pid]['red_qty'] += (int) $line['red_qty'];
                $byPid[$pid]['green_qty'] += (int) $line['green_qty'];
                if ($productTypeId !== self::PRODUCT_TYPE_KUNDAN_FULL) {
                    $byPid[$pid]['white_qty'] += (int) $line['white_qty'];
                }
            }
        }

        $rows = [];
        foreach ($byPid as $r) {
            $k = (float) $r['unit_kstone_wt'];
            $d = (float) $r['unit_die_wt'];
            $rq = (int) $r['red_qty'];
            $gq = (int) $r['green_qty'];

            if ($productTypeId === self::PRODUCT_TYPE_KUNDAN_FULL) {
                /** Kundan Total Qty column = merged Σ(design_product.qty × design_qty); total qty for white = that × kstone qty */
                $kundanTotalQty = (int) $r['total_color_qty'];
                $kstoneQty = max(0, (int) $r['kstone_qty']);
                $totalQuantity = $kundanTotalQty * $kstoneQty;
                $wq = max(0, (int) round($totalQuantity - $rq - $gq));
            } else {
                $wq = (int) $r['white_qty'];
            }

            if ($productTypeId === self::PRODUCT_TYPE_KUNDAN_FULL) {
                /** Kundanfull: Kstone Wt = channel Qty × master {@see RuhiKstone::stoneweight} (“Kstone” weight unit). */
                $ks = (float) $r['kstone_stone_wt'];
                $redKw = round($rq * $ks, 2);
                $greenKw = round($gq * $ks, 2);
                $whiteKw = round($wq * $ks, 2);
            } else {
                $redKw = round($rq * $k, 2);
                $greenKw = round($gq * $k, 2);
                $whiteKw = round($wq * $k, 2);
            }

            $redDw = round($rq * $d, 2);
            $greenDw = round($gq * $d, 2);
            $whiteDw = round($wq * $d, 2);

            $baseTotalQty = (int) $r['total_color_qty'];
            $sumChannelWt = round($redKw + $redDw + $greenKw + $greenDw + $whiteKw + $whiteDw, 2);

            if ($productTypeId === self::PRODUCT_TYPE_PULKI_FULL || $productTypeId === self::PRODUCT_TYPE_ADD_FULL) {
                /** Wt column: {@see RuhiKstone::stoneweight} × merged Total Qty */
                $totalWt = round((float) $r['kstone_stone_wt'] * $baseTotalQty, 2);
            } else {
                $totalWt = $sumChannelWt;
            }

            $rows[] = [
                'product_name' => (string) $r['product_name'],
                'kstone' => $this->formatKstoneForBlock($productTypeId, $r),
                'total_color_qty' => $baseTotalQty,
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

        usort($rows, function (array $a, array $b): int {
            return ReportNameSort::compareTuples(
                ReportNameSort::hyphenNameTuple((string) $a['product_name']),
                ReportNameSort::hyphenNameTuple((string) $b['product_name'])
            );
        });

        return array_values($rows);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function computeLine(RuhiDesignProduct $dp, RuhiGsOrderByColor $order, int $productTypeId): ?array
    {
        $itemType = $dp->itemType;
        if (! $itemType || strcasecmp(trim((string) $itemType->type_by_color), 'Yes') !== 0) {
            return null;
        }

        $product = $dp->product;
        if (! $product) {
            return null;
        }

        $cc = $dp->collateByColors;
        $onlyRed = (int) $cc->sum('only_red_qty');
        $red = (int) $cc->sum('red_qty');
        $onlyGreen = (int) $cc->sum('only_green_qty');
        $green = (int) $cc->sum('green_qty');

        $dq = (int) $order->design_qty;
        $designRed = (int) $order->design_red_qty;
        $designRedGreen = (int) $order->design_red_green_qty;
        $designGreen = (int) $order->design_green_qty;

        $qty = (int) $dp->quantity;
        $totalColorQty = $qty * $dq;

        if ($onlyRed !== 0 && $red !== 0) {
            $finalRed = $onlyRed * $designRed + $red * $designRedGreen;
        } elseif ($onlyRed !== 0 && $red === 0) {
            $finalRed = $onlyRed * $designRed;
        } elseif ($onlyRed === 0 && $red !== 0) {
            $finalRed = $red * $designRedGreen;
        } else {
            $finalRed = 0;
        }

        if ($onlyGreen !== 0 && $green !== 0) {
            $finalGreenPartial = $onlyGreen * $designGreen + $green * $designRedGreen;
        } elseif ($onlyGreen !== 0 && $green === 0) {
            $finalGreenPartial = $onlyGreen * $designGreen;
        } elseif ($onlyGreen === 0 && $green !== 0) {
            $finalGreenPartial = $green * $designGreen;
        } else {
            $finalGreenPartial = 0;
        }

        $finalRedgreen = 0;
        $finalTotalGreen = $finalGreenPartial + $finalRedgreen;

        if ($productTypeId === self::PRODUCT_TYPE_KUNDAN_FULL) {
            $whiteQty = 0;
        } else {
            $whiteQty = (int) round($totalColorQty - ($finalRed + $finalGreenPartial));
            if ($whiteQty < 0) {
                $whiteQty = 0;
            }
        }

        $wts = $this->kstoneUnitWeights((int) $product->id);

        return [
            'product_id' => (int) $product->id,
            'product_name' => (string) $product->product_name,
            'kstone_name' => $wts['name'],
            'kstone_qty' => $wts['qty'],
            'kstone_stone_wt' => $wts['stone_wt'],
            'total_color_qty' => (int) $totalColorQty,
            'red_qty' => (int) round($finalRed),
            'green_qty' => (int) round($finalTotalGreen),
            'white_qty' => $whiteQty,
            'unit_kstone_wt' => $wts['k'],
            'unit_die_wt' => $wts['d'],
        ];
    }

    /**
     * @return array{name: string, qty: int, stone_wt: float, k: float, d: float}
     */
    private function kstoneUnitWeights(int $productId): array
    {
        $line = RuhiItemKstone::query()
            ->where('item_id', $productId)
            ->with(['kstone' => fn ($q) => $q->withTrashed()])
            ->orderBy('id')
            ->first();

        if (! $line) {
            return ['name' => '', 'qty' => 0, 'stone_wt' => 0.0, 'k' => 0.0, 'd' => 0.0];
        }

        $name = $line->kstone ? (string) $line->kstone->name : '';
        $stoneWt = $line->kstone ? (float) $line->kstone->stoneweight : 0.0;

        return [
            'name' => $name,
            'qty' => (int) $line->kstone_quantity,
            'stone_wt' => $stoneWt,
            'k' => (float) $line->kstone_weight,
            'd' => (float) $line->kstone_dieweight,
        ];
    }

    /**
     * @param  array{kstone_name: string, kstone_qty: int, kstone_stone_wt: float}  $merged
     */
    private function formatKstoneForBlock(int $productTypeId, array $merged): string
    {
        if ($productTypeId === self::PRODUCT_TYPE_KUNDAN_FULL) {
            return $this->formatKstoneKundanfull(
                (string) $merged['kstone_name'],
                (int) $merged['kstone_qty']
            );
        }

        return $this->formatKstonePulkiAddFull(
            (string) $merged['kstone_name'],
            (float) $merged['kstone_stone_wt']
        );
    }

    /** Kundanfull block: `<kstone name>-(<kstone qty>)`. */
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

    /** Pulkifull & AddFull blocks: `kstone name (wt <weight>)` using {@see RuhiKstone::stoneweight}. */
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
}
