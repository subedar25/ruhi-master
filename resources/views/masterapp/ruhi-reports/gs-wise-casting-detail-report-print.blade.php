<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Casting Detail Report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; font-size: 14px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 6px; }
        .date-right { float: right; font-size: 16px; font-weight: 500; }
        .subtitle { margin-bottom: 14px; color: #555; }
        table { width: 100%; max-width: 72rem; border-collapse: collapse; font-size: 14px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; vertical-align: top; }
        th { background: #fafafa; font-weight: 700; text-align: left; }
        .col-design { width: 18rem; max-width: 18rem; word-break: break-word; }
        .col-casting { width: 18rem; max-width: 18rem; word-break: break-word; }
        .col-qty { text-align: left; width: 12rem; }
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

    <div class="title"><span class="date-right">Date: {{ now()->format('d-m-Y') }}</span>GS Wise Casting Detail Report</div>
    <div class="subtitle">
        @if($report['gs_name'] !== '' || $report['lot_name'] !== '')
            ({{ $report['gs_name'] }}@if($report['gs_name'] !== '' && $report['lot_name'] !== ''){{ ', ' }}@endif{{ $report['lot_name'] }})
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-design">Design Name</th>
                <th class="col-casting">Casting</th>
                <th class="col-qty">Total Quantity</th>
            </tr>
        </thead>
        <tbody>
            @if(($report['design_groups'] ?? []) === [])
                <tr>
                    <td colspan="3" style="text-align: center; color: #666;">No detail lines for this GS and lot.</td>
                </tr>
            @else
                @foreach($report['design_groups'] as $group)
                    @foreach($group['lines'] as $lineIdx => $line)
                        <tr>
                            @if($lineIdx === 0)
                                <td class="col-design" rowspan="{{ count($group['lines']) }}">{{ $group['design_name'] }}- {{ number_format((int) ($group['design_qty'] ?? 0), 0, '.', '') }}</td>
                            @endif
                            <td class="col-casting">{{ $line['casting'] }}</td>
                            <td class="col-qty">{{ number_format((int) $line['total_quantity'], 0, '.', '') }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td class="col-qty">{{ number_format((int) $report['grand_total_quantity'], 0, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
