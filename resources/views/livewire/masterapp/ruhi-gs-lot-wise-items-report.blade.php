<div>
    <style>
        .ruhi-lot-block-card { min-width: 0; }
        .ruhi-lot-block-table { table-layout: fixed; width: 100%; }
    </style>
    <div class="card">
        <div class="card-body">
            <div id="ruhi-gs-lot-wise-sync-anchor" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>
            <div class="d-flex flex-wrap align-items-end justify-content-between">
                <form wire:submit.prevent="submit" class="form-inline flex-wrap align-items-end">
                    <div class="form-group mr-3 mb-2">
                        <label class="mr-2 mb-0">GS</label>
                        <input type="hidden" wire:model="gsId" id="ruhi-gs-lot-wise-gsid">
                        <div wire:ignore class="d-inline-block ruhi-gs-lot-wise-select-wrap" style="min-width: 280px;">
                            <select
                                id="ruhiGsLotWiseGsSelect"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 280px;"
                                data-s2-hidden="#ruhi-gs-lot-wise-gsid"
                                data-s2-anchor="#ruhi-gs-lot-wise-sync-anchor"
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
                @if($submitted && $gsId)
                    <a
                        href="{{ route('masterapp.ruhi-reports.gs-lot-wise-items.print', ['gs' => $gsId]) }}"
                        target="_blank"
                        class="btn btn-outline-primary btn-sm mb-2"
                    >
                        <i class="fa fa-print mr-1"></i> Print
                    </a>
                @else
                    <button type="button" class="btn btn-outline-secondary btn-sm mb-2" disabled title="Select GS and submit first">
                        <i class="fa fa-print mr-1"></i> Print
                    </button>
                @endif
            </div>
            @error('gsId')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if($submitted && $gsId)
        <h5 class="mb-3">GS Wise Lot Wise item report @if($selectedGsName !== '')({{ $selectedGsName }})@endif</h5>

        @if($blocks->isEmpty())
            <div class="alert alert-info mb-0">No lots found for this GS.</div>
        @else
            <div class="row mx-0">
                @foreach($blocks as $block)
                    @php
                        /** @var \App\Models\RuhiSlot $lot */
                        $lot = $block['lot'];
                    @endphp
                    <div class="col-12 col-sm-6 col-lg-6 col-xl-4 mb-3 px-2 d-flex">
                        <div class="card flex-fill mb-0 w-100 ruhi-lot-block-card">
                            <div class="card-header py-2 px-2 d-flex flex-nowrap align-items-center justify-content-between">
                                <div class="font-weight-bold small text-truncate mr-2" style="min-width: 0; flex: 1 1 auto;" title="{{ $lot->slot_name }}">{{ $lot->slot_name }}</div>
                                <div class="small text-nowrap flex-shrink-0">Total Collate: <strong>{{ (int) round((float) $block['total_collate']) }}</strong></div>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0 ruhi-lot-block-table">
                                    <thead>
                                        <tr>
                                            <th class="py-1 px-2 small font-weight-bold">Design Name</th>
                                            <th class="py-1 px-2 text-left small font-weight-bold" style="width: 6.5rem;">Design Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($block['designs'] as $d)
                                            <tr>
                                                <td class="py-1 px-2 text-break small">{{ $d['design_name'] }}</td>
                                                <td class="py-1 px-2 text-left small">{{ $d['design_qty'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center text-muted small py-2">No items for this lot.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</div>
