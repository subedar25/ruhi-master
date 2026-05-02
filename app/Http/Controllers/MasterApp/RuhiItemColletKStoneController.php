<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\RuhiItemKstones\Services\RuhiItemKstoneService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RuhiItemColletKStoneController extends Controller
{
    public function index(int $product, RuhiItemKstoneService $service): View
    {
        $productModel = $service->findProductForColletPage($product);

        return view('masterapp.ruhi-items.collet-k-stones', [
            'product' => $productModel,
        ]);
    }
}
