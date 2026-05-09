<div>
    <div class="card">
        <div class="card-body">
            <div id="ruhi-gs-full-anchor-gs" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>
            <div class="d-flex flex-wrap align-items-end justify-content-between w-100">
                <form wire:submit.prevent="submit" class="form-inline flex-wrap align-items-end flex-grow-1">
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">GS</label>
                        <input type="hidden" wire:model.live="gsId" id="ruhi-gs-full-hidden-gs">
                        <div wire:ignore class="d-inline-block" style="min-width: 280px;">
                            <select
                                id="ruhi-gs-full-select-gs"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 280px;"
                                data-s2-hidden="#ruhi-gs-full-hidden-gs"
                                data-s2-anchor="#ruhi-gs-full-anchor-gs"
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
                            href="{{ route('masterapp.ruhi-reports.gs-full-report.print', ['gs' => $gsId]) }}"
                        class="btn btn-outline-primary btn-sm ruhi-print-preview-link"
                            title="Print all three sections"
                        >
                            <i class="fa fa-print mr-1"></i> Print all
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Select GS and submit first">
                            <i class="fa fa-print mr-1"></i> Print all
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
            $gsLabel = $selectedGsName !== '' ? $selectedGsName : $report['gs_name'];
        @endphp

        <div class="card mt-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between mb-3 gap-2">
                    <h5 class="mb-0 font-weight-bold">
                        GS Wise Kundanfull Report
                        @if($gsLabel !== '')
                            <span class="text-nowrap font-weight-normal">({{ $gsLabel }})</span>
                        @endif
                    </h5>
                    <a
                        href="{{ route('masterapp.ruhi-reports.gs-full-report.print', ['gs' => $gsId, 'section' => 'kundanfull']) }}"
                        class="btn btn-outline-secondary btn-sm flex-shrink-0 ruhi-print-preview-link"
                        title="Print this section only"
                    >
                        <i class="fa fa-print mr-1"></i> Print
                    </a>
                </div>
                <div class="table-responsive" style="max-width: 72rem;">
                    <table class="table table-bordered table-sm mb-0 w-100" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th class="text-break py-2" style="width: 18rem;">Kundanfull</th>
                                <th class="text-left py-2" style="width: 12rem;">Total Quantity</th>
                                <th class="text-left py-2" style="width: 12rem;">Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['kundanfull']['rows'] as $r)
                                <tr>
                                    <td class="text-break py-2">{{ $r['kundanfull'] }}</td>
                                    <td class="text-left py-2">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                                    <td class="text-left py-2">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No Kundan Full lines for this GS.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td class="font-weight-bold py-2">Grand Total</td>
                                <td class="text-left font-weight-bold py-2">{{ number_format((int) $report['kundanfull']['grand_total_quantity'], 0, '.', '') }}</td>
                                <td class="text-left font-weight-bold py-2">{{ number_format((float) $report['kundanfull']['grand_total_weight'], 2, '.', '') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between mb-3 gap-2">
                    <h5 class="mb-0 font-weight-bold">
                        GS Wise Pulkifull Report
                        @if($gsLabel !== '')
                            <span class="text-nowrap font-weight-normal">({{ $gsLabel }})</span>
                        @endif
                    </h5>
                    <a
                        href="{{ route('masterapp.ruhi-reports.gs-full-report.print', ['gs' => $gsId, 'section' => 'pulkifull']) }}"
                        class="btn btn-outline-secondary btn-sm flex-shrink-0 ruhi-print-preview-link"
                        title="Print this section only"
                    >
                        <i class="fa fa-print mr-1"></i> Print
                    </a>
                </div>
                <div class="table-responsive" style="max-width: 72rem;">
                    <table class="table table-bordered table-sm mb-0 w-100" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th class="text-break py-2" style="width: 18rem;">Pulkifull</th>
                                <th class="text-left py-2" style="width: 12rem;">Total Quantity</th>
                                <th class="text-left py-2" style="width: 12rem;">Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['pulkifull']['rows'] as $r)
                                <tr>
                                    <td class="text-break py-2">{{ $r['pulkifull'] }}</td>
                                    <td class="text-left py-2">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                                    <td class="text-left py-2">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No Polki Full lines for this GS.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td class="font-weight-bold py-2">Grand Total</td>
                                <td class="text-left font-weight-bold py-2">{{ number_format((int) $report['pulkifull']['grand_total_quantity'], 0, '.', '') }}</td>
                                <td class="text-left font-weight-bold py-2">{{ number_format((float) $report['pulkifull']['grand_total_weight'], 2, '.', '') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap align-items-start justify-content-between mb-3 gap-2">
                    <h5 class="mb-0 font-weight-bold">
                        GS Wise Addfull Report
                        @if($gsLabel !== '')
                            <span class="text-nowrap font-weight-normal">({{ $gsLabel }})</span>
                        @endif
                    </h5>
                    <a
                        href="{{ route('masterapp.ruhi-reports.gs-full-report.print', ['gs' => $gsId, 'section' => 'addfull']) }}"
                        class="btn btn-outline-secondary btn-sm flex-shrink-0 ruhi-print-preview-link"
                        title="Print this section only"
                    >
                        <i class="fa fa-print mr-1"></i> Print
                    </a>
                </div>
                <div class="table-responsive" style="max-width: 72rem;">
                    <table class="table table-bordered table-sm mb-0 w-100" style="table-layout: fixed;">
                        <thead>
                            <tr>
                                <th class="text-break py-2" style="width: 16rem;">Addfull</th>
                                <th class="text-break py-2" style="width: 14rem;">K Stone Name</th>
                                <th class="text-left py-2" style="width: 12rem;">Total Quantity</th>
                                <th class="text-left py-2" style="width: 12rem;">Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['addfull']['rows'] as $r)
                                <tr>
                                    <td class="text-break py-2">{{ $r['addfull'] }}</td>
                                    <td class="text-break py-2">{{ $r['kstone_name'] !== '' ? $r['kstone_name'] : '—' }}</td>
                                    <td class="text-left py-2">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                                    <td class="text-left py-2">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No AD Full lines for this GS.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td class="font-weight-bold py-2" colspan="2">Grand Total</td>
                                <td class="text-left font-weight-bold py-2">{{ number_format((int) $report['addfull']['grand_total_quantity'], 0, '.', '') }}</td>
                                <td class="text-left font-weight-bold py-2">{{ number_format((float) $report['addfull']['grand_total_weight'], 2, '.', '') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
