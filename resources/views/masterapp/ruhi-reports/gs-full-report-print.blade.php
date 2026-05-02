<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if(!empty($section) && $section === 'kundanfull')
            GS Wise Kundanfull Report
        @elseif(!empty($section) && $section === 'pulkifull')
            GS Wise Pulkifull Report
        @elseif(!empty($section) && $section === 'addfull')
            GS Wise Addfull Report
        @else
            GS Full Report
        @endif
    </title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; font-size: 11px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .page-title { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .subtitle { margin-bottom: 16px; color: #555; }
        .section-title { font-size: 14px; font-weight: 700; margin: 20px 0 8px; }
        table { width: 100%; max-width: 72rem; border-collapse: collapse; font-size: 10px; margin-bottom: 8px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; }
        th { background: #fafafa; font-weight: 700; text-align: left; }
        .col-name { max-width: 14rem; word-break: break-word; white-space: normal; }
        .num { text-align: left; white-space: nowrap; }
        tfoot td { font-weight: 700; background: #f5f5f5; }
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

    @php $g = $report['gs_name']; @endphp

    <div class="page-title">
        @if(!empty($section) && $section === 'kundanfull')
            GS Wise Kundanfull Report @if($g !== '') ({{ $g }}) @endif
        @elseif(!empty($section) && $section === 'pulkifull')
            GS Wise Pulkifull Report @if($g !== '') ({{ $g }}) @endif
        @elseif(!empty($section) && $section === 'addfull')
            GS Wise Addfull Report @if($g !== '') ({{ $g }}) @endif
        @else
            GS Full Report
        @endif
    </div>
    <div class="subtitle">
        @if($report['gs_name'] !== '')
            GS: {{ $report['gs_name'] }}
        @endif
        &nbsp;|&nbsp; Date: {{ now()->format('d-m-Y') }}
    </div>

    @if(empty($section) || $section === 'kundanfull')
    @if(empty($section))
    <div class="section-title">
        GS Wise Kundanfull Report @if($g !== '') ({{ $g }}) @endif
    </div>
    @endif
    <table>
        <thead>
            <tr>
                <th class="col-name">Kundanfull</th>
                <th class="num">Total Quantity</th>
                <th class="num">Weight</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['kundanfull']['rows'] as $r)
                <tr>
                    <td class="col-name">{{ $r['kundanfull'] }}</td>
                    <td class="num">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                    <td class="num">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center; color: #666;">No Kundan Full lines.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Grand Total</td>
                <td class="num">{{ number_format((int) $report['kundanfull']['grand_total_quantity'], 0, '.', '') }}</td>
                <td class="num">{{ number_format((float) $report['kundanfull']['grand_total_weight'], 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if(empty($section) || $section === 'pulkifull')
    @if(empty($section))
    <div class="section-title">
        GS Wise Pulkifull Report @if($g !== '') ({{ $g }}) @endif
    </div>
    @endif
    <table>
        <thead>
            <tr>
                <th class="col-name">Pulkifull</th>
                <th class="num">Total Quantity</th>
                <th class="num">Weight</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['pulkifull']['rows'] as $r)
                <tr>
                    <td class="col-name">{{ $r['pulkifull'] }}</td>
                    <td class="num">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                    <td class="num">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align: center; color: #666;">No Polki Full lines.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Grand Total</td>
                <td class="num">{{ number_format((int) $report['pulkifull']['grand_total_quantity'], 0, '.', '') }}</td>
                <td class="num">{{ number_format((float) $report['pulkifull']['grand_total_weight'], 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    @if(empty($section) || $section === 'addfull')
    @if(empty($section))
    <div class="section-title">
        GS Wise Addfull Report @if($g !== '') ({{ $g }}) @endif
    </div>
    @endif
    <table>
        <thead>
            <tr>
                <th class="col-name">Addfull</th>
                <th class="col-name">K Stone Name</th>
                <th class="num">Total Quantity</th>
                <th class="num">Weight</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['addfull']['rows'] as $r)
                <tr>
                    <td class="col-name">{{ $r['addfull'] }}</td>
                    <td class="col-name">{{ $r['kstone_name'] !== '' ? $r['kstone_name'] : '—' }}</td>
                    <td class="num">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                    <td class="num">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align: center; color: #666;">No AD Full lines.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Grand Total</td>
                <td class="num">{{ number_format((int) $report['addfull']['grand_total_quantity'], 0, '.', '') }}</td>
                <td class="num">{{ number_format((float) $report['addfull']['grand_total_weight'], 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif
</body>
</html>
