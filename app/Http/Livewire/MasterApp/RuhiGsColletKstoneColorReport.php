<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsColletKstoneColorReportService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RuhiGsColletKstoneColorReport extends Component
{
    public ?int $gsId = null;

    public bool $submitted = false;

    private ?GsColletKstoneColorReportService $service = null;

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

    private function svc(): GsColletKstoneColorReportService
    {
        return $this->service ??= app(GsColletKstoneColorReportService::class);
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

        return view('livewire.masterapp.ruhi-gs-collet-kstone-color-report', [
            'gsOptions' => $gsOptions,
            'report' => $report,
            'selectedGsName' => $selectedGsName,
        ]);
    }
}
