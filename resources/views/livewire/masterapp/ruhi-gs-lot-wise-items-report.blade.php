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
                        <input type="hidden" wire:model.live="gsId" id="ruhi-gs-lot-wise-gsid">
                        <div wire:ignore class="d-inline-block ruhi-gs-lot-wise-select-wrap" style="min-width: 280px;">
                            <select
                                id="ruhiGsLotWiseGsSelect"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%; min-width: 280px;"
                                data-s2-hidden="#ruhi-gs-lot-wise-gsid"
                                data-s2-anchor="#ruhi-gs-lot-wise-sync-anchor"
                                data-s2-placeholder="Select GS"
                                data-s2-allow-clear="true"
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
                        class="btn btn-outline-primary btn-sm mb-2 ruhi-print-preview-link"
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
            <div class="row mx-0 align-items-start">
                @foreach($sortedBlocks as $block)
                    @php
                        /** @var \App\Models\RuhiSlot $lot */
                        $lot = $block['lot'];
                    @endphp
                    <div class="col-12 col-sm-6 col-lg-6 col-xl-4 mb-3 px-2">
                        <div class="card mb-0 w-100 ruhi-lot-block-card">
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
