<div>
    <button id="ruhiAddItemTypeTrigger" type="button" class="d-none" wire:click="openCreateModal"></button>

    <div class="card">
        <div class="card-body p-0">
            <div id="itemTypeTableToolbar" class="d-flex flex-wrap align-items-center justify-content-end gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="search-input-wrapper flex-grow-1" style="max-width: 18rem; min-width: 9rem; position: relative;">
                    <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control search-input" style="padding-left:34px;" placeholder="Search item category..." autocomplete="off">
                </div>
            </div>

            <div class="px-3 py-2 border-bottom">
                <div class="show_page_align">
                    <div class="dataTables_info">
                        @if($types->total() > 0)
                            Showing {{ $types->firstItem() }} to {{ $types->lastItem() }} of {{ $types->total() }}
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
                            {{ $types->links() }}
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
                            <th class="master-table-actions" style="min-width: 120px; width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($types as $t)
                            <tr class="{{ $t->deleted_at ? 'table-danger' : '' }}">
                                <td style="width: 70px; white-space: nowrap;">{{ ($types->firstItem() ?? 1) + $loop->index }}</td>
                                <td>
                                    {{ $t->item_type }}
                                    @if($t->deleted_at)
                                        <span class="badge badge-danger ml-1">Deleted</span>
                                    @endif
                                </td>
                                <td style="min-width: 120px;">
                                    <div class="action-div master-actions">
                                        <a href="#" wire:click.prevent="openEditModal({{ $t->id }})" class="action-icon" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if($t->deleted_at)
                                            @if((auth()->user()?->user_type ?? '') === 'systemuser')
                                                <button type="button" class="btn btn-link p-0 action-icon text-success" title="Revert" wire:click="restoreById({{ $t->id }})" wire:confirm="Restore this item category?">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            @endif
                                        @elseif(auth()->user()?->can('delete-ruhi-item-type'))
                                                <button type="button" class="btn btn-link p-0 action-icon text-danger" title="Delete" wire:click="deleteById({{ $t->id }})" wire:confirm="Delete this item category?">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No item categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 clearfix border-top">
            <div class="show_page_align">
                <div class="dataTables_info">
                    @if($types->total() > 0)
                        Showing {{ $types->firstItem() }} to {{ $types->lastItem() }} of {{ $types->total() }}
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
                        {{ $types->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showCreateModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add item category</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body py-3">
                        <div class="form-group mb-0">
                            <label class="mb-1">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" wire:model.defer="name" maxlength="255" required>
                            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
                    <h5 class="modal-title">Edit item category</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body py-3">
                        <div class="form-group mb-0">
                            <label class="mb-1">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('name') is-invalid @enderror" wire:model.defer="name" maxlength="255" required>
                            @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
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
