<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiGsLots\Services\RuhiGsLotService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class RuhiGsLotsList extends Component
{
    use WithPagination;

    public int $gsId;
    public string $search = '';
    public ?int $lotFilterId = null;
    public int $perPage = 20;
    public bool $showAddLotModal = false;
    public bool $showAddItemModal = false;
    public bool $showEditModal = false;
    public ?int $editId = null;
    public string $lotName = '';
    public ?int $selectedLotId = null;
    public ?int $deleteLotTargetId = null;
    public array $addLotRows = [];
    public array $addItemRows = [];
    public ?int $editLotId = null;
    public ?int $editDesignId = null;
    public int $editDesignQty = 0;
    public int $editRedQty = 0;
    public int $editRedGreenQty = 0;
    public int $editGreenQty = 0;
    public int $editWhiteQty = 0;

    private ?RuhiGsLotService $service = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'lotFilterId' => ['except' => null],
        'perPage' => ['except' => 20],
    ];

    public function mount(int $gsId): void
    {
        $this->gsId = $gsId;
        $this->addDefaultLotRow();
        $this->addDefaultItemRow();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedLotFilterId(mixed $value): void
    {
        if ($value === '' || $value === null) {
            $this->lotFilterId = null;
        }
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

    public function paginationView(): string
    {
        return 'livewire.invoice-pagination';
    }

    public function openAddLotModal(): void
    {
        $this->resetValidation();
        $this->lotName = '';
        $this->addLotRows = [];
        $this->addDefaultLotRow();
        $this->showAddLotModal = true;
    }

    public function closeAddLotModal(): void
    {
        $this->showAddLotModal = false;
        $this->lotName = '';
        $this->addLotRows = [];
        $this->addDefaultLotRow();
        $this->resetValidation();
    }

    public function addLotRow(): void
    {
        $this->addDefaultLotRow();
    }

    public function removeLotRow(int $index): void
    {
        unset($this->addLotRows[$index]);
        $this->addLotRows = array_values($this->addLotRows);
        if (count($this->addLotRows) === 0) {
            $this->addDefaultLotRow();
        }
    }

    public function saveLotWithItems(): void
    {
        $this->addLotRows = array_map(function ($row): array {
            $row = is_array($row) ? $row : [];

            foreach (['design_qty', 'design_red_qty', 'design_red_green_qty', 'design_green_qty', 'white_qty'] as $field) {
                if (array_key_exists($field, $row) && preg_match('/^\d+$/', (string) $row[$field]) === 1) {
                    $row[$field] = (int) $row[$field];
                }
            }

            return $row;
        }, $this->addLotRows);

        $validated = $this->validate([
            'lotName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('r_slot', 'slot_name')->where(fn ($q) => $q->where('gs_id', $this->gsId)),
            ],
            'addLotRows' => ['required', 'array', 'min:1'],
            'addLotRows.*.design_id' => ['required', 'integer', Rule::exists('r_design', 'id')->whereNull('deleted_at')],
            'addLotRows.*.design_qty' => ['required', 'integer', 'min:1'],
            'addLotRows.*.design_red_qty' => ['required', 'integer', 'min:0'],
            'addLotRows.*.design_red_green_qty' => ['required', 'integer', 'min:0'],
            'addLotRows.*.design_green_qty' => ['required', 'integer', 'min:0'],
            'addLotRows.*.white_qty' => ['required', 'integer', 'min:0'],
        ], [
            'lotName.unique' => 'This lot name already exists for the selected GS.',
        ]);

        foreach ($validated['addLotRows'] as $row) {
            if (! $this->isColorSplitValid(
                (int) $row['design_qty'],
                (int) $row['design_red_qty'],
                (int) $row['design_red_green_qty'],
                (int) $row['design_green_qty'],
                (int) $row['white_qty']
            )) {
                throw ValidationException::withMessages([
                    'addLotRows' => ['For each row, Red + Red + Green + Green + White must equal Quantity.'],
                ]);
            }
        }

        $this->svc()->createLotWithItems($this->gsId, $validated['lotName'], $validated['addLotRows']);
        $this->lotFilterId = null;
        $this->resetPage();
        $this->dispatch('formResult', type: 'success', message: 'Lot added successfully.');
        $this->closeAddLotModal();
    }

    public function openAddItemModal(): void
    {
        $this->resetValidation();
        $this->selectedLotId = null;
        $this->addItemRows = [];
        $this->addDefaultItemRow();
        $this->showAddItemModal = true;
    }

    public function closeAddItemModal(): void
    {
        $this->showAddItemModal = false;
        $this->selectedLotId = null;
        $this->addItemRows = [];
        $this->addDefaultItemRow();
        $this->resetValidation();
    }

    /**
     * Delete the lot currently selected in the filter dropdown and all design assignments in that lot.
     * Master designs (r_design) are not deleted.
     */
    public function deleteSelectedFilterLot(): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-gs-lot') ?? false), 403);

        $this->validate([
            'lotFilterId' => [
                'required',
                'integer',
                Rule::exists('r_slot', 'id')->where(fn ($q) => $q->where('gs_id', $this->gsId)),
            ],
        ]);

        $lotId = (int) $this->lotFilterId;
        $this->svc()->deleteLotForGs($this->gsId, $lotId);
        $this->lotFilterId = null;
        $this->resetPage();
        $this->dispatch('formResult', type: 'success', message: 'Lot and its design assignments removed. Master designs were not deleted.');
    }

    public function addItemRow(): void
    {
        $this->addDefaultItemRow();
    }

    public function removeItemRow(int $index): void
    {
        unset($this->addItemRows[$index]);
        $this->addItemRows = array_values($this->addItemRows);
        if (count($this->addItemRows) === 0) {
            $this->addDefaultItemRow();
        }
    }

    public function saveItemsInLot(): void
    {
        $validated = $this->validate([
            'selectedLotId' => ['required', 'integer', Rule::exists('r_slot', 'id')->where(fn ($q) => $q->where('gs_id', $this->gsId))],
            'addItemRows' => ['required', 'array', 'min:1'],
            'addItemRows.*.design_id' => ['required', 'integer', Rule::exists('r_design', 'id')->whereNull('deleted_at')],
            'addItemRows.*.design_qty' => ['required', 'integer', 'min:1'],
            'addItemRows.*.design_red_qty' => ['required', 'integer', 'min:0'],
            'addItemRows.*.design_red_green_qty' => ['required', 'integer', 'min:0'],
            'addItemRows.*.design_green_qty' => ['required', 'integer', 'min:0'],
            'addItemRows.*.white_qty' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['addItemRows'] as $row) {
            if (! $this->isColorSplitValid(
                (int) $row['design_qty'],
                (int) $row['design_red_qty'],
                (int) $row['design_red_green_qty'],
                (int) $row['design_green_qty'],
                (int) $row['white_qty']
            )) {
                throw ValidationException::withMessages([
                    'addItemRows' => ['For each row, Red + Red + Green + Green + White must equal Quantity.'],
                ]);
            }
        }

        $designIds = array_map(fn (array $row): int => (int) $row['design_id'], $validated['addItemRows']);
        if (count($designIds) !== count(array_unique($designIds))) {
            throw ValidationException::withMessages([
                'addItemRows' => ['Each design can only be added once in this form.'],
            ]);
        }

        $lotId = (int) $validated['selectedLotId'];
        foreach ($designIds as $designId) {
            if ($this->svc()->lotHasDesignRow($this->gsId, $lotId, $designId)) {
                throw ValidationException::withMessages([
                    'addItemRows' => ['This design is already in this lot for this GS.'],
                ]);
            }
        }

        $rows = $validated['addItemRows'];

        $this->addItemRows = $rows;
        $this->svc()->addItemsInLot($this->gsId, (int) $validated['selectedLotId'], $rows);
        $this->resetPage();
        $this->dispatch('formResult', type: 'success', message: 'Items added in lot successfully.');
        $this->closeAddItemModal();
    }

    public function deleteLotItemById(int $id): void
    {
        abort_unless((bool) (auth()->user()?->can('delete-ruhi-gs-lot') ?? false), 403);
        $this->svc()->deleteLotItemById($id, $this->gsId);
        $this->resetPage();
        $this->dispatch('formResult', type: 'success', message: 'Lot item deleted permanently.');
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $row = $this->svc()->findLotItemById($id, $this->gsId);
        $this->editId = (int) $row->id;
        $this->editLotId = (int) $row->lot_id;
        $this->editDesignId = (int) $row->design_id;
        $this->editDesignQty = (int) $row->design_qty;
        $this->editRedQty = (int) $row->design_red_qty;
        $this->editRedGreenQty = (int) $row->design_red_green_qty;
        $this->editGreenQty = (int) $row->design_green_qty;
        $this->editWhiteQty = (int) $row->white_qty;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editId = null;
        $this->editLotId = null;
        $this->editDesignId = null;
        $this->editDesignQty = 0;
        $this->editRedQty = 0;
        $this->editRedGreenQty = 0;
        $this->editGreenQty = 0;
        $this->editWhiteQty = 0;
        $this->resetValidation();
    }

    public function saveEdit(): void
    {
        if (! $this->editId) {
            return;
        }

        $validated = $this->validate([
            'editLotId' => ['required', 'integer', Rule::exists('r_slot', 'id')->where(fn ($q) => $q->where('gs_id', $this->gsId))],
            'editDesignId' => ['required', 'integer', Rule::exists('r_design', 'id')->whereNull('deleted_at')],
            'editDesignQty' => ['required', 'integer', 'min:1'],
            'editRedQty' => ['required', 'integer', 'min:0'],
            'editRedGreenQty' => ['required', 'integer', 'min:0'],
            'editGreenQty' => ['required', 'integer', 'min:0'],
            'editWhiteQty' => ['required', 'integer', 'min:0'],
        ]);

        if (! $this->isColorSplitValid(
            (int) $validated['editDesignQty'],
            (int) $validated['editRedQty'],
            (int) $validated['editRedGreenQty'],
            (int) $validated['editGreenQty'],
            (int) $validated['editWhiteQty']
        )) {
            throw ValidationException::withMessages([
                'editColorSplit' => ['Red + Red + Green + Green + White must equal Quantity.'],
            ]);
        }

        $row = $this->svc()->findLotItemById($this->editId, $this->gsId);
        $this->svc()->updateLotItem($row, [
            'lot_id' => (int) $validated['editLotId'],
            'design_id' => (int) $validated['editDesignId'],
            'design_qty' => (int) $validated['editDesignQty'],
            'design_red_qty' => (int) $validated['editRedQty'],
            'design_red_green_qty' => (int) $validated['editRedGreenQty'],
            'design_green_qty' => (int) $validated['editGreenQty'],
            'white_qty' => (int) $validated['editWhiteQty'],
        ]);

        $this->dispatch('formResult', type: 'success', message: 'Lot item updated successfully.');
        $this->closeEditModal();
    }

    public function updatedAddItemRows($value, $key): void {}

    public function updatedAddLotRows($value, $key): void {}

    public function updatedEditDesignQty(): void {}

    public function updatedEditRedQty(): void {}

    public function updatedEditRedGreenQty(): void {}

    public function setEditRedGreenQty($value): void
    {
        $this->editRedGreenQty = max((int) $value, 0);
    }

    public function updatedEditGreenQty(): void {}

    private function addDefaultLotRow(): void
    {
        $this->addLotRows[] = [
            'design_id' => null,
            'design_qty' => 0,
            'design_red_qty' => 0,
            'design_red_green_qty' => 0,
            'design_green_qty' => 0,
            'white_qty' => 0,
        ];
    }

    private function addDefaultItemRow(): void
    {
        $this->addItemRows[] = [
            'design_id' => null,
            'design_qty' => 0,
            'design_red_qty' => 0,
            'design_red_green_qty' => 0,
            'design_green_qty' => 0,
            'white_qty' => 0,
        ];
    }

    private function svc(): RuhiGsLotService
    {
        return $this->service ??= app(RuhiGsLotService::class);
    }

    private function isColorSplitValid(int $qty, int $red, int $redGreen, int $green, int $white): bool
    {
        return ($red + $redGreen + $green + $white) === $qty;
    }

    public function render()
    {
        return view('livewire.masterapp.ruhi-gs-lots-list', [
            'rows' => $this->svc()->paginateLotItemRowsByGs($this->gsId, $this->search, $this->lotFilterId, $this->perPage),
            'designs' => $this->svc()->listDesignsForDropdown(),
            'slotOptions' => $this->svc()->listSlotsByGs($this->gsId),
        ]);
    }
}
