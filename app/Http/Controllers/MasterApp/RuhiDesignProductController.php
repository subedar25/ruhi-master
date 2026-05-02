<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\RuhiDesignProducts\Services\RuhiDesignProductService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RuhiDesignProductController extends Controller
{
    public function index(int $design, RuhiDesignProductService $service): View
    {
        $designModel = $service->findDesignForProductsPage($design);

        return view('masterapp.ruhi-designs.products', [
            'design' => $designModel,
        ]);
    }

    public function print(int $design, RuhiDesignProductService $service): View
    {
        $designModel = $service->findDesignForProductsPage($design);
        $blocks = $service->listPrintBlocks($design);

        return view('masterapp.ruhi-designs.print', [
            'design' => $designModel,
            'colorBlocks' => $blocks['colorBlocks'],
            'nonColorBlocks' => $blocks['nonColorBlocks'],
        ]);
    }
}
