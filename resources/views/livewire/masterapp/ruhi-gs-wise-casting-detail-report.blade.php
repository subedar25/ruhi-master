<div>
    <div class="card">
        <div class="card-body">
            <div id="ruhi-casting-detail-anchor-gs" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>
            <div id="ruhi-casting-detail-anchor-lot" class="d-none" data-s2-value="{{ $lotId ?? '' }}"></div>
            <div class="d-flex flex-wrap align-items-end justify-content-between w-100">
                <form wire:submit.prevent="submit" class="form-inline flex-wrap align-items-end flex-grow-1">
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">GS</label>
                        <input type="hidden" wire:model.live="gsId" id="ruhi-casting-detail-hidden-gs">
                        <div wire:ignore class="d-inline-block" style="min-width: 260px;">
                            <select
                                id="ruhi-casting-detail-select-gs"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 260px;"
                                data-s2-hidden="#ruhi-casting-detail-hidden-gs"
                                data-s2-anchor="#ruhi-casting-detail-anchor-gs"
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
                        <label class="mr-2 mb-0">Lot</label>
                        <input type="hidden" wire:model.live="lotId" id="ruhi-casting-detail-hidden-lot">
                        <div class="d-inline-block" style="min-width: 260px;">
                            <select
                                wire:key="ruhi-casting-detail-lot-select-{{ $gsId ?: 'none' }}"
                                id="ruhi-casting-detail-select-lot"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 260px;"
                                data-s2-hidden="#ruhi-casting-detail-hidden-lot"
                                data-s2-anchor="#ruhi-casting-detail-anchor-lot"
                                data-s2-placeholder="{{ $gsId ? 'Select Lot' : 'Select GS first' }}"
                            >
                                <option value="">{{ $gsId ? 'Select Lot' : 'Select GS first' }}</option>
                                @foreach($lotOptions as $slot)
                                    <option value="{{ $slot->id }}">{{ $slot->slot_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mb-2">Submit</button>
                </form>
                <div class="mb-2 flex-shrink-0 ml-2">
                    @if($submitted && $gsId && $lotId)
                        <a
                            href="{{ route('masterapp.ruhi-reports.gs-wise-casting-detail-report.print', ['gs' => $gsId, 'lot' => $lotId]) }}"
                            target="_blank"
                            rel="noopener"
                            class="btn btn-outline-primary btn-sm"
                        >
                            <i class="fa fa-print mr-1"></i> Print
                        </a>
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Select GS, Lot and submit first">
                            <i class="fa fa-print mr-1"></i> Print
                        </button>
                    @endif
                </div>
            </div>
            @error('gsId')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @error('lotId')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if($submitted && $report)
        <h5 class="mb-2 mt-3">
            <span class="font-weight-bold">GS Wise Casting Detail Report</span>
            @if($report['gs_name'] !== '' || $report['lot_name'] !== '')
                (<span class="text-nowrap">{{ $report['gs_name'] }}</span>@if($report['gs_name'] !== '' && $report['lot_name'] !== ''){{ ', ' }}@endif<span class="text-nowrap">{{ $report['lot_name'] }}</span>)
            @endif
        </h5>

        <div class="table-responsive d-inline-block w-100" style="max-width: 72rem;">
            <table class="table table-bordered table-sm mb-0 w-100" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th class="text-break py-2" style="width: 18rem;">Design Name</th>
                        <th class="text-break py-2" style="width: 18rem;">Casting</th>
                        <th class="text-left py-2" style="width: 12rem;">Total Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @if(($report['design_groups'] ?? []) === [])
                        <tr>
                            <td colspan="3" class="text-center text-muted">No detail lines for this GS and lot (design products with item type id 2).</td>
                        </tr>
                    @else
                        @foreach($report['design_groups'] as $group)
                            @foreach($group['lines'] as $lineIdx => $line)
                                <tr>
                                    @if($lineIdx === 0)
                                        <td class="text-break align-top py-2" rowspan="{{ count($group['lines']) }}">{{ $group['design_name'] }}- {{ number_format((int) ($group['design_qty'] ?? 0), 0, '.', '') }}</td>
                                    @endif
                                    <td class="text-break align-top py-2">{{ $line['casting'] }}</td>
                                    <td class="text-left py-2">{{ number_format((int) $line['total_quantity'], 0, '.', '') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="2" class="font-weight-bold py-2">Grand Total</td>
                        <td class="text-left font-weight-bold py-2">{{ number_format((int) $report['grand_total_quantity'], 0, '.', '') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif
</div>
