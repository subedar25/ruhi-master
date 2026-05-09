<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsFullReportService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RuhiGsFullReport extends Component
{
    public ?int $gsId = null;

    public bool $submitted = false;

    private ?GsFullReportService $service = null;

    public function updatedGsId(): void
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
        ]);

        $this->submitted = true;
    }

    private function svc(): GsFullReportService
    {
        return $this->service ??= app(GsFullReportService::class);
    }

    public function render()
    {
        $gsOptions = $this->svc()->listGsForDropdown();
        $report = null;
        $selectedGsName = '';

        if ($this->submitted && $this->gsId) {
            $report = $this->svc()->buildReport((int) $this->gsId);
            $selectedGsName = (string) ($gsOptions->firstWhere('id', (int) $this->gsId)?->name ?? '');
        }

        return view('livewire.masterapp.ruhi-gs-full-report', [
            'gsOptions' => $gsOptions,
            'report' => $report,
            'selectedGsName' => $selectedGsName,
        ]);
    }
}
