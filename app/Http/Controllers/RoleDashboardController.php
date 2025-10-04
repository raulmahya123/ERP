<?php

namespace App\Http\Controllers;

class RoleDashboardController extends Controller
{
    public function gm()       { return view('roles.gm'); }
    public function manager()  { return view('roles.manager'); }
    public function foreman()  { return view('roles.foreman'); }
    public function operator() { return view('roles.operator'); }
    public function hse()      { return view('roles.hse'); }
    public function hr()       { return view('roles.hr'); }
    public function finance()  { return view('roles.finance'); }
}
