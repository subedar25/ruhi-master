<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiItemKstones\Services\RuhiItemKstoneService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiItemColletKstonesList extends Component
{
    use WithPagination;

    public int $productId;

    public string $search = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editId = null;
    public array $createRows = [];

    public string $kstone_id = '';
    public int $kstone_quantity = 1;
    public string $kstone_weight = '0.000';
    public string $kstone_dieweight = '0.000';

    private ?RuhiItemKstoneService $service = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function mount(int $productId): void
    {
        $this->productId = $productId;
        $this->svc()->findProductForColletPage($productId);
    }

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
        $this->createRows = [$this->newCreateRow()];
        $this->showCreateModal = true;
    }

    public function addCreateRow(): void
    {
        $this->createRows[] = $this->newCreateRow();
    }

    public function removeCreateRow(int $index): void
    {
        if (count($this->createRows) <= 1) {
            return;
        }

        unset($this->createRows[$index]);
        $this->createRows = array_values($this->createRows);
    }

    public function openEditModal(int $id): void
    {
        $row = $this->svc()->findById($id);
        abort_unless((int) $row->item_id === (int) $this->productId, 404);

        $this->resetValidation();
        $this->editId = $row->id;
        $this->kstone_id = (string) $row->kstone_id;
        $this->kstone_quantity = (int) $row->kstone_quantity;
        $this->kstone_weight = number_format((float) $row->kstone_weight, 3, '.', '');
        $this->kstone_dieweight = number_format((float) $row->kstone_dieweight, 3, '.', '');
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
            'createRows' => ['required', 'array', 'min:1'],
            'createRows.*.kstone_id' => ['required', 'integer', Rule::exists('r_kstone', 'id')->whereNull('deleted_at')],
            'createRows.*.kstone_quantity' => ['required', 'integer', 'min:1'],
            'createRows.*.kstone_weight' => ['nullable', 'numeric', 'min:0'],
            'createRows.*.kstone_dieweight' => ['nullable', 'numeric', 'min:0'],
        ]);

        foreach ($validated['createRows'] as $row) {
            $this->svc()->create([
                'item_id' => $this->productId,
                'kstone_id' => (int) $row['kstone_id'],
                'kstone_quantity' => (int) $row['kstone_quantity'],
                'kstone_weight' => number_format((float) ($row['kstone_weight'] ?? 0), 3, '.', ''),
                'kstone_dieweight' => number_format((float) ($row['kstone_dieweight'] ?? 0), 3, '.', ''),
                'red' => 0,
                'rg_red' => 0,
                'rg_green' => 0,
                'green' => 0,
                'white' => 0,
                'rodo' => 0,
            ]);
        }

        $this->dispatch('formResult', type: 'success', message: 'K-Stone lines added successfully.');
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate([
            'kstone_id' => ['required', 'integer', Rule::exists('r_kstone', 'id')->whereNull('deleted_at')],
            'kstone_quantity' => ['required', 'integer', 'min:1'],
            'kstone_weight' => ['nullable', 'numeric', 'min:0'],
            'kstone_dieweight' => ['nullable', 'numeric', 'min:0'],
        ]);

        $row = $this->svc()->findById($this->editId);
        abort_unless((int) $row->item_id === (int) $this->productId, 404);

        $this->svc()->update($row, [
            'kstone_id' => (int) $validated['kstone_id'],
            'kstone_quantity' => (int) $validated['kstone_quantity'],
            'kstone_weight' => number_format((float) ($validated['kstone_weight'] ?? 0), 3, '.', ''),
            'kstone_dieweight' => number_format((float) ($validated['kstone_dieweight'] ?? 0), 3, '.', ''),
        ]);

        $this->dispatch('formResult', type: 'success', message: 'K-Stone line updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-collet-kstone') ?? false), 403);
        $row = $this->svc()->findById($id);
        abort_unless((int) $row->item_id === (int) $this->productId, 404);
        $this->svc()->deleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'K-Stone line removed successfully.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->editId = null;
        $this->kstone_id = '';
        $this->kstone_quantity = 1;
        $this->kstone_weight = '0.000';
        $this->kstone_dieweight = '0.000';
        $this->createRows = [$this->newCreateRow()];
    }

    private function newCreateRow(): array
    {
        return [
            'kstone_id' => '',
            'kstone_quantity' => 1,
            'kstone_weight' => '0.000',
            'kstone_dieweight' => '0.000',
        ];
    }

    private function svc(): RuhiItemKstoneService
    {
        return $this->service ??= app(RuhiItemKstoneService::class);
    }

    public function render()
    {
        $product = $this->svc()->findProductForColletPage($this->productId);
        $rows = $this->svc()->paginateForItem($this->productId, $this->search, $this->perPage);
        $kstones = $this->svc()->listKstonesForDropdown();

        return view('livewire.masterapp.ruhi-item-collet-kstones-list', [
            'product' => $product,
            'rows' => $rows,
            'kstones' => $kstones,
        ]);
    }
}
