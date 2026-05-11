<div>
    <div class="card">
        <div class="card-body">
            <div id="ruhi-collet-kstone-anchor-gs" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>
            <div class="d-flex flex-wrap align-items-end justify-content-between w-100">
                <form wire:submit.prevent="submit" class="form-inline flex-wrap align-items-end flex-grow-1">
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">GS</label>
                        <input type="hidden" wire:model.live="gsId" id="ruhi-collet-kstone-hidden-gs">
                        <div wire:ignore class="d-inline-block" style="min-width: 280px;">
                            <select
                                id="ruhi-collet-kstone-select-gs"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 280px;"
                                data-s2-hidden="#ruhi-collet-kstone-hidden-gs"
                                data-s2-anchor="#ruhi-collet-kstone-anchor-gs"
                                data-s2-placeholder="Select GS"
                            >
                                <option value="">Select GS</option>
                                @foreach($gsOptions as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mb-2">Submit</button>
                </form>
                <div class="mb-2 flex-shrink-0 ml-2">
                    @if($submitted && $gsId)
                        <a
                            href="{{ route('masterapp.ruhi-reports.gs-collet-kstone-color-report.print', ['gs' => $gsId]) }}"
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
        <h5 class="mb-2 mt-3 d-flex flex-wrap align-items-center">
            <i class="fas fa-gem text-info mr-2"></i>
            <span class="font-weight-bold mr-1">GS Wise Collet Kstone Color Report</span>
            @if($selectedGsName !== '')
                <span class="text-nowrap">({{ $selectedGsName }})</span>
            @endif
        </h5>

        <div class="table-responsive mb-4" style="max-width: 100%; overflow-x: auto;">
            <table class="table table-bordered table-sm mb-0 text-nowrap" style="min-width: 920px;">
                <thead class="thead-light">
                    <tr>
                        <th rowspan="2" class="align-middle text-break" style="min-width: 10rem;">Collet</th>
                        <th rowspan="2" class="align-middle text-break" style="min-width: 7rem;">Total Qty</th>
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
                    @if(count($report['rows']) === 0)
                        <tr>
                            <td colspan="12" class="text-center text-muted">No collet (product type 3) color rows for this GS.</td>
                        </tr>
                    @else
                        @include('masterapp.ruhi-reports.partials.gs-color-full-kstone-detail-rows', [
                            'rows' => $report['rows'],
                            'useBorderLeft' => true,
                        ])
                    @endif
                </tbody>
                @if(count($report['rows']) > 0)
                    @php
                        $tot = array_merge([
                            'total_color_qty' => 0,
                            'red_qty' => 0,
                            'red_kstone_wt' => 0.0,
                            'red_die_wt' => 0.0,
                            'green_qty' => 0,
                            'green_kstone_wt' => 0.0,
                            'green_die_wt' => 0.0,
                            'white_qty' => 0,
                            'white_kstone_wt' => 0.0,
                            'white_die_wt' => 0.0,
                        ], $report['totals'] ?? []);
                    @endphp
                    <tfoot>
                        <tr class="table-light font-weight-bold">
                            <td class="text-break">Grand Total</td>
                            <td>{{ number_format((int) $tot['total_color_qty'], 0, '.', '') }}</td>
                            <td></td>
                            <td class="border-left">{{ number_format((int) $tot['red_qty'], 0, '.', '') }}</td>
                            <td>{{ number_format((float) $tot['red_kstone_wt'], 2, '.', '') }}</td>
                            <td>{{ number_format((float) $tot['red_die_wt'], 2, '.', '') }}</td>
                            <td class="border-left">{{ number_format((int) $tot['green_qty'], 0, '.', '') }}</td>
                            <td>{{ number_format((float) $tot['green_kstone_wt'], 2, '.', '') }}</td>
                            <td>{{ number_format((float) $tot['green_die_wt'], 2, '.', '') }}</td>
                            <td class="border-left">{{ number_format((int) $tot['white_qty'], 0, '.', '') }}</td>
                            <td>{{ number_format((float) $tot['white_kstone_wt'], 2, '.', '') }}</td>
                            <td>{{ number_format((float) $tot['white_die_wt'], 2, '.', '') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif
</div>
