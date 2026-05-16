<div>
    <button id="ruhiColletKstoneAddTrigger" type="button" class="d-none" wire:click="openCreateModal"></button>

    <div class="card">
        <div class="card-body p-0">
            <div id="colletKstoneTableToolbar" class="d-flex flex-wrap align-items-center gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="d-flex flex-wrap align-items-center flex-grow-1" style="gap: .5rem; min-width: 0;">
                    <div class="search-input-wrapper flex-grow-1" style="max-width: 18rem; min-width: 9rem; position: relative;">
                        <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control search-input" style="padding-left:34px;" placeholder="Search..." autocomplete="off">
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm flex-shrink-0"
                        onclick="document.getElementById('ruhiColletKstoneAddTrigger')?.click();"
                    >
                        <i class="fas fa-plus mr-1"></i> Add K-Stone
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
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px; white-space: nowrap;">S. No.</th>
                            <th>Collet Name</th>
                            <th>K-Stone</th>
                            <th>K-Stone Quantity</th>
                            <th>K-Stone Weight</th>
                            <th>K-Stone Die</th>
                            <th class="master-table-actions" style="min-width: 100px; width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td style="width: 70px; white-space: nowrap;">{{ ($rows->firstItem() ?? 1) + $loop->index }}</td>
                                <td>{{ $row->product->product_name ?? $product->product_name }}</td>
                                <td>{{ $row->kstone->name ?? ('#'.$row->kstone_id) }}</td>
                                <td>{{ $row->kstone_quantity }}</td>
                                <td>{{ number_format((float) $row->kstone_weight, 3) }}</td>
                                <td>{{ number_format((float) $row->kstone_dieweight, 3) }}</td>
                                <td style="min-width: 100px;">
                                    <div class="action-div master-actions">
                                        <a href="#" wire:click.prevent="openEditModal({{ $row->id }})" class="action-icon" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if(auth()->user()?->can('delete-ruhi-collet-kstone'))
                                            <button type="button" class="btn btn-link p-0 action-icon text-danger" title="Delete" wire:click="deleteById({{ $row->id }})" wire:confirm="Remove this K-Stone line?">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No K-Stone lines for this item.</td>
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

    <div class="modal fade {{ $showCreateModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Add K-Stone</h5>
                        <p class="text-muted small mb-0">Item ID <strong>{{ $productId }}</strong> ({{ $product->product_name }})</p>
                    </div>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body py-3" style="max-height: 60vh; overflow-y: auto;">
                        @foreach($createRows as $i => $createRow)
                            <div class="row align-items-end mb-2" wire:key="create-row-{{ $i }}">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        @if($i === 0)
                                            <label class="mb-1">K-Stone <span class="text-danger">*</span></label>
                                        @endif
                                        <select class="form-control form-control-sm @error('createRows.'.$i.'.kstone_id') is-invalid @enderror" wire:model.defer="createRows.{{ $i }}.kstone_id" required>
                                            <option value="">Select K-Stone</option>
                                            @foreach($kstones as $ks)
                                                <option value="{{ $ks->id }}">{{ $ks->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('createRows.'.$i.'.kstone_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        @if($i === 0)
                                            <label class="mb-1">Quantity <span class="text-danger">*</span></label>
                                        @endif
                                        <input type="number" min="1" step="1" class="form-control form-control-sm @error('createRows.'.$i.'.kstone_quantity') is-invalid @enderror" wire:model.defer="createRows.{{ $i }}.kstone_quantity" required>
                                        @error('createRows.'.$i.'.kstone_quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        @if($i === 0)
                                            <label class="mb-1">K-Stone weight</label>
                                        @endif
                                        <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('createRows.'.$i.'.kstone_weight') is-invalid @enderror" wire:model.defer="createRows.{{ $i }}.kstone_weight">
                                        @error('createRows.'.$i.'.kstone_weight') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-0">
                                        @if($i === 0)
                                            <label class="mb-1">K-Stone die</label>
                                        @endif
                                        <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('createRows.'.$i.'.kstone_dieweight') is-invalid @enderror" wire:model.defer="createRows.{{ $i }}.kstone_dieweight">
                                        @error('createRows.'.$i.'.kstone_dieweight') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1 text-right">
                                    @if(count($createRows) > 1)
                                        <button type="button" class="btn btn-outline-danger btn-sm" wire:click="removeCreateRow({{ $i }})" title="Remove row">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" wire:click="addCreateRow">Add More</button>
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
                    <div>
                        <h5 class="modal-title mb-1">Edit K-Stone</h5>
                        <p class="text-muted small mb-0">Item ID <strong>{{ $productId }}</strong> ({{ $product->product_name }})</p>
                    </div>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body py-3">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label class="mb-1">K-Stone <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm @error('kstone_id') is-invalid @enderror" wire:model.defer="kstone_id" required>
                                        <option value="">Select K-Stone</option>
                                        @foreach($kstones as $ks)
                                            <option value="{{ $ks->id }}">{{ $ks->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('kstone_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label class="mb-1">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" min="1" step="1" class="form-control form-control-sm @error('kstone_quantity') is-invalid @enderror" wire:model.defer="kstone_quantity" required>
                                    @error('kstone_quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="mb-1">K-Stone weight</label>
                                    <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('kstone_weight') is-invalid @enderror" wire:model.defer="kstone_weight">
                                    @error('kstone_weight') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label class="mb-1">K-Stone die</label>
                                    <input type="number" step="0.001" min="0" class="form-control form-control-sm @error('kstone_dieweight') is-invalid @enderror" wire:model.defer="kstone_dieweight">
                                    @error('kstone_dieweight') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
