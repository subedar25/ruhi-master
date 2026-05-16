<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List Design Items for Design ({{ $design->design_name }})</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 20px; }
        .print-actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title { font-size: 24px; font-weight: 700; margin-bottom: 12px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; border: 1px solid #ccc; padding: 10px; margin-bottom: 16px; }
        .summary .label { font-size: 16px; color: #666; }
        .summary .value { font-size: 16px; font-weight: 600; margin-top: 2px; }
        .grid-two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
        .block { border: 1px solid #ccc; page-break-inside: avoid; margin-bottom: 12px; }
        .block-title { background: #f5f5f5; padding: 8px 10px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; font-size: 16px; text-align: left; }
        th { background: #fafafa; }
        .full-width { width: 100%; }
        @media print {
            .print-actions { display: none; }
            body { margin: 8mm; }
            .block { break-inside: avoid-page; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
        <div>
            <div class="title">List Design Items for Design ({{ $design->design_name }})</div>
        </div>
        <div style="font-size:16px; white-space:nowrap; margin-top:6px;">
            Date: {{ now()->format('d-m-Y') }}
        </div>
    </div>

    <div class="summary">
        <div>
            <div class="label">Total Collate</div>
            <div class="value">{{ $design->dubby_qty ?? 0 }}</div>
        </div>
        <div>
            <div class="label">Zumka</div>
            <div class="value">{{ $design->zumka_qty ?? 0 }}</div>
        </div>
        <div>
            <div class="label">UF</div>
            <div class="value">{{ $design->uf ?? 0 }}</div>
        </div>
        <div>
            <div class="label">Note</div>
            <div class="value">{{ $design->note ?? '' }}</div>
        </div>
    </div>

    @if($nonColorBlocks->count() > 0)
        <div class="grid-two">
            @foreach($nonColorBlocks as $block)
                <div class="block">
                    <div class="block-title">{{ $block['type']->item_type }} (Total: {{ $block['total'] }})</div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width:60px;">S No.</th>
                                <th>Item Name</th>
                                <th style="width:120px;">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($block['rows'] as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->product->product_name ?? ('#'.$row->product_id) }}</td>
                                    <td>{{ $row->quantity }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    @foreach($colorBlocks as $block)
        <div class="block full-width">
            <div class="block-title">{{ $block['type']->item_type }} (Total: {{ $block['total'] }})</div>
            <table>
                <thead>
                    <tr>
                        <th rowspan="2" style="width:60px;">S No.</th>
                        <th rowspan="2">Item Name</th>
                        <th rowspan="2" style="width:90px;">Qty</th>
                        <th rowspan="2" style="width:80px;">Red</th>
                        <th colspan="2" style="width:150px;">Red + Green</th>
                        <th rowspan="2" style="width:80px;">Green</th>
                        <th rowspan="2" style="width:80px;">White</th>
                    </tr>
                    <tr>
                        <th style="width:75px;">Red</th>
                        <th style="width:75px;">Green</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($block['rows'] as $index => $row)
                        @php
                            $onlyRedQty = (int) $row->collateByColors->sum('only_red_qty');
                            $redQty = (int) $row->collateByColors->sum('red_qty');
                            $greenQty = (int) $row->collateByColors->sum('green_qty');
                            $onlyGreenQty = (int) $row->collateByColors->sum('only_green_qty');
                            $whiteQty = (int) $row->collateByColors->sum('white_qty');
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $row->product->product_name ?? ('#'.$row->product_id) }}</td>
                            <td>{{ $row->quantity }}</td>
                            <td>{{ $onlyRedQty }}</td>
                            <td>{{ $redQty }}</td>
                            <td>{{ $greenQty }}</td>
                            <td>{{ $onlyGreenQty }}</td>
                            <td>{{ $whiteQty }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
</body>
</html>
