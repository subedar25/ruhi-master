<div>
    <button id="designProductKstoneAddTrigger" type="button" class="d-none" wire:click="openCreateModal"></button>

    <div class="card">
        <div class="card-body p-0">
            <div id="designProductKstoneTableToolbar" class="d-flex flex-wrap align-items-center gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="d-flex flex-wrap align-items-center flex-grow-1" style="gap: .5rem; min-width: 0;">
                    <div class="search-input-wrapper flex-grow-1" style="max-width: 18rem; min-width: 9rem; position: relative;">
                        <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control search-input" style="padding-left:34px;" placeholder="Search..." autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-primary btn-sm flex-shrink-0" wire:click="openCreateModal">
                        <i class="fa fa-plus mr-1"></i> Add
                    </button>
                </div>
            </div>


            <div class="table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px;">S No.</th>
                            <th>Item Name</th>
                            <th>KStone Name</th>
                            <th style="width:100px;">Qty</th>
                            <th style="width:80px;">Red</th>
                            <th colspan="2" style="width:160px;">Red + Green</th>
                            <th style="width:80px;">Green</th>
                            <th style="width:80px;">White</th>
                        </tr>
                        <tr>
                            <th colspan="5"></th>
                            <th style="width:80px;">Red</th>
                            <th style="width:80px;">Green</th>
                            <th colspan="2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ ($rows->firstItem() ?? 1) + $loop->index }}</td>
                                <td>{{ $row->product->product_name ?? ('#'.$row->product_id) }}</td>
                                <td>{{ $row->kstone->name ?? ('#'.$row->kstone_id) }}</td>
                                <td>{{ $row->kstone_quantity }}</td>
                                <td>{{ $row->red }}</td>
                                <td>{{ $row->rg_red }}</td>
                                <td>{{ $row->rg_green }}</td>
                                <td>{{ $row->green }}</td>
                                <td>{{ $row->white }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No K-Stone records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 clearfix border-top design-product-section-footer">
            <div class="show_page_align invoice-livewire-pagination">
                <div class="dataTables_info">
                    @if($rows->total() > 0)
                        Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }}
                    @else
                        Nothing to show
                    @endif
                </div>
                <div class="length_pagination d-flex flex-wrap align-items-center">
                    <div class="dataTables_length">
                        <label class="mb-0 d-inline-flex align-items-center" style="gap: .35rem;">
                            Show
                            <select wire:model.live="perPage" class="form-control form-control-sm d-inline-block" style="width:auto; min-width:4.5rem;">
                                @foreach([20, 10, 15, 25, 50, 100] as $n)
                                    <option value="{{ $n }}">{{ $n }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="dataTables_paginate paging_simple_numbers pagination-links mb-0" style="max-width: 100%; overflow-x: auto; white-space: nowrap;">
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
                    <h5 class="modal-title">Add K-Stone</h5>
                    <button type="button" class="close" wire:click="closeCreateModal"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body py-3" style="max-height: 65vh; overflow-y: auto;">
                        <div class="mb-2">
                            <label class="mb-1">Item Name</label>
                            <input type="text" class="form-control form-control-sm" value="{{ $product->product_name }}" readonly>
                        </div>
                        @foreach($createRows as $i => $row)
                            <div class="row align-items-end mb-2">
                                <div class="col-md-3">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">KStone Name</label>@endif
                                        <input type="text" class="form-control form-control-sm" value="{{ $row['kstone_name'] }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">Qty</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('createRows.'.$i.'.kstone_quantity') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.kstone_quantity">
                                        @error('createRows.'.$i.'.kstone_quantity') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1 text-danger">Red</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('createRows.'.$i.'.red') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.red">
                                        @error('createRows.'.$i.'.red') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    @if($i === 0)<label class="mb-1 text-success d-block">Red + Green</label>@endif
                                    <div class="d-flex" style="gap: 6px;">
                                        <div style="flex:1;">
                                            <input type="number" min="0" step="1" class="form-control form-control-sm @error('createRows.'.$i.'.rg_red') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.rg_red" placeholder="Red">
                                            @error('createRows.'.$i.'.rg_red') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                        <div style="flex:1;">
                                            <input type="number" min="0" step="1" class="form-control form-control-sm @error('createRows.'.$i.'.rg_green') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.rg_green" placeholder="Green">
                                            @error('createRows.'.$i.'.rg_green') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1 text-success">Green</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('createRows.'.$i.'.green') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.green">
                                        @error('createRows.'.$i.'.green') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group mb-0">
                                        @if($i === 0)<label class="mb-1">White</label>@endif
                                        <input type="number" min="0" step="1" class="form-control form-control-sm @error('createRows.'.$i.'.white') is-invalid @enderror" wire:model.live="createRows.{{ $i }}.white">
                                        @error('createRows.'.$i.'.white') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeCreateModal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
