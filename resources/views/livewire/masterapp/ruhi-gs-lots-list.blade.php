<div>
    <div class="card">
        <div class="card-body p-0">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                    <div class="search-input-wrapper" style="width: 230px; min-width: 160px; position: relative;">
                        <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" style="padding-left:34px;" placeholder="Search design/lot...">
                    </div>
                    <div id="ruhi-gs-lots-anchor-lot-filter" class="d-none" data-s2-value="{{ $lotFilterId ?? '' }}"></div>
                    <input type="hidden" wire:model.live="lotFilterId" id="ruhi-gs-lots-hidden-lot-filter">
                    <div wire:ignore class="d-inline-block" style="width: 200px;">
                        <select
                            id="ruhi-gs-lots-select-lot-filter"
                            class="form-control form-control-sm js-ruhi-master-select2"
                            style="width: 100%; min-width: 200px;"
                            data-s2-hidden="#ruhi-gs-lots-hidden-lot-filter"
                            data-s2-anchor="#ruhi-gs-lots-anchor-lot-filter"
                            data-s2-placeholder="All Lots"
                        >
                            <option value="">All Lots</option>
                            @foreach($slotOptions as $slot)
                                <option value="{{ $slot->id }}">{{ $slot->slot_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                <button type="button" class="btn btn-primary btn-sm" wire:click="openAddLotModal">
                    <i class="fa fa-plus mr-1"></i> Add Lots
                </button>
                <button type="button" class="btn btn-success btn-sm" wire:click="openAddItemModal">
                    <i class="fa fa-plus mr-1"></i> Add Item in Lot
                </button>
                </div>
            </div>

            <div class="px-3 py-2 border-bottom">
                <div class="show_page_align">
                    <div class="dataTables_info">
                        @if($rows->total() > 0)
                            Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }}
                        @else
                            Nothing to show
                        @endif
                    </div>
                    <div class="length_pagination d-flex flex-wrap align-items-center">
                        <div class="dataTables_length mr-3">
                            <label class="mb-0">
                                Show
                                <select wire:model.live="perPage" class="form-control form-control-sm d-inline-block" style="width:auto; min-width:4.5rem;">
                                    @foreach([20, 10, 15, 25, 50, 100] as $n)
                                        <option value="{{ $n }}">{{ $n }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="dataTables_paginate paging_simple_numbers pagination-links" style="max-width: 100%; overflow-x: auto; white-space: nowrap;">
                            {{ $rows->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width:70px;">S no</th>
                            <th>Design Name</th>
                            <th>Lot Name</th>
                            <th style="width:90px;">Design Qty</th>
                            <th style="width:90px;">Red Qty</th>
                            <th style="width:120px;">Red + Green Qty</th>
                            <th style="width:90px;">Green Qty</th>
                            <th style="width:90px;">White Qty</th>
                            <th style="width:85px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->design->design_name ?? ('#'.$row->design_id) }}</td>
                                <td>{{ $row->lot->slot_name ?? ('#'.$row->lot_id) }}</td>
                                <td>{{ $row->design_qty }}</td>
                                <td>{{ $row->design_red_qty }}</td>
                                <td>{{ $row->design_red_green_qty }}</td>
                                <td>{{ $row->design_green_qty }}</td>
                                <td>{{ $row->white_qty }}</td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn btn-link p-0 text-primary mr-2"
                                        title="Edit"
                                        wire:click="openEditModal({{ $row->id }})"
                                    >
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-link p-0 text-danger"
                                        title="Delete"
                                        wire:click="deleteLotItemById({{ $row->id }})"
                                        wire:confirm="Delete this row?"
                                    >
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No rows found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 clearfix border-top">
            <div class="show_page_align">
                <div class="dataTables_info">
                    @if($rows->total() > 0)
                        Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }}
                    @else
                        Nothing to show
                    @endif
                </div>
                <div class="length_pagination d-flex flex-wrap align-items-center">
                    <div class="dataTables_length mr-3">
                        <label class="mb-0">
                            Show
                            <select wire:model.live="perPage" class="form-control form-control-sm d-inline-block" style="width:auto; min-width:4.5rem;">
                                @foreach([20, 10, 15, 25, 50, 100] as $n)
                                    <option value="{{ $n }}">{{ $n }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="dataTables_paginate paging_simple_numbers pagination-links" style="max-width: 100%; overflow-x: auto; white-space: nowrap;">
                        {{ $rows->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showAddLotModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Lots</h5>
                    <button type="button" class="close" wire:click="closeAddLotModal"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveLotWithItems">
                    <div class="modal-body py-3" style="max-height: 65vh; overflow-y: auto;">
                        @if(count($errors->get('addLotRows.*.design_id')) > 0)
                            <div class="alert alert-danger py-2 px-3 mb-2">
                                Please select Design in all required rows.
                            </div>
                        @endif
                        @if(count($errors->get('addLotRows.*.design_qty')) > 0)
                            <div class="alert alert-danger py-2 px-3 mb-2">
                                Quantity must be at least 1 in all rows.
                            </div>
                        @endif
                        @if($errors->has('addLotRows'))
                            <div class="alert alert-danger py-2 px-3 mb-2">
                                {{ $errors->first('addLotRows') }}
                            </div>
                        @endif
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Enter Lot Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('lotName') is-invalid @enderror" wire:model.defer="lotName" maxlength="255" required>
                                    @error('lotName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @foreach($addLotRows as $i => $row)
                            <div class="row align-items-end mb-2">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Select Design <span class="text-danger">*</span></label>@endif
                                        <div id="ruhi-gs-lot-add-anchor-{{ $i }}" class="d-none" data-s2-value="{{ $row['design_id'] ?? '' }}"></div>
                                        <input type="hidden" wire:model.defer="addLotRows.{{ $i }}.design_id" id="ruhi-gs-lot-add-hidden-{{ $i }}">
                                        <div wire:ignore class="w-100">
                                            <select
                                                id="ruhi-gs-lot-add-select-{{ $i }}"
                                                class="form-control form-control-sm js-ruhi-master-select2"
                                                data-s2-hidden="#ruhi-gs-lot-add-hidden-{{ $i }}"
                                                data-s2-anchor="#ruhi-gs-lot-add-anchor-{{ $i }}"
                                                data-s2-placeholder="Select Design"
                                                data-s2-dropdown-parent="modal"
                                                data-s2-dropdown-class="design-product-select2-dropdown"
                                            >
                                                <option value="">Select Design</option>
                                                @foreach($designs as $design)
                                                    <option value="{{ $design->id }}">{{ $design->design_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Quantity <span class="text-danger">*</span></label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addLotRows.'.$i.'.design_qty') is-invalid @enderror" wire:model.live="addLotRows.{{ $i }}.design_qty">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Red</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addLotRows.'.$i.'.design_red_qty') is-invalid @enderror" wire:model.live="addLotRows.{{ $i }}.design_red_qty">
                                        @error('addLotRows.'.$i.'.design_red_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Red + Green</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addLotRows.'.$i.'.design_red_green_qty') is-invalid @enderror" wire:model.live="addLotRows.{{ $i }}.design_red_green_qty">
                                        @error('addLotRows.'.$i.'.design_red_green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Green</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addLotRows.'.$i.'.design_green_qty') is-invalid @enderror" wire:model.live="addLotRows.{{ $i }}.design_green_qty">
                                        @error('addLotRows.'.$i.'.design_green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">White</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm" wire:model.live="addLotRows.{{ $i }}.white_qty">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    @if(count($addLotRows) > 1)
                                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeLotRow({{ $i }})" title="Remove row">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" wire:click="addLotRow">
                            <i class="fa fa-plus mr-1"></i> Add More
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeAddLotModal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showAddItemModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Item in Lot</h5>
                    <button type="button" class="close" wire:click="closeAddItemModal"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveItemsInLot">
                    <div class="modal-body py-3" style="max-height: 65vh; overflow-y: auto;">
                        @php
                            $showSelectedLotError = $errors->has('selectedLotId');
                            $showDesignRequiredError = $errors->has('addItemRows.*.design_id');
                            $showQtyMinError = $errors->has('addItemRows.*.design_qty');
                            $showColorSplitError = $errors->has('addItemRows');
                        @endphp
                        @if($showSelectedLotError || $showDesignRequiredError || $showQtyMinError || $showColorSplitError)
                            <div class="alert alert-danger py-2 px-3 mb-2">
                                @if($showSelectedLotError)
                                    <div>Please select Lot.</div>
                                @endif
                                @if($showDesignRequiredError)
                                    <div>Please select Design in all required rows.</div>
                                @endif
                                @if($showQtyMinError)
                                    <div>Quantity must be at least 1 in all rows.</div>
                                @endif
                                @if($showColorSplitError)
                                    <div>{{ $errors->first('addItemRows') }}</div>
                                @endif
                            </div>
                        @endif
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Select Lot <span class="text-danger">*</span></label>
                                    <div id="ruhi-gs-item-modal-anchor-lot" class="d-none" data-s2-value="{{ $selectedLotId ?? '' }}"></div>
                                    <input type="hidden" wire:model.defer="selectedLotId" id="ruhi-gs-item-modal-hidden-lot" class="@error('selectedLotId') is-invalid @enderror">
                                    <div wire:ignore class="w-100">
                                        <select
                                            id="ruhi-gs-item-modal-select-lot"
                                            class="form-control form-control-sm js-ruhi-master-select2 @error('selectedLotId') is-invalid @enderror"
                                            data-s2-hidden="#ruhi-gs-item-modal-hidden-lot"
                                            data-s2-anchor="#ruhi-gs-item-modal-anchor-lot"
                                            data-s2-placeholder="Select Lot"
                                            data-s2-dropdown-parent="modal"
                                            data-s2-dropdown-class="design-product-select2-dropdown"
                                            required
                                        >
                                            <option value="">Select Lot</option>
                                            @foreach($slotOptions as $slot)
                                                <option value="{{ $slot->id }}">{{ $slot->slot_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('selectedLotId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        @foreach($addItemRows as $i => $row)
                            <div class="row align-items-end mb-2">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Select Design</label>@endif
                                        <div id="ruhi-gs-item-add-anchor-{{ $i }}" class="d-none" data-s2-value="{{ $row['design_id'] ?? '' }}"></div>
                                        <input type="hidden" wire:model.defer="addItemRows.{{ $i }}.design_id" id="ruhi-gs-item-add-hidden-{{ $i }}">
                                        <div wire:ignore class="w-100">
                                            <select
                                                id="ruhi-gs-item-add-select-{{ $i }}"
                                                class="form-control form-control-sm js-ruhi-master-select2"
                                                data-s2-hidden="#ruhi-gs-item-add-hidden-{{ $i }}"
                                                data-s2-anchor="#ruhi-gs-item-add-anchor-{{ $i }}"
                                                data-s2-placeholder="Select Design"
                                                data-s2-dropdown-parent="modal"
                                                data-s2-dropdown-class="design-product-select2-dropdown"
                                            >
                                                <option value="">Select Design</option>
                                                @foreach($designs as $design)
                                                    <option value="{{ $design->id }}">{{ $design->design_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Qty <span class="text-danger">*</span></label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm" wire:model.live="addItemRows.{{ $i }}.design_qty">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Red</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addItemRows.'.$i.'.design_red_qty') is-invalid @enderror" wire:model.live="addItemRows.{{ $i }}.design_red_qty">
                                        @error('addItemRows.'.$i.'.design_red_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Red + Green</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addItemRows.'.$i.'.design_red_green_qty') is-invalid @enderror" wire:model.live="addItemRows.{{ $i }}.design_red_green_qty">
                                        @error('addItemRows.'.$i.'.design_red_green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Green</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addItemRows.'.$i.'.design_green_qty') is-invalid @enderror" wire:model.live="addItemRows.{{ $i }}.design_green_qty">
                                        @error('addItemRows.'.$i.'.design_green_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">White</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('addItemRows.'.$i.'.white_qty') is-invalid @enderror" wire:model.live="addItemRows.{{ $i }}.white_qty">
                                        @error('addItemRows.'.$i.'.white_qty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    @if(count($addItemRows) > 1)
                                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeItemRow({{ $i }})" title="Remove row">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" wire:click="addItemRow">
                            <i class="fa fa-plus mr-1"></i> Add More
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeAddItemModal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showEditModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item in Lot</h5>
                    <button type="button" class="close" wire:click="closeEditModal"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body py-3" style="max-height: 65vh; overflow-y: auto;">
                        @php
                            $editValidationMessages = collect([
                                $errors->get('editLotId'),
                                $errors->get('editDesignId'),
                                $errors->get('editDesignQty'),
                                $errors->get('editRedQty'),
                                $errors->get('editRedGreenQty'),
                                $errors->get('editGreenQty'),
                                $errors->get('editWhiteQty'),
                                $errors->get('editColorSplit'),
                            ])->flatten()->filter(fn ($msg) => is_string($msg) && $msg !== '')->unique()->values();
                        @endphp
                        @if($editValidationMessages->isNotEmpty())
                            <div class="alert alert-danger py-2 px-3 mb-2">
                                @foreach($editValidationMessages as $msg)
                                    <div>{{ $msg }}</div>
                                @endforeach
                            </div>
                        @endif
                        <div class="row align-items-end mb-2">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Lot <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm @error('editLotId') is-invalid @enderror" wire:model.defer="editLotId" required>
                                        <option value="">Select Lot</option>
                                        @foreach($slotOptions as $slot)
                                            <option value="{{ $slot->id }}">{{ $slot->slot_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('editLotId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Design <span class="text-danger">*</span></label>
                                    <div id="ruhi-gs-edit-design-anchor" class="d-none" data-s2-value="{{ $editDesignId }}"></div>
                                    <input type="hidden" wire:model.defer="editDesignId" id="ruhi-gs-edit-design-hidden" class="@error('editDesignId') is-invalid @enderror">
                                    <div wire:ignore class="w-100">
                                        <select
                                            id="ruhi-gs-edit-design-select"
                                            class="form-control form-control-sm js-ruhi-master-select2 @error('editDesignId') is-invalid @enderror"
                                            data-s2-hidden="#ruhi-gs-edit-design-hidden"
                                            data-s2-anchor="#ruhi-gs-edit-design-anchor"
                                            data-s2-placeholder="Select Design"
                                            data-s2-dropdown-parent="modal"
                                            data-s2-dropdown-class="design-product-select2-dropdown"
                                        >
                                            <option value="">Select Design</option>
                                            @foreach($designs as $design)
                                                <option value="{{ $design->id }}">{{ $design->design_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('editDesignId') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Qty <span class="text-danger">*</span></label>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm @error('editDesignQty') is-invalid @enderror" wire:model.live="editDesignQty" required>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Red</label>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm @error('editRedQty') is-invalid @enderror" wire:model.live="editRedQty">
                                    @error('editRedQty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Red + Green</label>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm @error('editRedGreenQty') is-invalid @enderror" value="{{ $editRedGreenQty }}" wire:input="setEditRedGreenQty($event.target.value)">
                                    @error('editRedGreenQty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Green</label>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm @error('editGreenQty') is-invalid @enderror" wire:model.live="editGreenQty">
                                    @error('editGreenQty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group mb-0">
                                    <label class="mb-1">White</label>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm @error('editWhiteQty') is-invalid @enderror" wire:model.live="editWhiteQty">
                                    @error('editWhiteQty') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeEditModal">Cancel</button>
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
        .modal .js-ruhi-master-select2 + .select2-container {
            width: 100% !important;
        }
    </style>
</div>
