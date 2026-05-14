<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsDetailEachItemReportService;
use App\Models\RuhiGsOrderByColor;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class RuhiGsDetailEachItemReport extends Component
{
    #[Url(as: 'gs')]
    public ?int $gsId = null;

    /** @var array<int, int> */
    public array $productTypes = [];

    /** Comma-separated design IDs for Select2 multi sync */
    #[Url(as: 'designs', except: '')]
    public string $designIdsCsv = '';

    /** Comma-separated product IDs (optional) */
    #[Url(as: 'items', except: '')]
    public string $productIdsCsv = '';

    /** Collate items only: '' = all names, '1' = name without "(s)", '2' = name contains "(s)" (drop items ignore) */
    #[Url(as: 'nf', except: '')]
    public string $nameFilter = '';

    /** Comma-separated product type ids (mirrors print `types`); kept in sync with {@see $productTypes} */
    #[Url(as: 'types', except: '')]
    public string $typesCsv = '';

    public bool $submitted = false;

    private ?GsDetailEachItemReportService $service = null;

    public function mount(): void
    {
        $this->hydrateProductTypesFromTypesCsv();
        if ($this->productTypes === []) {
            $this->productTypes = GsDetailEachItemReportService::DEFAULT_PRODUCT_TYPES;
        }
        $this->syncTypesCsvFromProductTypes();
        $this->syncSubmittedFromFilters();
    }

    private function hydrateProductTypesFromTypesCsv(): void
    {
        $raw = trim($this->typesCsv);
        if ($raw === '') {
            return;
        }

        $parsed = array_values(array_unique(array_filter(array_map('intval', preg_split('/\s*,\s*/', $raw, -1, PREG_SPLIT_NO_EMPTY)))));
        $allowed = [3, 4, 5, 6, 8];
        $filtered = array_values(array_intersect($parsed, $allowed));
        if ($filtered === []) {
            return;
        }

        $this->productTypes = $filtered;
    }

    private function syncTypesCsvFromProductTypes(): void
    {
        $this->typesCsv = implode(',', $this->normalizedProductTypes());
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function reportValidationRules(): array
    {
        return [
            'gsId' => [
                'required',
                'integer',
                Rule::exists('r_gs', 'id'),
            ],
            'designIdsCsv' => ['nullable', 'string'],
            'productTypes' => ['required', 'array', 'min:1'],
            'productTypes.*' => ['integer', Rule::in([3, 4, 5, 6, 8])],
            'productIdsCsv' => ['nullable', 'string'],
            'nameFilter' => ['nullable', 'string', Rule::in(['', '1', '2'])],
        ];
    }

    private function syncSubmittedFromFilters(): void
    {
        if ($this->gsId === null) {
            $this->submitted = false;

            return;
        }

        $validator = Validator::make(
            [
                'gsId' => $this->gsId,
                'designIdsCsv' => $this->designIdsCsv,
                'productTypes' => $this->normalizedProductTypes(),
                'productIdsCsv' => $this->productIdsCsv,
                'nameFilter' => $this->nameFilter,
            ],
            $this->reportValidationRules(),
        );

        if ($validator->fails()) {
            $this->submitted = false;

            return;
        }

        if ($this->productTypes === []) {
            $this->productTypes = GsDetailEachItemReportService::DEFAULT_PRODUCT_TYPES;
        }
        $this->syncTypesCsvFromProductTypes();

        $this->resetValidation();
        $this->submitted = true;
    }

    public function updatedGsId(): void
    {
        $this->designIdsCsv = '';
        $this->productIdsCsv = '';
        $this->syncSubmittedFromFilters();
    }

    public function updatedDesignIdsCsv(): void
    {
        $this->productIdsCsv = '';
        $this->syncSubmittedFromFilters();
    }

    public function updatedProductIdsCsv(): void
    {
        $this->syncSubmittedFromFilters();
    }

    public function updatedNameFilter(): void
    {
        $this->syncSubmittedFromFilters();
    }

    public function updatedProductTypes(): void
    {
        $this->syncTypesCsvFromProductTypes();
        $this->syncSubmittedFromFilters();
    }

    public function submit(): void
    {
        if ($this->productTypes === []) {
            $this->productTypes = GsDetailEachItemReportService::DEFAULT_PRODUCT_TYPES;
        }
        $this->syncTypesCsvFromProductTypes();
        $this->validate($this->reportValidationRules());
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

        $sectionVis = GsDetailEachItemReportService::sectionVisibilityForProductTypes($this->normalizedProductTypes());

        return view('livewire.masterapp.ruhi-gs-detail-each-item-report', [
            'gsOptions' => $gsOptions,
            'designOptions' => $designOptions,
            'itemOptions' => $itemOptions,
            'report' => $report,
            'selectedGsName' => $selectedGsName,
            'printParams' => $printParams,
            'showCollateSection' => $sectionVis['show_collate'],
            'showDropSection' => $sectionVis['show_drop'],
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
