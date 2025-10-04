<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteContextController extends Controller
{
    // POST /admin/site/switch  (middleware: auth + hasrole:gm)
    public function switch(Request $request)
    {
        $request->validate([
            'site' => ['required','uuid','exists:sites,id'],
        ]);

        $site = Site::findOrFail($request->string('site')->toString());

        // ganti konteks
        session(['site_id' => $site->id]);

        return back()->with('status', "Site switched to {$site->code}");
    }
}
