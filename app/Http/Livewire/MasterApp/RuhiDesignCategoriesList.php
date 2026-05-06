<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiDesignCategories\Services\RuhiDesignCategoryService;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiDesignCategoriesList extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editId = null;

    public string $category_name = '';
    public string $abbreviation = '';

    private ?RuhiDesignCategoryService $service = null;

    protected $queryString = [
        'search' => ['except' => ''],
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

    public function updatedPerPage(): void
    {
        $allowed = [20, 10, 15, 25, 50, 100];
        if (! in_array((int) $this->perPage, $allowed, true)) {
            $this->perPage = 20;
        }
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        $row = $this->svc()->findById($id);
        $this->resetValidation();
        $this->editId = $row->id;
        $this->category_name = (string) $row->category_name;
        $this->abbreviation = (string) ($row->abbreviation ?? '');
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
            'category_name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
        ]);

        $this->svc()->create([
            'category_name' => $validated['category_name'],
            'abbreviation' => $validated['abbreviation'] !== '' ? $validated['abbreviation'] : null,
            'created_date' => now(),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Design category created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate([
            'category_name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['nullable', 'string', 'max:20'],
        ]);

        $row = $this->svc()->findById($this->editId);
        $this->svc()->update($row, [
            'category_name' => $validated['category_name'],
            'abbreviation' => $validated['abbreviation'] !== '' ? $validated['abbreviation'] : null,
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Design category updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-design-category') ?? false), 403);
        $this->svc()->softDeleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'Design category deleted successfully.');
        $this->resetPage();
    }

    public function restoreById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);
        $this->svc()->restoreById($id);
        $this->dispatch('formResult', type: 'success', message: 'Design category restored successfully.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->category_name = '';
        $this->abbreviation = '';
    }

    private function svc(): RuhiDesignCategoryService
    {
        return $this->service ??= app(RuhiDesignCategoryService::class);
    }

    public function render()
    {
        $includeDeleted = (auth()->user()?->user_type ?? '') === 'systemuser';
        $categories = $this->svc()->paginateForList($this->search, $this->perPage, $includeDeleted);

        return view('livewire.masterapp.ruhi-design-categories-list', [
            'categories' => $categories,
        ]);
    }
}
