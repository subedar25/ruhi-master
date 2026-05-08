<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiDesignProducts\Services\RuhiDesignProductService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiDesignProducts extends Component
{
    use WithPagination;

    public int $designId;
    public string $dubby_qty = '0';
    public string $zumka_qty = '';
    public string $uf = '';
    public string $note = '';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public string $activeTypeId = '';
    public string $product_id = '';
    public int $quantity = 1;
    public int $only_red_qty = 0;
    public int $red_qty = 0;
    public int $green_qty = 0;
    public int $only_green_qty = 0;
    public int $white_qty = 0;
    public ?int $editId = null;
    public array $perPageByType = [];
    public array $createRows = [];

    private ?RuhiDesignProductService $service = null;

    public function mount(int $designId): void
    {
        $this->designId = $designId;
        $design = $this->svc()->findDesignForProductsPage($designId);
        $this->dubby_qty = (string) ($design->dubby_qty ?? '0');
        $this->zumka_qty = (string) ($design->zumka_qty ?? '');
        $this->uf = (string) ($design->uf ?? '');
        $this->note = (string) ($design->note ?? '');
    }

    public function paginationView(): string
    {
        return 'livewire.invoice-pagination';
    }

    public function updatedPerPageByType($value, $key): void
    {
        if (is_array($value)) {
            return;
        }

        if ($key === null || $key === '') {
            return;
        }

        $value = (string) $value;
        $allowed = ['all', '20', '10', '15', '25', '50', '100'];
        if (! in_array($value, $allowed, true)) {
            $value = '20';
        }

        $this->perPageByType[(string) $key] = $value;
        $this->resetPage($this->svc()->pageName((int) $key));
    }

    public function saveSummary(): void
    {
        $validated = $this->validate([
            'dubby_qty' => ['nullable', 'numeric', 'min:0'],
            'zumka_qty' => ['nullable', 'integer', 'min:0'],
            'uf' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $design = $this->svc()->findDesignForProductsPage($this->designId);
        $this->svc()->updateDesignSummary($design, [
            'dubby_qty' => (string) ($validated['dubby_qty'] ?? '0'),
            'zumka_qty' => ($validated['zumka_qty'] === '' || $validated['zumka_qty'] === null)
                ? null
                : (int) $validated['zumka_qty'],
            'uf' => $validated['uf'] === '' ? null : $validated['uf'],
            'note' => $validated['note'] === '' ? null : $validated['note'],
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Design summary updated successfully.');
    }

    public function openCreateModal(int $typeId): void
    {
        $this->resetValidation();
        $this->showEditModal = false;
        $this->editId = null;
        $this->activeTypeId = (string) $typeId;
        $this->createRows = [$this->newCreateRow()];
        $this->product_id = '';
        $this->quantity = 1;
        $this->only_red_qty = 0;
        $this->red_qty = 0;
        $this->green_qty = 0;
        $this->only_green_qty = 0;
        $this->white_qty = $this->computedWhiteQty();
        $this->showCreateModal = true;
    }

    public function openEditModal(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('edit-ruhi-design-product') ?? false), 403);
        $row = $this->svc()->findById($id);
        abort_unless((int) $row->design_id === (int) $this->designId, 404);

        $this->resetValidation();
        $this->showCreateModal = false;
        $this->showEditModal = true;
        $this->editId = $row->id;
        $this->activeTypeId = (string) $row->item_type_id;
        $this->product_id = (string) $row->product_id;
        $this->quantity = (int) $row->quantity;
        $colorValues = $this->svc()->colorValuesForDesignProduct((int) $row->id);
        $this->only_red_qty = (int) ($colorValues['only_red_qty'] ?? 0);
        $this->red_qty = (int) ($colorValues['red_qty'] ?? 0);
        $this->green_qty = (int) ($colorValues['green_qty'] ?? 0);
        $this->only_green_qty = (int) ($colorValues['only_green_qty'] ?? 0);
        $this->white_qty = $this->computedWhiteQty();
    }

    public function closeModals(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editId = null;
        $this->activeTypeId = '';
        $this->product_id = '';
        $this->quantity = 1;
        $this->only_red_qty = 0;
        $this->red_qty = 0;
        $this->green_qty = 0;
        $this->only_green_qty = 0;
        $this->white_qty = 0;
        $this->createRows = [];
        $this->resetValidation();
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

    public function saveCreate(): void
    {
        $validated = $this->validate($this->createRowsRules());
        $typeId = (int) $this->activeTypeId;

        foreach ($validated['createRows'] as $row) {
            $created = $this->svc()->create([
                'design_id' => $this->designId,
                'item_type_id' => $typeId,
                'product_id' => (int) $row['product_id'],
                'quantity' => (int) $row['quantity'],
            ]);

            if ($this->activeTypeIsColor()) {
                $this->svc()->upsertColorValuesForDesignProduct((int) $created->id, [
                    'only_red_qty' => (int) ($row['only_red_qty'] ?? 0),
                    'red_qty' => (int) ($row['red_qty'] ?? 0),
                    'green_qty' => (int) ($row['green_qty'] ?? 0),
                    'only_green_qty' => (int) ($row['only_green_qty'] ?? 0),
                    'white_qty' => (int) ($row['white_qty'] ?? 0),
                ]);
            }
        }

        $this->dispatch('formResult', type: 'success', message: 'Design items added successfully.');
        $this->resetPage($this->svc()->pageName($typeId));
        $this->closeModals();
    }

    public function saveEdit(): void
    {
        abort_unless((bool) (auth()->user()?->can('edit-ruhi-design-product') ?? false), 403);
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate($this->productRules());
        $typeId = (int) $this->activeTypeId;
        $row = $this->svc()->findById($this->editId);
        abort_unless((int) $row->design_id === (int) $this->designId, 404);

        $this->svc()->update($row, [
            'item_type_id' => $typeId,
            'product_id' => (int) $validated['product_id'],
            'quantity' => (int) $validated['quantity'],
        ]);

        if ($this->activeTypeIsColor()) {
            $this->svc()->upsertColorValuesForDesignProduct((int) $row->id, [
                'only_red_qty' => (int) $validated['only_red_qty'],
                'red_qty' => (int) $validated['red_qty'],
                'green_qty' => (int) $validated['green_qty'],
                'only_green_qty' => (int) $validated['only_green_qty'],
                'white_qty' => (int) $validated['white_qty'],
            ]);
        }

        $this->dispatch('formResult', type: 'success', message: 'Design item updated successfully.');
        $this->closeModals();
    }

    public function deleteById(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-design-product') ?? false), 403);
        $row = $this->svc()->findById($id);
        abort_unless((int) $row->design_id === (int) $this->designId, 404);
        $this->svc()->deleteById($id);
        $this->dispatch('formResult', type: 'success', message: 'Design item removed successfully.');
        $this->resetPage($this->svc()->pageName((int) $row->item_type_id));
    }

    private function productRules(): array
    {
        $rules = [
            'activeTypeId' => ['required', 'integer', Rule::exists('r_item_type', 'id')->whereNull('deleted_at')],
            'product_id' => ['required', 'integer', Rule::exists('r_product', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'integer', 'min:1'],
        ];

        if ($this->activeTypeIsColor()) {
            $rules['only_red_qty'] = ['required', 'integer', 'min:0'];
            $rules['red_qty'] = ['required', 'integer', 'min:0'];
            $rules['green_qty'] = ['required', 'integer', 'min:0'];
            $rules['only_green_qty'] = ['required', 'integer', 'min:0'];
            $rules['white_qty'] = ['required', 'integer', 'min:0'];
        }

        return $rules;
    }

    private function createRowsRules(): array
    {
        $rules = [
            'activeTypeId' => ['required', 'integer', Rule::exists('r_item_type', 'id')->whereNull('deleted_at')],
            'createRows' => ['required', 'array', 'min:1'],
            'createRows.*.product_id' => ['required', 'integer', Rule::exists('r_product', 'id')->whereNull('deleted_at')],
            'createRows.*.quantity' => ['required', 'integer', 'min:1'],
        ];

        if ($this->activeTypeIsColor()) {
            $rules['createRows.*.only_red_qty'] = ['required', 'integer', 'min:0'];
            $rules['createRows.*.red_qty'] = ['required', 'integer', 'min:0'];
            $rules['createRows.*.green_qty'] = ['required', 'integer', 'min:0'];
            $rules['createRows.*.only_green_qty'] = ['required', 'integer', 'min:0'];
            $rules['createRows.*.white_qty'] = ['required', 'integer', 'min:0'];
        }

        return $rules;
    }

    public function activeTypeIsColor(): bool
    {
        if ($this->activeTypeId === '') {
            return false;
        }

        $type = $this->svc()->findItemTypeById((int) $this->activeTypeId);

        return strtolower((string) ($type->type_by_color ?? 'no')) === 'yes';
    }

    public function updatedQuantity(): void
    {
        $this->white_qty = $this->computedWhiteQty();
    }

    public function updatedOnlyRedQty(): void
    {
        $this->white_qty = $this->computedWhiteQty();
    }

    public function updatedRedQty(): void
    {
        $this->white_qty = $this->computedWhiteQty();
    }

    public function updatedGreenQty(): void
    {
        $this->white_qty = $this->computedWhiteQty();
    }

    public function updatedOnlyGreenQty(): void
    {
        $this->white_qty = $this->computedWhiteQty();
    }

    public function updatedCreateRows($value, $key): void
    {
        if (! is_string($key)) {
            return;
        }

        if (preg_match('/^(\d+)\.(quantity|only_red_qty|red_qty|green_qty|only_green_qty)$/', $key, $matches) !== 1) {
            return;
        }

        $rowIndex = (int) $matches[1];
        if (! isset($this->createRows[$rowIndex])) {
            return;
        }

        $this->createRows[$rowIndex]['white_qty'] = $this->computedWhiteQtyFromRow($this->createRows[$rowIndex]);
    }

    private function computedWhiteQty(): int
    {
        return max(
            (int) $this->quantity - ((int) $this->only_red_qty + (int) $this->red_qty + (int) $this->green_qty + (int) $this->only_green_qty),
            0
        );
    }

    private function computedWhiteQtyFromRow(array $row): int
    {
        return max(
            (int) ($row['quantity'] ?? 0) - (
                (int) ($row['only_red_qty'] ?? 0) +
                (int) ($row['red_qty'] ?? 0) +
                (int) ($row['green_qty'] ?? 0) +
                (int) ($row['only_green_qty'] ?? 0)
            ),
            0
        );
    }

    private function newCreateRow(): array
    {
        return [
            'product_id' => '',
            'quantity' => 1,
            'only_red_qty' => 0,
            'red_qty' => 0,
            'green_qty' => 0,
            'only_green_qty' => 0,
            'white_qty' => 1,
        ];
    }

    private function svc(): RuhiDesignProductService
    {
        return $this->service ??= app(RuhiDesignProductService::class);
    }

    private function perPageForType(int $typeId, int $total): int
    {
        $value = (string) ($this->perPageByType[(string) $typeId] ?? '20');
        if ($value === 'all') {
            return max($total, 1);
        }

        $allowed = [20, 10, 15, 25, 50, 100];
        $numeric = (int) $value;

        return in_array($numeric, $allowed, true) ? $numeric : 20;
    }

    public function render()
    {
        $design = $this->svc()->findDesignForProductsPage($this->designId);
        $types = $this->svc()->listItemTypes();
        $blocks = $types->map(function ($type) {
            $total = $this->svc()->countByDesignAndType($this->designId, (int) $type->id);
            $rows = $this->svc()->paginateByDesignAndType(
                $this->designId,
                (int) $type->id,
                $this->perPageForType((int) $type->id, $total)
            );

            return [
                'type' => $type,
                'total' => $total,
                'rows' => $rows,
            ];
        });

        $productsForActiveType = collect();
        if ($this->activeTypeId !== '') {
            $productsForActiveType = $this->svc()->listProductsForDropdown((int) $this->activeTypeId);
        }

        return view('livewire.masterapp.ruhi-design-products', [
            'design' => $design,
            'blocks' => $blocks,
            'productsForActiveType' => $productsForActiveType,
        ]);
    }
}
