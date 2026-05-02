<?php

namespace App\Http\Controllers\MasterApp;

use App\Http\Controllers\Controller;

class RuhiKstoneController extends Controller
{
    public function index()
    {
        return view('masterapp.ruhi-kstones.index');
    }
}
