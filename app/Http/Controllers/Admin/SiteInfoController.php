<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SiteInfo;

class SiteInfoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('can:siteinfo.manage')->only(['edit','update','updateSection']);
    }

    // Tampilkan form edit
    public function edit()
    {
        $info = SiteInfo::first();
        return view('admin.siteinfo.edit', compact('info'));
    }

    // Simpan semua sekaligus
    public function update(Request $request)
    {
        $data = $request->validate([
            'about_content'          => ['nullable', 'string'],
            'info_content'           => ['nullable', 'string'],
            'contact_content'        => ['nullable', 'string'],
            'service_hours_content'  => ['nullable', 'string'],
        ]);

        $info = SiteInfo::first() ?? new SiteInfo();
        foreach ($data as $k => $v) {
            $info->{$k} = $v ?? '';
        }
        $info->updated_by = auth()->id();
        $info->save();

        return back()->with('success','Landing page berhasil diperbarui.');
    }

    // Simpan per-card (section)
    public function updateSection(Request $request, string $section)
    {
        $map = [
            'about'   => 'about_content',
            'info'    => 'info_content',
            'contact' => 'contact_content',
            'hours'   => 'service_hours_content',
        ];

        if (!isset($map[$section])) {
            abort(404);
        }

        $field = $map[$section];

        $validated = $request->validate([
            $field => ['nullable','string'],
        ]);

        $info = SiteInfo::first() ?? new SiteInfo();
        $info->{$field} = $validated[$field] ?? '';
        $info->updated_by = auth()->id();
        $info->save();

        $labels = [
            'about'   => 'About Us',
            'info'    => 'Informasi',
            'contact' => 'Hubungi Kami',
            'hours'   => 'Jam Layanan',
        ];

        return back()->with('success', "{$labels[$section]} berhasil disimpan.");
    }
}
