<div>
    <button id="ruhiAddKstoneTrigger" type="button" class="d-none" wire:click="openCreateModal"></button>

    <div class="card">
        <div class="card-body p-0">
            <div id="kstoneTableToolbar" class="d-flex flex-wrap align-items-center gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="d-flex flex-wrap align-items-center flex-grow-1" style="gap: .5rem; min-width: 0;">
                    <div class="search-input-wrapper flex-grow-1" style="max-width: 18rem; min-width: 9rem; position: relative;">
                        <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control search-input" style="padding-left:34px;" placeholder="Search K Stone..." autocomplete="off">
                    </div>
                    <div id="ruhi-kstones-anchor-color-filter" class="d-none" data-s2-value="{{ $colorFilterId }}"></div>
                    <input type="hidden" wire:model.live="colorFilterId" id="ruhi-kstones-hidden-color-filter">
                    <div wire:ignore class="d-inline-block flex-shrink-0" style="width: 220px; min-width: 220px; max-width: 100%;">
                        <select
                            id="ruhi-kstones-select-color-filter"
                            class="form-control form-control-sm js-ruhi-master-select2"
                            style="width: 100%; min-width: 0;"
                            data-s2-hidden="#ruhi-kstones-hidden-color-filter"
                            data-s2-anchor="#ruhi-kstones-anchor-color-filter"
                            data-s2-placeholder="All Colors"
                            data-s2-allow-clear="true"
                            aria-label="Filter by color"
                        >
                            <option value=""></option>
                            @foreach($colors as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm flex-shrink-0"
                        onclick="document.getElementById('ruhiAddKstoneTrigger')?.click();"
                    >
                        <i class="fa fa-plus"></i> Add K Stone
                    </button>
                </div>
            </div>

            <div class="px-3 py-2 border-bottom">
                <div class="show_page_align">
                    <div class="dataTables_info">
                        @if($kstones->total() > 0)
                            Showing {{ $kstones->firstItem() }} to {{ $kstones->lastItem() }} of {{ $kstones->total() }}
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
                            {{ $kstones->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px; white-space: nowrap;">S. No.</th>
                            <th>Name</th>
                            <th>Color</th>
                            <th>Qty</th>
                            <th>Stone wt.</th>
                            <th>Die wt.</th>
                            <th class="master-table-actions" style="min-width: 120px; width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kstones as $ks)
                            <tr class="{{ $ks->deleted_at ? 'table-danger' : '' }}">
                                <td style="width: 70px; white-space: nowrap;">{{ ($kstones->firstItem() ?? 1) + $loop->index }}</td>
                                <td>
                                    {{ $ks->name }}
                                    @if($ks->deleted_at)
                                        <span class="badge badge-danger ml-1">Deleted</span>
                                    @endif
                                </td>
                                <td>{{ $ks->color->name ?? ('#'.$ks->color_id) }}</td>
                                <td>{{ $ks->quantity }}</td>
                                <td>{{ number_format((float) $ks->stoneweight, 3) }}</td>
                                <td>{{ number_format((float) $ks->dieweight, 3) }}</td>
                                <td style="min-width: 120px;">
                                    <div class="action-div master-actions">
                                        <a href="#" wire:click.prevent="openEditModal({{ $ks->id }})" class="action-icon" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if($ks->deleted_at)
                                            @if((auth()->user()?->user_type ?? '') === 'systemuser')
                                                <button type="button" class="btn btn-link p-0 action-icon text-success" title="Revert" wire:click="restoreById({{ $ks->id }})" wire:confirm="Restore this K Stone?">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            @endif
                                        @elseif(auth()->user()?->can('delete-ruhi-kstone'))
                                                <button type="button" class="btn btn-link p-0 action-icon text-danger" title="Delete" wire:click="deleteById({{ $ks->id }})" wire:confirm="Delete this K Stone?">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No K Stone found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 clearfix border-top">
            <div class="show_page_align">
                <div class="dataTables_info">
                    @if($kstones->total() > 0)
                        Showing {{ $kstones->firstItem() }} to {{ $kstones->lastItem() }} of {{ $kstones->total() }}
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
                        {{ $kstones->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showCreateModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add K Stone</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" wire:model.defer="name" maxlength="100" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Color <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm @error('color_id') is-invalid @enderror" wire:model.defer="color_id" required>
                                        <option value="">Select color</option>
                                        @foreach($colors as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('color_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm @error('quantity') is-invalid @enderror" wire:model.defer="quantity">
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Stone weight</label>
                                    <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('stoneweight') is-invalid @enderror" wire:model.defer="stoneweight">
                                    @error('stoneweight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Die weight</label>
                                    <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('dieweight') is-invalid @enderror" wire:model.defer="dieweight">
                                    @error('dieweight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showEditModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit K Stone</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" wire:model.defer="name" maxlength="100" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Color <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm @error('color_id') is-invalid @enderror" wire:model.defer="color_id" required>
                                        <option value="">Select color</option>
                                        @foreach($colors as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('color_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Quantity</label>
                                    <input type="number" min="0" step="1" class="form-control form-control-sm @error('quantity') is-invalid @enderror" wire:model.defer="quantity">
                                    @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Stone weight</label>
                                    <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('stoneweight') is-invalid @enderror" wire:model.defer="stoneweight">
                                    @error('stoneweight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-2">
                                    <label class="mb-1">Die weight</label>
                                    <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('dieweight') is-invalid @enderror" wire:model.defer="dieweight">
                                    @error('dieweight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
