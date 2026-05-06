<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiItemTypes\Services\RuhiItemTypeService;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiItemTypesList extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editId = null;

    /** Form label "Name" — maps to `r_item_type.item_type`. */
    public string $name = '';

    private ?RuhiItemTypeService $service = null;

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
        $this->name = (string) $row->item_type;
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
            'name' => ['required', 'string', 'max:255'],
        ]);

        $this->svc()->create([
            'item_type' => $validated['name'],
            'abbreviation' => null,
            'type_by_color' => 'No',
            'required_kstone' => 'No',
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Item category created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $row = $this->svc()->findById($this->editId);
        $this->svc()->update($row, [
            'item_type' => $validated['name'],
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Item category updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-item-type') ?? false), 403);
        $this->svc()->softDeleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'Item category deleted successfully.');
        $this->resetPage();
    }

    public function restoreById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);
        $this->svc()->restoreById($id);
        $this->dispatch('formResult', type: 'success', message: 'Item category restored successfully.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->name = '';
    }

    private function svc(): RuhiItemTypeService
    {
        return $this->service ??= app(RuhiItemTypeService::class);
    }

    public function render()
    {
        $includeDeleted = (auth()->user()?->user_type ?? '') === 'systemuser';
        $types = $this->svc()->paginateForList($this->search, $this->perPage, $includeDeleted);

        return view('livewire.masterapp.ruhi-item-types-list', [
            'types' => $types,
        ]);
    }
}
