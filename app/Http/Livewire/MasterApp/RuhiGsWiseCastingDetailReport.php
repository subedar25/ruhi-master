<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsWiseCastingReportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class RuhiGsWiseCastingDetailReport extends Component
{
    #[Url(as: 'gs')]
    public ?int $gsId = null;

    #[Url(as: 'lot')]
    public ?int $lotId = null;

    public bool $submitted = false;

    private ?GsWiseCastingReportService $service = null;

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
            'lotId' => [
                'required',
                'integer',
                Rule::exists('r_slot', 'id')->where(fn ($q) => $q->where('gs_id', $this->gsId)),
            ],
        ];
    }

    public function mount(): void
    {
        $this->syncSubmittedFromSelections();
    }

    private function syncSubmittedFromSelections(): void
    {
        if ($this->gsId === null || $this->lotId === null) {
            $this->submitted = false;

            return;
        }

        $validator = Validator::make(
            [
                'gsId' => $this->gsId,
                'lotId' => $this->lotId,
            ],
            $this->reportValidationRules(),
        );

        if ($validator->fails()) {
            $this->submitted = false;

            return;
        }

        $this->resetValidation();
        $this->submitted = true;
    }

    public function updatedGsId(): void
    {
        $this->lotId = null;
        $this->submitted = false;
    }

    public function updatedLotId(): void
    {
        $this->syncSubmittedFromSelections();
    }

    public function submit(): void
    {
        $this->validate($this->reportValidationRules());
        $this->submitted = true;
    }

    private function svc(): GsWiseCastingReportService
    {
        return $this->service ??= app(GsWiseCastingReportService::class);
    }

    public function render()
    {
        $gsOptions = $this->svc()->listGsForDropdown();
        $lotOptions = collect();

        if ($this->gsId) {
            $lotOptions = $this->svc()->listLotsForGs((int) $this->gsId);
        }

        $report = null;

        if ($this->submitted && $this->gsId && $this->lotId) {
            $report = $this->svc()->buildDetailReport((int) $this->gsId, (int) $this->lotId);
        }

        return view('livewire.masterapp.ruhi-gs-wise-casting-detail-report', [
            'gsOptions' => $gsOptions,
            'lotOptions' => $lotOptions,
            'report' => $report,
        ]);
    }
}
