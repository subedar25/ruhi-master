<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiKstones\Services\RuhiKstoneService;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiKstonesList extends Component
{
    use WithPagination;

    public string $search = '';
    /** Filter listing by r_kstone_color.id (separate from form color_id in modals). */
    public string $colorFilterId = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editId = null;

    public string $name = '';
    public string $color_id = '';
    public int $quantity = 0;
    public string $stoneweight = '0.000';
    public string $dieweight = '0.000';

    private ?RuhiKstoneService $kstoneService = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'colorFilterId' => ['except' => ''],
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

    public function updatedColorFilterId(): void
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
        $row = $this->service()->findById($id);
        $this->resetValidation();
        $this->editId = $row->id;
        $this->name = (string) $row->name;
        $this->color_id = (string) $row->color_id;
        $this->quantity = (int) $row->quantity;
        $this->stoneweight = number_format((float) $row->stoneweight, 3, '.', '');
        $this->dieweight = number_format((float) $row->dieweight, 3, '.', '');
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
            'name' => ['required', 'string', 'max:100'],
            'color_id' => ['required', 'integer', 'exists:r_kstone_color,id'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'stoneweight' => ['nullable', 'numeric', 'min:0'],
            'dieweight' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->service()->create([
            'name' => $validated['name'],
            'color_id' => (string) $validated['color_id'],
            'quantity' => (int) ($validated['quantity'] ?? 0),
            'stoneweight' => number_format((float) ($validated['stoneweight'] ?? 0), 3, '.', ''),
            'dieweight' => number_format((float) ($validated['dieweight'] ?? 0), 3, '.', ''),
            'create_date' => now()->toDateString(),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'K Stone created successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'color_id' => ['required', 'integer', 'exists:r_kstone_color,id'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'stoneweight' => ['nullable', 'numeric', 'min:0'],
            'dieweight' => ['nullable', 'numeric', 'min:0'],
        ]);

        $row = $this->service()->findById($this->editId);
        $this->service()->update($row, [
            'name' => $validated['name'],
            'color_id' => (string) $validated['color_id'],
            'quantity' => (int) ($validated['quantity'] ?? 0),
            'stoneweight' => number_format((float) ($validated['stoneweight'] ?? 0), 3, '.', ''),
            'dieweight' => number_format((float) ($validated['dieweight'] ?? 0), 3, '.', ''),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'K Stone updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);
        $this->service()->softDeleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'K Stone deleted successfully.');
        $this->resetPage();
    }

    public function restoreById(int $id): void
    {
        abort_unless((auth()->user()?->user_type ?? '') === 'systemuser', 403);
        $this->service()->restoreById($id);
        $this->dispatch('formResult', type: 'success', message: 'K Stone restored successfully.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->name = '';
        $this->color_id = '';
        $this->quantity = 0;
        $this->stoneweight = '0.000';
        $this->dieweight = '0.000';
    }

    private function service(): RuhiKstoneService
    {
        return $this->kstoneService ??= app(RuhiKstoneService::class);
    }

    public function render()
    {
        $includeDeleted = (auth()->user()?->user_type ?? '') === 'systemuser';
        $kstones = $this->service()->paginateForList($this->search, $this->colorFilterId, $this->perPage, $includeDeleted);
        $colors = $this->service()->listColors();

        return view('livewire.masterapp.ruhi-kstones-list', [
            'kstones' => $kstones,
            'colors' => $colors,
        ]);
    }
}
