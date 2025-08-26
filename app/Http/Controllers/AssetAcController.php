<?php

namespace App\Http\Controllers;

use App\Models\AssetAc;
use App\Models\AssetHistory; 
use Illuminate\Http\Request;

class AssetAcController extends Controller
{
    // LIST
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));

        $items = AssetAc::query()
            ->when($q !== '', function ($qb) use ($q) {
                $like = '%'.$q.'%';
                $qb->where(function ($w) use ($like) {
                    $w->where('id_ac', 'like', $like)
                        ->orWhere('unit_kerja', 'like', $like)
                        ->orWhere('ruang', 'like', $like)
                        ->orWhere('merk', 'like', $like)
                        ->orWhere('tipe_asset', 'like', $like)
                        ->orWhere('ukuran_pk', 'like', $like)
                        ->orWhere('kondisi', 'like', $like)
                        ->orWhere('remote', 'like', $like)
                        ->orWhere('tahun_pembelian', 'like', $like);
                });
            })
            ->orderBy('id_ac')
            ->paginate(20)
            ->withQueryString();

        return view('inventory.ac.index', compact('items','q'));
    }

    // DETAIL
    public function show(Request $request, string $id)
    {
        $data = AssetAc::where('id_ac', $id)->firstOrFail();

        // kalau dipanggil dari modal (?readonly=1) / AJAX → kirim partial saja (tanpa layout/sidebar)
        if ($request->boolean('readonly') || $request->ajax()) {
            return view('inventory.ac._detail', compact('data'));
        }

        // kalau buka langsung URL → tampilkan layout penuh
        return view('inventory.ac.show', compact('data'));
    }

    // --------- opsional (biar tombol Tambah/Edit tidak error) ---------
    public function create()
    {
        $data = new AssetAc();
        // label field sederhana: pakai fillable sebagai label
        $fields = collect((new AssetAc)->getFillable())->mapWithKeys(
            fn($f) => [$f => str_replace('_',' ', $f)]
        )->all();

        return view('inventory.ac.form', ['mode' => 'create', 'data' => $data, 'fields' => $fields]);
    }

    public function store(Request $request)
    {
        $data = new AssetAc();
        $data->fill($request->only((new AssetAc)->getFillable()));
        $data->save();

        return redirect()->route('inventory.ac.index')->with('success','Data AC ditambahkan.');
    }

    public function edit(string $id)
    {
        $data = AssetAc::where('id_ac', $id)->firstOrFail();
        $fields = collect((new AssetAc)->getFillable())->mapWithKeys(
            fn($f) => [$f => str_replace('_',' ', $f)]
        )->all();

        return view('inventory.ac.form', ['mode' => 'edit', 'data' => $data, 'fields' => $fields]);
    }

    public function update(Request $request, string $id)
    {
        $data = AssetAc::where('id_ac', $id)->firstOrFail();
        $data->fill($request->only((new AssetAc)->getFillable()));
        $data->save();

        return redirect()->route('inventory.ac.index')->with('success','Data AC diperbarui.');
    }

    public function destroy(string $id)
    {
        $data = AssetAc::where('id_ac', $id)->firstOrFail();
        $data->delete();

        return back()->with('success','Data AC dihapus.');
    }
}
