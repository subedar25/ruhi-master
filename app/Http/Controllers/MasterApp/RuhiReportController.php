<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\RuhiReports\Services\GsDetailEachItemReportService;
use App\Core\RuhiReports\Services\GsDieReportService;
use App\Core\RuhiReports\Services\GsFullReportService;
use App\Core\RuhiReports\Services\GsLotWiseItemsReportService;
use App\Core\RuhiReports\Services\GsWiseCastingReportService;
use App\Core\RuhiReports\Services\GsColorColletReportService;
use App\Core\RuhiReports\Services\GsColletKstoneColorReportService;
use App\Core\RuhiReports\Services\GsColorFullReportService;
use App\Core\RuhiReports\Services\GsWiseColletReportService;
use App\Core\RuhiReports\Services\GsWiseDubbyReportService;
use App\Core\RuhiReports\Services\GsWiseDropReportService;
use App\Models\RuhiGs;
use App\Models\RuhiSlot;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RuhiReportController extends Controller
{
    public function gsLotWiseItems(): View
    {
        return view('masterapp.ruhi-reports.gs-lot-wise-items');
    }

    public function gsWiseCastingReport(): View
    {
        return view('masterapp.ruhi-reports.gs-wise-casting-report');
    }

    public function gsWiseCastingDetailReport(): View
    {
        return view('masterapp.ruhi-reports.gs-wise-casting-detail-report');
    }

    public function gsWiseDubbyReport(): View
    {
        return view('masterapp.ruhi-reports.gs-wise-dubby-report');
    }

    public function gsWiseColletReport(): View
    {
        return view('masterapp.ruhi-reports.gs-wise-collet-report');
    }

    public function gsColorColletReport(): View
    {
        return view('masterapp.ruhi-reports.gs-color-collet-report');
    }

    public function gsColorFullReport(): View
    {
        return view('masterapp.ruhi-reports.gs-color-full-report');
    }

    public function gsColletKstoneColorReport(): View
    {
        return view('masterapp.ruhi-reports.gs-collet-kstone-color-report');
    }

    public function gsWiseDropReport(): View
    {
        return view('masterapp.ruhi-reports.gs-wise-drop-report');
    }

    public function gsFullReport(): View
    {
        return view('masterapp.ruhi-reports.gs-full-report');
    }

    public function gsDieReport(): View
    {
        return view('masterapp.ruhi-reports.gs-die-report');
    }

    public function gsDetailEachItemReport(): View
    {
        return view('masterapp.ruhi-reports.gs-detail-each-item-report');
    }

    public function gsDetailEachItemReportPrint(Request $request, GsDetailEachItemReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $typesRaw = (string) $request->query('types', '');
        $types = array_values(array_filter(array_map('intval', $typesRaw !== '' ? explode(',', $typesRaw) : [])));
        if ($types === []) {
            $types = GsDetailEachItemReportService::DEFAULT_PRODUCT_TYPES;
        }

        $designsRaw = (string) $request->query('designs', '');
        $designIds = array_values(array_filter(array_map('intval', explode(',', $designsRaw))));
        // Empty = all designs on GS (handled inside buildReport)

        $itemsRaw = (string) $request->query('items', '');
        $productIds = array_values(array_filter(array_map('intval', explode(',', $itemsRaw))));

        $nf = (string) $request->query('nf', '');
        abort_unless(in_array($nf, ['', '1', '2'], true), 404);

        $report = $service->buildReport($gsId, $types, $designIds, $productIds, $nf);

        return view('masterapp.ruhi-reports.gs-detail-each-item-report-print', [
            'report' => $report,
        ]);
    }

    public function gsDieReportPrint(Request $request, GsDieReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildReport($gsId);

        return view('masterapp.ruhi-reports.gs-die-report-print', [
            'report' => $report,
        ]);
    }

    public function gsFullReportPrint(Request $request, GsFullReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $section = $request->query('section');
        $section = is_string($section) ? trim($section) : '';
        $allowedSections = ['kundanfull', 'pulkifull', 'addfull'];
        abort_unless($section === '' || in_array($section, $allowedSections, true), 404);

        $report = $service->buildReport($gsId);

        return view('masterapp.ruhi-reports.gs-full-report-print', [
            'report' => $report,
            'section' => $section === '' ? null : $section,
        ]);
    }

    public function gsWiseColletReportPrint(Request $request, GsWiseColletReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildReport($gsId);

        return view('masterapp.ruhi-reports.gs-wise-collet-report-print', [
            'report' => $report,
        ]);
    }

    public function gsColorColletReportPrint(Request $request, GsColorColletReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildReport($gsId);

        return view('masterapp.ruhi-reports.gs-color-collet-report-print', [
            'report' => $report,
        ]);
    }

    public function gsColorFullReportPrint(Request $request, GsColorFullReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $sfilter = (int) $request->query('sfilter', 0);
        $sfilter = $sfilter === 0 ? null : $sfilter;

        $report = $service->buildReport($gsId, $sfilter);

        return view('masterapp.ruhi-reports.gs-color-full-report-print', [
            'report' => $report,
        ]);
    }

    /**
     * Print a single GS Color Full Report section (Kundanfull, Pulkifull, or AddFull).
     */
    public function gsColorFullReportBlockPrint(Request $request, GsColorFullReportService $service, string $block): View
    {
        $allowed = ['kundanfull', 'pulkifull', 'addfull'];
        abort_unless(in_array($block, $allowed, true), 404);

        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $sfilter = (int) $request->query('sfilter', 0);
        $sfilter = $sfilter === 0 ? null : $sfilter;

        $full = $service->buildReport($gsId, $sfilter);

        $meta = [
            'kundanfull' => [
                'title' => 'GS Wise Kundanfull Color Report',
                'layout' => 'detail',
            ],
            'pulkifull' => [
                'title' => 'GS Wise Pulkifull Color Report',
                'layout' => 'simple',
            ],
            'addfull' => [
                'title' => 'GS Wise AddFull Color Report',
                'layout' => 'simple',
            ],
        ][$block];

        $firstCol = match ($block) {
            'pulkifull' => 'Pulki',
            'addfull' => 'AddFull',
            default => 'Pulki',
        };

        return view('masterapp.ruhi-reports.gs-color-full-report-block-print', [
            'gs_name' => $full['gs_name'],
            'block_title' => $meta['title'],
            'layout' => $meta['layout'],
            'totalLabel' => 'Kundan Total Qty',
            'firstCol' => $firstCol,
            'rows' => $full[$block],
            'totals' => $full['totals_'.$block] ?? [],
        ]);
    }

    public function gsColletKstoneColorReportPrint(Request $request, GsColletKstoneColorReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildReport($gsId);

        return view('masterapp.ruhi-reports.gs-collet-kstone-color-report-print', [
            'report' => $report,
        ]);
    }

    public function gsWiseDropReportPrint(Request $request, GsWiseDropReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildReport($gsId);

        return view('masterapp.ruhi-reports.gs-wise-drop-report-print', [
            'report' => $report,
        ]);
    }

    public function gsWiseDubbyReportPrint(Request $request, GsWiseDubbyReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildReport($gsId);

        return view('masterapp.ruhi-reports.gs-wise-dubby-report-print', [
            'report' => $report,
        ]);
    }

    public function gsWiseCastingReportPrint(Request $request, GsWiseCastingReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        $lotId = (int) $request->query('lot', 0);
        abort_unless($gsId > 0 && $lotId > 0, 404);

        abort_unless(
            RuhiSlot::query()->where('gs_id', $gsId)->whereKey($lotId)->exists(),
            404
        );

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildReport($gsId, $lotId);

        return view('masterapp.ruhi-reports.gs-wise-casting-report-print', [
            'report' => $report,
        ]);
    }

    public function gsWiseCastingDetailReportPrint(Request $request, GsWiseCastingReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        $lotId = (int) $request->query('lot', 0);
        abort_unless($gsId > 0 && $lotId > 0, 404);

        abort_unless(
            RuhiSlot::query()->where('gs_id', $gsId)->whereKey($lotId)->exists(),
            404
        );

        RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);

        $report = $service->buildDetailReport($gsId, $lotId);

        return view('masterapp.ruhi-reports.gs-wise-casting-detail-report-print', [
            'report' => $report,
        ]);
    }

    public function gsLotWiseItemsPrint(Request $request, GsLotWiseItemsReportService $service): View
    {
        $gsId = (int) $request->query('gs', 0);
        abort_unless($gsId > 0, 404);

        $gs = RuhiGs::query()->whereNull('deleted_at')->findOrFail($gsId);
        $blocks = $service->lotBlocksForGs($gsId);

        return view('masterapp.ruhi-reports.gs-lot-wise-items-print', [
            'gs' => $gs,
            'blocks' => $blocks,
        ]);
    }
}
