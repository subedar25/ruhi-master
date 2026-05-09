<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Drop Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 14px; font-size: 14px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .doc-title { font-size: 16px; font-weight: 700; margin-bottom: 10px; }
        .date-right { float: right; font-weight: 500; }
        table.data { width: 100%; max-width: 52rem; border-collapse: collapse; font-size: 14px; margin-bottom: 10px; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 5px 8px; text-align: left; }
        table.data thead th { background: #fafafa; font-weight: 700; }
        table.data tfoot td { font-weight: 700; background: #f5f5f5; }
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

    <div class="doc-title">
        <span class="date-right">Date: {{ now()->format('d-m-Y') }}</span>
        GS Wise Drop Report
        @if($report['gs_name'] !== '')
            ({{ $report['gs_name'] }})
        @endif
    </div>

    <table class="data">
        <thead>
            <tr>
                <th>Drop</th>
                <th>Red</th>
                <th>Green</th>
                <th>White</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['rows'] as $r)
                <tr>
                    <td>{{ $r['drop'] }}</td>
                    <td>{{ number_format((int) $r['red'], 0, '.', '') }}</td>
                    <td>{{ number_format((int) $r['green'], 0, '.', '') }}</td>
                    <td>{{ number_format((int) $r['white'], 0, '.', '') }}</td>
                </tr>
            @endforeach
        </tbody>
        @if(count($report['rows']) > 0)
            <tfoot>
                <tr>
                    <td>Grand Total</td>
                    <td>{{ number_format((int) ($report['grand_red'] ?? 0), 0, '.', '') }}</td>
                    <td>{{ number_format((int) ($report['grand_green'] ?? 0), 0, '.', '') }}</td>
                    <td>{{ number_format((int) ($report['grand_white'] ?? 0), 0, '.', '') }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    @if(count($report['rows']) === 0)
        <p style="color:#666;">No data for this GS.</p>
    @endif
</body>
</html>
