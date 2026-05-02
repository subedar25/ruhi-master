<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Dubby Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; font-size: 12px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .subtitle { margin-bottom: 14px; color: #555; }
        table { width: 100%; max-width: 72rem; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #fafafa; font-weight: 700; text-align: left; }
        .col-dubby { width: 18rem; max-width: 18rem; word-break: break-word; }
        .col-qty { text-align: left; width: 12rem; }
        .col-wt { text-align: left; width: 12rem; }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="title">GS Wise Dubby Report</div>
    <div class="subtitle">
        @if($report['gs_name'] !== '')
            GS: {{ $report['gs_name'] }}
        @endif
        &nbsp;|&nbsp; Date: {{ now()->format('d-m-Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-dubby">Dubby</th>
                <th class="col-qty">Total Quantity</th>
                <th class="col-wt">Weight</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $r)
                <tr>
                    <td class="col-dubby">{{ $r['dubby'] }}</td>
                    <td class="col-qty">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                    <td class="col-wt">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #666;">No dubby lines for this GS.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
