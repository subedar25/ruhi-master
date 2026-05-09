<div>
    <div class="card">
        <div class="card-body">
            <div id="ruhi-casting-anchor-gs" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>
            <div id="ruhi-casting-anchor-lot" class="d-none" data-s2-value="{{ $lotId ?? '' }}"></div>
            <div class="d-flex flex-wrap align-items-end justify-content-between w-100">
                <form wire:submit.prevent="submit" class="form-inline flex-wrap align-items-end flex-grow-1">
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">GS</label>
                        <input type="hidden" wire:model.live="gsId" id="ruhi-casting-hidden-gs">
                        <div wire:ignore class="d-inline-block" style="min-width: 260px;">
                            <select
                                id="ruhi-casting-select-gs"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 260px;"
                                data-s2-hidden="#ruhi-casting-hidden-gs"
                                data-s2-anchor="#ruhi-casting-anchor-gs"
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
                        <input type="hidden" wire:model.live="lotId" id="ruhi-casting-hidden-lot">
                        {{-- wire:key replaces the select when GS changes so lots refresh and Select2 re-inits --}}
                        <div class="d-inline-block" style="min-width: 260px;">
                            <select
                                wire:key="ruhi-casting-lot-select-{{ $gsId ?: 'none' }}"
                                id="ruhi-casting-select-lot"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 260px;"
                                data-s2-hidden="#ruhi-casting-hidden-lot"
                                data-s2-anchor="#ruhi-casting-anchor-lot"
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
                <div class="mb-2 flex-shrink-0 ml-2 d-flex align-items-center flex-wrap" style="gap: .35rem;">
                    @if($submitted && $gsId && $lotId)
                        <a
                            href="{{ route('masterapp.ruhi-reports.gs-wise-casting-detail-report', ['gs' => $gsId, 'lot' => $lotId]) }}"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            Details
                        </a>
                        <button
                            type="button"
                            wire:click="openPrintPreview"
                            class="btn btn-outline-primary btn-sm"
                        >
                            <i class="fa fa-print mr-1"></i> Print
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Select GS, Lot and submit first">
                            Details
                        </button>
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
            <span class="font-weight-bold">GS Wise Casting Report</span>
            @if($report['gs_name'] !== '' || $report['lot_name'] !== '')
                (<span class="text-nowrap">{{ $report['gs_name'] }}</span>@if($report['gs_name'] !== '' && $report['lot_name'] !== ''){{ ', ' }}@endif<span class="text-nowrap">{{ $report['lot_name'] }}</span>)
            @endif
        </h5>

        <p class="mb-2">
            <span class="font-weight-bold">Design Name</span>
            @if($report['design_names_csv'] !== '')
                &nbsp;{{ $report['design_names_csv'] }}
            @else
                <span class="text-muted">&mdash;</span>
            @endif
        </p>

        <div class="table-responsive d-inline-block w-100" style="max-width: 72rem;">
            <table class="table table-bordered table-sm mb-0 ruhi-casting-report-table w-100" style="table-layout: fixed;">
                <thead>
                    <tr>
                        <th class="text-break py-2" style="width: 18rem; max-width: 18rem;">Casting</th>
                        <th class="text-left py-2" style="width: 12rem;">Total Quantity</th>
                        <th class="text-left py-2" style="width: 12rem;">Weight</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $r)
                        <tr>
                            <td class="text-break align-top py-2" style="max-width: 18rem; word-break: break-word;">{{ $r['casting'] }}</td>
                            <td class="text-left py-2">{{ number_format((int) $r['total_quantity'], 0, '.', '') }}</td>
                            <td class="text-left py-2">{{ number_format((float) $r['weight'], 2, '.', '') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">No casting lines for this GS and lot (design products with item type id 2).</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td class="font-weight-bold py-2">Grand Total</td>
                        <td class="text-left font-weight-bold py-2">{{ number_format((int) $report['grand_total_quantity'], 0, '.', '') }}</td>
                        <td class="text-left font-weight-bold py-2">{{ number_format((float) $report['grand_total_weight'], 2, '.', '') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    <div class="modal fade {{ $showPrintPreviewModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.65);">
        <div class="modal-dialog modal-xl" role="document" style="max-width: 92vw;">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h5 class="modal-title">Print Preview - GS Wise Casting Report</h5>
                    <button type="button" class="close" wire:click="closePrintPreview"><span>&times;</span></button>
                </div>
                <div class="modal-body p-0" style="height: 78vh;">
                    @if($printPreviewUrl !== '')
                        <iframe
                            src="{{ $printPreviewUrl }}"
                            title="GS Wise Casting Report Print Preview"
                            style="width:100%; height:100%; border:0;"
                        ></iframe>
                    @endif
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closePrintPreview">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
