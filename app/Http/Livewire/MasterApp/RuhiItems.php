<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\File\Services\FileManagementService;
use App\Core\RuhiItems\Services\RuhiItemService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class RuhiItems extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $itemTypeId = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showImagePreviewModal = false;
    public ?int $editId = null;
    public string $product_name = '';
    public string $product_type = '';
    public string $weight = '0.00';
    public $photo1 = null;
    public ?string $existingPhoto1 = null;
    public string $previewImageUrl = '';
    public string $previewImageName = '';
    private ?RuhiItemService $ruhiItems = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'itemTypeId' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function paginationView(): string
    {
        return 'livewire.invoice-pagination';
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedItemTypeId(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $allowed = [20, 10, 15, 25, 50, 100];
        if (! in_array((int) $this->perPage, $allowed, true)) {
            $this->perPage = 20;
        }
        $this->resetPage();
    }

    public function deleteById(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-item') ?? false), 403);

        $this->service()->softDeleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'Item deleted successfully.');
        $this->resetPage();
    }

    public function restoreById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);

        $this->service()->restoreById($id);
        $this->dispatch('formResult', type: 'success', message: 'Item restored successfully.');
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        $item = $this->service()->findById($id);
        $this->resetValidation();
        $this->editId = $item->id;
        $this->product_name = (string) $item->product_name;
        $this->product_type = (string) $item->product_type;
        $this->weight = number_format((float) $item->weight, 2, '.', '');
        $this->existingPhoto1 = $item->photo1;
        $this->photo1 = null;
        $this->showEditModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showImagePreviewModal = false;
        $this->previewImageUrl = '';
        $this->previewImageName = '';
        $this->resetForm();
    }

    public function openImagePreviewById(int $id): void
    {
        $item = $this->service()->findById($id);
        if (trim((string) $item->photo1) === '') {
            return;
        }

        $this->previewImageUrl = (string) $item->photo1;
        $this->previewImageName = (string) $item->product_name;
        $this->showImagePreviewModal = true;
    }

    public function closeImagePreview(): void
    {
        $this->showImagePreviewModal = false;
        $this->previewImageUrl = '';
        $this->previewImageName = '';
    }

    public function saveCreate(): void
    {
        $validated = $this->validate([
            'product_type' => ['required', 'integer', 'exists:r_item_type,id'],
            'weight' => ['nullable', 'numeric', 'decimal:0,2'],
            'photo1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'product_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('r_product', 'product_name')->whereNull('deleted_at'),
            ],
        ], [
            'product_name.unique' => 'Item name already exists.',
        ]);

        $photoPath = '';
        if ($this->photo1) {
            $fileService = app(FileManagementService::class);
            $photoPath = $fileService->upload($this->photo1, 'ruhi-products');
        }

        $this->service()->create([
            'product_name' => $validated['product_name'],
            'product_desc' => null,
            'photo1' => $photoPath,
            'photo2' => null,
            'product_type' => (int) $validated['product_type'],
            'weight' => number_format((float) ($validated['weight'] ?? 0), 2, '.', ''),
            'create_date' => now()->toDateString(),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Item created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate([
            'product_name' => ['required', 'string', 'max:100'],
            'product_type' => ['required', 'integer', 'exists:r_item_type,id'],
            'weight' => ['nullable', 'numeric', 'decimal:0,2'],
            'photo1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $item = $this->service()->findById($this->editId);
        $photoPath = $item->photo1;

        if ($this->photo1) {
            $fileService = app(FileManagementService::class);
            $photoPath = $fileService->upload($this->photo1, 'ruhi-products');
            $fileService->delete($item->photo1);
        }

        $this->service()->update($item, [
            'product_name' => $validated['product_name'],
            'photo1' => $photoPath,
            'product_type' => (int) $validated['product_type'],
            'weight' => number_format((float) ($validated['weight'] ?? 0), 2, '.', ''),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Item updated successfully.');
        $this->closeModals();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->product_name = '';
        $this->product_type = '';
        $this->weight = '0.00';
        $this->photo1 = null;
        $this->existingPhoto1 = null;
    }

    private function service(): RuhiItemService
    {
        return $this->ruhiItems ??= app(RuhiItemService::class);
    }

    public function render()
    {
        $includeDeleted = (auth()->user()?->user_type ?? '') === 'systemuser';
        $items = $this->service()->paginateForList($this->search, $this->itemTypeId, $this->perPage, $includeDeleted);
        $itemTypes = $this->service()->listTypes();

        return view('livewire.masterapp.ruhi-items', [
            'items' => $items,
            'itemTypes' => $itemTypes,
        ]);
    }
}

