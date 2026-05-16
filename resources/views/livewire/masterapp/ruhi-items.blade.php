<div>
    <button id="ruhiAddItemTrigger" type="button" class="d-none" wire:click="openCreateModal"></button>

    <div class="card">
        <div class="card-body p-0">
            <div id="itemTableToolbar" class="d-flex flex-wrap align-items-center gap-2 px-3 pt-3 pb-2 border-bottom">
                <div class="d-flex flex-wrap align-items-center flex-grow-1" style="gap: .5rem; min-width: 0;">
                    <div class="search-input-wrapper flex-grow-1" style="max-width: 18rem; min-width: 9rem; position: relative;">
                        <i class="fa fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none;"></i>
                        <input type="search" wire:model.live.debounce.500ms="search" class="form-control search-input" style="padding-left:34px;" placeholder="Search item or design..." autocomplete="off">
                    </div>
                    <div id="ruhi-items-anchor-item-type" class="d-none" data-s2-value="{{ $itemTypeId }}"></div>
                    <input type="hidden" wire:model.live="itemTypeId" id="ruhi-items-hidden-item-type">
                    <div wire:ignore class="d-inline-block flex-shrink-0" style="width: 220px; min-width: 220px; max-width: 100%;">
                        <select
                            id="ruhi-items-select-item-type"
                            class="form-control form-control-sm js-ruhi-master-select2"
                            style="width: 100%; min-width: 0;"
                            data-s2-hidden="#ruhi-items-hidden-item-type"
                            data-s2-anchor="#ruhi-items-anchor-item-type"
                            data-s2-placeholder="All Item Categories"
                            data-s2-allow-clear="true"
                            aria-label="Filter by item category"
                        >
                            <option value=""></option>
                            @foreach($itemTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->item_type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm flex-shrink-0"
                        onclick="document.getElementById('ruhiAddItemTrigger')?.click();"
                    >
                        <i class="fa fa-plus"></i> Add Item
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 70px; white-space: nowrap;">S. No.</th>
                            <th>Name</th>
                            <th class="item-designs-col">Designs</th>
                            <th>Weight</th>
                            <th>Image</th>
                            <th>Type</th>
                            <th style="width: 120px; white-space: nowrap;">K-Stone Count</th>
                            <th class="master-table-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr class="{{ $item->deleted_at ? 'table-danger' : '' }}">
                                <td style="width: 70px; white-space: nowrap;">{{ ($items->firstItem() ?? 1) + $loop->index }}</td>
                                <td>
                                    {{ $item->product_name }}
                                    @if($item->deleted_at)
                                        <span class="badge badge-danger ml-1">Deleted</span>
                                    @endif
                                </td>
                                <td class="item-designs-cell">
                                    @php
                                        $designsUsed = $item->designProducts
                                            ->filter(fn ($row) => $row->design !== null)
                                            ->unique('design_id')
                                            ->map(fn ($row) => $row->design);
                                        $designsVisibleLimit = 4;
                                        $designsTotalCount = $designsUsed->count();
                                        $designsHiddenCount = max(0, $designsTotalCount - $designsVisibleLimit);
                                        $designsHiddenNames = $designsUsed->skip($designsVisibleLimit)->pluck('design_name')->implode(', ');
                                        $designSearchTerm = trim($search);
                                        $designNameMatchesSearch = static function ($design) use ($designSearchTerm): bool {
                                            if ($designSearchTerm === '') {
                                                return false;
                                            }

                                            return stripos((string) $design->design_name, $designSearchTerm) !== false;
                                        };
                                    @endphp
                                    @if($designsUsed->isEmpty())
                                        <span class="item-designs-empty text-muted">—</span>
                                    @else
                                        <div
                                            class="item-designs-wrap{{ $designsHiddenCount > 0 ? ' item-designs-wrap--collapsible' : '' }}"
                                            x-data="{ expanded: false }"
                                        >
                                            @if($designsHiddenCount > 0)
                                                <button
                                                    type="button"
                                                    class="item-designs-toggle"
                                                    title="{{ $designsHiddenNames }}"
                                                    aria-label="Toggle all {{ $designsTotalCount }} designs"
                                                    @click.prevent="expanded = !expanded"
                                                >
                                                    <span x-show="!expanded">+{{ $designsTotalCount }}</span>
                                                    <span x-show="expanded" x-cloak>−{{ $designsTotalCount }}</span>
                                                </button>
                                            @endif
                                            <div class="item-designs-list" aria-label="Designs using this item">
                                            @foreach($designsUsed as $design)
                                                @php
                                                    $designMatchesSearch = $designNameMatchesSearch($design);
                                                    $designNameHtml = e($design->design_name);
                                                    if ($designMatchesSearch) {
                                                        $designNameHtml = preg_replace(
                                                            '/(' . preg_quote($designSearchTerm, '/') . ')/iu',
                                                            '<mark class="item-design-match-text">$1</mark>',
                                                            $designNameHtml
                                                        );
                                                    }
                                                @endphp
                                                <a
                                                    href="{{ route('masterapp.ruhi-designs.products', $design->id) }}"
                                                    class="item-design-chip{{ $design->trashed() ? ' item-design-chip--deleted' : '' }}{{ $designMatchesSearch ? ' item-design-chip--match' : '' }}"
                                                    title="{{ $design->trashed() ? 'Deleted design — ' : '' }}Open {{ $design->design_name }}"
                                                    @if($designsHiddenCount > 0 && $loop->index >= $designsVisibleLimit)
                                                        x-show="expanded"
                                                        x-cloak
                                                    @endif
                                                >{!! $designNameHtml !!}</a>
                                            @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $item->weight }}</td>
                                <td>
                                    @if(!empty($item->photo1))
                                        <button
                                            type="button"
                                            class="btn btn-link p-0"
                                            wire:click="openImagePreviewById({{ $item->id }})"
                                            title="View large image"
                                        >
                                            <span style="position:relative; display:inline-block; width:40px; height:40px;">
                                                <img src="{{ asset($item->photo1) }}" alt="{{ $item->product_name }}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
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
                                <td>{{ $item->itemType->item_type ?? ('#'.$item->product_type) }}</td>
                                <td style="width: 120px; white-space: nowrap;">{{ $item->item_kstones_count ?? 0 }}</td>
                                <td>
                                    <div class="action-div master-actions">
                                        <a
                                            href="{{ route('masterapp.ruhi-items.collet-k-stones', $item->id) }}"
                                            class="btn btn-outline-success btn-sm mr-1 d-inline-flex align-items-center"
                                            style="border-radius: 4px; font-size: 11px; line-height: 1.2; font-weight: 600; white-space: nowrap; gap: 4px;"
                                            title="Manage Collet K-Stones"
                                        >
                                            <i class="fa fa-plus"></i>
                                            <span>K-Stone</span>
                                        </a>
                                        <a href="#" wire:click.prevent="openEditModal({{ $item->id }})" class="action-icon" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if($item->deleted_at)
                                            @if((auth()->user()?->user_type ?? '') === 'systemuser')
                                                <button type="button" class="btn btn-link p-0 action-icon text-success" title="Revert" wire:click="restoreById({{ $item->id }})" wire:confirm="Restore this item?">
                                                    <i class="fa fa-undo"></i>
                                                </button>
                                            @endif
                                        @elseif(auth()->user()?->can('delete-ruhi-item'))
                                                <button type="button" class="btn btn-link p-0 action-icon text-danger" title="Delete" wire:click="deleteById({{ $item->id }})" wire:confirm="Delete this item?">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No items found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 clearfix border-top">
            <div class="show_page_align">
                <div class="dataTables_info">
                    @if($items->total() > 0)
                        Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }}
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
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade {{ $showCreateModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Item</h5>
                    <button type="button" class="close" wire:click="closeImagePreview"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveCreate">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('product_name') is-invalid @enderror" wire:model.defer="product_name" maxlength="100" required>
                                    @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-1">Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm @error('product_type') is-invalid @enderror" wire:model.defer="product_type" required>
                                        <option value="">Select Type</option>
                                        @foreach($itemTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->item_type }}</option>
                                        @endforeach
                                    </select>
                                    @error('product_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="mb-1">Weight</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm @error('weight') is-invalid @enderror" wire:model.defer="weight">
                                    @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-1">Photo</label><br>
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

    <div class="modal fade {{ $showEditModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5)">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Item</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <form wire:submit.prevent="saveEdit">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm @error('product_name') is-invalid @enderror" wire:model.defer="product_name" maxlength="100" required>
                                    @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-1">Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm @error('product_type') is-invalid @enderror" wire:model.defer="product_type" required>
                                        <option value="">Select Type</option>
                                        @foreach($itemTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->item_type }}</option>
                                        @endforeach
                                    </select>
                                    @error('product_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="mb-1">Weight</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm @error('weight') is-invalid @enderror" wire:model.defer="weight">
                                    @error('weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="mb-1">Photo</label><br>
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

    <div class="modal fade {{ $showImagePreviewModal ? 'show d-block' : '' }}" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.65)">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Item Image{{ $previewImageName !== '' ? ' - '.$previewImageName : '' }}</h5>
                    <button type="button" class="close" wire:click="closeModals"><span>&times;</span></button>
                </div>
                <div class="modal-body text-center">
                    @if($previewImageUrl !== '')
                        <img
                            src="{{ asset($previewImageUrl) }}"
                            alt="{{ $previewImageName !== '' ? $previewImageName : 'Item image' }}"
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
        .item-designs-col {
            min-width: 12rem;
            width: 22%;
        }

        .item-designs-cell {
            vertical-align: top;
            padding-top: 0.45rem;
            padding-bottom: 0.45rem;
        }

        .item-designs-wrap {
            position: relative;
            min-width: 8rem;
            max-width: 16rem;
        }

        .item-designs-wrap--collapsible {
            padding-right: 2.5rem;
        }

        .item-designs-toggle {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.75rem;
            height: 1.15rem;
            padding: 0 0.25rem;
            font-size: 0.6875rem;
            font-weight: 700;
            line-height: 1;
            color: #495057;
            background: #dee2e6;
            border: 1px solid #ced4da;
            border-radius: 0.2rem;
            cursor: pointer;
            font-family: inherit;
        }

        .item-designs-toggle:hover,
        .item-designs-toggle:focus {
            color: #212529;
            background: #ced4da;
            border-color: #adb5bd;
            outline: none;
        }

        .item-designs-list {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.3rem;
            line-height: 1.35;
        }

        .item-design-chip {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 0.2rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.35;
            color: #495057;
            background: #e9ecef;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            text-decoration: none;
            text-align: left;
            white-space: normal;
            word-break: break-word;
            transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
        }

        a.item-design-chip:hover,
        a.item-design-chip:focus {
            color: #212529;
            background: #dee2e6;
            border-color: #adb5bd;
            text-decoration: none;
        }

        .item-design-chip--deleted {
            color: #6c757d;
            background: #e2e3e5;
            border-color: #ced4da;
        }

        a.item-design-chip--deleted:hover,
        a.item-design-chip--deleted:focus {
            color: #495057;
            background: #d6d8db;
            border-color: #adb5bd;
        }

        .item-design-chip--match {
            color: #004085;
            background: #fff3cd;
            border-color: #ffc107;
            box-shadow: 0 0 0 1px rgba(255, 193, 7, 0.35);
        }

        a.item-design-chip--match:hover,
        a.item-design-chip--match:focus {
            color: #002752;
            background: #ffe69c;
            border-color: #e0a800;
        }

        .item-design-chip--match.item-design-chip--deleted {
            color: #495057;
            background: #fff3cd;
            border-color: #ffc107;
        }

        .item-design-match-text {
            padding: 0 0.1em;
            color: inherit;
            background: rgba(255, 193, 7, 0.55);
            border-radius: 0.15rem;
            font-weight: 700;
        }

        [x-cloak] {
            display: none !important;
        }

        .item-designs-empty {
            font-size: 0.875rem;
        }
    </style>
</div>
