<?php

namespace App\Core\RuhiReports\Services;

use App\Core\RuhiReports\ReportNameSort;
use App\Models\RuhiCollateByColor;
use App\Models\RuhiDesign;
use App\Models\RuhiDesignProduct;
use App\Models\RuhiGs;
use App\Models\RuhiGsOrderByColor;
use App\Models\RuhiProduct;
use Illuminate\Support\Collection;

class GsDetailEachItemReportService
{
    /** Default `r_product.product_type` filter set (Collet, AD Full, Polki Full, Kundan Full, Drop). */
    public const DEFAULT_PRODUCT_TYPES = [3, 4, 5, 6, 8];

    /** `r_product.product_type` values that can appear in the Collate Item section (excludes Drop). */
    public const COLLATE_PRODUCT_TYPE_IDS = [3, 4, 5, 6];

    /** `r_product.product_type` for Drop items (checkbox "Drop"). */
    public const DROP_PRODUCT_TYPE_ID = 8;

    private const DROP_ITEM_TYPE_ID = 8;

    /**
     * Whether to show Collate / Drop blocks from the current product-type checkboxes.
     * Empty selection is treated as "all types" (same as default) so both sections show.
     *
     * @param  array<int|string>  $productTypeIds
     * @return array{show_collate: bool, show_drop: bool}
     */
    public static function sectionVisibilityForProductTypes(array $productTypeIds): array
    {
        $pt = array_values(array_unique(array_map('intval', $productTypeIds)));
        if ($pt === []) {
            return ['show_collate' => true, 'show_drop' => true];
        }

        return [
            'show_collate' => count(array_intersect($pt, self::COLLATE_PRODUCT_TYPE_IDS)) > 0,
            'show_drop' => in_array(self::DROP_PRODUCT_TYPE_ID, $pt, true),
        ];
    }

    public function listGsForDropdown(): Collection
    {
        return RuhiGs::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Designs that appear on the selected GS (from `r_gs_order_by_color`).
     *
     * @return Collection<int, RuhiDesign>
     */
    public function listDesignsForGs(int $gsId): Collection
    {
        $designIds = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->distinct()
            ->pluck('design_id')
            ->all();

        if ($designIds === []) {
            return collect();
        }

        return RuhiDesign::query()
            ->withTrashed()
            ->whereIn('id', $designIds)
            ->get(['id', 'design_name'])
            ->sort(function (RuhiDesign $a, RuhiDesign $b): int {
                $ta = ReportNameSort::hyphenNameTuple((string) $a->design_name);
                $tb = ReportNameSort::hyphenNameTuple((string) $b->design_name);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values();
    }

    /**
     * Distinct products used on the selected designs, restricted by product types (master `product_type`).
     *
     * @param  array<int>  $designIds
     * @param  array<int>  $productTypes
     * @return Collection<int, RuhiProduct>
     */
    public function listProductsForDesignsAndTypes(array $designIds, array $productTypes): Collection
    {
        if ($designIds === [] || $productTypes === []) {
            return collect();
        }

        $productIds = RuhiDesignProduct::query()
            ->whereIn('design_id', $designIds)
            ->whereHas('product', function ($q) use ($productTypes): void {
                $q->whereIn('product_type', $productTypes);
            })
            ->distinct()
            ->pluck('product_id')
            ->all();

        if ($productIds === []) {
            return collect();
        }

        return RuhiProduct::query()
            ->withTrashed()
            ->whereIn('id', $productIds)
            ->whereIn('product_type', $productTypes)
            ->get(['id', 'product_name', 'product_type'])
            ->sort(function (RuhiProduct $a, RuhiProduct $b): int {
                $ta = ReportNameSort::hyphenNameTuple((string) $a->product_name);
                $tb = ReportNameSort::hyphenNameTuple((string) $b->product_name);

                return ReportNameSort::compareTuples($ta, $tb);
            })
            ->values();
    }

    /**
     * @param  array<int>  $productTypes
     * @param  array<int>  $designIds  Designs to include (must exist on GS); empty = all designs on GS
     * @param  array<int>  $productIds  Empty = all products matching types & designs
     * @param  ''|'1'|'2'  $nameFilter  Collate Item rows only: '' = all names; '1' = name does not contain "(s)"; '2' = name contains "(s)" (literal brackets). Drop Item rows ignore this filter.
     * @return array{
     *     gs_name: string,
     *     blocks: array<int, array{
     *         design_id: int,
     *         design_name: string,
     *         header: array{color_qty: int|string, collate_qty: string, zumka: string, uf: string, note: string},
     *         order_footer: array{color_count: int, red: int, red_green: int, green: int, white: int},
     *         collate_rows: array<int, array{item: string, total_qty: int, red: int, green: int, white: int}>,
     *         collate_column_totals: array{total_qty: int, red: int, green: int, white: int},
     *         drop_rows: array<int, array{item: string, total_qty: int, red: int, green: int, white: int}>,
     *         drop_column_totals: array{total_qty: int, red: int, green: int, white: int}
     *     }>
     * }
     */
    public function buildReport(
        int $gsId,
        array $productTypes,
        array $designIds,
        array $productIds,
        string $nameFilter,
    ): array {
        $gs = RuhiGs::query()->find($gsId);
        $gsName = (string) ($gs->name ?? '');

        $productTypes = array_values(array_unique(array_map('intval', $productTypes)));
        sort($productTypes);

        $designIds = array_values(array_unique(array_map('intval', $designIds)));
        sort($designIds);

        $productIds = array_values(array_unique(array_map('intval', $productIds)));

        $allGsDesignIds = RuhiGsOrderByColor::query()
            ->where('gs_id', $gsId)
            ->distinct()
            ->pluck('design_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($designIds === []) {
            $designIds = $allGsDesignIds;
        } else {
            $designIds = array_values(array_intersect($designIds, $allGsDesignIds));
        }

        $blocks = [];

        foreach ($designIds as $designId) {
            $design = RuhiDesign::query()->withTrashed()->find($designId);
            if (! $design) {
                continue;
            }

            $orderRowsForDesign = RuhiGsOrderByColor::query()
                ->where('gs_id', $gsId)
                ->where('design_id', $designId)
                ->get();

            $scale = (int) $orderRowsForDesign->sum('design_qty');

            $orderFooter = RuhiGsOrderByColor::query()
                ->where('gs_id', $gsId)
                ->where('design_id', $designId)
                ->selectRaw('COALESCE(SUM(design_qty),0) as dq')
                ->selectRaw('COALESCE(SUM(design_red_qty),0) as dr')
                ->selectRaw('COALESCE(SUM(design_red_green_qty),0) as drg')
                ->selectRaw('COALESCE(SUM(design_green_qty),0) as dg')
                ->selectRaw('COALESCE(SUM(white_qty),0) as dw')
                ->first();

            $colorCount = (int) ($orderFooter->dq ?? 0);
            $footerRed = (int) ($orderFooter->dr ?? 0);
            $footerRedGreen = (int) ($orderFooter->drg ?? 0);
            $footerGreen = (int) ($orderFooter->dg ?? 0);
            $footerWhite = (int) ($orderFooter->dw ?? 0);

            $collateRows = [];
            $dropRows = [];

            $dps = RuhiDesignProduct::query()
                ->where('design_id', $designId)
                ->with([
                    'product' => fn ($q) => $q->withTrashed(),
                    'itemType',
                    'collateByColors',
                ])
                ->get();

            foreach ($dps as $dp) {
                $product = $dp->product;
                if (! $product) {
                    continue;
                }
                if (! in_array((int) $product->product_type, $productTypes, true)) {
                    continue;
                }
                if ($productIds !== [] && ! in_array((int) $product->id, $productIds, true)) {
                    continue;
                }

                $itemType = $dp->itemType;
                $isDrop = (int) $dp->item_type_id === self::DROP_ITEM_TYPE_ID;
                $isCollate = $itemType && strcasecmp(trim((string) $itemType->type_by_color), 'Yes') === 0;

                if ($isCollate && ! $this->matchesNameFilter((string) $product->product_name, $nameFilter)) {
                    continue;
                }

                if ($isDrop || $isCollate) {
                    [$rowTotal, $rowRed, $rowGreen, $rowWhite] = $this->collateRowQuantitiesForDesignProduct(
                        $dp,
                        $orderRowsForDesign
                    );
                }

                if ($isDrop) {
                    $dropRows[] = [
                        'item' => (string) $product->product_name,
                        'total_qty' => $rowTotal,
                        'red' => $rowRed,
                        'green' => $rowGreen,
                        'white' => $rowWhite,
                    ];
                } elseif ($isCollate) {
                    $collateRows[] = [
                        'item' => (string) $product->product_name,
                        'total_qty' => $rowTotal,
                        'red' => $rowRed,
                        'green' => $rowGreen,
                        'white' => $rowWhite,
                    ];
                }
            }

            usort($collateRows, fn (array $a, array $b): int => ReportNameSort::compareTuples(
                ReportNameSort::hyphenNameTuple($a['item']),
                ReportNameSort::hyphenNameTuple($b['item'])
            ));
            usort($dropRows, fn (array $a, array $b): int => ReportNameSort::compareTuples(
                ReportNameSort::hyphenNameTuple($a['item']),
                ReportNameSort::hyphenNameTuple($b['item'])
            ));

            // Show design block when at least one section has data.
            // Hide only sections where both Collate and Drop are empty.
            if ($collateRows === [] && $dropRows === []) {
                continue;
            }

            $blocks[] = [
                'design_id' => $designId,
                'design_name' => (string) $design->design_name,
                'header' => [
                    'color_qty' => $colorCount,
                    'collate_qty' => (string) ($design->dubby_qty ?? ''),
                    'zumka' => (string) ($design->zumka_qty ?? ''),
                    'uf' => (string) ($design->uf ?? ''),
                    'note' => (string) ($design->note ?? ''),
                ],
                'order_footer' => [
                    'color_count' => $colorCount,
                    'red' => $footerRed,
                    'red_green' => $footerRedGreen,
                    'green' => $footerGreen,
                    'white' => $footerWhite,
                ],
                'collate_rows' => array_values($collateRows),
                'collate_column_totals' => $this->sumQtyCols($collateRows),
                'drop_rows' => array_values($dropRows),
                'drop_column_totals' => $this->sumQtyCols($dropRows),
            ];
        }

        return [
            'gs_name' => $gsName,
            'blocks' => $blocks,
        ];
    }

    /**
     * @param  array<int, array{item: string, total_qty: int, red: int, green: int, white: int}>  $rows
     * @return array{total_qty: int, red: int, green: int, white: int}
     */
    private function sumQtyCols(array $rows): array
    {
        $t = ['total_qty' => 0, 'red' => 0, 'green' => 0, 'white' => 0];
        foreach ($rows as $r) {
            $t['total_qty'] += (int) $r['total_qty'];
            $t['red'] += (int) $r['red'];
            $t['green'] += (int) $r['green'];
            $t['white'] += (int) $r['white'];
        }

        return $t;
    }

    /**
     * Collate-only product name rule (caller passes only for collate rows).
     * Matches the literal substring "(s)" with brackets — no stripping of brackets or names.
     *
     * @param  ''|'1'|'2'|'all'  $mode  ''|'all' include all; '1' without "(s)" in name; '2' only if name contains "(s)"
     */
    private function matchesNameFilter(string $productName, string $mode): bool
    {
        $mode = trim($mode);
        if ($mode === '' || $mode === 'all') {
            return true;
        }

        $hasBracketS = stripos($productName, '(s)') !== false;

        if ($mode === '1') {
            return ! $hasBracketS;
        }

        if ($mode === '2') {
            return $hasBracketS;
        }

        return true;
    }

    /**
     * Totals for one design product (Collate or Drop) on this GS/design: for each GS order line,
     * total = r_design_products.quantity × r_gs_order_by_color.design_qty; red/green from
     * r_collate_by_color × order color qtys; white = total − red − green (per line), then summed.
     * If there are no collate-by-color rows, red/green are 0 and white equals total per line.
     *
     * @param  \Illuminate\Support\Collection<int, RuhiGsOrderByColor>  $orderRowsForDesign
     * @return array{0: int, 1: int, 2: int, 3: int} total_qty, red, green, white
     */
    private function collateRowQuantitiesForDesignProduct(RuhiDesignProduct $dp, Collection $orderRowsForDesign): array
    {
        $dpQty = (int) $dp->quantity;
        $collateLines = $dp->collateByColors;
        if ($orderRowsForDesign->isEmpty()) {
            return [0, 0, 0, 0];
        }

        $totalQtySum = 0;
        $redSum = 0;
        $greenSum = 0;
        $whiteSum = 0;

        foreach ($orderRowsForDesign as $productDetail) {
            $totalQtyOnce = $dpQty * (int) $productDetail->design_qty;
            $lineRed = 0;
            $lineGreen = 0;
            if ($collateLines->isNotEmpty()) {
                foreach ($collateLines as $designDetail) {
                    $lineRed += $this->collateRedContribution($designDetail, $productDetail);
                    $lineGreen += $this->collateGreenContribution($designDetail, $productDetail);
                }
            }
            $lineWhite = $totalQtyOnce - ($lineRed + $lineGreen);

            $totalQtySum += $totalQtyOnce;
            $redSum += $lineRed;
            $greenSum += $lineGreen;
            $whiteSum += $lineWhite;
        }

        return [$totalQtySum, $redSum, $greenSum, $whiteSum];
    }

    private function collateRedContribution(RuhiCollateByColor $designDetail, RuhiGsOrderByColor $productDetail): int
    {
        $onlyRed = $designDetail->only_red_qty;
        $red = $designDetail->red_qty;
        $designRedQty = (int) $productDetail->design_red_qty;
        $designRedGreenQty = (int) $productDetail->design_red_green_qty;

        if (! empty($onlyRed) && empty($red)) {
            return (int) $onlyRed * $designRedQty;
        }
        if (empty($onlyRed) && ! empty($red)) {
            return (int) $red * $designRedGreenQty;
        }
        if (! empty($onlyRed) && ! empty($red)) {
            return (int) $onlyRed * $designRedQty + (int) $red * $designRedGreenQty;
        }

        return 0;
    }

    private function collateGreenContribution(RuhiCollateByColor $designDetail, RuhiGsOrderByColor $productDetail): int
    {
        $onlyGreen = $designDetail->only_green_qty;
        $green = $designDetail->green_qty;
        $designGreenQty = (int) $productDetail->design_green_qty;
        $designRedGreenQty = (int) $productDetail->design_red_green_qty;

        if (! empty($onlyGreen) && empty($green)) {
            return (int) $onlyGreen * $designGreenQty;
        }
        if (empty($onlyGreen) && ! empty($green)) {
            return (int) $green * $designRedGreenQty;
        }
        if (! empty($onlyGreen) && ! empty($green)) {
            return (int) $onlyGreen * $designGreenQty + (int) $green * $designRedGreenQty;
        }

        return 0;
    }
}
