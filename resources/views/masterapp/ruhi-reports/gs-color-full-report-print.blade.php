<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Color Full Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 14px; font-size: 16px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .doc-title { font-size: 15px; font-weight: 700; margin-bottom: 10px; }
        .date-right { float: right; font-weight: 500; }
        .block-title { font-size: 16px; font-weight: 700; margin: 16px 0 8px; }
        table.data { width: 100%; border-collapse: collapse; font-size: 16px; margin-bottom: 14px; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 4px 6px; }
        table.data thead th { background: #f0f0f0; }
        table.data tfoot td { font-weight: 700; background: #f5f5f5; }
        .print-block { display: block; }
        .print-break-before { page-break-before: always; break-before: page; }
        @media print {
            .actions { display: none; }
            body { margin: 6mm; }
            .print-block { display: block; }
            .print-break-before { page-break-before: always; break-before: page; }
            body, table, th, td { font-size: 16px !important; }
            thead { display: table-row-group; }
            tfoot { display: table-row-group; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="doc-title">
        <span class="date-right">Date: {{ now()->format('d-m-Y') }}</span>
        GS Color Full Report
        @if($report['gs_name'] !== '')
            ({{ $report['gs_name'] }})
        @endif
    </div>

    @php
        $printBlocks = [
            ['title' => 'GS Wise Kundanfull Color Report', 'layout' => 'detail', 'totalLabel' => 'Kundan Total Qty', 'rows' => $report['kundanfull'], 'totals' => $report['totals_kundanfull'] ?? []],
            ['title' => 'GS Wise Pulkifull Color Report', 'layout' => 'simple', 'firstCol' => 'Pulki', 'rows' => $report['pulkifull'], 'totals' => $report['totals_pulkifull'] ?? []],
            ['title' => 'GS Wise AddFull Color Report', 'layout' => 'simple', 'firstCol' => 'AddFull', 'rows' => $report['addfull'], 'totals' => $report['totals_addfull'] ?? []],
        ];
    @endphp

    @foreach($printBlocks as $block)
        <section class="print-block {{ $loop->first ? '' : 'print-break-before' }}">
            <div class="block-title">{{ $block['title'] }}@if($report['gs_name'] !== '') ({{ $report['gs_name'] }})@endif</div>
            @if(($block['layout'] ?? 'detail') === 'simple')
                <table class="data">
                    <thead>
                        <tr>
                            <th>{{ $block['firstCol'] }}</th>
                            <th>Kstone</th>
                            <th>Total Qty</th>
                            <th>Red</th>
                            <th>Green</th>
                            <th>White</th>
                            <th>Wt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($block['rows'] as $r)
                            <tr>
                                <td>{{ $r['product_name'] }}</td>
                                <td>{{ $r['kstone'] }}</td>
                                <td>{{ number_format((int) $r['total_color_qty'], 0, '.', '') }}</td>
                                <td>{{ number_format((int) $r['red_qty'], 0, '.', '') }}</td>
                                <td>{{ number_format((int) $r['green_qty'], 0, '.', '') }}</td>
                                <td>{{ number_format((int) $r['white_qty'], 0, '.', '') }}</td>
                                <td>{{ number_format((float) ($r['total_wt'] ?? 0), 2, '.', '') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="text-align:center;color:#666;">No data.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($block['rows']) > 0)
                        @php $pt = $block['totals'] ?? []; @endphp
                        <tfoot>
                            <tr>
                                <td colspan="2">Grand Total</td>
                                <td>{{ number_format((int) ($pt['total_color_qty'] ?? 0), 0, '.', '') }}</td>
                                <td>{{ number_format((int) ($pt['red_qty'] ?? 0), 0, '.', '') }}</td>
                                <td>{{ number_format((int) ($pt['green_qty'] ?? 0), 0, '.', '') }}</td>
                                <td>{{ number_format((int) ($pt['white_qty'] ?? 0), 0, '.', '') }}</td>
                                <td>{{ number_format((float) ($pt['total_wt'] ?? 0), 2, '.', '') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            @else
                <table class="data">
                    <thead>
                        <tr>
                            <th rowspan="2">Product</th>
                            <th rowspan="2">{{ $block['totalLabel'] }}</th>
                            <th rowspan="2">Kstone</th>
                            <th colspan="3">Red</th>
                            <th colspan="3">Green</th>
                            <th colspan="3">White</th>
                        </tr>
                        <tr>
                            <th>Qty</th><th>Kstone Wt</th><th>Die Wt</th>
                            <th>Qty</th><th>Kstone Wt</th><th>Die Wt</th>
                            <th>Qty</th><th>Kstone Wt</th><th>Die Wt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($block['rows']) === 0)
                            <tr><td colspan="12" style="text-align:center;color:#666;">No data.</td></tr>
                        @else
                            @include('masterapp.ruhi-reports.partials.gs-color-full-kstone-detail-rows', [
                                'rows' => $block['rows'],
                                'useBorderLeft' => false,
                            ])
                        @endif
                    </tbody>
                    @if(count($block['rows']) > 0)
                        @php $pt = $block['totals'] ?? []; @endphp
                        <tfoot>
                            <tr>
                                <td>Grand Total</td>
                                <td>{{ number_format((int) ($pt['total_color_qty'] ?? 0), 0, '.', '') }}</td>
                                <td></td>
                                <td>{{ number_format((int) ($pt['red_qty'] ?? 0), 0, '.', '') }}</td>
                                <td>{{ number_format((float) ($pt['red_kstone_wt'] ?? 0), 2, '.', '') }}</td>
                                <td>{{ number_format((float) ($pt['red_die_wt'] ?? 0), 2, '.', '') }}</td>
                                <td>{{ number_format((int) ($pt['green_qty'] ?? 0), 0, '.', '') }}</td>
                                <td>{{ number_format((float) ($pt['green_kstone_wt'] ?? 0), 2, '.', '') }}</td>
                                <td>{{ number_format((float) ($pt['green_die_wt'] ?? 0), 2, '.', '') }}</td>
                                <td>{{ number_format((int) ($pt['white_qty'] ?? 0), 0, '.', '') }}</td>
                                <td>{{ number_format((float) ($pt['white_kstone_wt'] ?? 0), 2, '.', '') }}</td>
                                <td>{{ number_format((float) ($pt['white_die_wt'] ?? 0), 2, '.', '') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            @endif
        </section>
    @endforeach
</body>
</html>
