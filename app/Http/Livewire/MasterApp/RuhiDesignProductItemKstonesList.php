<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiDesignProductItemKstones\Services\RuhiDesignProductItemKstoneService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiDesignProductItemKstonesList extends Component
{
    use WithPagination;

    public int $designId;
    public int $productId;
    public string $search = '';
    public int $perPage = 20;
    public bool $showCreateModal = false;
    public array $createRows = [];

    private ?RuhiDesignProductItemKstoneService $service = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function mount(int $designId, int $productId): void
    {
        $this->designId = $designId;
        $this->productId = $productId;
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
        $this->resetValidation();
        $existingRows = $this->svc()->existingByDesignAndProduct($this->designId, $this->productId);
        $this->createRows = $this->svc()->listKstonesByProductFromItemKstone($this->productId)
            ->map(function ($row) use ($existingRows) {
                $existing = $existingRows->get((int) $row->kstone_id);
                return [
                    'kstone_id' => (int) $row->kstone_id,
                    'kstone_name' => (string) ($row->kstone->name ?? ('#'.$row->kstone_id)),
                    'kstone_quantity' => (int) ($existing?->kstone_quantity ?? $row->kstone_quantity ?? 0),
                    'red' => (int) ($existing?->red ?? 0),
                    'rg_red' => (int) ($existing?->rg_red ?? 0),
                    'rg_green' => (int) ($existing?->rg_green ?? 0),
                    'green' => (int) ($existing?->green ?? 0),
                    'white' => (int) ($existing?->white ?? 0),
                ];
            })
            ->values()
            ->all();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->createRows = [];
        $this->resetValidation();
    }

    public function saveCreate(): void
    {
        $validated = $this->validate([
            'createRows' => ['required', 'array', 'min:1'],
            'createRows.*.kstone_id' => ['required', 'integer', Rule::exists('r_kstone', 'id')->whereNull('deleted_at')],
            'createRows.*.kstone_quantity' => ['required', 'integer', 'min:0'],
            'createRows.*.red' => ['required', 'integer', 'min:0'],
            'createRows.*.rg_red' => ['required', 'integer', 'min:0'],
            'createRows.*.rg_green' => ['required', 'integer', 'min:0'],
            'createRows.*.green' => ['required', 'integer', 'min:0'],
            'createRows.*.white' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['createRows'] as $row) {
            $keys = [
                'design_id' => $this->designId,
                'product_id' => $this->productId,
                'kstone_id' => (int) $row['kstone_id'],
            ];

            $this->svc()->updateOrCreateByKeys($keys, [
                'kstone_quantity' => (int) $row['kstone_quantity'],
                'red' => (int) $row['red'],
                'rg_red' => (int) $row['rg_red'],
                'rg_green' => (int) $row['rg_green'],
                'green' => (int) $row['green'],
                'white' => (int) $row['white'],
                'rodo' => 0,
            ]);
        }

        $this->dispatch('formResult', type: 'success', message: 'K-Stone items added successfully.');
        $this->closeCreateModal();
        $this->resetPage();
    }

    private function svc(): RuhiDesignProductItemKstoneService
    {
        return $this->service ??= app(RuhiDesignProductItemKstoneService::class);
    }

    public function render()
    {
        $rows = $this->svc()->paginateForDesignAndProduct($this->designId, $this->productId, $this->search, $this->perPage);
        $product = $this->svc()->findProduct($this->productId);

        return view('livewire.masterapp.ruhi-design-product-item-kstones-list', [
            'rows' => $rows,
            'product' => $product,
        ]);
    }
}
