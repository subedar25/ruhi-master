<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsLotWiseItemsReportService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RuhiGsLotWiseItemsReport extends Component
{
    public ?int $gsId = null;

    public bool $submitted = false;

    private ?GsLotWiseItemsReportService $service = null;

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

    private function svc(): GsLotWiseItemsReportService
    {
        return $this->service ??= app(GsLotWiseItemsReportService::class);
    }

    public function render()
    {
        $gsOptions = $this->svc()->listGsForDropdown();
        $blocks = collect();

        $selectedGsName = '';

        if ($this->submitted && $this->gsId) {
            $blocks = $this->svc()->lotBlocksForGs((int) $this->gsId);
            $selectedGsName = (string) ($gsOptions->firstWhere('id', (int) $this->gsId)?->name ?? '');
        }

        return view('livewire.masterapp.ruhi-gs-lot-wise-items-report', [
            'gsOptions' => $gsOptions,
            'blocks' => $blocks,
            'selectedGsName' => $selectedGsName,
        ]);
    }
}
