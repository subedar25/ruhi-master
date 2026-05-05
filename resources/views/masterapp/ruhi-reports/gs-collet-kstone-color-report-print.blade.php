<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Collet Kstone Color Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 14px; font-size: 10px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .doc-title { font-size: 15px; font-weight: 700; margin-bottom: 10px; }
        table.data { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 14px; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 4px 6px; }
        table.data thead th { background: #f0f0f0; }
        table.data tfoot td { font-weight: 700; background: #f5f5f5; }
        @media print {
            .actions { display: none; }
            body { margin: 6mm; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="doc-title">
        GS Wise Collet Kstone Color Report
        @if($report['gs_name'] !== '')
            ({{ $report['gs_name'] }})
        @endif
        &nbsp;|&nbsp; {{ now()->format('d-m-Y') }}
    </div>

    <table class="data">
        <thead>
            <tr>
                <th rowspan="2">Collet</th>
                <th rowspan="2">Total Qty</th>
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
            @forelse($report['rows'] as $r)
                <tr>
                    <td>{{ $r['product_name'] }}</td>
                    <td>{{ number_format((int) $r['total_color_qty'], 0, '.', '') }}</td>
                    <td>{{ $r['kstone'] }}</td>
                    <td>{{ number_format((int) $r['red_qty'], 0, '.', '') }}</td>
                    <td>{{ number_format((float) $r['red_kstone_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((float) $r['red_die_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((int) $r['green_qty'], 0, '.', '') }}</td>
                    <td>{{ number_format((float) $r['green_kstone_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((float) $r['green_die_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((int) $r['white_qty'], 0, '.', '') }}</td>
                    <td>{{ number_format((float) $r['white_kstone_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((float) $r['white_die_wt'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr><td colspan="12" style="text-align:center;color:#666;">No data.</td></tr>
            @endforelse
        </tbody>
        @if(count($report['rows']) > 0)
            @php
                $tot = array_merge([
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
                ], $report['totals'] ?? []);
            @endphp
            <tfoot>
                <tr>
                    <td>Grand Total</td>
                    <td>{{ number_format((int) $tot['total_color_qty'], 0, '.', '') }}</td>
                    <td></td>
                    <td>{{ number_format((int) $tot['red_qty'], 0, '.', '') }}</td>
                    <td>{{ number_format((float) $tot['red_kstone_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((float) $tot['red_die_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((int) $tot['green_qty'], 0, '.', '') }}</td>
                    <td>{{ number_format((float) $tot['green_kstone_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((float) $tot['green_die_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((int) $tot['white_qty'], 0, '.', '') }}</td>
                    <td>{{ number_format((float) $tot['white_kstone_wt'], 2, '.', '') }}</td>
                    <td>{{ number_format((float) $tot['white_die_wt'], 2, '.', '') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
