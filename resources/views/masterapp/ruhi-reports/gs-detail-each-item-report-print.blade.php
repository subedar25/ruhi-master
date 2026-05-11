<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Detail Report of Each Item</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 14px; font-size: 16px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .doc-title { font-size: 17px; font-weight: 700; margin-bottom: 10px; }
        .block { margin-bottom: 18px; page-break-inside: avoid; }
        .design-detail-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #ccc;
        }
        .mini-table { border-collapse: collapse; margin-bottom: 10px; max-width: 72rem; font-size: 16px; }
        .mini-table th, .mini-table td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
        .mini-table th.bg { background: #f5f5f5; font-weight: 700; white-space: nowrap; }
        table.data { width: 100%; max-width: 72rem; border-collapse: collapse; font-size: 16px; margin-bottom: 8px; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        table.data thead th { background: #fafafa; font-weight: 700; }
        .section-title { font-size: 16px; font-weight: 700; margin: 10px 0 6px; }
        .footer-line { font-size: 16px; padding: 4px 0; }
        .date-right { float: right; font-size: 16px; font-weight: 500; }
        .collate-after-spacer { height: 12px; }
        .collate-summary-row {
            max-width: 72rem;
            width: 100%;
            margin-bottom: 12px;
        }
        .collate-summary-row .left {
            text-align: left;
            white-space: nowrap;
        }
        .collate-summary-row .collate-summary-one-line {
            font-size: 16px;
            line-height: 1.45;
        }
        .collate-summary-row .collate-summary-one-line .head {
            font-weight: 700;
        }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
            body, table, th, td, .mini-table, .footer-line { font-size: 16px !important; }
            thead { display: table-row-group; }
            tfoot { display: table-row-group; }
            .block + .block { page-break-before: always; break-before: page; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="section-title" style="margin-bottom: 10px;"><span class="date-right">Date: {{ now()->format('d-m-Y') }}</span>GS Wise Detail Report of Each Item</div>

    @foreach($report['blocks'] as $block)
        <div class="block design-detail">
            <div class="design-detail-title">
                GS Wise Detail Report of Each Item
                @if($report['gs_name'] !== '')
                    <span style="font-weight: 700;">({{ $report['gs_name'] }})</span>
                @endif
            </div>
            <table class="mini-table" style="width:100%; max-width:72rem;">
                <tr>
                    <th class="bg">Design</th>
                    <th class="bg">Color Qty</th>
                    <th class="bg">Collate Qty</th>
                    <th class="bg">Zumka</th>
                    <th class="bg">UF</th>
                </tr>
                <tr>
                    <td style="font-weight:700;">{{ $block['design_name'] }} - {{ (int) $block['order_footer']['color_count'] }}</td>
                    <td>
                        <div>Red:- {{ $block['order_footer']['red'] }}</div>
                        <div>Red+Green:- {{ $block['order_footer']['red_green'] }}</div>
                        <div>Green:- {{ $block['order_footer']['green'] }}</div>
                        <div>White:- {{ $block['order_footer']['white'] }}</div>
                    </td>
                    <td>{{ $block['header']['collate_qty'] }}</td>
                    <td>{{ $block['header']['zumka'] }}</td>
                    <td>{{ $block['header']['uf'] }}</td>
                </tr>
                <tr>
                    <th class="bg">Note</th>
                    <td colspan="4">{{ $block['header']['note'] }}</td>
                </tr>
            </table>

            @if($showCollateSection ?? true)
            <div class="section-title">Collate Item</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Collate Item</th>
                        <th>Total Quantity</th>
                        <th>Red</th>
                        <th>Green</th>
                        <th>White</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($block['collate_rows'] as $r)
                        <tr>
                            <td>{{ $r['item'] }}</td>
                            <td>{{ number_format((int) $r['total_qty'], 0, '.', '') }}</td>
                            <td>{{ number_format((int) $r['red'], 0, '.', '') }}</td>
                            <td>{{ number_format((int) $r['green'], 0, '.', '') }}</td>
                            <td>{{ number_format((int) $r['white'], 0, '.', '') }}</td>
                        </tr>
                    @endforeach
                    @if(count($block['collate_rows']) > 0)
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong>{{ number_format((int) $block['collate_column_totals']['total_qty'], 0, '.', '') }}</strong></td>
                            <td><strong>{{ number_format((int) $block['collate_column_totals']['red'], 0, '.', '') }}</strong></td>
                            <td><strong>{{ number_format((int) $block['collate_column_totals']['green'], 0, '.', '') }}</strong></td>
                            <td><strong>{{ number_format((int) $block['collate_column_totals']['white'], 0, '.', '') }}</strong></td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="collate-after-spacer" aria-hidden="true"></div>
            @endif
            @if(($showCollateSection ?? true) || ($showDropSection ?? true))
            <div class="collate-summary-row">
                <div class="left collate-summary-one-line">
                    <span class="head">({{ $report['gs_name'] }})({{ $block['design_name'] }}) - {{ (int) $block['order_footer']['color_count'] }}</span><span>&nbsp;&nbsp;&nbsp;&nbsp;</span><span>Red:- {{ (int) $block['order_footer']['red'] }}&nbsp;&nbsp;&nbsp;&nbsp;Red+Green:- {{ (int) $block['order_footer']['red_green'] }}&nbsp;&nbsp;&nbsp;&nbsp;Green:- {{ (int) $block['order_footer']['green'] }}&nbsp;&nbsp;&nbsp;&nbsp;White:- {{ (int) $block['order_footer']['white'] }}</span>
                </div>
            </div>
            @endif
            @if($showDropSection ?? true)
            <div class="section-title">Drop Item</div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Drop Item</th>
                        <th>Total Quantity</th>
                        <th>Red</th>
                        <th>Green</th>
                        <th>White</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($block['drop_rows'] as $r)
                        <tr>
                            <td>{{ $r['item'] }}</td>
                            <td>{{ number_format((int) $r['total_qty'], 0, '.', '') }}</td>
                            <td>{{ number_format((int) $r['red'], 0, '.', '') }}</td>
                            <td>{{ number_format((int) $r['green'], 0, '.', '') }}</td>
                            <td>{{ number_format((int) $r['white'], 0, '.', '') }}</td>
                        </tr>
                    @endforeach
                    @if(count($block['drop_rows']) > 0)
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong>{{ number_format((int) $block['drop_column_totals']['total_qty'], 0, '.', '') }}</strong></td>
                            <td><strong>{{ number_format((int) $block['drop_column_totals']['red'], 0, '.', '') }}</strong></td>
                            <td><strong>{{ number_format((int) $block['drop_column_totals']['green'], 0, '.', '') }}</strong></td>
                            <td><strong>{{ number_format((int) $block['drop_column_totals']['white'], 0, '.', '') }}</strong></td>
                        </tr>
                    @endif
                </tbody>
            </table>
            @endif
        </div>
    @endforeach

    @if(count($report['blocks']) === 0)
        <p style="color:#666;">No data for this selection.</p>
    @endif
</body>
</html>
