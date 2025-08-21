<?php

namespace App\Http\Controllers;

use App\Models\AssetProyektor;
use Illuminate\Http\Request;

class AssetProyektorController extends Controller
{
    public function index()
    {
        $columns = [
            'id_proyektor' => 'ID Proyektor',
            'ruang' => 'No. Ruang',
            'nama_ruang' => 'Nama Ruang',
            'merk' => 'Merk',
            'tipe_proyektor' => 'Tipe',
            'resolusi_max' => 'Resolusi',
            'vga_support' => 'VGA',
            'hdmi_support' => 'HDMI',
            'remote' => 'Remote',
            'tahun_pembelian' => 'Tahun',
        ];
        $items = AssetProyektor::orderBy('id_proyektor')->paginate(12);
        return view('inventory.proyektor.index', compact('items','columns'));
    }

    public function create()
    {
        $fields = [
            'id_proyektor' => 'ID Proyektor',
            'ruang' => 'No. Ruang',
            'nama_ruang' => 'Nama Ruang',
            'merk' => 'Merk',
            'tipe_proyektor' => 'Tipe Proyektor',
            'resolusi_max' => 'Resolusi Maks',
            'vga_support' => 'VGA Support (Ya/Tidak)',
            'hdmi_support' => 'HDMI Support (Ya/Tidak)',
            'kabel_hdmi' => 'Kabel HDMI (Ya/Tidak)',
            'remote' => 'Remote (Ya/Tidak)',
            'tahun_pembelian' => 'Tahun Pembelian',
            'keterangan_tambahan' => 'Keterangan Tambahan',
        ];
        return view('inventory.proyektor.form', ['mode'=>'create','fields'=>$fields,'data'=>new AssetProyektor()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_proyektor' => 'required|string|unique:asset_proyektor,id_proyektor',
            'tahun_pembelian' => 'nullable|integer',
        ]);

        AssetProyektor::create($request->only((new AssetProyektor)->getFillable()));
        return redirect()->route('proyektor.index')->with('success','Proyektor berhasil ditambahkan.');
    }

    public function edit(AssetProyektor $proyektor)
    {
        $fields = (new self)->create()->getData()['fields'];
        return view('inventory.proyektor.form', ['mode'=>'edit','fields'=>$fields,'data'=>$proyektor]);
    }

    public function update(Request $request, AssetProyektor $proyektor)
    {
        $request->validate([
            'id_proyektor' => 'required|string|unique:asset_proyektor,id_proyektor,'.$proyektor->id_proyektor.',id_proyektor',
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $proyektor->update($request->only($proyektor->getFillable()));
        return redirect()->route('proyektor.index')->with('success','Proyektor berhasil diperbarui.');
    }

    public function destroy(AssetProyektor $proyektor)
    {
        $proyektor->delete();
        return redirect()->route('proyektor.index')->with('success','Proyektor berhasil dihapus.');
    }

    public function show(AssetProyektor $proyektor)
{
    return view('inventory.proyektor._detail', ['data' => $proyektor]);
}

}
