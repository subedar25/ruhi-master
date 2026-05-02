<div>
    <div class="card">
        <div class="card-body">
            <div id="ruhi-color-collet-anchor-gs" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>
            <div class="d-flex flex-wrap align-items-end justify-content-between w-100">
                <form wire:submit.prevent="submit" class="form-inline flex-wrap align-items-end flex-grow-1">
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">GS</label>
                        <input type="hidden" wire:model.live="gsId" id="ruhi-color-collet-hidden-gs">
                        <div wire:ignore class="d-inline-block" style="min-width: 280px;">
                            <select
                                id="ruhi-color-collet-select-gs"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 280px;"
                                data-s2-hidden="#ruhi-color-collet-hidden-gs"
                                data-s2-anchor="#ruhi-color-collet-anchor-gs"
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
                            href="{{ route('masterapp.ruhi-reports.gs-color-collet-report.print', ['gs' => $gsId]) }}"
                            target="_blank"
                            rel="noopener"
                            class="btn btn-outline-primary btn-sm"
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
            <i class="fas fa-file-alt text-primary mr-2"></i>
            <span class="font-weight-bold mr-1">GS Color Collet Report</span>
            @if($selectedGsName !== '')
                <span class="text-nowrap">({{ $selectedGsName }})</span>
            @endif
        </h5>
        <div class="table-responsive d-inline-block w-100" style="max-width: 72rem;">
            <table class="table table-bordered table-sm mb-0 w-100">
                <thead class="thead-light">
                    <tr>
                        <th class="text-break py-2"><i class="fas fa-th-list mr-1 text-secondary"></i> Collet</th>
                        <th class="text-left py-2"><i class="fas fa-palette mr-1 text-danger"></i> Red</th>
                        <th class="text-left py-2"><i class="fas fa-palette mr-1 text-success"></i> Green</th>
                        <th class="text-left py-2"><i class="fas fa-balance-scale mr-1 text-secondary"></i> Weight</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $r)
                        <tr>
                            <td class="text-break align-top py-2">{{ $r['collet'] }}</td>
                            <td class="text-left py-2">{{ number_format((int) $r['red'], 0, '.', '') }}</td>
                            <td class="text-left py-2">{{ number_format((int) $r['green'], 0, '.', '') }}</td>
                            <td class="text-left py-2">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">No collet (product type 3) lines with collate data for this GS.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($report['rows']) > 0)
                    <tfoot>
                        <tr class="table-light">
                            <td class="font-weight-bold py-2">Grand Total</td>
                            <td class="text-left font-weight-bold py-2">{{ number_format((int) $report['grand_red'], 0, '.', '') }}</td>
                            <td class="text-left font-weight-bold py-2">{{ number_format((int) $report['grand_green'], 0, '.', '') }}</td>
                            <td class="text-left font-weight-bold py-2">{{ number_format((float) $report['grand_weight'], 2, '.', '') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @endif
</div>
