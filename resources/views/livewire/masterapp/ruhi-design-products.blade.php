<div>
    <div class="card mb-3">
        <div class="card-body">
            <form wire:submit.prevent="saveSummary">
                <div class="row align-items-end">
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="mb-1">Total Collate</label>
                            <input type="number" min="0" step="0.001" class="form-control form-control-sm @error('dubby_qty') is-invalid @enderror" wire:model.defer="dubby_qty">
                            @error('dubby_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="mb-1">Zumka</label>
                            <input type="number" min="0" step="1" class="form-control form-control-sm @error('zumka_qty') is-invalid @enderror" wire:model.defer="zumka_qty">
                            @error('zumka_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label class="mb-1">UF</label>
                            <input type="text" class="form-control form-control-sm @error('uf') is-invalid @enderror" wire:model.defer="uf" maxlength="100">
                            @error('uf') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="mb-1">Note</label>
                            <input type="text" class="form-control form-control-sm @error('note') is-invalid @enderror" wire:model.defer="note" maxlength="255">
                            @error('note') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="col-md-2 text-md-right mt-2 mt-md-0">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save mr-1"></i> Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @foreach($blocks as $block)
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center">
                <h6 class="mb-0">{{ $block['type']->item_type }} (Total: {{ $block['total'] }})</h6>
                <div class="ml-auto d-flex align-items-center" style="gap: .5rem;">
                    <label class="mb-0 small text-muted">Show</label>
                    <select class="form-control form-control-sm" style="width:auto; min-width:4.5rem;" wire:model.live="perPageByType.{{ $block['type']->id }}">
                        @foreach([20, 10, 15, 25, 50, 100] as $n)
                            <option value="{{ $n }}">{{ $n }}</option>
                        @endforeach
                        <option value="all">All</option>
                    </select>
                    <button type="button" class="btn btn-outline-primary btn-sm" wire:click="openCreateModal({{ $block['type']->id }})">
                        <i class="fa fa-plus mr-1"></i> Add
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        @php
                            $isColorType = strtolower((string) ($block['type']->type_by_color ?? 'no')) === 'yes';
                        @endphp
                        @if($isColorType)
                            <tr>
                                <th rowspan="2" style="width: 70px; white-space: nowrap;">S. No.</th>
                                <th rowspan="2">Item Name</th>
                                <th rowspan="2" style="width: 130px; white-space: nowrap;">Item Quantity</th>
                                <th rowspan="2" style="width: 90px; white-space: nowrap;">Red</th>
                                <th colspan="2" style="width: 180px; white-space: nowrap;">Red + Green</th>
                                <th rowspan="2" style="width: 90px; white-space: nowrap;">Green</th>
                                <th rowspan="2" style="width: 90px; white-space: nowrap;">White</th>
                                <th rowspan="2" style="width: 200px; white-space: nowrap;">Action</th>
                            </tr>
                            <tr>
                                <th style="width: 90px; white-space: nowrap;">Red</th>
                                <th style="width: 90px; white-space: nowrap;">Green</th>
                            </tr>
                        @else
                            <tr>
                                <th style="width: 70px; white-space: nowrap;">S. No.</th>
                                <th>Item Name</th>
                                <th style="width: 150px; white-space: nowrap;">Item Quantity</th>
                                <th style="width: 200px; white-space: nowrap;">Action</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @forelse($block['rows'] as $row)
                            @php
                                $onlyRedQty = (int) $row->collateByColors->sum('only_red_qty');
                                $redQty = (int) $row->collateByColors->sum('red_qty');
                                $greenQty = (int) $row->collateByColors->sum('green_qty');
                                $onlyGreenQty = (int) $row->collateByColors->sum('only_green_qty');
                                $whiteQty = max((int) $row->quantity - ($onlyRedQty + $redQty + $greenQty + $onlyGreenQty), 0);
                            @endphp
                            <tr>
                                <td>{{ ($block['rows']->firstItem() ?? 1) + $loop->index }}</td>
                                <td>{{ $row->product->product_name ?? ('#'.$row->product_id) }}</td>
                                <td>{{ $row->quantity }}</td>
                                @if($isColorType)
                                    <td>{{ $onlyRedQty }}</td>
                                    <td>{{ $redQty }}</td>
                                    <td>{{ $greenQty }}</td>
                                    <td>{{ $onlyGreenQty }}</td>
                                    <td>{{ $whiteQty }}</td>
                                @endif
                                <td>
                                    <div class="action-div master-actions">
                                        @if(strtolower((string) ($block['type']->required_kstone ?? 'No')) === 'yes')
                                            <a
                                                href="{{ route('masterapp.ruhi-designs.products.kstones', ['design' => $row->design_id, 'product' => $row->product_id]) }}"
                                                class="btn btn-outline-success btn-sm mr-1 d-inline-flex align-items-center"
                                                style="border-radius: 4px; font-size: 11px; line-height: 1.2; font-weight: 600; white-space: nowrap; gap: 4px;"
                                                title="List K-Stone"
                                            >
                                                <i class="fa fa-plus"></i>
                                                <span>K-Stone</span>
                                            </a>
                                        @endif
                                        <a href="#" wire:click.prevent="openEditModal({{ $row->id }})" class="action-icon" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if((auth()->user()?->user_type ?? '') === 'systemuser')
                                            <button type="button" class="btn btn-link p-0 action-icon text-danger" wire:click="deleteById({{ $row->id }})" wire:confirm="Remove this design item?" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isColorType ? 9 : 4 }}" class="text-center">No items added in this type.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-2">
                {{ $block['rows']->links() }}
            </div>
        </div>
    @endforeach

    <div class="modal fade {{ $showCreateModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 62vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Design Item</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body py-3" style="max-height: 65vh; overflow-y: auto;">
                        @if($this->activeTypeIsColor())
                            @foreach($createRows as $i => $row)
                                <div class="row align-items-end mb-2">
                                    <div class="col-md-3">
                                        <div class="form-group mb-0">
                                            @if($i === 0)<label class="mb-1">Item Name <span class="text-danger">*</span></label>@endif
                                            <div id="ruhi-dp-create-anchor-{{ $i }}" class="d-none" data-s2-value="{{ $row['product_id'] ?? '' }}"></div>
                                            <input type="hidden" wire:model.defer="createRows.{{ $i }}.product_id" id="ruhi-dp-create-hidden-{{ $i }}" class="@error('createRows.'.$i.'.product_id') is-invalid @enderror">
                                            <div wire:ignore class="w-100">
                                                <select
                                                    id="ruhi-dp-create-select-{{ $i }}"
                                                    class="form-control form-control-sm js-ruhi-master-select2 @error('createRows.'.$i.'.product_id') is-invalid @enderror"
                                                    data-s2-hidden="#ruhi-dp-create-hidden-{{ $i }}"
                                                    data-s2-anchor="#ruhi-dp-create-anchor-{{ $i }}"
                                                    data-s2-placeholder="Select Item"
                                                    data-s2-dropdown-parent="modal"
                                                    data-s2-dropdown-class="design-product-select2-dropdown"
                                                    required
                                                >
                                                    <option value="">Select Item</option>
                                                    @foreach($productsForActiveType as $product)
                                                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('createRows.'.$i.'.product_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group mb-0">
                                            @if($i === 0)<label class="mb-1">Qty <span class="text-danger">*</span></label>@endif
                                            <input type="number" min="1" step="1" class="form-control form-control-sm design-item-small-input @error('createRows.'.$i.'.quantity') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.quantity" required>
                                            @error('createRows.'.$i.'.quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group mb-0">
                                            @if($i === 0)<label class="mb-1 text-danger">Red</label>@endif
                                            <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('createRows.'.$i.'.only_red_qty') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.only_red_qty">
                                            @error('createRows.'.$i.'.only_red_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        @if($i === 0)<label class="mb-1 text-success d-block">Red + Green</label>@endif
                                        <div class="d-flex" style="gap: 6px;">
                                            <div style="flex:1; min-width: 0;">
                                                <div class="form-group mb-0">
                                                    @if($i === 0)<label class="mb-1 small">Red</label>@endif
                                                    <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('createRows.'.$i.'.red_qty') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.red_qty">
                                                    @error('createRows.'.$i.'.red_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div style="flex:1; min-width: 0;">
                                                <div class="form-group mb-0">
                                                    @if($i === 0)<label class="mb-1 small">Green</label>@endif
                                                    <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('createRows.'.$i.'.green_qty') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.green_qty">
                                                    @error('createRows.'.$i.'.green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group mb-0">
                                            @if($i === 0)<label class="mb-1 text-success">Green</label>@endif
                                            <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('createRows.'.$i.'.only_green_qty') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.only_green_qty">
                                            @error('createRows.'.$i.'.only_green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group mb-0">
                                            @if($i === 0)<label class="mb-1">White</label>@endif
                                            <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('createRows.'.$i.'.white_qty') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.white_qty">
                                            @error('createRows.'.$i.'.white_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-right">
                                        @if(count($createRows) > 1)
                                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeCreateRow({{ $i }})"><i class="fa fa-times"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            @foreach($createRows as $i => $row)
                                <div class="row align-items-end mb-2">
                                    <div class="col-md-5">
                                        <div class="form-group mb-0">
                                            @if($i === 0)<label class="mb-1">Item Name <span class="text-danger">*</span></label>@endif
                                            <div id="ruhi-dp-create-anchor-{{ $i }}" class="d-none" data-s2-value="{{ $row['product_id'] ?? '' }}"></div>
                                            <input type="hidden" wire:model.defer="createRows.{{ $i }}.product_id" id="ruhi-dp-create-hidden-{{ $i }}" class="@error('createRows.'.$i.'.product_id') is-invalid @enderror">
                                            <div wire:ignore class="w-100">
                                                <select
                                                    id="ruhi-dp-create-select-{{ $i }}"
                                                    class="form-control form-control-sm js-ruhi-master-select2 @error('createRows.'.$i.'.product_id') is-invalid @enderror"
                                                    data-s2-hidden="#ruhi-dp-create-hidden-{{ $i }}"
                                                    data-s2-anchor="#ruhi-dp-create-anchor-{{ $i }}"
                                                    data-s2-placeholder="Select Item"
                                                    data-s2-dropdown-parent="modal"
                                                    data-s2-dropdown-class="design-product-select2-dropdown"
                                                    required
                                                >
                                                    <option value="">Select Item</option>
                                                    @foreach($productsForActiveType as $product)
                                                        <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('createRows.'.$i.'.product_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group mb-0">
                                            @if($i === 0)<label class="mb-1">Item Quantity <span class="text-danger">*</span></label>@endif
                                            <input type="number" min="1" step="1" class="form-control form-control-sm design-item-small-input @error('createRows.'.$i.'.quantity') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.quantity" required>
                                            @error('createRows.'.$i.'.quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-right">
                                        @if(count($createRows) > 1)
                                            <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeCreateRow({{ $i }})"><i class="fa fa-times"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="addCreateRow">
                                <i class="fa fa-plus mr-1"></i> Add More
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showEditModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 62vw;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Design Item</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body py-3">
                        @if($this->activeTypeIsColor())
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        <label class="mb-1">Item Name <span class="text-danger">*</span></label>
                                        <div id="ruhi-dp-edit-anchor" class="d-none" data-s2-value="{{ $product_id }}"></div>
                                        <input type="hidden" wire:model.defer="product_id" id="ruhi-dp-edit-hidden" class="@error('product_id') is-invalid @enderror">
                                        <div wire:ignore class="w-100">
                                            <select
                                                id="ruhi-dp-edit-select"
                                                class="form-control form-control-sm js-ruhi-master-select2 @error('product_id') is-invalid @enderror"
                                                data-s2-hidden="#ruhi-dp-edit-hidden"
                                                data-s2-anchor="#ruhi-dp-edit-anchor"
                                                data-s2-placeholder="Select Item"
                                                data-s2-dropdown-parent="modal"
                                                data-s2-dropdown-class="design-product-select2-dropdown"
                                                required
                                            >
                                                <option value="">Select Item</option>
                                                @foreach($productsForActiveType as $product)
                                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('product_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        <label class="mb-1">Qty <span class="text-danger">*</span></label>
                                        <input type="number" min="1" step="1" class="form-control form-control-sm design-item-small-input @error('quantity') is-invalid @enderror" wire:model.live="quantity" required>
                                        @error('quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        <label class="mb-1 text-danger">Red</label>
                                        <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('only_red_qty') is-invalid @enderror" wire:model.live="only_red_qty">
                                        @error('only_red_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="mb-1 text-success d-block">Red + Green</label>
                                    <div class="d-flex" style="gap: 6px;">
                                        <div style="flex:1; min-width: 0;">
                                            <div class="form-group mb-0">
                                                <label class="mb-1 small">Red</label>
                                                <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('red_qty') is-invalid @enderror" wire:model.live="red_qty">
                                                @error('red_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div style="flex:1; min-width: 0;">
                                            <div class="form-group mb-0">
                                                <label class="mb-1 small">Green</label>
                                                <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('green_qty') is-invalid @enderror" wire:model.live="green_qty">
                                                @error('green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        <label class="mb-1 text-success">Green</label>
                                        <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('only_green_qty') is-invalid @enderror" wire:model.live="only_green_qty">
                                        @error('only_green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="mb-1">White</label>
                                        <input type="number" min="0" step="1" class="form-control form-control-sm design-item-small-input @error('white_qty') is-invalid @enderror" wire:model.live="white_qty">
                                        @error('white_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <div class="form-group mb-0">
                                        <label class="mb-1">Item Name <span class="text-danger">*</span></label>
                                        <div id="ruhi-dp-edit-anchor" class="d-none" data-s2-value="{{ $product_id }}"></div>
                                        <input type="hidden" wire:model.defer="product_id" id="ruhi-dp-edit-hidden" class="@error('product_id') is-invalid @enderror">
                                        <div wire:ignore class="w-100">
                                            <select
                                                id="ruhi-dp-edit-select"
                                                class="form-control form-control-sm js-ruhi-master-select2 @error('product_id') is-invalid @enderror"
                                                data-s2-hidden="#ruhi-dp-edit-hidden"
                                                data-s2-anchor="#ruhi-dp-edit-anchor"
                                                data-s2-placeholder="Select Item"
                                                data-s2-dropdown-parent="modal"
                                                data-s2-dropdown-class="design-product-select2-dropdown"
                                                required
                                            >
                                                <option value="">Select Item</option>
                                                @foreach($productsForActiveType as $product)
                                                    <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('product_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        <label class="mb-1">Item Quantity <span class="text-danger">*</span></label>
                                        <input type="number" min="1" step="1" class="form-control form-control-sm design-item-small-input @error('quantity') is-invalid @enderror" wire:model.defer="quantity" required>
                                        @error('quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        .select2-dropdown.design-product-select2-dropdown .select2-search--dropdown {
            display: block !important;
        }
        .design-item-small-input {
            width: 100%;
            min-width: 0;
            max-width: 78px;
        }
        .modal .js-ruhi-master-select2 + .select2-container {
            width: 100% !important;
        }
    </style>
</div>
