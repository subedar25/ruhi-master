<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Lot Wise item report</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; font-size: 12px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
        .subtitle { margin-bottom: 14px; color: #555; }
        .wrap { display: flex; flex-wrap: wrap; gap: 12px; align-items: stretch; }
        .lot-card {
            flex: 1 1 260px;
            max-width: calc(33.333% - 10px);
            min-width: 220px;
            border: 1px solid #ccc;
            border-radius: 4px;
            overflow: hidden;
        }
        .lot-head {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 8px 10px;
            background: #f5f5f5;
            font-weight: 700;
            font-size: 12px;
        }
        .lot-head .name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
        .lot-head .collate { flex-shrink: 0; white-space: nowrap; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #fafafa; font-weight: 700; }
        .qty-col { width: 6.5rem; }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
            .lot-card { break-inside: avoid-page; }
        }
        @media (max-width: 900px) {
            .lot-card { max-width: calc(50% - 8px); }
        }
        @media (max-width: 560px) {
            .lot-card { max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button type="button" class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div class="title">GS Wise Lot Wise item report</div>
    <div class="subtitle">GS: {{ $gs->name }} &nbsp;|&nbsp; Date: {{ now()->format('d-m-Y') }}</div>

    @if($blocks->isEmpty())
        <p>No lots found for this GS.</p>
    @else
        <div class="wrap">
            @foreach($blocks as $block)
                @php $lot = $block['lot']; @endphp
                <div class="lot-card">
                    <div class="lot-head">
                        <span class="name" title="{{ $lot->slot_name }}">{{ $lot->slot_name }}</span>
                        <span class="collate">Total Collate: <strong>{{ (int) round((float) $block['total_collate']) }}</strong></span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Design Name</th>
                                <th class="qty-col">Design Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($block['designs'] as $d)
                                <tr>
                                    <td>{{ $d['design_name'] }}</td>
                                    <td class="qty-col">{{ $d['design_qty'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" style="text-align:center;color:#888;">No items for this lot.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>
