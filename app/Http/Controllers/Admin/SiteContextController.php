<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteContextController extends Controller
{
 public function switch(Request $request)
    {
        $data = $request->validate([
            'site_id' => ['required','uuid','exists:sites,id'], 
        ]);

        $site = Site::findOrFail($data['site_id']);

        // ganti konteks
        session(['site_id' => $site->id]);

        return back()->with('status', "Site switched to {$site->code}");
    }
}
