<?php

namespace App\Http\Controllers;

use App\Models\AssetProyektor;
use App\Models\AssetHistory; // <— tambahkan
use Illuminate\Http\Request;

class AssetProyektorController extends Controller
{
    public function index(Request $request)
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

        $q = trim($request->query('q', ''));

        $items = AssetProyektor::query()
            ->when($q !== '', function ($qb) use ($q) {
                $like = '%'.$q.'%';
                $qb->where(function ($w) use ($like) {
                    $w->where('id_proyektor', 'like', $like)
                        ->orWhere('nama_ruang', 'like', $like)
                        ->orWhere('ruang', 'like', $like)
                        ->orWhere('merk', 'like', $like)
                        ->orWhere('tipe_proyektor', 'like', $like)
                        ->orWhere('resolusi_max', 'like', $like)
                        ->orWhere('remote', 'like', $like)
                        ->orWhere('tahun_pembelian', 'like', $like);
                });
            })
            ->orderBy('id_proyektor')
            ->paginate(12)
            ->withQueryString();

        return view('inventory.proyektor.index', compact('items','columns','q'));
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
            'catatan_histori' => 'nullable|string', // opsional
        ]);

        // Ambil input yang diizinkan & nilai lama (before)
        $input  = $request->only($proyektor->getFillable());
        $before = $proyektor->only(array_keys($input));

        // Fill tanpa save → deteksi perbedaan
        $proyektor->fill($input);
        $dirty = $proyektor->getDirty(); // [field => newVal]

        // Tentukan jenis aksi
        $upgradeFields = ['merk','tipe_proyektor','resolusi_max','vga_support','hdmi_support','kabel_hdmi','remote'];
        $repairFields  = []; // tambahkan kalau ada kolom 'kondisi' di masa depan

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
        $proyektor->save();

        // Catat histori jika ada perubahan
        if (!empty($dirty)) {
            AssetHistory::create([
                'asset_type'   => 'proyektor',
                'asset_id'     => $proyektor->id_proyektor,
                'action'       => $action,
                'changes_json' => $changes,
                'note'         => $request->input('catatan_histori'),
                'created_at'   => now('Asia/Jakarta'),
            ]);
        }

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
