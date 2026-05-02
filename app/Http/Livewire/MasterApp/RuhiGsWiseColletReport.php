<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsWiseColletReportService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RuhiGsWiseColletReport extends Component
{
    public ?int $gsId = null;

    public bool $submitted = false;

    private ?GsWiseColletReportService $service = null;

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
                Rule::exists('r_gs', 'id')->whereNull('deleted_at'),
            ],
        ]);

        $this->submitted = true;
    }

    private function svc(): GsWiseColletReportService
    {
        return $this->service ??= app(GsWiseColletReportService::class);
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

        return view('livewire.masterapp.ruhi-gs-wise-collet-report', [
            'gsOptions' => $gsOptions,
            'report' => $report,
            'selectedGsName' => $selectedGsName,
        ]);
    }
}
