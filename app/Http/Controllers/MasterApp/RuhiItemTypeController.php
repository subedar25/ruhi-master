<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;

class RuhiItemTypeController extends Controller
{
    public function index()
    {
        return view('masterapp.ruhi-item-types.index');
    }
}
