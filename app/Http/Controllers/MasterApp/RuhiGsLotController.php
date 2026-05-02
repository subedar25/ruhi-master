<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\RuhiGsLots\Services\RuhiGsLotService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RuhiGsLotController extends Controller
{
    public function index(int $gs, RuhiGsLotService $service): View
    {
        $gsModel = $service->findGs($gs);

        return view('masterapp.ruhi-gs.lots', [
            'gs' => $gsModel,
        ]);
    }
}
