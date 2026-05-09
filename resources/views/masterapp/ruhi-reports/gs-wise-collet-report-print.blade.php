<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Collet Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; font-size: 14px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .date-right { float: right; font-size: 16px; font-weight: 500; }
        .subtitle { margin-bottom: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; table-layout: auto; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; }
        th { background: #fafafa; font-weight: 700; text-align: left; white-space: nowrap; }
        .col-name { max-width: 12rem; word-break: break-word; white-space: normal; }
        .num { text-align: left; white-space: nowrap; }
        tfoot td { font-weight: 700; background: #f5f5f5; }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
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

    <div class="title"><span class="date-right">Date: {{ now()->format('d-m-Y') }}</span>GS Wise Collet Report</div>
    <div class="subtitle">
        @if($report['gs_name'] !== '')
            GS: {{ $report['gs_name'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-name">Collet</th>
                <th class="col-name">K Stone</th>
                <th class="num">Total Quantity</th>
                <th class="num">Weight</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $r)
                <tr>
                    <td class="col-name">{{ $r['collet'] }}</td>
                    <td class="col-name">{{ $r['kstone_name'] !== '' ? $r['kstone_name'] : '—' }}</td>
                    <td class="num">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                    <td class="num">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #666;">No collet lines for this GS.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Grand Total</td>
                <td>—</td>
                <td class="num">{{ number_format((int) $report['grand_total_quantity'], 0, '.', '') }}</td>
                <td class="num">{{ number_format((float) $report['grand_total_weight'], 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
