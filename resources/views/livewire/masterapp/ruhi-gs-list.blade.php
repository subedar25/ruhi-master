<div>
    <button id="ruhiAddGsTrigger" type="button" class="d-none" wire:click="openCreateModal"></button>

    <div class="card">
        <div class="card-body p-0">
            <div id="gsTableToolbar" class="d-flex flex-wrap align-items-center justify-content-end gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="search-input-wrapper flex-grow-1" style="max-width: 18rem; min-width: 9rem; position: relative;">
                    <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control search-input" style="padding-left:34px;" placeholder="Search GS..." autocomplete="off">
                </div>
            </div>

            <div class="px-3 py-2 border-bottom">
                <div class="show_page_align">
                    <div class="dataTables_info">
                        @if($gss->total() > 0)
                            Showing {{ $gss->firstItem() }} to {{ $gss->lastItem() }} of {{ $gss->total() }}
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
                            {{ $gss->links() }}
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
                            <th class="master-table-actions" style="min-width: 170px; width: 170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gss as $gs)
                            <tr>
                                <td style="width: 70px; white-space: nowrap;">{{ ($gss->firstItem() ?? 1) + $loop->index }}</td>
                                <td>
                                    {{ $gs->name }}
                                </td>
                                <td style="min-width: 170px;">
                                    <div class="action-div master-actions">
                                        <a
                                            href="{{ route('masterapp.ruhi-gs.lots', $gs->id) }}"
                                            class="btn btn-outline-success btn-sm mr-1 d-inline-flex align-items-center"
                                            style="border-radius: 4px; font-size: 11px; line-height: 1.2; font-weight: 600; white-space: nowrap; gap: 4px; padding: 4px 8px;"
                                            title="+Add Lot"
                                        >
                                            <i class="fa fa-plus"></i>
                                            <span>Add Lot</span>
                                        </a>
                                        <a href="#" wire:click.prevent="openEditModal({{ $gs->id }})" class="action-icon" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if(auth()->user()?->can('delete-ruhi-gs'))
                                            <button type="button" class="btn btn-link p-0 action-icon text-danger" title="Delete permanently" wire:click="deleteById({{ $gs->id }})" wire:confirm="Permanently delete this GS, all its lots, and all lot items? This cannot be undone.">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No GS found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 clearfix border-top">
            <div class="show_page_align">
                <div class="dataTables_info">
                    @if($gss->total() > 0)
                        Showing {{ $gss->firstItem() }} to {{ $gss->lastItem() }} of {{ $gss->total() }}
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
                        {{ $gss->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showCreateModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add GS</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label class="mb-1">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" wire:model.defer="name" maxlength="255" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit GS</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label class="mb-1">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" wire:model.defer="name" maxlength="255" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
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

