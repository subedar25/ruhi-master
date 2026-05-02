<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\File\Services\FileManagementService;
use App\Core\RuhiDesigns\Services\RuhiDesignService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class RuhiDesigns extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $categoryId = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editId = null;
    public string $design_name = '';
    public string $category_id = '';
    public $photo1 = null;
    public ?string $existingPhoto1 = null;
    private ?RuhiDesignService $ruhiDesigns = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['except' => ''],
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

    public function updatedCategoryId(): void
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

    public function fillNameFromCategory($value): void
    {
        if ($value === '' || $value === null) {
            return;
        }

        $category = $this->service()->findCategoryById((int) $value);
        if (! empty($category->abbreviation)) {
            $this->design_name = (string) $category->abbreviation;
        }
    }

    public function deleteById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);
        $this->service()->softDeleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'Design deleted successfully.');
        $this->resetPage();
    }

    public function restoreById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);
        $this->service()->restoreById($id);
        $this->dispatch('formResult', type: 'success', message: 'Design restored successfully.');
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        $design = $this->service()->findById($id);
        $this->resetValidation();
        $this->editId = $design->id;
        $this->design_name = (string) $design->design_name;
        $this->category_id = (string) $design->category_id;
        $this->existingPhoto1 = $design->photo1;
        $this->photo1 = null;
        $this->showEditModal = true;
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function saveCreate(): void
    {
        $validated = $this->validate([
            'design_name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'integer', 'exists:r_design_category,id'],
            'photo1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $photoPath = null;
        if ($this->photo1) {
            $fileService = app(FileManagementService::class);
            $photoPath = $fileService->upload($this->photo1, 'ruhi-designs');
        }

        $this->service()->create([
            'design_name' => $validated['design_name'],
            'design_desc' => null,
            'category_id' => (int) $validated['category_id'],
            'photo1' => $photoPath,
            'photo2' => null,
            'dubby_qty' => '0',
            'zumka_qty' => null,
            'uf' => null,
            'note' => null,
            'create_date' => now()->toDateString(),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Design created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate([
            'design_name' => ['required', 'string', 'max:100'],
            'category_id' => ['required', 'integer', 'exists:r_design_category,id'],
            'photo1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $design = $this->service()->findById($this->editId);
        $photoPath = $design->photo1;
        if ($this->photo1) {
            $fileService = app(FileManagementService::class);
            $photoPath = $fileService->upload($this->photo1, 'ruhi-designs');
            $fileService->delete($design->photo1);
        }

        $this->service()->update($design, [
            'design_name' => $validated['design_name'],
            'category_id' => (int) $validated['category_id'],
            'photo1' => $photoPath,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Design updated successfully.');
        $this->closeModals();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->design_name = '';
        $this->category_id = '';
        $this->photo1 = null;
        $this->existingPhoto1 = null;
    }

    private function service(): RuhiDesignService
    {
        return $this->ruhiDesigns ??= app(RuhiDesignService::class);
    }

    public function render()
    {
        $includeDeleted = (auth()->user()?->user_type ?? '') === 'systemuser';
        $designs = $this->service()->paginateForList($this->search, $this->categoryId, $this->perPage, $includeDeleted);
        $categories = $this->service()->listCategories();

        return view('livewire.masterapp.ruhi-designs', [
            'designs' => $designs,
            'categories' => $categories,
        ]);
    }
}

