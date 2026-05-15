<div>
    <button id="ruhiAddDesignTrigger" type="button" class="d-none" wire:click="openCreateModal"></button>

    <div class="card">
        <div class="card-body p-0">
            <div id="designTableToolbar" class="d-flex flex-wrap align-items-center gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="d-flex flex-wrap align-items-center flex-grow-1" style="gap: .5rem; min-width: 0;">
                    <div class="search-input-wrapper flex-grow-1" style="max-width: 18rem; min-width: 9rem; position: relative;">
                        <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control search-input" style="padding-left:34px;" placeholder="Search design..." autocomplete="off">
                    </div>
                    <div id="ruhi-designs-anchor-category" class="d-none" data-s2-value="{{ $categoryId }}"></div>
                    <input type="hidden" wire:model.live="categoryId" id="ruhi-designs-hidden-category">
                    <div wire:ignore class="d-inline-block flex-shrink-0" style="width: 220px; min-width: 220px; max-width: 100%;">
                        <select
                            id="ruhi-designs-select-category"
                            class="form-control form-control-sm js-ruhi-master-select2"
                            style="width: 100%; min-width: 0;"
                            data-s2-hidden="#ruhi-designs-hidden-category"
                            data-s2-anchor="#ruhi-designs-anchor-category"
                            data-s2-placeholder="All Design Categories"
                            data-s2-allow-clear="true"
                            aria-label="Filter by design category"
                        >
                            <option value=""></option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm flex-shrink-0"
                        onclick="document.getElementById('ruhiAddDesignTrigger')?.click();"
                    >
                        <i class="fa fa-plus"></i> Add Design
                    </button>
                </div>
            </div>

            <div class="px-3 py-2 border-bottom">
                <div class="show_page_align">
                    <div class="dataTables_info">
                        @if($designs->total() > 0)
                            Showing {{ $designs->firstItem() }} to {{ $designs->lastItem() }} of {{ $designs->total() }}
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
                            {{ $designs->links() }}
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
                            <th>Image</th>
                            <th>Design Category</th>
                            <th class="master-table-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($designs as $design)
                            <tr class="{{ $design->deleted_at ? 'table-danger' : '' }}">
                                <td style="width: 70px; white-space: nowrap;">{{ ($designs->firstItem() ?? 1) + $loop->index }}</td>
                                <td>
                                    {{ $design->design_name }}
                                    @if($design->deleted_at)
                                        <span class="badge badge-danger ml-1">Deleted</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($design->photo1))
                                        <button
                                            type="button"
                                            class="btn btn-link p-0"
                                            wire:click="openImagePreviewById({{ $design->id }})"
                                            title="View large image"
                                        >
                                            <span style="position:relative; display:inline-block; width:40px; height:40px;">
                                                <img src="{{ asset($design->photo1) }}" alt="{{ $design->design_name }}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                                <span
                                                    aria-hidden="true"
                                                    style="position:absolute; right:2px; bottom:2px; width:16px; height:16px; display:flex; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); color:#fff; border-radius:3px; font-size:10px; line-height:1;"
                                                >
                                                    <i class="fa fa-search-plus" style="font-size:10px;"></i>
                                                </span>
                                            </span>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $design->category->category_name ?? ('#'.$design->category_id) }}</td>
                                <td>
                                    <div class="action-div master-actions">
                                        <a
                                            href="{{ route('masterapp.ruhi-designs.products', $design->id) }}"
                                            class="btn btn-outline-primary btn-sm mr-1 d-inline-flex align-items-center"
                                            style="border-radius: 4px; font-size: 11px; line-height: 1.2; font-weight: 600; white-space: nowrap; gap: 4px;"
                                            title="Design Product"
                                        >
                                            <i class="fa fa-plus"></i>
                                            <span>Item</span>
                                        </a>
                                        <a href="#" wire:click.prevent="openEditModal({{ $design->id }})" class="action-icon" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if($design->deleted_at)
                                            @if((auth()->user()?->user_type ?? '') === 'systemuser')
                                                <button type="button" class="btn btn-link p-0 action-icon text-success" title="Revert" wire:click="restoreById({{ $design->id }})" wire:confirm="Restore this design?">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            @endif
                                        @elseif(auth()->user()?->can('delete-ruhi-design'))
                                                <button type="button" class="btn btn-link p-0 action-icon text-danger" title="Delete" wire:click="deleteById({{ $design->id }})" wire:confirm="Delete this design?">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No designs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 clearfix border-top">
            <div class="show_page_align">
                <div class="dataTables_info">
                    @if($designs->total() > 0)
                        Showing {{ $designs->firstItem() }} to {{ $designs->lastItem() }} of {{ $designs->total() }}
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
                        {{ $designs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($showCreateModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Design</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="mb-1">Design Category <span class="text-danger">*</span></label>
                                    <div id="ruhi-design-create-modal-anchor-category" class="d-none" data-s2-value="{{ $category_id }}"></div>
                                    <input type="hidden" wire:model.live="category_id" id="ruhi-design-create-modal-hidden-category">
                                    <div wire:ignore class="d-inline-block w-100" style="min-width: 0;">
                                        <select
                                            id="ruhi-design-modal-create-select-category"
                                            class="form-control form-control-sm js-ruhi-master-select2"
                                            style="width: 100%; min-width: 100%;"
                                            data-s2-hidden="#ruhi-design-create-modal-hidden-category"
                                            data-s2-anchor="#ruhi-design-create-modal-anchor-category"
                                            data-s2-placeholder="Select Category"
                                            data-s2-allow-clear="true"
                                            data-s2-dropdown-parent="modal"
                                            data-s2-dropdown-class="design-product-select2-dropdown"
                                        >
                                            <option value=""></option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('design_name') is-invalid @enderror" wire:model.defer="design_name" maxlength="100" required>
                                    @error('design_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-1">Image</label><br>
                                    <label class="btn btn-outline-primary btn-sm mb-0">
                                        <i class="fa fa-upload mr-1"></i> Choose
                                        <input type="file" class="d-none" wire:model="photo1" accept="image/*">
                                    </label>
                                    @error('photo1') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        @if($photo1)
                            <div class="mt-1">
                                <img src="{{ $photo1->temporaryUrl() }}" alt="Preview" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModals">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($showEditModal)
    <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Design</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="mb-1">Design Category <span class="text-danger">*</span></label>
                                    <div id="ruhi-design-edit-modal-anchor-category" class="d-none" data-s2-value="{{ $category_id }}"></div>
                                    <input type="hidden" wire:model.live="category_id" id="ruhi-design-edit-modal-hidden-category">
                                    <div wire:ignore class="d-inline-block w-100" style="min-width: 0;">
                                        <select
                                            id="ruhi-design-modal-edit-select-category"
                                            class="form-control form-control-sm js-ruhi-master-select2"
                                            style="width: 100%; min-width: 100%;"
                                            data-s2-hidden="#ruhi-design-edit-modal-hidden-category"
                                            data-s2-anchor="#ruhi-design-edit-modal-anchor-category"
                                            data-s2-placeholder="Select Category"
                                            data-s2-allow-clear="true"
                                            data-s2-dropdown-parent="modal"
                                            data-s2-dropdown-class="design-product-select2-dropdown"
                                        >
                                            <option value=""></option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('category_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('design_name') is-invalid @enderror" wire:model.defer="design_name" maxlength="100" required>
                                    @error('design_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-1">Image</label><br>
                                    <label class="btn btn-outline-primary btn-sm mb-0">
                                        <i class="fa fa-upload mr-1"></i> Change
                                        <input type="file" class="d-none" wire:model="photo1" accept="image/*">
                                    </label>
                                    @error('photo1') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="mt-1 d-flex align-items-center" style="gap:10px;">
                            @if($photo1)
                                <img src="{{ $photo1->temporaryUrl() }}" alt="Preview" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                            @elseif($existingPhoto1)
                                <img src="{{ asset($existingPhoto1) }}" alt="Current image" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                            @endif
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
    @endif

    <div class="modal fade {{ $showImagePreviewModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.65)">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Design Image{{ $previewImageName !== '' ? ' - '.$previewImageName : '' }}</h5>
                    <button type="button" class="close" wire:click="closeImagePreview"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    @if($previewImageUrl !== '')
                        <img
                            src="{{ asset($previewImageUrl) }}"
                            alt="{{ $previewImageName !== '' ? $previewImageName : 'Design image' }}"
                            style="max-width:100%; max-height:70vh; object-fit:contain; border:1px solid #dee2e6; border-radius:6px;"
                        >
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeImagePreview">Close</button>
                </div>
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

