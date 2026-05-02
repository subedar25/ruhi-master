<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;

class RuhiDesignCategoryController extends Controller
{
    public function index()
    {
        return view('masterapp.ruhi-design-categories.index');
    }
}
