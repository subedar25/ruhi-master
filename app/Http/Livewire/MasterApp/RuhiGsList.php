<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiGs\Services\RuhiGsService;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiGsList extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editId = null;
    public string $name = '';
    private ?RuhiGsService $gsService = null;

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
        $gs = $this->service()->findById($id);
        $this->resetValidation();
        $this->editId = $gs->id;
        $this->name = (string) $gs->name;
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

        $this->service()->create([
            'name' => $validated['name'],
            'created_date' => now()->toDateString(),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'GS created successfully.');
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

        $gs = $this->service()->findById($this->editId);
        $this->service()->update($gs, [
            'name' => $validated['name'],
        ]);

        $this->dispatch('formResult', type: 'success', message: 'GS updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-gs') ?? false), 403);
        $this->service()->softDeleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'GS deleted successfully.');
        $this->resetPage();
    }

    public function restoreById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);
        $this->service()->restoreById($id);
        $this->dispatch('formResult', type: 'success', message: 'GS restored successfully.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->name = '';
    }

    private function service(): RuhiGsService
    {
        return $this->gsService ??= app(RuhiGsService::class);
    }

    public function render()
    {
        $includeDeleted = (auth()->user()?->user_type ?? '') === 'systemuser';
        $gss = $this->service()->paginateForList($this->search, $this->perPage, $includeDeleted);

        return view('livewire.masterapp.ruhi-gs-list', [
            'gss' => $gss,
        ]);
    }
}

