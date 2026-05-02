<?php

namespace App\Http\Livewire\MasterApp;

use App\Core\RuhiReports\Services\GsWiseCastingReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Livewire\Component;

class RuhiGsWiseCastingDetailReport extends Component
{
    public ?int $gsId = null;

    public ?int $lotId = null;

    public bool $submitted = false;

    private ?GsWiseCastingReportService $service = null;

    public function mount(Request $request): void
    {
        $g = (int) $request->query('gs', 0);
        $l = (int) $request->query('lot', 0);

        if ($g > 0) {
            $this->gsId = $g;
        }
        if ($l > 0) {
            $this->lotId = $l;
        }

        if ($this->gsId && $this->lotId) {
            $validator = Validator::make(
                [
                    'gsId' => $this->gsId,
                    'lotId' => $this->lotId,
                ],
                [
                    'gsId' => [
                        'required',
                        'integer',
                        Rule::exists('r_gs', 'id')->whereNull('deleted_at'),
                    ],
                    'lotId' => [
                        'required',
                        'integer',
                        Rule::exists('r_slot', 'id')->where(fn ($q) => $q->where('gs_id', $this->gsId)),
                    ],
                ]
            );

            if (! $validator->fails()) {
                $this->submitted = true;
            }
        }
    }

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
                Rule::exists('r_gs', 'id')->whereNull('deleted_at'),
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
            $report = $this->svc()->buildDetailReport((int) $this->gsId, (int) $this->lotId);
        }

        return view('livewire.masterapp.ruhi-gs-wise-casting-detail-report', [
            'gsOptions' => $gsOptions,
            'lotOptions' => $lotOptions,
            'report' => $report,
        ]);
    }
}
