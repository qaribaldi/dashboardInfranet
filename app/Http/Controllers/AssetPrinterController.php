<?php

namespace App\Http\Controllers;

use App\Models\AssetPrinter;
use Illuminate\Http\Request;

class AssetPrinterController extends Controller
{
    public function index()
    {
        $columns = [
            'id_printer' => 'ID Printer',
            'unit_kerja' => 'Unit Kerja',
            'user' => 'User',
            'ruang' => 'Ruang',
            'jenis_printer' => 'Jenis',
            'merk' => 'Merk',
            'tipe' => 'Tipe',
            'scanner' => 'Scanner',
            'status_warna' => 'Warna',
            'kondisi' => 'Kondisi',
            'tahun_pembelian' => 'Tahun',
        ];
        $items = AssetPrinter::orderBy('id_printer')->paginate(12);
        return view('inventory.printer.index', compact('items','columns'));
    }

    public function create()
    {
        $fields = [
            'id_printer' => 'ID Printer',
            'unit_kerja' => 'Unit Kerja',
            'user' => 'User',
            'jabatan' => 'Jabatan',
            'ruang' => 'Ruang',
            'jenis_printer' => 'Jenis Printer',
            'merk' => 'Merk',
            'tipe' => 'Tipe',
            'scanner' => 'Scanner (Ya/Tidak)',
            'tinta' => 'Tinta',
            'status_warna' => 'Status Warna (Color/BW)',
            'kondisi' => 'Kondisi',
            'tahun_pembelian' => 'Tahun Pembelian',
            'keterangan_tambahan' => 'Keterangan Tambahan',
        ];
        return view('inventory.printer.form', ['mode'=>'create','fields'=>$fields,'data'=>new AssetPrinter()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_printer' => 'required|string|unique:asset_printer,id_printer',
            'tahun_pembelian' => 'nullable|integer',
        ]);

        AssetPrinter::create($request->only((new AssetPrinter)->getFillable()));
        return redirect()->route('printer.index')->with('success','Printer berhasil ditambahkan.');
    }

    public function edit(AssetPrinter $printer)
    {
        $fields = (new self)->create()->getData()['fields'];
        return view('inventory.printer.form', ['mode'=>'edit','fields'=>$fields,'data'=>$printer]);
    }

    public function update(Request $request, AssetPrinter $printer)
    {
        $request->validate([
            'id_printer' => 'required|string|unique:asset_printer,id_printer,'.$printer->id_printer.',id_printer',
            'tahun_pembelian' => 'nullable|integer',
        ]);

        $printer->update($request->only($printer->getFillable()));
        return redirect()->route('printer.index')->with('success','Printer berhasil diperbarui.');
    }

    public function destroy(AssetPrinter $printer)
    {
        $printer->delete();
        return redirect()->route('printer.index')->with('success','Printer berhasil dihapus.');
    }

    public function show(AssetPrinter $printer)
{
    return view('inventory.printer._detail', ['data' => $printer]);
}

}
