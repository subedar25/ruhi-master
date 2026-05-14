<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsDieReportService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class RuhiGsDieReport extends Component
{
    #[Url(as: 'gs')]
    public ?int $gsId = null;

    public bool $submitted = false;

    private ?GsDieReportService $service = null;

    /**
     * @return array<string, array<int, mixed>>
     */
    private function gsIdValidationRules(): array
    {
        return [
            'gsId' => [
                'required',
                'integer',
                Rule::exists('r_gs', 'id'),
            ],
        ];
    }

    public function mount(): void
    {
        $this->syncSubmittedFromGsId();
    }

    private function syncSubmittedFromGsId(): void
    {
        if ($this->gsId === null) {
            $this->submitted = false;

            return;
        }

        $validator = Validator::make(
            ['gsId' => $this->gsId],
            $this->gsIdValidationRules(),
        );

        if ($validator->fails()) {
            $this->gsId = null;
            $this->submitted = false;

            return;
        }

        $this->resetValidation();
        $this->submitted = true;
    }

    public function updatedGsId(): void
    {
        $this->syncSubmittedFromGsId();
    }

    public function submit(): void
    {
        $this->validate($this->gsIdValidationRules());
        $this->submitted = true;
    }

    private function svc(): GsDieReportService
    {
        return $this->service ??= app(GsDieReportService::class);
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

        return view('livewire.masterapp.ruhi-gs-die-report', [
            'gsOptions' => $gsOptions,
            'report' => $report,
            'selectedGsName' => $selectedGsName,
        ]);
    }
}
