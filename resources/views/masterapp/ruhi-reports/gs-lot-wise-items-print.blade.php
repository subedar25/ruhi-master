<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GS Wise Lot Wise item report @if(trim((string) $gs->name) !== '') ({{ $gs->name }}) @endif</title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; margin: 18px; font-size: 16px; }
        .actions { margin-bottom: 12px; }
        .print-btn { padding: 6px 10px; border: 1px solid #444; background: #fff; cursor: pointer; }
        .title { font-size: 18px; font-weight: 700; margin-bottom: 14px; }
        .date-right { float: right; font-size: 16px; font-weight: 500; }
        .wrap { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-start; }
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
            font-size: 16px;
        }
        .lot-head .name { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; }
        .lot-head .collate { flex-shrink: 0; white-space: nowrap; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; font-size: 16px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; text-align: left; }
        th { background: #fafafa; font-weight: 700; }
        .qty-col { width: 6.5rem; }
        @media print {
            .actions { display: none; }
            body { margin: 8mm; }
            .lot-card { break-inside: avoid-page; }
            body, table, th, td { font-size: 16px !important; }
            thead { display: table-row-group; }
            tfoot { display: table-row-group; }
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

    <div class="title"><span class="date-right">Date: {{ now()->format('d-m-Y') }}</span>GS Wise Lot Wise item report @if(trim((string) $gs->name) !== '') ({{ $gs->name }}) @endif</div>

    @if($blocks->isEmpty())
        <p>No lots found for this GS.</p>
    @else
        @php
            $sortedBlocks = $blocks->sort(function ($a, $b) {
                $aName = trim((string) (($a['lot']->slot_name ?? '')));
                $bName = trim((string) (($b['lot']->slot_name ?? '')));

                preg_match('/(\d+)\s*$/', $aName, $aNumMatch) || preg_match_all('/\d+/', $aName, $aAllNumMatch);
                preg_match('/(\d+)\s*$/', $bName, $bNumMatch) || preg_match_all('/\d+/', $bName, $bAllNumMatch);
                $aHasNum = isset($aNumMatch[1]);
                $bHasNum = isset($bNumMatch[1]);
                $aNum = $aHasNum ? (int) $aNumMatch[1] : (isset($aAllNumMatch[0]) && !empty($aAllNumMatch[0]) ? (int) end($aAllNumMatch[0]) : 0);
                $bNum = $bHasNum ? (int) $bNumMatch[1] : (isset($bAllNumMatch[0]) && !empty($bAllNumMatch[0]) ? (int) end($bAllNumMatch[0]) : 0);
                $aHasAnyNum = $aHasNum || (isset($aAllNumMatch[0]) && !empty($aAllNumMatch[0]));
                $bHasAnyNum = $bHasNum || (isset($bAllNumMatch[0]) && !empty($bAllNumMatch[0]));

                $aPrefix = mb_strtolower(trim(preg_replace('/\d+\s*$/', '', $aName)));
                $bPrefix = mb_strtolower(trim(preg_replace('/\d+\s*$/', '', $bName)));
                if ($aHasAnyNum && $bHasAnyNum) {
                    $cmp = $aNum <=> $bNum;
                    if ($cmp !== 0) {
                        return $cmp;
                    }
                } elseif ($aHasAnyNum !== $bHasAnyNum) {
                    return $aHasAnyNum ? -1 : 1;
                }

                if ($aPrefix !== $bPrefix) {
                    return $aPrefix <=> $bPrefix;
                }

                $cmp = strcasecmp($aName, $bName);
                if ($cmp !== 0) {
                    return $cmp;
                }

                return ((int) ($a['lot']->id ?? 0)) <=> ((int) ($b['lot']->id ?? 0));
            })->values();
        @endphp
        <div class="wrap">
            @foreach($sortedBlocks as $block)
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
