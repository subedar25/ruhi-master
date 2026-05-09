<div>
    <style>
        .ruhi-gs-detail-form .ruhi-form-section-title {
            font-size: 0.7rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0.65rem;
        }
        .ruhi-gs-detail-form .ruhi-product-types-box {
            border: 1px solid #dee2e6;
            border-radius: 0.35rem;
            background: #f8f9fa;
            padding: 0.85rem 1rem;
        }
        .ruhi-gs-detail-form .ruhi-product-types-box label {
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            margin-bottom: 0.35rem;
            transition: background-color 0.15s ease;
        }
        .ruhi-gs-detail-form .ruhi-product-types-box label:hover {
            background: rgba(0, 0, 0, 0.04);
        }
        .ruhi-gs-detail-form .ruhi-filters-panel {
            border: 1px solid #dee2e6;
            border-radius: 0.35rem;
            background: #fff;
            padding: 1rem 1.1rem;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }
        .ruhi-gs-detail-form .ruhi-detail-filter-grid {
            display: grid;
            gap: 1rem 1.25rem;
            align-items: start;
        }
        @media (min-width: 992px) {
            .ruhi-gs-detail-form .ruhi-detail-filter-grid {
                grid-template-columns: minmax(160px, 190px) minmax(130px, 160px) minmax(200px, 1fr) minmax(200px, 1fr);
            }
        }
        .ruhi-gs-detail-form .ruhi-field-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.35rem;
            line-height: 1.3;
        }
        .ruhi-gs-detail-form .ruhi-field-hint {
            font-size: 0.7rem;
            color: #868e96;
            margin-top: 0.35rem;
            line-height: 1.35;
        }
        .ruhi-gs-detail-form .ruhi-detail-gs-wrap,
        .ruhi-gs-detail-form .ruhi-detail-namefilter-wrap,
        .ruhi-gs-detail-form .ruhi-detail-designs-wrap,
        .ruhi-gs-detail-form .ruhi-detail-items-wrap {
            width: 100%;
            min-width: 0;
        }
        @media (max-width: 991.98px) {
            .ruhi-gs-detail-form .ruhi-detail-filter-grid {
                grid-template-columns: 1fr;
            }
        }
        .ruhi-gs-detail-form .ruhi-form-actions {
            border-top: 1px solid #e9ecef;
            margin-top: 1rem;
            padding-top: 1rem;
        }
        /* Smaller type in Select2 (GS, designs, items) and native name filter */
        .ruhi-gs-detail-form .select2-container {
            font-size: 0.75rem;
        }
        .ruhi-gs-detail-form .select2-container--default .select2-selection--single {
            min-height: 1.85rem;
        }
        .ruhi-gs-detail-form .select2-container--default .select2-selection--single .select2-selection__rendered {
            font-size: 0.75rem;
            line-height: 1.35;
            padding-top: 0.15rem;
            padding-bottom: 0.15rem;
        }
        .ruhi-gs-detail-form .select2-container--default .select2-selection--multiple {
            min-height: 1.85rem;
            padding: 0.12rem 0.25rem;
        }
        .ruhi-gs-detail-form .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            font-size: 0.75rem;
        }
        .ruhi-gs-detail-form .select2-container--default .select2-selection--multiple .select2-selection__choice {
            font-size: 0.7rem;
            line-height: 1.25;
            padding: 0.08rem 0.35rem;
            margin-top: 0.12rem;
        }
        .ruhi-gs-detail-form .select2-dropdown {
            font-size: 0.75rem;
        }
        .ruhi-gs-detail-form select.form-control-sm:not(.select2-hidden-accessible) {
            font-size: 0.75rem;
        }
        @media print {
            .ruhi-gs-detail-design-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            .ruhi-gs-detail-design-block + .ruhi-gs-detail-design-block {
                page-break-before: always;
                break-before: page;
            }
        }
        .ruhi-gs-detail-design-block {
            scroll-margin-top: 5rem;
        }
        @media print {
            .ruhi-jump-design-card {
                display: none !important;
            }
        }
    </style>
    <div class="card ruhi-gs-detail-form">
        <div class="card-body pb-3">
            <div id="ruhi-detail-anchor-gs" class="d-none" data-s2-value="{{ $gsId ?? '' }}"></div>

            <div class="ruhi-product-types-box mb-3">
                <div class="ruhi-form-section-title">Product type</div>
                <div class="d-flex flex-wrap align-items-center" style="gap: 0.35rem 0.75rem;">
                    @foreach([
                        3 => 'Collet',
                        4 => 'AD Full',
                        5 => 'Polki Full',
                        6 => 'Kundan Full',
                        8 => 'Drop',
                    ] as $tid => $label)
                        <label class="mb-0 small text-nowrap">
                            <input type="checkbox" wire:model.live="productTypes" value="{{ $tid }}" class="mr-1 align-middle">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="ruhi-filters-panel ruhi-detail-filter-row mb-0">
                <div class="ruhi-form-section-title mb-2">Scope &amp; selection</div>
                <div class="ruhi-detail-filter-grid">
                    <div class="ruhi-detail-col-flex">
                        <label class="ruhi-field-label" for="ruhi-detail-select-gs">GS</label>
                        <input type="hidden" wire:model.live="gsId" id="ruhi-detail-hidden-gs">
                        <div wire:ignore class="ruhi-detail-gs-wrap">
                            <select
                                id="ruhi-detail-select-gs"
                                class="form-control form-control-sm js-ruhi-master-select2"
                                style="width: 100%;"
                                data-s2-hidden="#ruhi-detail-hidden-gs"
                                data-s2-anchor="#ruhi-detail-anchor-gs"
                                data-s2-placeholder="Select GS"
                            >
                                <option value="">Select GS</option>
                                @foreach($gsOptions as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="ruhi-detail-col-flex">
                        <label class="ruhi-field-label" for="ruhi-detail-namefilter">Name filter <span class="font-weight-normal" style="color:#868e96;">(Collate)</span></label>
                        <div class="ruhi-detail-namefilter-wrap">
                            <select wire:model.live="nameFilter" id="ruhi-detail-namefilter" class="form-control form-control-sm w-100" title="Collate Item only. Searches for literal (s) with brackets in the product name. Without (s): exclude names containing (s). Only (s): include only names containing (s). Drop Item is not filtered.">
                                <option value="">All names</option>
                                <option value="1">Without (s)</option>
                                <option value="2">Only (s)</option>
                            </select>
                        </div>
                    </div>
                    <div class="ruhi-detail-col-flex">
                        <label class="ruhi-field-label">Designs</label>
                        <div id="ruhi-detail-anchor-designs" class="d-none" data-s2-value="{{ $designIdsCsv }}"></div>
                        <input type="hidden" wire:model.live="designIdsCsv" id="ruhi-detail-hidden-designs">
                        <div class="w-100 ruhi-detail-designs-wrap" wire:key="detail-designs-{{ $gsId }}-{{ $designOptions->count() }}">
                            <select
                                id="ruhi-detail-select-designs"
                                multiple
                                class="form-control form-control-sm js-ruhi-master-select2 w-100"
                                style="width: 100% !important;"
                                data-s2-hidden="#ruhi-detail-hidden-designs"
                                data-s2-anchor="#ruhi-detail-anchor-designs"
                                data-s2-placeholder="Select designs"
                                data-s2-allow-clear="true"
                                data-s2-close-on-select="false"
                            >
                                @foreach($designOptions as $d)
                                    <option value="{{ $d->id }}">{{ $d->design_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($gsId && $designOptions->isEmpty())
                            <div class="ruhi-field-hint text-warning mb-0">No designs on this GS order.</div>
                        @endif
                        @error('designIdsCsv')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="ruhi-detail-col-flex">
                        <label class="ruhi-field-label">Items / products</label>
                        <div id="ruhi-detail-anchor-products" class="d-none" data-s2-value="{{ $productIdsCsv }}"></div>
                        <input type="hidden" wire:model.live="productIdsCsv" id="ruhi-detail-hidden-products">
                        <div class="w-100 ruhi-detail-items-wrap" wire:key="detail-products-{{ $gsId }}-{{ md5($designIdsCsv . '|' . json_encode($productTypes)) }}-{{ $itemOptions->count() }}">
                            <select
                                id="ruhi-detail-select-products"
                                multiple
                                class="form-control form-control-sm js-ruhi-master-select2 w-100"
                                style="width: 100% !important;"
                                data-s2-hidden="#ruhi-detail-hidden-products"
                                data-s2-anchor="#ruhi-detail-anchor-products"
                                data-s2-placeholder="Select items (optional)"
                                data-s2-allow-clear="true"
                                data-s2-close-on-select="false"
                            >
                                @foreach($itemOptions as $p)
                                    <option value="{{ $p->id }}">{{ $p->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ruhi-form-actions d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                <button type="button" wire:click="submit" class="btn btn-primary btn-sm px-3">Run report</button>
                @if($printParams)
                    <a
                        href="{{ route('masterapp.ruhi-reports.gs-detail-each-item-report.print', $printParams) }}"
                        class="btn btn-outline-secondary btn-sm px-3 ruhi-print-preview-link"
                    >
                        <i class="fa fa-print mr-1"></i> Print
                    </a>
                @else
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" disabled title="Submit the report first">Print</button>
                @endif
            </div>
            @error('gsId')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
            @error('productTypes')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if($submitted && $report)
        @php $gsLabel = $selectedGsName !== '' ? $selectedGsName : $report['gs_name']; @endphp

        @if(count($report['blocks']) > 0)
            <div class="card mb-3 ruhi-jump-design-card">
                <div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap: 0.65rem 1rem;">
                    <label for="ruhi-jump-design" class="small font-weight-bold mb-0 text-nowrap">Jump to design</label>
                    <select
                        id="ruhi-jump-design"
                        class="form-control form-control-sm"
                        style="max-width: min(100%, 26rem);"
                        onchange="(function(sel){var id=sel.value;if(!id)return;var el=document.getElementById(id);if(el){el.scrollIntoView({behavior:'smooth',block:'start'});try{el.focus({preventScroll:true});}catch(e){}}})(this)"
                    >
                        <option value="">— Select design —</option>
                        @foreach($report['blocks'] as $jb)
                            <option value="ruhi-design-anchor-{{ $jb['design_id'] }}">{{ $jb['design_name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        @foreach($report['blocks'] as $block)
            <div id="ruhi-design-anchor-{{ $block['design_id'] }}" tabindex="-1" class="card mb-4 ruhi-gs-detail-design-block @if($loop->first) mt-2 @endif">
                <div class="card-body">
                    <h5 class="ruhi-detail-design-doc-title mb-3 pb-2 border-bottom font-weight-bold">
                        GS Wise Detail Report of Each Item
                        @if($gsLabel !== '')
                            <span class="font-weight-normal text-nowrap">({{ $gsLabel }})</span>
                        @endif
                    </h5>
                    <div class="table-responsive mb-3" style="max-width: 72rem;">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Design</th>
                                    <th>Color Qty</th>
                                    <th>Collate Qty</th>
                                    <th>Zumka</th>
                                    <th>UF</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-break align-top font-weight-bold">{{ $block['design_name'] }} - {{ (int) $block['order_footer']['color_count'] }}</td>
                                    <td class="align-top small">
                                        <div>Red:- {{ $block['order_footer']['red'] }}</div>
                                        <div>Red+Green:- {{ $block['order_footer']['red_green'] }}</div>
                                        <div>Green:- {{ $block['order_footer']['green'] }}</div>
                                        <div class="mb-0">White:- {{ $block['order_footer']['white'] }}</div>
                                    </td>
                                    <td class="align-top">{{ $block['header']['collate_qty'] }}</td>
                                    <td class="align-top">{{ $block['header']['zumka'] }}</td>
                                    <td class="text-break align-top">{{ $block['header']['uf'] }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-nowrap">Note</th>
                                    <td colspan="4" class="text-break">{{ $block['header']['note'] }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold mb-2">Collate Item</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm mb-0" style="max-width: 72rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Collate Item</th>
                                    <th class="text-left">Total Quantity</th>
                                    <th class="text-left">Red</th>
                                    <th class="text-left">Green</th>
                                    <th class="text-left">White</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($block['collate_rows'] as $r)
                                    <tr>
                                        <td class="text-break">{{ $r['item'] }}</td>
                                        <td class="text-left">{{ number_format((int) $r['total_qty'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $r['red'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $r['green'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $r['white'], 0, '.', '') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No collate-by-color lines for this filter.</td>
                                    </tr>
                                @endforelse
                                @if(count($block['collate_rows']) > 0)
                                    <tr class="table-light font-weight-bold">
                                        <td>Total</td>
                                        <td class="text-left">{{ number_format((int) $block['collate_column_totals']['total_qty'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $block['collate_column_totals']['red'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $block['collate_column_totals']['green'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $block['collate_column_totals']['white'], 0, '.', '') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mb-3 text-left text-dark" style="max-width: 72rem; overflow-x: auto;">
                        <div class="w-100" style="height: 1rem;" aria-hidden="true"></div>
                        <div class="text-nowrap" style="font-size: 0.9rem; line-height: 1.45;">
                            <span class="font-weight-bold">({{ $gsLabel }})({{ $block['design_name'] }}) - {{ (int) $block['order_footer']['color_count'] }}</span><span class="font-weight-normal">&nbsp;&nbsp;&nbsp;&nbsp;</span><span>Red:- {{ (int) $block['order_footer']['red'] }}&nbsp;&nbsp;&nbsp;&nbsp;Red+Green:- {{ (int) $block['order_footer']['red_green'] }}&nbsp;&nbsp;&nbsp;&nbsp;Green:- {{ (int) $block['order_footer']['green'] }}&nbsp;&nbsp;&nbsp;&nbsp;White:- {{ (int) $block['order_footer']['white'] }}</span>
                        </div>
                    </div>

                    <h6 class="font-weight-bold mb-2">Drop Item</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0" style="max-width: 72rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Drop Item</th>
                                    <th class="text-left">Total Quantity</th>
                                    <th class="text-left">Red</th>
                                    <th class="text-left">Green</th>
                                    <th class="text-left">White</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($block['drop_rows'] as $r)
                                    <tr>
                                        <td class="text-break">{{ $r['item'] }}</td>
                                        <td class="text-left">{{ number_format((int) $r['total_qty'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $r['red'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $r['green'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $r['white'], 0, '.', '') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No drop lines for this filter.</td>
                                    </tr>
                                @endforelse
                                @if(count($block['drop_rows']) > 0)
                                    <tr class="table-light font-weight-bold">
                                        <td>Total</td>
                                        <td class="text-left">{{ number_format((int) $block['drop_column_totals']['total_qty'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $block['drop_column_totals']['red'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $block['drop_column_totals']['green'], 0, '.', '') }}</td>
                                        <td class="text-left">{{ number_format((int) $block['drop_column_totals']['white'], 0, '.', '') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        @if(count($report['blocks']) === 0)
            <p class="text-muted">No blocks to show for the selected filters.</p>
        @endif
    @endif
</div>
