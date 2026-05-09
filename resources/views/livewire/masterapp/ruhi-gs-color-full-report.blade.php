<div>
    <div class="card">
        <div class="card-body">
            <div id="ruhi-color-full-anchor-gs" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>
            <div class="d-flex flex-wrap align-items-end justify-content-between w-100">
                <form wire:submit.prevent="submit" class="form-inline flex-wrap align-items-end flex-grow-1">
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">GS</label>
                        <input type="hidden" wire:model.live="gsId" id="ruhi-color-full-hidden-gs">
                        <div wire:ignore class="d-inline-block" style="min-width: 280px;">
                            <select
                                id="ruhi-color-full-select-gs"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 280px;"
                                data-s2-hidden="#ruhi-color-full-hidden-gs"
                                data-s2-anchor="#ruhi-color-full-anchor-gs"
                                data-s2-placeholder="Select GS"
                            >
                                <option value="">Select GS</option>
                                @foreach($gsOptions as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">Filter</label>
                        <select wire:model="sfilter" class="form-control form-control-sm" style="min-width: 12rem;" title="Legacy sfilter: (S) in product name">
                            <option value="0">All</option>
                            <option value="1">Exclude “(S)” in name</option>
                            <option value="2">Only “(S)” in name</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mb-2">Submit</button>
                </form>
                <div class="mb-2 flex-shrink-0 ml-2">
                    @if($submitted && $gsId)
                        <a
                            href="{{ route('masterapp.ruhi-reports.gs-color-full-report.print', ['gs' => $gsId] + (($sfilter ?? 0) > 0 ? ['sfilter' => $sfilter] : [])) }}"
                            class="btn btn-outline-primary btn-sm ruhi-print-preview-link"
                        >
                            <i class="fa fa-print mr-1"></i> Print
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Select GS and submit first">
                            <i class="fa fa-print mr-1"></i> Print
                        </button>
                    @endif
                </div>
            </div>
            @error('gsId')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if($submitted && $report)
        @php
            $blocks = [
                [
                    'title' => 'GS Wise Kundanfull Color Report',
                    'layout' => 'detail',
                    'totalLabel' => 'Kundan Total Qty',
                    'printBlock' => 'kundanfull',
                    'rows' => $report['kundanfull'],
                    'totals' => $report['totals_kundanfull'] ?? [],
                ],
                [
                    'title' => 'GS Wise Pulkifull Color Report',
                    'layout' => 'simple',
                    'firstCol' => 'Pulki',
                    'printBlock' => 'pulkifull',
                    'rows' => $report['pulkifull'],
                    'totals' => $report['totals_pulkifull'] ?? [],
                ],
                [
                    'title' => 'GS Wise AddFull Color Report',
                    'layout' => 'simple',
                    'firstCol' => 'AddFull',
                    'printBlock' => 'addfull',
                    'rows' => $report['addfull'],
                    'totals' => $report['totals_addfull'] ?? [],
                ],
            ];
        @endphp

        @foreach($blocks as $block)
            <div class="d-flex flex-wrap align-items-center justify-content-between w-100 mt-4 mb-2">
                <h5 class="mb-0 d-flex flex-wrap align-items-center">
                    <i class="fas fa-palette text-secondary mr-2"></i>
                    <span class="font-weight-bold mr-1">{{ $block['title'] }}</span>
                    @if($selectedGsName !== '')
                        <span class="text-nowrap">({{ $selectedGsName }})</span>
                    @endif
                </h5>
                @if($submitted && $gsId)
                    @php
                        $blockPrintParams = ['block' => $block['printBlock'], 'gs' => $gsId];
                        if (($sfilter ?? 0) > 0) {
                            $blockPrintParams['sfilter'] = $sfilter;
                        }
                    @endphp
                    <a
                        href="{{ route('masterapp.ruhi-reports.gs-color-full-report.print.block', $blockPrintParams) }}"
                        class="btn btn-outline-secondary btn-sm mb-0 ml-2 flex-shrink-0 ruhi-print-preview-link"
                        title="Print this section only"
                    >
                        <i class="fa fa-print mr-1"></i> Print section
                    </a>
                @endif
            </div>

            <div class="table-responsive mb-4" style="max-width: 100%; overflow-x: auto;">
                @if(($block['layout'] ?? 'detail') === 'simple')
                    <table class="table table-bordered table-sm mb-0 text-nowrap" style="min-width: 42rem;">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-break" style="min-width: 10rem;">{{ $block['firstCol'] }}</th>
                                <th>Kstone</th>
                                <th>Total Qty</th>
                                <th>Red</th>
                                <th>Green</th>
                                <th>White</th>
                                <th>Wt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($block['rows'] as $r)
                                <tr>
                                    <td class="text-break">{{ $r['product_name'] }}</td>
                                    <td class="text-break">{{ $r['kstone'] }}</td>
                                    <td>{{ number_format((int) $r['total_color_qty'], 0, '.', '') }}</td>
                                    <td>{{ number_format((int) $r['red_qty'], 0, '.', '') }}</td>
                                    <td>{{ number_format((int) $r['green_qty'], 0, '.', '') }}</td>
                                    <td>{{ number_format((int) $r['white_qty'], 0, '.', '') }}</td>
                                    <td>{{ number_format((float) ($r['total_wt'] ?? 0), 2, '.', '') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No rows for this section.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($block['rows']) > 0)
                            @php $t = $block['totals'] ?? []; @endphp
                            <tfoot>
                                <tr class="table-light">
                                    <td class="font-weight-bold" colspan="2">Grand Total</td>
                                    <td class="font-weight-bold">{{ number_format((int) ($t['total_color_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((int) ($t['red_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((int) ($t['green_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((int) ($t['white_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((float) ($t['total_wt'] ?? 0), 2, '.', '') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                @else
                    <table class="table table-bordered table-sm mb-0 text-nowrap" style="min-width: 920px;">
                        <thead class="thead-light">
                            <tr>
                                <th rowspan="2" class="align-middle text-break" style="min-width: 10rem;">Product</th>
                                <th rowspan="2" class="align-middle text-break" style="min-width: 7rem;">{{ $block['totalLabel'] }}</th>
                                <th rowspan="2" class="align-middle">Kstone</th>
                                <th colspan="3" class="text-center border-left">Red</th>
                                <th colspan="3" class="text-center border-left">Green</th>
                                <th colspan="3" class="text-center border-left">White</th>
                            </tr>
                            <tr>
                                <th class="border-left">Qty</th>
                                <th>Kstone Wt</th>
                                <th>Die Wt</th>
                                <th class="border-left">Qty</th>
                                <th>Kstone Wt</th>
                                <th>Die Wt</th>
                                <th class="border-left">Qty</th>
                                <th>Kstone Wt</th>
                                <th>Die Wt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($block['rows'] as $r)
                                <tr>
                                    <td class="text-break">{{ $r['product_name'] }}</td>
                                    <td class="text-break">{{ number_format((int) $r['total_color_qty'], 0, '.', '') }}</td>
                                    <td class="text-break">{{ $r['kstone'] }}</td>
                                    <td class="border-left">{{ number_format((int) $r['red_qty'], 0, '.', '') }}</td>
                                    <td>{{ number_format((float) $r['red_kstone_wt'], 2, '.', '') }}</td>
                                    <td>{{ number_format((float) $r['red_die_wt'], 2, '.', '') }}</td>
                                    <td class="border-left">{{ number_format((int) $r['green_qty'], 0, '.', '') }}</td>
                                    <td>{{ number_format((float) $r['green_kstone_wt'], 2, '.', '') }}</td>
                                    <td>{{ number_format((float) $r['green_die_wt'], 2, '.', '') }}</td>
                                    <td class="border-left">{{ number_format((int) $r['white_qty'], 0, '.', '') }}</td>
                                    <td>{{ number_format((float) $r['white_kstone_wt'], 2, '.', '') }}</td>
                                    <td>{{ number_format((float) $r['white_die_wt'], 2, '.', '') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted">No rows for this section.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(count($block['rows']) > 0)
                            @php $t = $block['totals'] ?? []; @endphp
                            <tfoot>
                                <tr class="table-light">
                                    <td class="font-weight-bold">Grand Total</td>
                                    <td class="font-weight-bold">{{ number_format((int) ($t['total_color_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td></td>
                                    <td class="border-left font-weight-bold">{{ number_format((int) ($t['red_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((float) ($t['red_kstone_wt'] ?? 0), 2, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((float) ($t['red_die_wt'] ?? 0), 2, '.', '') }}</td>
                                    <td class="border-left font-weight-bold">{{ number_format((int) ($t['green_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((float) ($t['green_kstone_wt'] ?? 0), 2, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((float) ($t['green_die_wt'] ?? 0), 2, '.', '') }}</td>
                                    <td class="border-left font-weight-bold">{{ number_format((int) ($t['white_qty'] ?? 0), 0, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((float) ($t['white_kstone_wt'] ?? 0), 2, '.', '') }}</td>
                                    <td class="font-weight-bold">{{ number_format((float) ($t['white_die_wt'] ?? 0), 2, '.', '') }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                @endif
            </div>
        @endforeach
    @endif
</div>
