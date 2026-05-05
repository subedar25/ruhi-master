<?php

namespace App\Core\RuhiReports\Services;

use Illuminate\Support\Collection;

/**
 * GS Wise Collet Kstone Color Report: legacy CI {@code gsColletKstoneReport}.
 *
 * For the selected GS, each {@code gs_order_by_color} row (lot + design) feeds
 * {@code getFullReportByDesignAndType($details, 3, $colletdetails)}: collet products
 * ({@code product_type} = 3) with full-report collate math, then
 * {@code calculateDuplicateColor}, natural case sort on product name, and
 * {@code calculateKStonesByProductId}-style k-stone channel weights.
 *
 * Implementation is delegated to {@see GsColorFullReportService::buildColletKstoneColorReport()}.
 */
final class GsColletKstoneColorReportService
{
    public function __construct(
        private readonly GsColorFullReportService $colorFull
    ) {}

    /**
     * @return array{
     *     gs_name: string,
     *     rows: array<int, array<string, mixed>>,
     *     totals: array<string, int|float>
     * }
     */
    public function buildReport(int $gsId): array
    {
        return $this->colorFull->buildColletKstoneColorReport($gsId);
    }

    public function listGsForDropdown(): Collection
    {
        return $this->colorFull->listGsForDropdown();
    }
}
