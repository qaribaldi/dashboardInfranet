<?php

namespace App\Http\Controllers;

use App\Models\AssetPc;
use App\Models\AssetHistory; 
use Illuminate\Http\Request;

class AssetPcController extends Controller
{
    public function index(Request $request)
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

    // query params
    $q    = trim((string) $request->query('q', ''));
    $proc = trim((string) $request->query('proc', ''));
    $ram  = trim((string) $request->query('ram', ''));
    $sto  = trim((string) $request->query('sto', ''));

    // base query
    $base = AssetPc::query();

    if ($q !== '') {
        $base->where(function($w) use ($q) {
            $like = "%{$q}%";
            $w->where('id_pc','like',$like)
              ->orWhere('unit_kerja','like',$like)
              ->orWhere('user','like',$like)
              ->orWhere('ruang','like',$like)
              ->orWhere('merk','like',$like)
              ->orWhere('processor','like',$like)
              ->orWhere('total_kapasitas_ram','like',$like)
              ->orWhere('storage_1','like',$like)
              ->orWhere('storage_2','like',$like)
              ->orWhere('storage_3','like',$like)
              ->orWhere('operating_sistem','like',$like)
              ->orWhere('tahun_pembelian','like',$like);
        });
    }

    // filters
    if ($proc !== '') {
        $base->where('processor', $proc);
    }
    if ($ram !== '') {
        $base->where('total_kapasitas_ram', $ram);
    }
    if ($sto !== '') {
        // storage cocok di salah satu slot
        $base->where(function($w) use ($sto) {
            $w->where('storage_1', $sto)
              ->orWhere('storage_2', $sto)
              ->orWhere('storage_3', $sto);
        });
    }

    $items = $base->orderBy('id_pc')->paginate(12)->appends($request->query());

    // options untuk dropdown filter
    $processors = AssetPc::whereNotNull('processor')
        ->where('processor','<>','')
        ->distinct()->orderBy('processor')->pluck('processor');

    $rams = AssetPc::whereNotNull('total_kapasitas_ram')
        ->where('total_kapasitas_ram','<>','')
        ->distinct()->orderBy('total_kapasitas_ram')->pluck('total_kapasitas_ram');

    // gabungkan storage_1/2/3 sebagai opsi
    $storages = collect()
        ->merge(AssetPc::whereNotNull('storage_1')->where('storage_1','<>','')->pluck('storage_1'))
        ->merge(AssetPc::whereNotNull('storage_2')->where('storage_2','<>','')->pluck('storage_2'))
        ->merge(AssetPc::whereNotNull('storage_3')->where('storage_3','<>','')->pluck('storage_3'))
        ->filter()->unique()->sort()->values();

    return view('inventory.pc.index', compact(
        'items','columns','q','proc','ram','sto','processors','rams','storages'
    ));
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
        return redirect()->route('inventory.pc.index')->with('success','Aset PC berhasil ditambahkan.');
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
            'catatan_histori' => 'nullable|string', // optional note
        ]);

        // Ambil input yang diizinkan & simpan nilai lama untuk pembanding
        $input = $request->only($pc->getFillable());
        $before = $pc->only(array_keys($input)); // nilai lama (sebelum di-fill)

        // Fill ke model TANPA save dulu → cek field yang berubah
        $pc->fill($input);
        $dirty = $pc->getDirty(); // array [field => newValue] yang berubah

        // Tentukan jenis aksi (upgrade/repair/update)
        // Catatan: tabel PC default tidak ada kolom 'kondisi', jadi repairFields kosong.
        $upgradeFields = ['processor','total_kapasitas_ram','storage_1','storage_2','storage_3','operating_sistem','merk'];
        $repairFields  = []; // tambahkan kalau nanti ada kolom perbaikan seperti 'kondisi'

        $action = 'update';
        $dirtyKeys = array_keys($dirty);
        if (count(array_intersect($dirtyKeys, $upgradeFields)) > 0) {
            $action = 'upgrade';
        } elseif (count(array_intersect($dirtyKeys, $repairFields)) > 0) {
            $action = 'repair';
        }

        // Siapkan ringkasan perubahan old -> new untuk disimpan ke history
        $changes = [];
        foreach ($dirty as $k => $newVal) {
            $changes[$k] = ['from' => $before[$k] ?? null, 'to' => $newVal];
        }

        // Simpan perubahan ke DB
        $pc->save();

        // Jika ada perubahan, catat ke tabel history
        if (!empty($dirty)) {
            AssetHistory::create([
                'asset_type'   => 'pc',
                'asset_id'     => $pc->id_pc,
                'action'       => $action,
                'changes_json' => $changes,
                'note'         => $request->input('catatan_histori'),
                'created_at'   => now('Asia/Jakarta'),
            ]);
        }

        return redirect()->route('inventory.pc.index')->with('success','Aset PC berhasil diperbarui.');
    }

    public function destroy(AssetPc $pc)
    {
        $pc->delete();
        return redirect()->route('inventory.pc.index')->with('success','Aset PC berhasil dihapus.');
    }

    // show untuk modal detail (partial)
    public function show(AssetPc $pc)
    {
        return view('inventory.pc._detail', ['data' => $pc]);
    }
}
