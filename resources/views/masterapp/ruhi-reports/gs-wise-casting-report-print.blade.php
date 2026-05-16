<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Casting Report @if(trim((string) ($report['gs_name'] ?? '')) !== '') ({{ trim((string) ($report['gs_name'] ?? '')) }}) @endif</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; font-size: 16px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title-row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 14px; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 0; }
        .title-date { font-size: 16px; color: #555; text-align: right; white-space: nowrap; }
        table { width: 100%; max-width: 72rem; border-collapse: collapse; font-size: 16px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #fafafa; font-weight: 700; text-align: left; }
        .col-qty { text-align: left; width: 12rem; }
        .col-wt { text-align: left; width: 12rem; }
        .col-casting { width: 18rem; max-width: 18rem; word-break: break-word; }
        tfoot td { font-weight: 700; background: #f5f5f5; }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
            /* Prevent browser from repeating table header/footer on each printed page */
            thead { display: table-row-group; }
            tfoot { display: table-row-group; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="title-row">
        <div class="title">GS Wise Casting Report @if(trim((string) ($report['gs_name'] ?? '')) !== '') ({{ trim((string) ($report['gs_name'] ?? '')) }}) @endif</div>
        <div class="title-date">Date: {{ now()->format('d-m-Y') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-casting">Casting</th>
                <th class="col-qty">Total Quantity</th>
                <th class="col-wt">Weight</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $r)
                <tr>
                    <td class="col-casting">{{ $r['casting'] }}</td>
                    <td class="col-qty">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                    <td class="col-wt">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #666;">No casting lines for this GS and lot.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Grand Total</td>
                <td class="col-qty">{{ number_format((int) $report['grand_total_quantity'], 0, '.', '') }}</td>
                <td class="col-wt">{{ number_format((float) $report['grand_total_weight'], 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
