<?php

namespace App\Http\Controllers;

class StaticPageController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
    }
}
