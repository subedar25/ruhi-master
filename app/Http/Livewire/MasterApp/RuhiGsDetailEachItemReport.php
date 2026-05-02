<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsDetailEachItemReportService;
use App\Models\RuhiGsOrderByColor;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RuhiGsDetailEachItemReport extends Component
{
    public ?int $gsId = null;

    /** @var array<int, int> */
    public array $productTypes = [];

    /** Comma-separated design IDs for Select2 multi sync */
    public string $designIdsCsv = '';

    /** Comma-separated product IDs (optional) */
    public string $productIdsCsv = '';

    /** Collate items only: '' = all names, '1' = name without "(s)", '2' = name contains "(s)" (drop items ignore) */
    public string $nameFilter = '';

    public bool $submitted = false;

    private ?GsDetailEachItemReportService $service = null;

    public function mount(): void
    {
        $this->productTypes = GsDetailEachItemReportService::DEFAULT_PRODUCT_TYPES;
    }

    public function updatedGsId(): void
    {
        $this->submitted = false;
        $this->designIdsCsv = '';
        $this->productIdsCsv = '';
    }

    public function updatedDesignIdsCsv(): void
    {
        $this->submitted = false;
        $this->productIdsCsv = '';
    }

    public function updatedProductIdsCsv(): void
    {
        $this->submitted = false;
    }

    public function updatedNameFilter(): void
    {
        $this->submitted = false;
    }

    public function updatedProductTypes(): void
    {
        $this->submitted = false;
    }

    public function submit(): void
    {
        $this->validate([
            'gsId' => [
                'required',
                'integer',
                Rule::exists('r_gs', 'id')->whereNull('deleted_at'),
            ],
            'designIdsCsv' => ['nullable', 'string'],
            'productTypes' => ['required', 'array', 'min:1'],
            'productTypes.*' => ['integer', Rule::in([3, 4, 5, 6, 8])],
            'productIdsCsv' => ['nullable', 'string'],
            'nameFilter' => ['nullable', 'string', Rule::in(['', '1', '2'])],
        ]);

        $this->submitted = true;
    }

    private function svc(): GsDetailEachItemReportService
    {
        return $this->service ??= app(GsDetailEachItemReportService::class);
    }

    /**
     * @return array<int, int>
     */
    private function parsedDesignIds(): array
    {
        return $this->parseIdCsv($this->designIdsCsv);
    }

    /**
     * @return array<int, int>
     */
    private function parsedProductIds(): array
    {
        return $this->parseIdCsv($this->productIdsCsv);
    }

    /**
     * @return array<int, int>
     */
    private function parseIdCsv(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        $parts = preg_split('/\s*,\s*/', trim($csv), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique(array_filter(array_map('intval', $parts))));
    }

    /**
     * Designs used to load the item/product list: explicit selection, or all designs on GS if none selected.
     *
     * @return array<int, int>
     */
    private function designIdsForItemPicker(): array
    {
        if (! $this->gsId) {
            return [];
        }

        $parsed = $this->parsedDesignIds();
        if ($parsed !== []) {
            $onGs = RuhiGsOrderByColor::query()
                ->where('gs_id', (int) $this->gsId)
                ->distinct()
                ->pluck('design_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            return array_values(array_intersect($parsed, $onGs));
        }

        return $this->svc()->listDesignsForGs((int) $this->gsId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public function render()
    {
        $gsOptions = $this->svc()->listGsForDropdown();

        $designOptions = collect();
        $itemOptions = collect();
        if ($this->gsId) {
            $designOptions = $this->svc()->listDesignsForGs((int) $this->gsId);
        }
        $didsForItemPicker = $this->designIdsForItemPicker();
        if ($this->gsId && $didsForItemPicker !== []) {
            $types = $this->normalizedProductTypes();
            $itemOptions = $this->svc()->listProductsForDesignsAndTypes($didsForItemPicker, $types);
        }

        $report = null;
        $selectedGsName = '';
        $printParams = null;

        if ($this->submitted && $this->gsId) {
            $report = $this->svc()->buildReport(
                (int) $this->gsId,
                $this->normalizedProductTypes(),
                $this->parsedDesignIds(),
                $this->parsedProductIds(),
                $this->nameFilter,
            );
            $selectedGsName = (string) ($gsOptions->firstWhere('id', (int) $this->gsId)?->name ?? '');
            $printParams = [
                'gs' => (int) $this->gsId,
                'types' => implode(',', $this->normalizedProductTypes()),
                'designs' => $this->designIdsCsv,
                'items' => $this->productIdsCsv,
                'nf' => $this->nameFilter,
            ];
        }

        return view('livewire.masterapp.ruhi-gs-detail-each-item-report', [
            'gsOptions' => $gsOptions,
            'designOptions' => $designOptions,
            'itemOptions' => $itemOptions,
            'report' => $report,
            'selectedGsName' => $selectedGsName,
            'printParams' => $printParams,
        ]);
    }

    /**
     * @return array<int, int>
     */
    private function normalizedProductTypes(): array
    {
        $t = array_values(array_unique(array_map('intval', $this->productTypes)));

        return $t === [] ? GsDetailEachItemReportService::DEFAULT_PRODUCT_TYPES : $t;
    }
}
