<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RuhiDesignController extends Controller
{
    public function index(): View
    {
        return view('masterapp.ruhi-designs.index');
    }
}

