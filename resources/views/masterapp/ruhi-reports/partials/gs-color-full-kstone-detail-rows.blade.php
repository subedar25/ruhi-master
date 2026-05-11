{{--
  Detail table body rows: one sub-row per item–kstone line.
  Product + Total Qty use rowspan; Kstone + Red/Green/White Qty + weights are per kstone line.
  Footer red/green/white qty totals sum every line (see GsColorFullReportService).
  Falls back to a single row when kstone_rows is empty.
  @var iterable<int, array<string, mixed>> $rows
  @var bool $useBorderLeft  Bootstrap border-left on first column of each color group (on-screen tables)
  @var string|null $productTdExtraClass  e.g. collet-col (print, product column only)
  @var string|null $totalQtyTdExtraClass  optional class on Total Qty column
  @var string|null $kstoneTdExtraClass  e.g. kstone-col (print)
--}}
@foreach($rows as $r)
    @php
        $krows = $r['kstone_rows'] ?? [];
        $bl = ($useBorderLeft ?? false) ? 'border-left' : '';
        $pExtra = trim($productTdExtraClass ?? '');
        $qExtra = trim($totalQtyTdExtraClass ?? '');
        $ksExtra = trim($kstoneTdExtraClass ?? '');
        $rsProduct = trim('align-top text-break ' . $pExtra);
        $rsQty = trim('align-top text-break ' . $qExtra);
        $singleProduct = trim('text-break ' . $pExtra);
        $singleQty = trim('text-break ' . $qExtra);
        $ksMulti = trim('text-break align-top ' . $ksExtra);
        $ksSingle = trim('text-break ' . $ksExtra);
    @endphp
    @if(count($krows) > 0)
        @php $nk = count($krows); @endphp
        @foreach($krows as $idx => $kr)
            <tr>
                @if($idx === 0)
                    <td rowspan="{{ $nk }}" class="{{ $rsProduct }}">{{ $r['product_name'] }}</td>
                    <td rowspan="{{ $nk }}" class="{{ $rsQty }}">{{ number_format((int) $r['total_color_qty'], 0, '.', '') }}</td>
                @endif
                <td class="{{ $ksMulti }}">{{ $kr['label'] }}</td>
                <td class="{{ $bl }}">{{ number_format((int) $kr['red_qty'], 0, '.', '') }}</td>
                <td>{{ number_format((float) $kr['red_kstone_wt'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $kr['red_die_wt'], 2, '.', '') }}</td>
                <td class="{{ $bl }}">{{ number_format((int) $kr['green_qty'], 0, '.', '') }}</td>
                <td>{{ number_format((float) $kr['green_kstone_wt'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $kr['green_die_wt'], 2, '.', '') }}</td>
                <td class="{{ $bl }}">{{ number_format((int) $kr['white_qty'], 0, '.', '') }}</td>
                <td>{{ number_format((float) $kr['white_kstone_wt'], 2, '.', '') }}</td>
                <td>{{ number_format((float) $kr['white_die_wt'], 2, '.', '') }}</td>
            </tr>
        @endforeach
    @else
        <tr>
            <td class="{{ $singleProduct }}">{{ $r['product_name'] }}</td>
            <td class="{{ $singleQty }}">{{ number_format((int) $r['total_color_qty'], 0, '.', '') }}</td>
            <td class="{{ $ksSingle }}">{{ $r['kstone'] ?? '' }}</td>
            <td class="{{ $bl }}">{{ number_format((int) $r['red_qty'], 0, '.', '') }}</td>
            <td>{{ number_format((float) $r['red_kstone_wt'], 2, '.', '') }}</td>
            <td>{{ number_format((float) $r['red_die_wt'], 2, '.', '') }}</td>
            <td class="{{ $bl }}">{{ number_format((int) $r['green_qty'], 0, '.', '') }}</td>
            <td>{{ number_format((float) $r['green_kstone_wt'], 2, '.', '') }}</td>
            <td>{{ number_format((float) $r['green_die_wt'], 2, '.', '') }}</td>
            <td class="{{ $bl }}">{{ number_format((int) $r['white_qty'], 0, '.', '') }}</td>
            <td>{{ number_format((float) $r['white_kstone_wt'], 2, '.', '') }}</td>
            <td>{{ number_format((float) $r['white_die_wt'], 2, '.', '') }}</td>
        </tr>
    @endif
@endforeach
