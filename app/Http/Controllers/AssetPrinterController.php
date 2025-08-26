<?php

namespace App\Http\Controllers;

use App\Models\AssetPrinter;
use App\Models\AssetHistory;
use Illuminate\Http\Request;

class AssetPrinterController extends Controller
{
    public function index(Request $request)
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

        $q = trim($request->query('q', ''));

        $items = AssetPrinter::query()
            ->when($q !== '', function ($qb) use ($q) {
                $like = '%'.$q.'%';
                $qb->where(function ($w) use ($like) {
                    $w->where('id_printer', 'like', $like)
                        ->orWhere('unit_kerja', 'like', $like)
                        ->orWhere('user', 'like', $like)
                        ->orWhere('ruang', 'like', $like)
                        ->orWhere('jenis_printer', 'like', $like)
                        ->orWhere('merk', 'like', $like)
                        ->orWhere('tipe', 'like', $like)
                        ->orWhere('status_warna', 'like', $like)
                        ->orWhere('kondisi', 'like', $like)
                        ->orWhere('tahun_pembelian', 'like', $like);
                });
            })
            ->orderBy('id_printer')
            ->paginate(12)
            ->withQueryString();

        return view('inventory.printer.index', compact('items','columns','q'));
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
        return redirect()->route('inventory.printer.index')->with('success','Printer berhasil ditambahkan.');
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
            'catatan_histori' => 'nullable|string', // opsional
        ]);

        // Ambil input yang diizinkan & simpan nilai lama
        $input  = $request->only($printer->getFillable());
        $before = $printer->only(array_keys($input));

        // Fill tanpa save → deteksi perbedaan
        $printer->fill($input);
        $dirty = $printer->getDirty(); // [field => newVal]

        // Tentukan jenis aksi
        $upgradeFields = ['merk','tipe','status_warna','scanner','tinta'];
        $repairFields  = ['kondisi'];

        $action = 'update';
        $dirtyKeys = array_keys($dirty);
        if (count(array_intersect($dirtyKeys, $upgradeFields)) > 0) {
            $action = 'upgrade';
        } elseif (count(array_intersect($dirtyKeys, $repairFields)) > 0) {
            $action = 'repair';
        }

        // Ringkasan perubahan old -> new
        $changes = [];
        foreach ($dirty as $k => $newVal) {
            $changes[$k] = ['from' => $before[$k] ?? null, 'to' => $newVal];
        }

        // Simpan perubahan
        $printer->save();

        // Catat histori jika ada perubahan
        if (!empty($dirty)) {
            AssetHistory::create([
                'asset_type'   => 'printer',
                'asset_id'     => $printer->id_printer,
                'action'       => $action,
                'changes_json' => $changes,
                'note'         => $request->input('catatan_histori'),
                'created_at'   => now('Asia/Jakarta'),
            ]);
        }

        return redirect()->route('inventory.printer.index')->with('success','Printer berhasil diperbarui.');
    }

    public function destroy(AssetPrinter $printer)
    {
        $printer->delete();
        return redirect()->route('inventory.printer.index')->with('success','Printer berhasil dihapus.');
    }

    public function show(AssetPrinter $printer)
    {
        return view('inventory.printer._detail', ['data' => $printer]);
    }
}
