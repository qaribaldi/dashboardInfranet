<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteInfo;

class PublicController extends Controller
{
    // tampilkan landing (publik)
    public function landing()
    {
        $info = SiteInfo::first(); 
        return view('landing', compact('info'));
    }

    // update info (khusus admin)
    public function updateInfo(Request $request)
    {
        $data = $request->validate([
            'content' => ['nullable','string'],
        ]);

        $info = SiteInfo::first();
        if (!$info) $info = new SiteInfo();
        $info->content    = $data['content'] ?? '';
        $info->updated_by = auth()->id();
        $info->save();

        return back()->with('status','Informasi berhasil diperbarui.');
    }
}
