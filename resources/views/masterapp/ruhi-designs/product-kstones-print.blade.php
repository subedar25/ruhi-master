<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List KStone Print</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; }
        .actions { margin-bottom: 10px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title { font-size: 22px; font-weight: 700; margin-bottom: 4px; }
        .subtitle { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; font-size: 12px; }
        th { background: #f5f5f5; text-align: left; }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button class="print-btn" onclick="window.print()">Print / Save PDF</button>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
        <div>
            <div class="title">List KStone - ({{ $product->product_name }})</div>
            <div class="subtitle">List K-Stone | Design: {{ $design->design_name }}</div>
        </div>
        <div style="font-size:12px; white-space:nowrap; margin-top:6px;">
            Date: {{ now()->format('d-m-Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:70px;">S No.</th>
                <th>Item Name</th>
                <th>KStone Name</th>
                <th style="width:100px;">Qty</th>
                <th style="width:80px;">Red</th>
                <th colspan="2" style="width:160px;">Red + Green</th>
                <th style="width:80px;">Green</th>
                <th style="width:80px;">White</th>
            </tr>
            <tr>
                <th colspan="5"></th>
                <th style="width:80px;">Red</th>
                <th style="width:80px;">Green</th>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->product->product_name ?? ('#'.$row->product_id) }}</td>
                    <td>{{ $row->kstone->name ?? ('#'.$row->kstone_id) }}</td>
                    <td>{{ $row->kstone_quantity }}</td>
                    <td>{{ $row->red }}</td>
                    <td>{{ $row->rg_red }}</td>
                    <td>{{ $row->rg_green }}</td>
                    <td>{{ $row->green }}</td>
                    <td>{{ $row->white }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;">No K-Stone records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
