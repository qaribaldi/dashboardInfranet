<?php

namespace App\Http\Controllers;

use App\Models\AssetPc;
use Illuminate\Http\Request;

class AssetPcController extends Controller
{
    public function index()
    {
        $columns = [
            'id_pc' => 'ID PC',
            'unit_kerja' => 'Unit Kerja',
            'user' => 'User',
            'ruang' => 'Ruang',
            'merk' => 'Merk',
            'processor' => 'Processor',
            'total_kapasitas_ram' => 'Total RAM',
            'storage_1' => 'Storage 1',
            'operating_sistem' => 'OS',
            'tahun_pembelian' => 'Tahun',
        ];
        $items = AssetPc::orderBy('id_pc')->paginate(12);
        return view('inventory.pc.index', compact('items','columns'));
    }

    public function create()
    {
        $fields = [
            'id_pc' => 'ID PC',
            'unit_kerja' => 'Unit Kerja',
            'user' => 'User',
            'jabatan' => 'Jabatan',
            'ruang' => 'Ruang',
            'tipe_asset' => 'Tipe Asset',
            'merk' => 'Merk',
            'processor' => 'Processor',
            'socket_processor' => 'Socket Processor',
            'motherboard' => 'Motherboard',
            'jumlah_slot_ram' => 'Jumlah Slot RAM',
            'total_kapasitas_ram' => 'Total RAM',
            'tipe_ram' => 'Tipe RAM',
            'ram_1' => 'RAM 1',
            'ram_2' => 'RAM 2',
            'tipe_storage_1' => 'Tipe Storage 1',
            'storage_1' => 'Storage 1',
            'tipe_storage_2' => 'Tipe Storage 2',
            'storage_2' => 'Storage 2',
            'storage_3' => 'Storage 3',
            'vga' => 'VGA',
            'optical_drive' => 'Optical Drive',
            'network_adapter' => 'Network Adapter',
            'power_suply' => 'Power Supply',
            'operating_sistem' => 'Operating System',
            'monitor' => 'Monitor',
            'keyboard' => 'Keyboard',
            'mouse' => 'Mouse',
            'tahun_pembelian' => 'Tahun Pembelian',
        ];
        return view('inventory.pc.form', ['mode'=>'create','fields'=>$fields,'data'=>new AssetPc()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pc' => 'required|string|unique:asset_pc,id_pc',
            'unit_kerja' => 'nullable|string',
            'user' => 'nullable|string',
            'ruang' => 'nullable|string',
            'merk' => 'nullable|string',
            'processor' => 'nullable|string',
            'total_kapasitas_ram' => 'nullable|string',
            'operating_sistem' => 'nullable|string',
            'tahun_pembelian' => 'nullable|integer',
        ]);

        AssetPc::create($request->only((new AssetPc)->getFillable()));
        return redirect()->route('pc.index')->with('success','Aset PC berhasil ditambahkan.');
    }

    public function edit(AssetPc $pc)
    {
        $fields = (new self)->create()->getData()['fields']; // pakai field yang sama
        return view('inventory.pc.form', ['mode'=>'edit','fields'=>$fields,'data'=>$pc]);
    }

    public function update(Request $request, AssetPc $pc)
    {
        $request->validate([
            'id_pc' => 'required|string|unique:asset_pc,id_pc,'.$pc->id_pc.',id_pc',
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $pc->update($request->only($pc->getFillable()));
        return redirect()->route('pc.index')->with('success','Aset PC berhasil diperbarui.');
    }

    public function destroy(AssetPc $pc)
    {
        $pc->delete();
        return redirect()->route('pc.index')->with('success','Aset PC berhasil dihapus.');
    }

    // optional: show
    public function show(AssetPc $pc)
{
    // Kirim semua kolom ke view detail
    return view('inventory.pc._detail', ['data' => $pc]);
}

}
