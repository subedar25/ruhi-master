<?php

namespace App\Http\Controllers\MasterApp;

use App\Core\RuhiDesignProductItemKstones\Services\RuhiDesignProductItemKstoneService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RuhiDesignProductItemKstoneController extends Controller
{
    public function index(int $design, int $product, RuhiDesignProductItemKstoneService $service): View
    {
        $designModel = $service->findDesign($design);
        $productModel = $service->findProduct($product);

        return view('masterapp.ruhi-designs.product-kstones', [
            'design' => $designModel,
            'product' => $productModel,
        ]);
    }

    public function print(int $design, int $product, RuhiDesignProductItemKstoneService $service): View
    {
        $designModel = $service->findDesign($design);
        $productModel = $service->findProduct($product);
        $rows = $service->listAllForDesignAndProduct($design, $product);

        return view('masterapp.ruhi-designs.product-kstones-print', [
            'design' => $designModel,
            'product' => $productModel,
            'rows' => $rows,
        ]);
    }
}
