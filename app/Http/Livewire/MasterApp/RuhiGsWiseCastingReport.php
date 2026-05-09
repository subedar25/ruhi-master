<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsWiseCastingReportService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RuhiGsWiseCastingReport extends Component
{
    public ?int $gsId = null;

    public ?int $lotId = null;

    public bool $submitted = false;

    private ?GsWiseCastingReportService $service = null;

    public function updatedGsId(mixed $value): void
    {
        $this->lotId = null;
        $this->submitted = false;
    }

    public function updatedLotId(): void
    {
        $this->submitted = false;
    }

    public function submit(): void
    {
        $this->validate([
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
        ]);

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
            $report = $this->svc()->buildReport((int) $this->gsId, (int) $this->lotId);
        }

        return view('livewire.masterapp.ruhi-gs-wise-casting-report', [
            'gsOptions' => $gsOptions,
            'lotOptions' => $lotOptions,
            'report' => $report,
        ]);
    }
}
