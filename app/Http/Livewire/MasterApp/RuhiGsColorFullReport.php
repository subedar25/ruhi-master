<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsColorFullReportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class RuhiGsColorFullReport extends Component
{
    #[Url(as: 'gs')]
    public ?int $gsId = null;

    /** 0 = all; 1 = exclude names containing "(S)"; 2 = only those names (legacy CI {@code sfilter}). */
    #[Url(as: 'sfilter', except: 0)]
    public int $sfilter = 0;

    public bool $submitted = false;

    private ?GsColorFullReportService $service = null;

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
            'sfilter' => ['integer', Rule::in([0, 1, 2])],
        ];
    }

    public function mount(): void
    {
        $this->syncSubmittedFromFilters();
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
                'sfilter' => $this->sfilter,
            ],
            $this->reportValidationRules(),
        );

        if ($validator->fails()) {
            if ($validator->errors()->has('gsId')) {
                $this->gsId = null;
                $this->submitted = false;

                return;
            }

            if ($validator->errors()->has('sfilter')) {
                $this->sfilter = 0;
                $validator = Validator::make(
                    [
                        'gsId' => $this->gsId,
                        'sfilter' => $this->sfilter,
                    ],
                    $this->reportValidationRules(),
                );
                if ($validator->fails()) {
                    $this->submitted = false;

                    return;
                }
            } else {
                $this->submitted = false;

                return;
            }
        }

        $this->resetValidation();
        $this->submitted = true;
    }

    public function updatedGsId(): void
    {
        $this->syncSubmittedFromFilters();
    }

    public function updatedSfilter(): void
    {
        $this->syncSubmittedFromFilters();
    }

    public function submit(): void
    {
        $this->validate($this->reportValidationRules());
        $this->submitted = true;
    }

    private function svc(): GsColorFullReportService
    {
        return $this->service ??= app(GsColorFullReportService::class);
    }

    public function render()
    {
        $gsOptions = $this->svc()->listGsForDropdown();
        $report = null;
        $selectedGsName = '';

        if ($this->submitted && $this->gsId) {
            $report = $this->svc()->buildReport(
                (int) $this->gsId,
                $this->sfilter === 0 ? null : $this->sfilter
            );
            $selectedGsName = (string) ($gsOptions->firstWhere('id', (int) $this->gsId)?->name ?? '');
        }

        return view('livewire.masterapp.ruhi-gs-color-full-report', [
            'gsOptions' => $gsOptions,
            'report' => $report,
            'selectedGsName' => $selectedGsName,
        ]);
    }
}
